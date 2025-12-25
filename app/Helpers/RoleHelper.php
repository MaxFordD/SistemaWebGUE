<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class RoleHelper
{
    /**
     * Obtener todos los roles del usuario autenticado
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getUserRoles()
    {
        $user = auth()->user();
        if (!$user) {
            return collect();
        }

        try {
            // Resolver ID real en la tabla Usuario
            $uid = $user->usuario_id ?? $user->id ?? null;

            // Si no hay uid, intentar mapear por nombre_usuario
            if (!$uid && !empty($user->nombre_usuario)) {
                $row = DB::select('SELECT usuario_id FROM Usuario WHERE nombre_usuario = ? LIMIT 1', [$user->nombre_usuario]);
                if (!empty($row)) {
                    $uid = (int) $row[0]->usuario_id;
                }
            }

            // O por email contra Persona.correo
            if (!$uid && !empty($user->email)) {
                $row = DB::select('
                    SELECT u.usuario_id
                    FROM Usuario u
                    INNER JOIN Persona p ON u.persona_id = p.persona_id
                    WHERE p.correo = ?
                    LIMIT 1
                ', [$user->email]);
                if (!empty($row)) {
                    $uid = (int) $row[0]->usuario_id;
                }
            }

            // Traer roles del usuario
            if ($uid) {
                return collect(DB::select('CALL sp_UsuarioRol_ListarPorUsuario(?)', [$uid]))
                    ->pluck('nombre')
                    ->filter()
                    ->map(fn($n) => mb_strtolower(trim($n)));
            }

            return collect();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Verificar si el usuario tiene uno o más roles específicos
     *
     * @param string|array $roles Rol o array de roles a verificar
     * @return bool
     */
    public static function hasRole($roles)
    {
        $userRoles = self::getUserRoles();

        if (is_string($roles)) {
            $roles = [$roles];
        }

        $roles = collect($roles)->map(fn($r) => mb_strtolower(trim($r)));

        return $userRoles->intersect($roles)->isNotEmpty();
    }

    /**
     * Verificar si el usuario es Administrador o Director (control total)
     *
     * @return bool
     */
    public static function isAdmin()
    {
        return self::hasRole(['Director', 'Administrador', 'Admin']);
    }

    /**
     * Verificar si el usuario puede gestionar Mesa de Partes
     *
     * @return bool
     */
    public static function canManageMesaPartes()
    {
        return self::isAdmin() || self::hasRole('MesaPartes');
    }

    /**
     * Verificar si el usuario puede publicar noticias
     *
     * @return bool
     */
    public static function canPublishNews()
    {
        return self::isAdmin() || self::hasRole(['Editor', 'Secretaria']);
    }

    /**
     * Verificar si el usuario puede gestionar usuarios y roles
     *
     * @return bool
     */
    public static function canManageUsers()
    {
        return self::isAdmin();
    }

    /**
     * Verificar si el usuario puede gestionar el comité directivo
     *
     * @return bool
     */
    public static function canManageDirective()
    {
        return self::isAdmin();
    }
}
