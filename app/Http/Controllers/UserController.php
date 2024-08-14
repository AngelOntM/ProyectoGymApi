<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Obtener la membresía activa del usuario (si la tiene), incluyendo solo el nombre del producto
        $activeMembership = $user->userMemberships()
            ->active()
            ->with('membershipDetail.product')
            ->first();

        // Inicializar los datos de la membresía activa en null
        $activeMembershipData = null;

        // Verificar si existe una membresía activa
        if ($activeMembership) {
            $membershipProductName = $activeMembership->membershipDetail->product->product_name;

            // Crear el array con los datos de la membresía activa
            $activeMembershipData = [
                'id' => $activeMembership->id,
                'user_id' => $activeMembership->user_id,
                'membership_id' => $activeMembership->membership_id,
                'start_date' => $activeMembership->start_date,
                'end_date' => $activeMembership->end_date,
                'membership_name' => $membershipProductName,
            ];
        }

        // Retornar la información del usuario junto con la membresía activa y el nombre del producto (o null si no tiene)
        return response()->json([
            'user' => $user,
            'active_membership' => $activeMembershipData,
        ], 200);
    }

    // Retornar la información de los usuarios
    public function getClientes()
    {
        // Asumiendo que el rol de Cliente tiene el rol_id = 3
        $clientes = User::whereHas('rol', function($query) {
            $query->where('rol_name', 'Cliente');
        })
        ->orderBy('id', 'asc') 
        ->get();

        return response()->json(['clientes' => $clientes], 200);
    }

    // Retornar la información de los empleados
    public function getEmpleados()
    {
        // Obtener los empleados y cargar la relación 'rol' para incluir el nombre del rol
        $empleados = User::whereHas('rol', function($query) {
            $query->whereIn('rol_name', ['Empleado', 'Admin']);
        })
        ->with('rol')
        ->orderBy('id', 'asc') 
        ->get();

        // Preparar la respuesta incluyendo los nombres de los roles
        $empleadosConRol = $empleados->map(function ($empleado) {
            return [
                'id' => $empleado->id,
                'name' => $empleado->name,
                'email' => $empleado->email,
                'phone_number' => $empleado->phone_number,
                'address' => $empleado->address,
                'date_of_birth' => $empleado->date_of_birth,
                'rol_id' => $empleado->rol_id,
                'rol_name' => $empleado->rol->rol_name, // Incluye el nombre del rol
                'created_at' => $empleado->created_at,
                'updated_at' => $empleado->updated_at,
            ];
        });

        return response()->json(['empleados' => $empleadosConRol], 200);
    }

    // Actualizar la información del usuario
    public function updateUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|string|email|max:50|unique:users,email,' . $id,
            'phone_number' => 'sometimes|required|string|max:10',
            'address' => 'sometimes|required|string|max:60',
            'date_of_birth' => 'sometimes|required|date',
            'face_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::findOrFail($id);

        // Actualizar los campos del usuario
        $user->update($request->only([
            'name',
            'email',
            'phone_number',
            'address',
            'date_of_birth',
        ]));

        // Si se envió una imagen, procesarla y enviarla al microservicio de Python
        if ($request->hasFile('face_image')) {
            $image = $request->file('face_image');
            $imagePath = $image->getPathname();
            $imageName = $image->getClientOriginalName();

            // Obtener la URL del microservicio desde el archivo .env
            $microserviceUrl = env('MICROSERVICE_URL') . '/upload';

            // Enviar la imagen al microservicio de Python
            $response = Http::attach('face_image', file_get_contents($imagePath), $imageName)
                ->post($microserviceUrl, [
                    'user_id' => $user->id
                ]);

            // Procesar la respuesta del microservicio
            if ($response->successful()) {
                $responseData = $response->json();
                $user->face_image_path = $responseData['file_name'];
                $user->save();
            } else {
                return response()->json(['error' => 'Error uploading face image'], 500);
            }
        }

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    // Actualizar la información del empleado
    public function updateEmployee(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|string|email|max:50|unique:users,email,' . $id,
            'phone_number' => 'sometimes|required|string|max:10',
            'address' => 'sometimes|required|string|max:60',
            'date_of_birth' => 'sometimes|required|date',
            'rol_id' => 'sometimes|required|integer|exists:rols,id',
            'face_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Obtener el usuario que está realizando la solicitud
        $authenticatedUserId = Auth::id();

        // Verificar si el usuario está intentando actualizar su propio rol
        if ($authenticatedUserId == $id && $request->has('rol_id')) {
            return response()->json(['error' => 'You cannot update your own role.'], 403);
        }

        $user = User::findOrFail($id);

        // Actualizar los campos del usuario
        $user->update($request->only([
            'name',
            'email',
            'phone_number',
            'address',
            'date_of_birth',
            'rol_id',
        ]));

        // Si se envió una imagen, procesarla y enviarla al microservicio de Python
        if ($request->hasFile('face_image')) {
            $image = $request->file('face_image');
            $imagePath = $image->getPathname();
            $imageName = $image->getClientOriginalName();

            // Obtener la URL del microservicio desde el archivo .env
            $microserviceUrl = env('MICROSERVICE_URL') . '/upload';

            // Enviar la imagen al microservicio de Python
            $response = Http::attach('face_image', file_get_contents($imagePath), $imageName)
                ->post($microserviceUrl, [
                    'user_id' => $user->id
                ]);

            // Procesar la respuesta del microservicio
            if ($response->successful()) {
                $responseData = $response->json();
                $user->face_image_path = $responseData['file_name'];
                $user->save();
            } else {
                return response()->json(['error' => 'Error uploading face image'], 500);
            }
        }

        return response()->json(['message' => 'Employee updated successfully', 'user' => $user], 200);
    }

    // Eliminar un usuario
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() == $user->id) {
            return response()->json(['message' => 'You cannot delete yourself.'], 403);
        }

        // Get the URL of the Flask microservice from the .env file
        $microserviceUrl = env('MICROSERVICE_URL') . "/user/image/{$user->id}";

        // Send a DELETE request to the Flask microservice to remove the user's image
        $response = Http::delete($microserviceUrl);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to delete user image from the microservice'], 500);
        }

        // Delete the user from the database
        $user->delete();

        return response()->json(['message' => 'User and associated image deleted successfully'], 200);
    }

    // Obtener la imagen del usuario
    public function getUserImage($userId)
    {
        // Obtener la URL del microservicio desde el archivo .env
        $microserviceUrl = env('MICROSERVICE_URL') . "/user/image/{$userId}";

        // Obtener la URL de la imagen del microservicio Python
        $response = Http::get($microserviceUrl);

        // Verificar si la solicitud fue exitosa
        if ($response->successful()) {
            // Crear una respuesta con la imagen
            return response()->make($response->body(), 200, [
                'Content-Type' => $response->header('Content-Type'),
                'Content-Disposition' => 'inline; filename="' . $userId . '.jpg"',
            ]);
        } else {
            return response()->json(['error' => 'Image not found'], 404);
        }
    }
}
