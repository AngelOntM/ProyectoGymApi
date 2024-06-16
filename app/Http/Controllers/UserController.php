<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

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
}
