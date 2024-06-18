<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Retornar la información del usuario
        return response()->json($user, 200);
    }

    public function getClientes()
    {
        // Asumiendo que el rol de Cliente tiene el rol_id = 3
        $clientes = User::whereHas('rol', function($query) {
            $query->where('rol_name', 'Cliente');
        })->get();

        return response()->json(['clientes' => $clientes], 200);
    }

    public function getEmpleados()
    {
        // Asumiendo que el rol de Cliente tiene el rol_id = 2
        $empleados = User::whereHas('rol', function($query) {
            $query->where('rol_name', 'Empleado');
        })->get();

        return response()->json(['empleados' => $empleados], 200);
    }

    public function updateUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|string|email|max:50|unique:users,email,' . $id,
            'phone_number' => 'sometimes|required|string|max:10',
            'address' => 'sometimes|required|string|max:60',
            'date_of_birth' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::findOrFail($id);

        $user->update($request->only([
            'name',
            'email',
            'phone_number',
            'address',
            'date_of_birth',
        ]));

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    public function updateEmployee(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|string|email|max:50|unique:users,email,' . $id,
            'phone_number' => 'sometimes|required|string|max:10',
            'address' => 'sometimes|required|string|max:60',
            'date_of_birth' => 'sometimes|required|date',
            'rol_id' => 'sometimes|required|integer|exists:rols,rol_id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::findOrFail($id);

        $user->update($request->only([
            'name',
            'email',
            'phone_number',
            'address',
            'date_of_birth',
            'rol_id',
        ]));

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() == $user->id) {
            return response()->json(['message' => 'You cannot delete yourself.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
