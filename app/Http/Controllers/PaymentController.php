<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
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
        } else {
            $order->estado = 'Proceso';
        }
        $order->save();

        return response()->json(['message' => 'Payment recorded successfully'], 201);
    }
}

