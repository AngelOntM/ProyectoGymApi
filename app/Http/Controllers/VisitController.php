<?php

namespace App\Http\Controllers;

use App\Models\UserMembership;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
                $query->active()
                    ->with(['membershipDetail' => function ($query) {
                        $query->with('product:id,product_name'); // Cargar el producto y seleccionar solo el nombre
                    }]);
            }])
            ->get();

        return response()->json($visits, 200);
    }

        // Procesar imagen recibida y verificar membresía
    public function processImage(Request $request)
    {
        $request->validate([
            'face_image' => 'required|image|max:2048', // Ajusta el tamaño máximo según tus necesidades
        ]);

        // Guardar la imagen en el servidor
        $imagePath = $request->file('face_image')->store('temp');

        try {
            // Enviar la imagen al microservicio de reconocimiento facial
            $response = Http::attach(
                'face_image',
                file_get_contents(storage_path('app/' . $imagePath)),
                'face_image.jpg'
            )->post('http://localhost:5002/recognize');

            // Obtener el ID del usuario si es reconocido
            $userId = $response->json('user_id');

            if (!$userId) {
                return response()->json(['message' => 'Usuario no reconocido por el microservicio'], 404);
            }

            // Verificar si el usuario tiene una membresía activa
            $hasActiveMembership = UserMembership::where('user_id', $userId)
                ->where('end_date', '>', now())
                ->exists();

            if (!$hasActiveMembership) {
                return response()->json(['message' => 'El usuario no tiene una membresía activa'], 400);
            }

            // Registrar la visita
            $visit = Visit::create([
                'user_id' => $userId,
                'visit_date' => now()->toDateString(),
                'check_in_time' => now()->toTimeString(),
            ]);

            return response()->json($visit, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al procesar la imagen y verificar la membresía'], 500);
        } finally {
            // Eliminar la imagen temporal
            unlink(storage_path('app/' . $imagePath));
        }
    }
}
