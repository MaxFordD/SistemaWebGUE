<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioRolController extends Controller
{
    public function index()
    {
        $usuarios = collect(DB::select('EXEC sp_Usuario_Listar'));
        return view('admin.usuario-rol.index', compact('usuarios'));
    }

    public function show($usuarioId)
    {
        $usuario = collect(DB::select('EXEC sp_Usuario_ObtenerPorId ?', [(int)$usuarioId]))->first();

        if (!$usuario) {
            return redirect()->route('admin.usuario-rol.index')->with('error', 'Usuario no encontrado');
        }

        $rolesAsignados = collect(DB::select('EXEC sp_UsuarioRol_ListarPorUsuario ?', [(int)$usuarioId]));
        $todosLosRoles = collect(DB::select('EXEC sp_Rol_Listar'))->where('estado', 'A');

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

        $sql = "
            DECLARE @resultado BIT, @mensaje VARCHAR(200);
            EXEC sp_UsuarioRol_Asignar
                @usuario_id = ?,
                @rol_id = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [(int)$data['usuario_id'], (int)$data['rol_id']]);
        $ok = (int)($out[0]->resultado ?? 0) === 1;

        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function remover(Request $request)
    {
        $data = $request->validate([
            'usuario_id' => 'required|integer',
            'rol_id'     => 'required|integer',
        ]);

        $sql = "
            DECLARE @resultado BIT, @mensaje VARCHAR(200);
            EXEC sp_UsuarioRol_Remover
                @usuario_id = ?,
                @rol_id = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [(int)$data['usuario_id'], (int)$data['rol_id']]);
        $ok = (int)($out[0]->resultado ?? 0) === 1;

        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }
}
