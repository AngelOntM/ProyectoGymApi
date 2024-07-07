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



//----------------------------------------------------------------Users
//Usuario autenticado
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});
//Admin
Route::middleware(['auth:sanctum', 'role.admin'])->group(function () {
    Route::get('/users/empleados', [UserController::class, 'getEmpleados']);
    Route::put('/users/admin/{id}', [UserController::class, 'updateEmployee']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
});
//Empleado
Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->group(function () {
    Route::get('/users/clientes', [UserController::class, 'getClientes']);
    Route::put('/users/{id}', [UserController::class, 'updateUser']);
});

//----------------------------------------------------------------Memberships
Route::prefix('membresias')->group(function () {
    Route::get('', [MembershipController::class, 'index']);

    Route::middleware(['auth:sanctum', 'role.admin'])->group(function () {
        Route::get('/all', [MembershipController::class, 'indexAll']);
        Route::post('/', [MembershipController::class, 'store']);
        Route::put('/{id}', [MembershipController::class, 'update']);
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
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::put('/{id}/toggle-active', [ProductController::class, 'toggleActive']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });
});

//----------------------------------------------------------------Orders
Route::prefix('orders')->group(function () {
    Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->group(function () {
        Route::get('', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/products', [OrderController::class, 'storeProductsOrder']);
        Route::delete('/{id}', [OrderController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/memberships', [OrderController::class, 'storeMembershipsOrder']);
        // Payment routes
        Route::get('/{orderId}/payments', [PaymentController::class, 'show']);
        Route::post('/{orderId}/payments', [PaymentController::class, 'store']);
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
});

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
