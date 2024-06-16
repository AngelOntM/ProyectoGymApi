<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Retornar la información del usuario
        return response()->json($user, 200);
    }
}
