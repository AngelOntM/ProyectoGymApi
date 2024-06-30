<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ProductController;


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

//----------------------------------------------------------------Memberships
Route::prefix('productos')->group(function () {
    Route::get('/', [ProductController::class, 'index']); 

    Route::middleware(['auth:sanctum', 'role.admin'])->group(function () {
        Route::get('/all', [ProductController::class, 'all']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{id}', [ProductController::class, 'update']); 
        Route::put('/{id}/toggle-active', [ProductController::class, 'toggleActive']); 
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });
});

//----------------------------------------------------------------Auth
Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->post('/register/user', [AuthController::class, 'registerUser']);
Route::middleware(['auth:sanctum', 'role.admin'])->post('/register/employee', [AuthController::class, 'registerEmployee']);
Route::post('/login/user', [AuthController::class, 'loginUser']);
Route::post('/login/employee', [AuthController::class, 'loginEmployee']);
Route::post('verify-2fa', [AuthController::class, 'verifyTwoFactor']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
