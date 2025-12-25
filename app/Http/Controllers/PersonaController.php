<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    public function index()
    {
        $personas = collect(DB::select('CALL sp_Persona_Listar()'));
        return view('admin.personas.index', compact('personas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombres'    => 'required|string|max:100',
            'apellidos'  => 'required|string|max:100',
            'dni'        => 'nullable|string|size:8',
            'telefono'   => 'nullable|string|size:9',
            'correo'     => 'nullable|email|max:100',
        ], [
            'nombres.required'    => 'El campo nombres es obligatorio',
            'apellidos.required'  => 'El campo apellidos es obligatorio',
            'dni.size'            => 'El DNI debe tener 8 dígitos',
            'telefono.size'       => 'El teléfono debe tener 9 dígitos',
            'correo.email'        => 'El correo debe ser válido',
        ]);

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_Persona_Insertar(?, ?, ?, ?, ?, @resultado, @mensaje)', [
            $data['nombres'],
            $data['apellidos'],
            $data['dni'] ?? null,
            $data['telefono'] ?? null,
            $data['correo'] ?? null
        ]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

        $ok = (int)($out[0]->resultado ?? 0) > 0;
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'nombres'    => 'required|string|max:100',
            'apellidos'  => 'required|string|max:100',
            'dni'        => 'nullable|string|size:8',
            'telefono'   => 'nullable|string|size:9',
            'correo'     => 'nullable|email|max:100',
            'estado'     => 'required|in:A,I',
        ], [
            'nombres.required'    => 'El campo nombres es obligatorio',
            'apellidos.required'  => 'El campo apellidos es obligatorio',
            'dni.size'            => 'El DNI debe tener 8 dígitos',
            'telefono.size'       => 'El teléfono debe tener 9 dígitos',
            'correo.email'        => 'El correo debe ser válido',
        ]);

        // Actualizar directamente (el SP aún no está creado)
        $affected = DB::table('Persona')->where('persona_id', (int)$id)->update([
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'dni' => $data['dni'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'correo' => $data['correo'] ?? null,
            'estado' => $data['estado']
        ]);

        $ok = $affected > 0;
        return back()->with($ok ? 'success' : 'error', $ok ? 'Persona actualizada exitosamente' : 'Error al actualizar persona');
    }

    public function destroy($id)
    {
        // Eliminar lógicamente (el SP aún no está creado)
        $affected = DB::table('Persona')->where('persona_id', (int)$id)->update(['estado' => 'I']);

        $ok = $affected > 0;
        return back()->with($ok ? 'success' : 'error', $ok ? 'Persona eliminada exitosamente' : 'Error al eliminar persona');
    }
}
