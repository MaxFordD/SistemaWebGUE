<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    public function index()
    {
        $personas = collect(DB::select('EXEC sp_Persona_Listar'));
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

        $sql = "
            DECLARE @resultado INT, @mensaje VARCHAR(200);
            EXEC sp_Persona_Insertar
                @nombres = ?,
                @apellidos = ?,
                @dni = ?,
                @telefono = ?,
                @correo = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [
            $data['nombres'],
            $data['apellidos'],
            $data['dni'] ?? null,
            $data['telefono'] ?? null,
            $data['correo'] ?? null
        ]);

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

        $sql = "
            DECLARE @resultado BIT, @mensaje VARCHAR(200);
            EXEC sp_Persona_Actualizar
                @persona_id = ?,
                @nombres = ?,
                @apellidos = ?,
                @dni = ?,
                @telefono = ?,
                @correo = ?,
                @estado = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [
            (int)$id,
            $data['nombres'],
            $data['apellidos'],
            $data['dni'] ?? null,
            $data['telefono'] ?? null,
            $data['correo'] ?? null,
            $data['estado']
        ]);

        $ok = (int)($out[0]->resultado ?? 0) === 1;
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function destroy($id)
    {
        $sql = "
            DECLARE @resultado BIT, @mensaje VARCHAR(200);
            EXEC sp_Persona_Eliminar
                @persona_id = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [(int)$id]);
        $ok = (int)($out[0]->resultado ?? 0) === 1;
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }
}
