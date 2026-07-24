<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasPermission
{
    /**
     * Permite el paso si el usuario tiene AL MENOS UNO de los permisos indicados,
     * o si es Director/Administrador (superadmin, ver Usuario::isSuperAdmin()).
     */
    public function handle(Request $request, Closure $next, ...$permisosRequeridos)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        if (!$user->hasAnyPermission($permisosRequeridos)) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
