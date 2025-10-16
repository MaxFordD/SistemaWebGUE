<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = collect(DB::select('EXEC sp_Usuario_Listar'));
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $personas = collect(DB::select('EXEC sp_Persona_Listar'))->where('estado', 'A');
        return view('admin.usuarios.create', compact('personas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'persona_id'     => 'required|integer|exists:Persona,persona_id',
            'nombre_usuario' => 'required|string|max:100',
            'contrasena'     => 'required|string|min:6|confirmed',
        ], [
            'persona_id.required'     => 'Debe seleccionar una persona',
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio',
            'contrasena.required'     => 'La contraseña es obligatoria',
            'contrasena.min'          => 'La contraseña debe tener al menos 6 caracteres',
            'contrasena.confirmed'    => 'Las contraseñas no coinciden',
        ]);

        // Hash de la contraseña
        $hashedPassword = Hash::make($data['contrasena']);

        $sql = "
            DECLARE @resultado INT, @mensaje VARCHAR(200);
            EXEC sp_Usuario_Insertar
                @persona_id = ?,
                @nombre_usuario = ?,
                @contrasena = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [
            $data['persona_id'],
            $data['nombre_usuario'],
            $hashedPassword
        ]);

        $ok = (int)($out[0]->resultado ?? 0) > 0;

        if ($ok) {
            return redirect()->route('admin.usuarios.index')->with('success', $out[0]->mensaje ?? 'Usuario creado exitosamente');
        }

        return back()->withInput()->with('error', $out[0]->mensaje ?? 'Error al crear usuario');
    }

    public function edit($id)
    {
        $usuario = collect(DB::select('EXEC sp_Usuario_ObtenerPorId ?', [(int)$id]))->first();

        if (!$usuario) {
            return redirect()->route('admin.usuarios.index')->with('error', 'Usuario no encontrado');
        }

        return view('admin.usuarios.edit', compact('usuario'));
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'nombre_usuario' => 'required|string|max:100',
            'estado'         => 'required|in:A,I',
        ], [
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio',
        ]);

        $sql = "
            DECLARE @resultado BIT, @mensaje VARCHAR(200);
            EXEC sp_Usuario_Actualizar
                @usuario_id = ?,
                @nombre_usuario = ?,
                @estado = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [
            (int)$id,
            $data['nombre_usuario'],
            $data['estado']
        ]);

        $ok = (int)($out[0]->resultado ?? 0) === 1;
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function destroy($id)
    {
        $sql = "
            DECLARE @resultado BIT, @mensaje VARCHAR(200);
            EXEC sp_Usuario_Eliminar
                @usuario_id = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [(int)$id]);
        $ok = (int)($out[0]->resultado ?? 0) === 1;
        return redirect()->route('admin.usuarios.index')->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function changePassword($id)
    {
        $usuario = collect(DB::select('EXEC sp_Usuario_ObtenerPorId ?', [(int)$id]))->first();

        if (!$usuario) {
            return redirect()->route('admin.usuarios.index')->with('error', 'Usuario no encontrado');
        }

        return view('admin.usuarios.change-password', compact('usuario'));
    }

    public function updatePassword($id, Request $request)
    {
        $data = $request->validate([
            'contrasena_actual' => 'required|string',
            'contrasena_nueva'  => 'required|string|min:6|confirmed',
        ], [
            'contrasena_actual.required'  => 'La contraseña actual es obligatoria',
            'contrasena_nueva.required'   => 'La contraseña nueva es obligatoria',
            'contrasena_nueva.min'        => 'La contraseña debe tener al menos 6 caracteres',
            'contrasena_nueva.confirmed'  => 'Las contraseñas no coinciden',
        ]);

        $hashedActual = Hash::make($data['contrasena_actual']);
        $hashedNueva = Hash::make($data['contrasena_nueva']);

        $sql = "
            DECLARE @resultado BIT, @mensaje VARCHAR(200);
            EXEC sp_Usuario_CambiarContrasena
                @usuario_id = ?,
                @contrasena_actual = ?,
                @contrasena_nueva = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [
            (int)$id,
            $hashedActual,
            $hashedNueva
        ]);

        $ok = (int)($out[0]->resultado ?? 0) === 1;

        if ($ok) {
            return redirect()->route('admin.usuarios.index')->with('success', $out[0]->mensaje ?? 'Contraseña actualizada exitosamente');
        }

        return back()->with('error', $out[0]->mensaje ?? 'Error al actualizar contraseña');
    }
}
