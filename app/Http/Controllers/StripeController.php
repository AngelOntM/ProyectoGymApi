<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Order;

class StripeController extends Controller
{
    public function createPaymentIntent(Request $request, $orderId)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Obtener el total de la orden basado en $orderId
        $order = Order::findOrFail($orderId);
        $totalAmount = $order->total_amount; // Asume que tienes un campo total_amount en tu modelo de Orden

        // Crear el PaymentIntent en Stripe
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $totalAmount * 100, // Convertir a centavos
                'currency' => 'mxn',
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            // Manejar errores si ocurren al crear el PaymentIntent
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function confirmStripePayment(Request $request)
    {
        $request->validate([
            'stripe_payment_intent_id' => 'required|string',
        ]);

        $paymentIntentId = $request->input('stripe_payment_intent_id');

        try {
            $stripe = new \Stripe\StripeClient('sk_test_51Pc08PLTqA9tmmllMRosVpyrwO8Wb0NDQGZva01FcLTEywOxggsIrbieFRCdK3yE68ESlY5XQ0uuMsrf2tOLPuwB00ZLrf9CJe');
            $stripe->paymentIntents->confirm(
            $paymentIntentId,
            [
                'payment_method' => 'pm_card_visa',
                'return_url' => 'https://www.example.com',
            ]
            );
            return response()->json(['message' => 'Payment confirmed and recorded successfully'], 201);
        } catch (\Exception $e) {
            // Maneja la excepción si falla la confirmación
            return response()->json(['message' => 'Payment confirmation failed', 'error' => $e->getMessage()], 400);
        }
    }
}
