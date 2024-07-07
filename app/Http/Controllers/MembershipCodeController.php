<?php

namespace App\Http\Controllers;

use App\Models\MembershipCode;
use App\Models\UserMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MembershipCodeController extends Controller
{
    // Canjear un código de membresía para el usuario autenticado
    public function redeemForAuthenticatedUser(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:membership_codes,code',
        ]);

        $userId = Auth::id();
        return $this->redeemMembershipCode($request->code, $userId);
    }

    // Canjear un código de membresía para un usuario específico
    public function redeemForSpecificUser(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:membership_codes,code',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        return $this->redeemMembershipCode($request->code, $request->user_id);
    }

    // Método privado para canjear el código de membresía
    private function redeemMembershipCode($code, $userId)
    {
        // Verificar si el usuario tiene una membresía activa
        $hasActiveMembership = UserMembership::where('user_id', $userId)
            ->where('end_date', '>', now())
            ->exists();

        if ($hasActiveMembership) {
            return response()->json(['message' => 'El usuario ya tiene una membresía activa'], 400);
        }

        // Buscar el código de membresía y verificar que esté disponible
        $membershipCode = MembershipCode::where('code', $code)
            ->where('available', true)
            ->first();

        if (!$membershipCode) {
            return response()->json(['message' => 'El código de membresía no es válido o ya ha sido utilizado'], 404);
        }

        // Asignar el usuario a la membresía y marcar el código como usado
        $userMembership = $membershipCode->userMembership;
        $userMembership->user_id = $userId;
        $userMembership->save();

        $membershipCode->available = false;
        $membershipCode->save();

        return response()->json(['message' => 'Código canjeado exitosamente'], 200);
    }
}
