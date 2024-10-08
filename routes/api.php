<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\MembershipCodeController;
use App\Http\Controllers\StripeController;

//----------------------------------------------------------------Users
//Usuario autenticado
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/users/image/{userId}', [UserController::class, 'getUserImage']);
});
//Admin
Route::middleware(['auth:sanctum', 'role.admin'])->group(function () {
    Route::get('/users/empleados', [UserController::class, 'getEmpleados']);
    Route::post('/users/admin/{id}', [UserController::class, 'updateEmployee']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
});
//Empleado y admin
Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->group(function () {
    Route::get('/users/clientes', [UserController::class, 'getClientes']);
    Route::post('/users/{id}', [UserController::class, 'updateUser']);
});

//----------------------------------------------------------------Memberships
Route::prefix('membresias')->group(function () {
    Route::get('', [MembershipController::class, 'index']);

    Route::middleware(['auth:sanctum', 'role.admin'])->group(function () {
        Route::get('/all', [MembershipController::class, 'indexAll']);
        Route::post('/', [MembershipController::class, 'store']);
        Route::post('/{id}', [MembershipController::class, 'update']);
        Route::put('/{id}/toggle-active', [MembershipController::class, 'toggleActive']);
        Route::delete('/{id}', [MembershipController::class, 'destroy']);
    });
});

//----------------------------------------------------------------Products
Route::prefix('productos')->group(function () {
    Route::get('', [ProductController::class, 'index']);

    Route::middleware(['auth:sanctum', 'role.admin'])->group(function () {
        Route::get('/all', [ProductController::class, 'indexAll']);
        Route::post('/', [ProductController::class, 'store']);
        Route::post('/{id}', [ProductController::class, 'update']);
        Route::put('/{id}/toggle-active', [ProductController::class, 'toggleActive']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });
});

//----------------------------------------------------------------Orders
Route::prefix('orders')->group(function () {
    Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->group(function () {
        Route::get('', [OrderController::class, 'index']);

        Route::post('/products', [OrderController::class, 'storeProductsOrder']);
        Route::delete('/{id}', [OrderController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/memberships', [OrderController::class, 'storeMembershipsOrder']); //ESTE EN MOVIL 1RO
        // Payment routes
        Route::get('/{orderId}/payments', [PaymentController::class, 'show']);
        Route::post('/{orderId}/payments', [PaymentController::class, 'store']);

        // Stripe payment route - Web / Mobile
        Route::post('/{orderId}/create-checkout', [StripeController::class, 'createCheckoutSession']); // ESTE MOVIL 2DO

        Route::post('/{orderId}/create-payment-intent', [StripeController::class, 'createPaymentIntent']); // Para crear el pago en stripe - ESTE NO SE USA

        Route::post('/confirm-stripe-payment', [StripeController::class, 'confirmStripePayment']); //4TO 

        Route::post('/{orderId}/stripe-payment', [PaymentController::class, 'storeStripePayment']); // Para guardar el pago en la base de datos - 3RO

        //region SOLO PARA MOVIL


        Route::get('/order/user', [OrderController::class, 'showUserOrders']); // Ver todas las ordenes de un usuario
        Route::post('/{orderId}/create-payment-intent-mobile', [StripeController::class, 'createPaymentIntentMobile']); // Crear el PaymentIntent en Stripe para movil

        Route::post('/cancel-payment-intent-mobile', [StripeController::class, 'cancelPaymentIntentMobile']); // Cancelar el PaymentIntent en Stripe para movil

        Route::post('/confirm-stripe-payment-mobile', [StripeController::class, 'confirmStripePaymentMobile']); // Confirmar el pago en Stripe para movil

        Route::post('/{orderId}/stripe-payment-mobile', [PaymentController::class, 'storeStripePaymentMobile']); // Guardar el pago en la base de datos para movil
        //endregion
    });
});

//----------------------------------------------------------------Payment Methods
Route::prefix('payment-methods')->group(function () {
    Route::get('/', [PaymentMethodController::class, 'index']);
});

//----------------------------------------------------------------Visits
Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->group(function () {
    Route::post('/visits', [VisitController::class, 'store']); // Registrar una visita
    Route::get('/visits', [VisitController::class, 'index']); // Ver todas las visitas
    Route::get('/visits/{userId}', [VisitController::class, 'showUserVisits']); // Ver visitas de un usuario específico

    Route::post('/visits/process-image', [VisitController::class, 'processImage']); // Registrar una visita con imagen
});

Route::get('/user/visits', [VisitController::class, 'getAuthenticatedUserVisits'])->middleware('auth:sanctum');

//----------------------------------------------------------------Membership Codes
Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->group(function () {
    // Canjear código de membresía para un usuario específico - Escritorio
    Route::post('/membership/redeem/user', [MembershipCodeController::class, 'redeemForSpecificUser']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Canjear código de membresía para el usuario autenticado - Web y Movil
    Route::post('/membership/redeem', [MembershipCodeController::class, 'redeemForAuthenticatedUser']);
});

//----------------------------------------------------------------Auth
Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->post('/register/user', [AuthController::class, 'registerUser']);
Route::middleware(['auth:sanctum', 'role.admin'])->post('/register/employee', [AuthController::class, 'registerEmployee']);
Route::post('/login/user', [AuthController::class, 'loginUser']);
Route::post('/login/employee', [AuthController::class, 'loginEmployee']);
Route::post('verify-2fa', [AuthController::class, 'verifyTwoFactor']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
