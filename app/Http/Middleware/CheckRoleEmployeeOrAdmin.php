<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRoleEmployeeOrAdmin
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && ($user->rol_id == 2 || $user->rol_id == 1)) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized.'], 403);
    }
}
