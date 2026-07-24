<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioRolController extends Controller
{
    public function index()
    {
        $usuarios = collect(DB::select('CALL sp_Usuario_Listar()'));
        return view('admin.usuario-rol.index', compact('usuarios'));
    }

    public function show($usuarioId)
    {
        $usuario = collect(DB::select('CALL sp_Usuario_ObtenerPorId(?)', [(int)$usuarioId]))->first();

        if (!$usuario) {
            return redirect()->route('admin.usuario-rol.index')->with('error', 'Usuario no encontrado');
        }

        $rolesAsignados = collect(DB::select('CALL sp_UsuarioRol_ListarPorUsuario(?)', [(int)$usuarioId]));
        $todosLosRoles = collect(DB::select('CALL sp_Rol_Listar()'))->where('estado', 'A');

        // Filtrar roles disponibles (que no están asignados)
        $rolesDisponibles = $todosLosRoles->filter(function($rol) use ($rolesAsignados) {
            return !$rolesAsignados->contains('rol_id', $rol->rol_id);
        });

        return view('admin.usuario-rol.show', compact('usuario', 'rolesAsignados', 'rolesDisponibles'));
    }

    public function asignar(Request $request)
    {
        $data = $request->validate([
            'usuario_id' => 'required|integer',
            'rol_id'     => 'required|integer',
        ]);

        // Solo Director/Administrador pueden otorgar el rol Administrador o Director.
        // Sin esto, cualquiera con el permiso "Asignar Roles" podría auto-otorgarse
        // control total del sistema.
        $rol = collect(DB::select('CALL sp_Rol_Listar()'))->firstWhere('rol_id', (int)$data['rol_id']);
        if ($rol && in_array(mb_strtolower(trim($rol->nombre)), ['administrador', 'director']) && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', "Solo Director o Administrador pueden asignar el rol \"{$rol->nombre}\".");
        }

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_UsuarioRol_Asignar(?, ?, @resultado, @mensaje)', [
            (int)$data['usuario_id'],
            (int)$data['rol_id']
        ]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

        $ok = (int)($out[0]->resultado ?? 0) === 1;

        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function remover(Request $request)
    {
        $data = $request->validate([
            'usuario_id' => 'required|integer',
            'rol_id'     => 'required|integer',
        ]);

        // Solo Director/Administrador pueden quitar el rol Administrador o Director
        // a alguien. Evita que un rol con el permiso "Asignar Roles" pueda quitarle
        // el acceso a un superadmin.
        $rol = collect(DB::select('CALL sp_Rol_Listar()'))->firstWhere('rol_id', (int)$data['rol_id']);
        if ($rol && in_array(mb_strtolower(trim($rol->nombre)), ['administrador', 'director']) && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', "Solo Director o Administrador pueden quitar el rol \"{$rol->nombre}\".");
        }

        // Evitar que el sistema se quede sin ningún Director/Administrador: si el
        // rol a remover es uno de esos y es el último usuario que lo tiene, y
        // tampoco tiene el otro rol superadmin, se bloquea.
        if ($rol && in_array(mb_strtolower(trim($rol->nombre)), ['administrador', 'director'])) {
            $rolesDelUsuario = collect(DB::select('CALL sp_UsuarioRol_ListarPorUsuario(?)', [(int)$data['usuario_id']]))
                ->pluck('nombre')->map(fn($n) => mb_strtolower(trim($n)));

            $otroSuperadminEnEsteUsuario = $rolesDelUsuario
                ->filter(fn($n) => in_array($n, ['administrador', 'director']))
                ->reject(fn($n) => $n === mb_strtolower(trim($rol->nombre)))
                ->isNotEmpty();

            if (!$otroSuperadminEnEsteUsuario) {
                $totalConAlgunSuperadmin = collect(DB::select("
                    SELECT COUNT(DISTINCT ur.usuario_id) AS total
                    FROM UsuarioRol ur
                    INNER JOIN Rol r ON r.rol_id = ur.rol_id
                    WHERE LOWER(TRIM(r.nombre)) IN ('administrador','director')
                "))->first()->total ?? 0;

                if ((int)$totalConAlgunSuperadmin <= 1) {
                    return back()->with('error', 'No se puede quitar este rol: es el último usuario con acceso de Director/Administrador. El sistema se quedaría sin nadie que pueda administrarlo.');
                }
            }
        }

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_UsuarioRol_Remover(?, ?, @resultado, @mensaje)', [
            (int)$data['usuario_id'],
            (int)$data['rol_id']
        ]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

        $ok = (int)($out[0]->resultado ?? 0) === 1;

        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }
}
