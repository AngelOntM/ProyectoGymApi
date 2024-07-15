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
    // Retornar la información del usuario autenticado
    public function getUser(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Obtener la membresía activa del usuario
        $activeMembership = $user->userMemberships()->active()->with('membershipDetail')->first();

        // Retornar la información del usuario junto con la membresía activa
        return response()->json([
            'user' => $user,
            'active_membership' => $activeMembership
        ], 200);
    }

    // Retornar la información de los usuarios
    public function getClientes()
    {
        // Asumiendo que el rol de Cliente tiene el rol_id = 3
        $clientes = User::whereHas('rol', function($query) {
            $query->where('rol_name', 'Cliente');
        })->get();

        return response()->json(['clientes' => $clientes], 200);
    }

    // Retornar la información de los empleados
    public function getEmpleados()
    {
        // Asumiendo que el rol de Empleado tiene el rol_id = 2
        $empleados = User::whereHas('rol', function($query) {
            $query->where('rol_name', 'Empleado');
        })->get();

        return response()->json(['empleados' => $empleados], 200);
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

            // Enviar la imagen al microservicio de Python
            $response = Http::attach('face_image', file_get_contents($imagePath), $imageName)
                ->post('http://localhost:5001/upload', [
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

            // Enviar la imagen al microservicio de Python
            $response = Http::attach('face_image', file_get_contents($imagePath), $imageName)
                ->post('http://localhost:5001/upload', [
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

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    // Obtener la imagen del usuario
    public function getUserImage($userId)
    {
        // Obtener la URL de la imagen del microservicio Python
        $response = Http::get("http://localhost:5001/user/image/{$userId}");

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
