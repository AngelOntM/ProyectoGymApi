<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\UserMembership;
use App\Models\MembershipCode;
use App\Notifications\MembershipCodesNotification;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

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

    // Store payments for an order (Stripe-specific)
    public function storeStripePayment(Request $request, $orderId)
    {
        $request->validate([
            'stripe_payment_intent_id' => 'required|string',
        ]);

        $order = Order::findOrFail($orderId);
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntentId = $request->input('stripe_payment_intent_id');
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

        if ($paymentIntent->status === 'succeeded') {
            $amount = $paymentIntent->amount;

            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => 4, // Stripe payment method id
                'amount' => $amount / 100, // Convert cents to dollars
                'payment_date' => now(),
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);

            $order->estado = 'Pagada';
            $order->save(); // Guardar el estado de la orden como 'Pagada'

            // Verificar si la orden contiene un producto de membresía en su detalle
            $membershipProduct = $order->orderDetails->first()->product;
            if ($membershipProduct && $membershipProduct->category_id == 2) {
                $this->generateMembershipCodes($order);
            }

            return response()->json(['message' => 'Payment recorded successfully'], 201);
        } else {
            return response()->json(['message' => 'Payment failed or not completed', 'status' => $paymentIntent], 400);
        }
    }

    // Method to generate membership codes
    protected function generateMembershipCodes(Order $order)
    {
        $product = $order->orderDetails->first()->product;
        $membershipDetail = $product->membershipDetails()->firstOrFail();

        // Array to store the generated membership codes
        $membershipCodes = [];

        // Create user memberships and generate membership codes
        for ($i = 0; $i < $membershipDetail->size; $i++) {
            // Create UserMembership with null user_id
            $userMembership = UserMembership::create([
                'user_id' => null, // Will be assigned later
                'membership_id' => $membershipDetail->id,
                'start_date' => now(),
                'end_date' => now()->addDays($membershipDetail->duration_days),
            ]);

            // Generate MembershipCode
            $membershipCode = MembershipCode::create([
                'code' => MembershipCode::generateMembershipCode(), // Function to generate unique code
                'user_membership_id' => $userMembership->id,
                'available' => true,
            ]);

            $membershipCodes[] = $membershipCode->code; // Add the code to the array
        }

        // Send email with the generated codes to the user
        $order->user->notify(new MembershipCodesNotification($membershipCodes));
    }
}
