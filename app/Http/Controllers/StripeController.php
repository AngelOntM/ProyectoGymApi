<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use App\Models\Order;
use Stripe\Stripe;

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
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function createPaymentIntentMobile(Request $request, $orderId)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Obtener el total de la orden basado en $orderId
        $order = Order::findOrFail($orderId);
        $totalAmount = $order->total_amount; // Asume que tienes un campo total_amount en tu modelo de Orden

        // Opcionalmente obtener la descripción de la solicitud
        $description = $request->input('description', null);

        // Opcionalmente obtener el correo electrónico del recibo
        $receiptEmail = $request->input('receipt_email', null);

        // Crear el PaymentIntent en Stripe
        try {
            $paymentIntentParams = [
                'amount' => $totalAmount * 100, // Convertir a centavos
                'currency' => 'mxn',
                'automatic_payment_methods' => [
                    "allow_redirects" => "never",
                    'enabled' => true
                ]
            ];

            // Añadir la descripción solo si se proporciona
            if ($description) {
                $paymentIntentParams['description'] = $description;
            }

            // Añadir el correo electrónico del recibo solo si se proporciona
            if ($receiptEmail) {
                $paymentIntentParams['receipt_email'] = $receiptEmail;
            }

            $paymentIntent = PaymentIntent::create($paymentIntentParams);

            // Devolver el ID del PaymentIntent y el cliente secreto
            return response()->json([
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            // Manejar errores si ocurren al crear el PaymentIntent
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function cancelPaymentIntentMobile(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntentId = $request->input('payment_intent_id');

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $paymentIntent->cancel();
            return response()->json(['message' => 'Payment intent canceled successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error canceling payment intent', 'error' => $e->getMessage()], 400);
        }

    }

    public function createCheckoutSession(Request $request, $orderId)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $order = Order::findOrFail($orderId);
        $totalAmount = $order->total_amount * 100; // Convertir a centavos

        try {
            $checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'mxn',
                            'product_data' => [
                                'name' => 'Order #' . $orderId, // Puedes ajustar esto
                            ],
                            'unit_amount' => $totalAmount,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => env('APP_URLS') . '/success?order=' . $orderId . '&payment_intent={CHECKOUT_SESSION_ID}', // URL de éxito
                'cancel_url' => env('APP_URLS') . '/cancel',   // URL de cancelación
            ]);

            // Obtener el payment_intent_id
            $paymentIntentId = $checkoutSession->payment_intent;

            // Construir la URL de éxito con el payment_intent_id
            $successUrl = env('APP_URLS') . '/success?payment_intent=' . $paymentIntentId;

            // Actualizar la sesión de pago con la nueva URL de éxito
            // $checkoutSession = \Stripe\Checkout\Session::update($checkoutSession->id, [
            //     'success_url' => $successUrl,
            // ]);

            return response()->json([
                'id' => $checkoutSession->id,
                'payment_intent_id' => $paymentIntentId,
            ]);

            // return response()->json([
            //     'id' => $checkoutSession->id,
            // ]);
        } catch (\Exception $e) {
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

    public function confirmStripePaymentMobile(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntentId = $request->input('payment_intent_id');

        try {
            // Retrieve the PaymentIntent
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            // Check the status
            if ($paymentIntent->status === 'succeeded') {
                return response()->json(['message' => 'Payment intent succeeded'], 200);
            } else {
                return response()->json(['message' => 'Payment intent not succeeded', 'status' => $paymentIntent->status], 200);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['message' => 'Error retrieving payment intent', 'error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving payment intent', 'error' => $e->getMessage()], 400);
        }
    }
}
