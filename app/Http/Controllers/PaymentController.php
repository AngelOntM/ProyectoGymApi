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
        // Validar que se envíe el stripe_payment_intent_id en la solicitud
        $request->validate([
            'stripe_payment_intent_id' => 'required|string',
        ]);

        try {
            // Buscar la orden por su ID
            $order = Order::findOrFail($orderId);

            // Verificar si la orden ya está marcada como pagada
            if ($order->estado === 'Pagada') {
                return response()->json(['message' => 'La orden ya está pagada'], 400);
            }

            // Establecer la clave de la API de Stripe
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Obtener el ID del Payment Intent desde la solicitud
            $paymentIntentId = $request->input('stripe_payment_intent_id');
            
            // Obtener el Payment Intent desde Stripe
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            // Verificar si el Payment Intent ha sido exitoso
            if ($paymentIntent->status === 'succeeded') {
                // Obtener el monto del Payment Intent (en centavos)
                $amount = $paymentIntent->amount;

                // Crear un registro de pago en la base de datos
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => 4, // ID del método de pago de Stripe
                    'amount' => $amount / 100, // Convertir centavos a dólares
                    'payment_date' => now(),
                    'stripe_payment_intent_id' => $paymentIntent->id,
                ]);

                // Marcar la orden como pagada
                $order->estado = 'Pagada';
                $order->save();

                // Verificar si la orden contiene un producto de membresía en su detalle
                $membershipProduct = $order->orderDetails->first()->product;
                if ($membershipProduct && $membershipProduct->category_id == 2) {
                    $this->generateMembershipCodes($order);
                }

                return response()->json(['message' => 'Payment recorded successfully'], 201);
            } else {
                // Si el Payment Intent no ha sido exitoso, devolver un mensaje de error
                return response()->json(['message' => 'Payment failed or not completed', 'status' => $paymentIntent], 400);
            }
        } catch (\Exception $e) {
            // Capturar cualquier excepción que ocurra durante el proceso
            return response()->json(['message' => 'Error processing payment', 'error' => $e->getMessage()], 500);
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
