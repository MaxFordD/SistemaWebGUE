<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = collect(DB::select('CALL sp_Usuario_Listar()'));
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $personas = collect(DB::select('CALL sp_Persona_Listar()'))->where('estado', 'A');
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

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_Usuario_Insertar(?, ?, ?, @resultado, @mensaje)', [
            $data['persona_id'],
            $data['nombre_usuario'],
            $hashedPassword
        ]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

        $ok = (int)($out[0]->resultado ?? 0) > 0;

        if ($ok) {
            return redirect()->route('admin.usuarios.index')->with('success', $out[0]->mensaje ?? 'Usuario creado exitosamente');
        }

        return back()->withInput()->with('error', $out[0]->mensaje ?? 'Error al crear usuario');
    }

    public function edit($id)
    {
        $usuario = collect(DB::select('CALL sp_Usuario_ObtenerPorId(?)', [(int)$id]))->first();

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

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_Usuario_Actualizar(?, ?, ?, @resultado, @mensaje)', [
            (int)$id,
            $data['nombre_usuario'],
            $data['estado']
        ]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

        $ok = (int)($out[0]->resultado ?? 0) === 1;
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function destroy($id)
    {
        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_Usuario_Eliminar(?, @resultado, @mensaje)', [(int)$id]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

        $ok = (int)($out[0]->resultado ?? 0) === 1;
        return redirect()->route('admin.usuarios.index')->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function changePassword($id)
    {
        $usuario = collect(DB::select('CALL sp_Usuario_ObtenerPorId(?)', [(int)$id]))->first();

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

        // Obtener el usuario actual para verificar la contraseña
        $usuario = collect(DB::select('CALL sp_Usuario_ObtenerPorId(?)', [(int)$id]))->first();

        if (!$usuario) {
            return back()->with('error', 'Usuario no encontrado');
        }

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($data['contrasena_actual'], $usuario->contrasena)) {
            return back()->with('error', 'La contraseña actual es incorrecta');
        }

        // Hashear la nueva contraseña
        $hashedNueva = Hash::make($data['contrasena_nueva']);

        // Actualizar contraseña directamente (ya validamos la contraseña actual en Laravel)
        DB::table('Usuario')->where('usuario_id', (int)$id)->update(['contrasena' => $hashedNueva]);

        $out = [(object)['resultado' => 1, 'mensaje' => 'Contraseña actualizada exitosamente']];

        $ok = (int)($out[0]->resultado ?? 0) === 1;

        if ($ok) {
            return redirect()->route('admin.usuarios.index')->with('success', $out[0]->mensaje ?? 'Contraseña actualizada exitosamente');
        }

        return back()->with('error', $out[0]->mensaje ?? 'Error al actualizar contraseña');
    }
}
