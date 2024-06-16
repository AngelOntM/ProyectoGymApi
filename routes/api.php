<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:api')->get('/user', [UserController::class, 'getUser']);
Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->get('/users/clientes', [UserController::class, 'getClientes']);


Route::post('/change-password', [AuthController::class, 'changePassword']);


Route::middleware(['auth:sanctum', 'role.employee_or_admin'])->post('/register/user', [AuthController::class, 'registerUser']);
Route::middleware(['auth:sanctum', 'role.admin'])->post('/register/employee', [AuthController::class, 'registerEmployee']);

Route::post('/login/user', [AuthController::class, 'loginUser']);
Route::post('/login/employee', [AuthController::class, 'loginEmployee']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
