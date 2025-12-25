<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $roles = collect(DB::select('CALL sp_Rol_Listar()'));
        return view('admin.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_Rol_Insertar(?, ?, @resultado, @mensaje)', [
            $data['nombre'],
            $data['descripcion'] ?? null
        ]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

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

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_Rol_Actualizar(?, ?, ?, ?, @resultado, @mensaje)', [
            (int)$id,
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['estado']
        ]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

        $ok  = (int)($out[0]->resultado ?? 0) === 1;
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function destroy($id)
    {
        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_Rol_Eliminar(?, @resultado, @mensaje)', [(int)$id]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

        $ok  = (int)($out[0]->resultado ?? 0) === 1;
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }
}
