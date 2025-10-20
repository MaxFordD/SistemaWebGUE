<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$rolesRequeridos)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $usuarioId = $user->usuario_id ?? $user->id ?? null;
        if (!$usuarioId) {
            abort(403);
        }

        $roles = collect(DB::select('EXEC sp_UsuarioRol_ListarPorUsuario ?', [(int)$usuarioId]))
            ->pluck('nombre_rol') // ✅ CORREGIDO: nombre_rol
            ->map(fn($n) => mb_strtolower(trim($n)));

        $rolesRequeridos = collect($rolesRequeridos)->map(fn($n) => mb_strtolower(trim($n)));

        $tiene = $roles->intersect($rolesRequeridos)->isNotEmpty();
        if (!$tiene) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
