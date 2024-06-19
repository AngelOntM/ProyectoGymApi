<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MembresiaController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//----------------------------------------------------------------Users
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

Route::middleware(['auth:sanctum', 'role.admin'])->group(function () {
    Route::get('/users/empleados', [UserController::class, 'getEmpleados']);
    Route::put('/users/admin/{id}', [UserController::class, 'updateEmployee']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
});

Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->group(function () {
    Route::get('/users/clientes', [UserController::class, 'getClientes']);
    Route::put('/users/{id}', [UserController::class, 'updateUser']);
});

//----------------------------------------------------------------Memberships
Route::get('membresias', [MembresiaController::class, 'index']); // Membresías activas

Route::middleware(['auth:sanctum', 'role.admin'])->group(function () {
    Route::get('membresias/all', [MembresiaController::class, 'indexAll']); // Todas las membresías
    Route::post('membresias', [MembresiaController::class, 'store']);
    Route::put('membresias/{id}', [MembresiaController::class, 'update']);
    Route::put('membresias/{id}/toggle-active', [MembresiaController::class, 'toggleActive']);
    Route::delete('membresias/{id}', [MembresiaController::class, 'destroy']);
});

//----------------------------------------------------------------Auth
Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->post('/register/user', [AuthController::class, 'registerUser']);
Route::middleware(['auth:sanctum', 'role.admin'])->post('/register/employee', [AuthController::class, 'registerEmployee']);
Route::post('/login/user', [AuthController::class, 'loginUser']);
Route::post('/login/employee', [AuthController::class, 'loginEmployee']);
Route::post('verify-2fa', [AuthController::class, 'verifyTwoFactor']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
