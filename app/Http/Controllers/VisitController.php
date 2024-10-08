<?php

namespace App\Http\Controllers;

use App\Models\UserMembership;
use App\Models\Visit;
use App\Models\User;
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

        // Si el usuario no tiene una membresía activa, retornar un error. pero los administradores pueden ingresar
        if (!$hasActiveMembership) {
            $user = User::find($request->user_id);
            if ($user->rol_id != 1 && $user->rol_id != 2) {
                return response()->json(['message' => 'El usuario no tiene una membresía activa'], 400);
            }
        }

        // Registrar la visita
        $visit = Visit::create([
            'user_id' => $request->user_id,
            'visit_date' => now()->toDateString(),
            'check_in_time' => now()->toTimeString(),
        ]);

        return response()->json($visit, 201);
    }

    // Ver todas las visitas dentro de un rango de fechas
    public function index(Request $request)
    {
        // Validar los parámetros de fecha
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Obtener las fechas de inicio y fin del request, si están presentes
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Consultar las visitas
        $query = Visit::query();

        // Filtrar por fecha si se proporcionan
        if ($startDate) {
            $query->where('visit_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('visit_date', '<=', $endDate);
        }

        // Obtener las visitas con el usuario relacionado
        $visits = $query->with('user')->get();

        // Retornar las visitas en formato JSON
        return response()->json($visits, 200);
    }


    // Ver visitas de un usuario específico
    public function showUserVisits($userId)
    {
        $visits = Visit::where('user_id', $userId)
            ->with([
                'user',
                'user.userMemberships' => function ($query) {
                    $query->active()
                        ->with([
                            'membershipDetail' => function ($query) {
                                $query->with('product:id,product_name'); // Cargar el producto y seleccionar solo el nombre
                            }
                        ]);
                }
            ])
            ->get();

        return response()->json($visits, 200);
    }

    // Procesar imagen recibida y verificar membresía
    public function processImage(Request $request)
    {
        $request->validate([
            'face_image' => 'required|image|max:2048',
        ]);

        // Guardar la imagen en una carpeta temporal alternativa
        $tempDir = storage_path('app/temp_alt');
        
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true); // Crear la carpeta si no existe
        }

        $file = $request->file('face_image');
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $tempDir . '/' . $filename;

        // Mover el archivo a la carpeta temporal alternativa
        $file->move($tempDir, $filename);

        // Verificar si se guardó correctamente
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'El archivo no se guardó correctamente en la carpeta temporal alternativa'], 500);
        }

        try {
            // Obtener la URL del microservicio desde el archivo .env
            $microserviceUrl = env('MICROSERVICE_URL') . '/recognize';

            // Enviar la imagen al microservicio de reconocimiento facial
            $response = Http::attach(
                'face_image',
                file_get_contents($filePath),
                'face_image.jpg'
            )->post($microserviceUrl);

            // Obtener el ID del usuario si es reconocido
            $userId = $response->json('user_id');

            if (!$userId) {
                return response()->json(['message' => 'Usuario no reconocido'], 404);
            }

            $user = User::find($userId);

            if ($user->rol_id == 1 || $user->rol_id == 2) {
                $visit = Visit::create([
                    'user_id' => $user->id,
                    'visit_date' => now()->toDateString(),
                    'check_in_time' => now()->toTimeString(),
                ]);

                return response()->json($visit, 201);
            }

            $hasActiveMembership = UserMembership::where('user_id', $userId)
                ->where('end_date', '>', now())
                ->exists();

            if (!$hasActiveMembership) {
                return response()->json(['message' => 'El usuario no tiene una membresía activa'], 400);
            }

            $visit = Visit::create([
                'user_id' => $userId,
                'visit_date' => now()->toDateString(),
                'check_in_time' => now()->toTimeString(),
            ]);

            return response()->json($visit, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al procesar la imagen y verificar la membresía', 'error' => $e->getMessage()], 500);
        } finally {
            // Eliminar la imagen temporal
            unlink($filePath);
        }
    }



    // Ver las visitas del usuario autenticado
    public function getAuthenticatedUserVisits()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Obtener las visitas del usuario autenticado
        $visits = Visit::where('user_id', $user->id)
            ->with('user') // Solo carga el usuario relacionado
            ->get()
            ->map(function ($visit) {
                return [
                    'id' => $visit->id,
                    'user_id' => $visit->user_id,
                    'visit_date' => $visit->visit_date,
                    'check_in_time' => $visit->check_in_time,
                    'user' => $visit->user->name,
                ];
            });

        // Retornar las visitas en formato JSON
        return response()->json($visits, 200);
    }
}
