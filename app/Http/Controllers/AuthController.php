<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\UserRegistered;
use App\Notifications\SendTwoFactorCode;
use Carbon\Carbon;


class AuthController extends Controller
{
    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:users',
            'phone_number' => 'required|string|max:10',
            'address' => 'required|string|max:60',
            'date_of_birth' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Generar contraseña aleatoria de longitud 10
        $randomPassword = Str::random(10);
        $hashedPassword = Hash::make($randomPassword);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $hashedPassword,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'rol_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->notify(new UserRegistered($randomPassword));

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    // Método para registrar un empleado
    public function registerEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:users',
            'phone_number' => 'required|string|max:10',
            'address' => 'required|string|max:60',
            'date_of_birth' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $randomPassword = Str::random(10);
        $hashedPassword = Hash::make($randomPassword);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $hashedPassword,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'rol_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->notify(new UserRegistered($randomPassword));
        return response()->json(['message' => 'Employee registered successfully', 'user' => $user], 201);
    }

    // Método para iniciar sesión de un usuario normal
    public function loginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:50',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->rol->rol_name == 'Admin') {
                $request->user()->generateTwoFactorCode();
                $request->user()->notify(new SendTwoFactorCode());

                return response()->json(['message' => '2FA code sent to your email', 'user' => $user], 200);
            }

            if ($user->rol->rol_name == 'Cliente') {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json(['message' => 'User logged in successfully', 'token' => $token, 'user' => $user], 200);
            } else {
                    Auth::logout();
                    return response()->json(['message' => 'Unauthorized'], 401);

                }
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Método para iniciar sesión de un empleado
    public function loginEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:50',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->rol->rol_name == 'Admin') {
                $request->user()->generateTwoFactorCode();
                $request->user()->notify(new SendTwoFactorCode());

                return response()->json(['message' => '2FA code sent to your email', 'user' => $user], 200);
            }

            if ($user->rol->rol_name == 'Empleado') {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json(['message' => 'Employee logged in successfully', 'token' => $token, 'user' => $user], 200);
            } else {
                Auth::logout();
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function verifyTwoFactor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:50',
            'two_factor_code' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::where('email', $request->email)
                    ->where('two_factor_code', $request->two_factor_code)
                    ->where('two_factor_expires_at', '>', Carbon::now())
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired 2FA code'], 401);
        }

        $user->resetTwoFactorCode();

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['message' => 'User logged in successfully', 'token' => $token, 'user' => $user], 200);
    }

    public function changePassword(Request $request)
    {
        // Validar la solicitud
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Obtener el usuario autenticado
        $user = Auth::user();

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 400);
        }

        // Actualizar la contraseña
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}