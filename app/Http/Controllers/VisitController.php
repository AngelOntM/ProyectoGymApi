<?php

namespace App\Http\Controllers;

use App\Models\UserMembership;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitController extends Controller
{
    // Registrar una visita
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Verificar si el usuario tiene una membresía activa
        $hasActiveMembership = UserMembership::where('user_id', $request->user_id)
            ->where('end_date', '>', now())
            ->exists();

        if (!$hasActiveMembership) {
            return response()->json(['message' => 'El usuario no tiene una membresía activa'], 400);
        }

        // Registrar la visita
        $visit = Visit::create([
            'user_id' => $request->user_id,
            'visit_date' => now()->toDateString(),
            'check_in_time' => now()->toTimeString(),
        ]);

        return response()->json($visit, 201);
    }

    // Ver todas las visitas
    public function index()
    {
        $visits = Visit::with('user')->get();
        return response()->json($visits, 200);
    }

    // Ver visitas de un usuario específico
    public function showUserVisits($userId)
    {
        $visits = Visit::where('user_id', $userId)
            ->with(['user', 'user.userMemberships' => function ($query) {
                $query->active(); // Usamos el scope `active` para filtrar los userMemberships activos
            }])
            ->get();
        return response()->json($visits, 200);
    }
}
