<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Membresia;
use Illuminate\Support\Facades\Validator;

class MembresiaController extends Controller
{
    // Obtener todas las membresías
    public function index()
    {
        $membresias = Membresia::where('active', true)->get();
        return response()->json($membresias);
    }

// ----------------------------------------------------------------
    // Obtener todas las membresías (solo para admin)
    public function indexAll()
    {
        $membresias = Membresia::all();
        return response()->json($membresias);
    }

// ----------------------------------------------------------------
    // Crear una nueva membresía
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'membership_type' => 'required|string|max:255',
            'price' => 'required|numeric',
            'duration_days' => 'required|integer',
            'size' => 'required|integer',
            'active' => 'required|boolean',
            'benefits' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $membresia = Membresia::create($request->all());
        return response()->json($membresia, 201);
    }

// ----------------------------------------------------------------
    // Actualizar una membresía existente
    public function update(Request $request, $id)
    {
        $membresia = Membresia::find($id);

        if (!$membresia) {
            return response()->json(['message' => 'Membresía no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'membership_type' => 'required|string|max:255',
            'price' => 'required|numeric',
            'duration_days' => 'required|integer',
            'size' => 'required|integer',
            'active' => 'required|boolean',
            'benefits' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $membresia->update($request->all());
        return response()->json($membresia);
    }

// ----------------------------------------------------------------
    // Eliminar una membresía
    public function destroy($id)
    {
        $membresia = Membresia::find($id);

        if (!$membresia) {
            return response()->json(['message' => 'Membresía no encontrada'], 404);
        }

        $membresia->delete();
        return response()->json(['message' => 'Membresía eliminada'], 200);
    }

// ----------------------------------------------------------------
    // Alternar el estado de una membresía
    public function toggleActive($id)
    {
        $membresia = Membresia::find($id);

        if (!$membresia) {
            return response()->json(['message' => 'Membresía no encontrada'], 404);
        }

        $membresia->active = !$membresia->active;
        $membresia->save();

        return response()->json(['message' => 'Estado de membresía cambiado', 'active' => $membresia->active], 200);
    }
}
