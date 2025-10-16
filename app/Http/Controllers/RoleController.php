<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $roles = collect(DB::select('EXEC sp_Rol_Listar'));
        return view('admin.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $sql = "
            DECLARE @resultado INT, @mensaje VARCHAR(200);
            EXEC sp_Rol_Insertar
                @nombre = ?,
                @descripcion = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [$data['nombre'], $data['descripcion'] ?? null]);
        $ok  = (int)($out[0]->resultado ?? 0) > 0;
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
            'estado'      => 'required|in:A,I',
        ]);

        $sql = "
            DECLARE @resultado BIT, @mensaje VARCHAR(200);
            EXEC sp_Rol_Actualizar
                @rol_id = ?,
                @nombre = ?,
                @descripcion = ?,
                @estado = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [(int)$id, $data['nombre'], $data['descripcion'] ?? null, $data['estado']]);
        $ok  = (int)($out[0]->resultado ?? 0) === 1;
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function destroy($id)
    {
        $sql = "
            DECLARE @resultado BIT, @mensaje VARCHAR(200);
            EXEC sp_Rol_Eliminar
                @rol_id = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [(int)$id]);
        $ok  = (int)($out[0]->resultado ?? 0) === 1;
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }
}
