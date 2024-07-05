<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\MembershipDetail;
use App\Models\UserMembership;
use App\Models\MembershipCode;
use App\Notifications\MembershipCodesNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // GET - /orders
    public function index()
    {
        try {
            $orders = Order::with('orderDetails')->get();
            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener las órdenes'], 500);
        }
    }

    // GET - /orders/{id}
    public function show($id)
    {
        try {
            $order = Order::with(['orderDetails.product.category'])->findOrFail($id);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener la orden'], 500);
        }
    }

    // POST - /orders/products
    public function storeProductsOrder(Request $request)
    {
        $request->validate([
            'orderDetails' => 'required|array|min:1',
            'orderDetails.*.product_id' => 'required|integer|exists:products,id',
            'orderDetails.*.quantity' => 'required|integer|min:1',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $orderDetailsData = $request->orderDetails;
        $totalAmount = 0;
        $orderDetails = [];

        foreach ($orderDetailsData as $detail) {
            $product = Product::findOrFail($detail['product_id']);
            $totalPrice = $product->price * $detail['quantity'];
            $totalAmount += $totalPrice;

            $orderDetails[] = [
                'product_id' => $product->id,
                'quantity' => $detail['quantity'],
                'unit_price' => $product->price,
                'total_price' => $totalPrice,
            ];

            $product->stock -= $detail['quantity'];
            $product->save();
        }

        $order = Order::create([
            'user_id' => $request->user_id, // ID del cliente que recibe la membresía
            'employee_id' => Auth::id(), // ID del empleado que realiza la orden
            'order_date' => now(),
            'total_amount' => $totalAmount,
            'estado' => 'Proceso',
        ]);

        foreach ($orderDetails as $detail) {
            $detail['order_id'] = $order->id;
            OrderDetail::create($detail);
        }

        return response()->json($order, 201);
    }

    // POST - /orders/memberships
    public function storeMembershipsOrder(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Validar que el usuario sea de tipo cliente (rol_id = 3)
        $user = User::findOrFail($request->user_id);
        if ($user->rol_id != 3) {
            return response()->json(['message' => 'El usuario debe ser de tipo cliente'], 400);
        }

        // Obtener el producto de membresía y su detalle
        $product = Product::findOrFail($request->product_id);
        $membershipDetail = $product->membershipDetails()->firstOrFail();

        // Determinar quién realiza la orden: empleado o cliente
        $orderData = [
            'user_id' => $request->user_id, // ID del cliente que recibe la membresía
            'order_date' => now(),
            'total_amount' => $product->price,
            'estado' => 'Proceso', // Estado de la orden
        ];

        if (auth()->check() && (auth()->user()->rol_id == 1 || auth()->user()->rol_id == 2)) {
            // Si el usuario autenticado tiene rol de administrador (1) o empleado (2)
            $orderData['employee_id'] = auth()->id(); // ID del empleado que realiza la orden
        }

        // Crear la orden
        $order = Order::create($orderData);

        // Crear el detalle de la orden para la membresía
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1, // Solo se compra una membresía por orden
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ]);

        return response()->json($order, 201);
    }

    // DELETE - /orders/{id}
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->estado = 'cancelado';
            $order->save();

            $orderDetails = OrderDetail::where('order_id', $id)->get();
            foreach ($orderDetails as $orderDetail) {
                $product = Product::find($orderDetail->product_id);
                if ($product && $product->category_id == 1) { // assuming category_id 1 is for products
                    $product->stock += $orderDetail->quantity;
                    $product->save();
                }
            }

            return response()->json(['message' => 'Orden cancelada'], 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al cancelar la orden'], 500);
        }
    }
}
