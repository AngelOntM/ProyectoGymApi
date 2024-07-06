<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\UserMembership;
use App\Models\MembershipCode;
use App\Notifications\MembershipCodesNotification;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // Get payments for an order
    public function show($orderId)
    {
        $order = Order::findOrFail($orderId);
        $payments = $order->payments()->with('paymentMethod')->get();

        return response()->json($payments, 200);
    }

    // Store payments for an order
    public function store(Request $request, $orderId)
    {
        $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|integer|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
        ]);

        $order = Order::findOrFail($orderId);
        $totalPaid = 0;

        foreach ($request->payments as $paymentData) {
            $totalPaid += $paymentData['amount'];

            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $paymentData['payment_method_id'],
                'amount' => $paymentData['amount'],
                'payment_date' => now(),
            ]);
        }

        if ($totalPaid >= $order->total_amount) {
            $order->estado = 'Pagada';
            $order->save(); // Guardar el estado de la orden como 'Pagada'

            // Verificar si la orden contiene un producto de membresía en su detalle
            $membershipProduct = $order->orderDetails->first()->product;
            if ($membershipProduct && $membershipProduct->category_id == 2) {
                $this->generateMembershipCodes($order);
            }
        } else {
            $order->estado = 'Proceso';
            $order->save(); // Guardar el estado de la orden como 'Proceso'
        }

        return response()->json(['message' => 'Payment recorded successfully'], 201);
    }

    // Method to generate membership codes
    protected function generateMembershipCodes(Order $order)
    {
        $product = $order->orderDetails->first()->product;
        $membershipDetail = $product->membershipDetails()->firstOrFail();

        // Array para almacenar los códigos de membresía creados
        $membershipCodes = [];

        // Crear los userMemberships y generar los códigos de membresía
        for ($i = 0; $i < $membershipDetail->size; $i++) {
            // Crear UserMembership con user_id nulo
            $userMembership = UserMembership::create([
                'user_id' => null, // Se asignará después
                'membership_id' => $membershipDetail->id,
                'start_date' => now(),
                'end_date' => now()->addDays($membershipDetail->duration_days),
            ]);

            // Generar MembershipCode
            $membershipCode = MembershipCode::create([
                'code' => MembershipCode::generateMembershipCode(), // Función para generar código único
                'user_membership_id' => $userMembership->id,
                'available' => true,
            ]);

            $membershipCodes[] = $membershipCode->code; // Agregar el código al array
        }

        // Enviar correo con los códigos generados al usuario
        $order->user->notify(new MembershipCodesNotification($membershipCodes));
    }
}