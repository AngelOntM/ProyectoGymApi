<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $request->validate([
            'amount' => 'required|integer|min:1'
        ]);

        $amount = $request->input('amount');

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount * 100,
            'currency' => 'mxn',
        ]);

        return response()->json([
            'client_secret' => $paymentIntent->id,
        ]);
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
