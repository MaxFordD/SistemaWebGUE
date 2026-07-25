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
        if ($ok) {
            $this->registrarBitacora('roles', "Creó el rol \"{$data['nombre']}\"");
        }
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    /**
     * Roles que el sistema reconoce por nombre exacto para dar acceso total
     * (ver Usuario::isSuperAdmin()). Si se renombran o desactivan sin querer,
     * nadie podría volver a entrar a las secciones restringidas y no hay
     * forma de arreglarlo sin acceso directo a la base de datos.
     */
    protected array $rolesProtegidos = ['administrador', 'director'];

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
            'estado'      => 'required|in:A,I',
        ]);

        $rolActual = collect(DB::select('CALL sp_Rol_Listar()'))->firstWhere('rol_id', (int)$id);
        if ($rolActual && in_array(mb_strtolower(trim($rolActual->nombre)), $this->rolesProtegidos)) {
            $nombreProtegido = mb_strtolower(trim($rolActual->nombre)) !== mb_strtolower(trim($data['nombre']));
            $desactivando    = $data['estado'] === 'I';
            if ($nombreProtegido || $desactivando) {
                return back()->with('error', "El rol \"{$rolActual->nombre}\" es necesario para el funcionamiento del sistema: no se puede renombrar ni desactivar.");
            }
        }

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
        if ($ok) {
            $this->registrarBitacora('roles', "Actualizó el rol \"{$data['nombre']}\" (estado: {$data['estado']})");
        }
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    public function destroy($id)
    {
        $rolActual = collect(DB::select('CALL sp_Rol_Listar()'))->firstWhere('rol_id', (int)$id);
        if ($rolActual && in_array(mb_strtolower(trim($rolActual->nombre)), $this->rolesProtegidos)) {
            return back()->with('error', "El rol \"{$rolActual->nombre}\" es necesario para el funcionamiento del sistema: no se puede eliminar.");
        }

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_Rol_Eliminar(?, @resultado, @mensaje)', [(int)$id]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

        $ok  = (int)($out[0]->resultado ?? 0) === 1;
        if ($ok && $rolActual) {
            $this->registrarBitacora('roles', "Desactivó el rol \"{$rolActual->nombre}\"");
        }
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }

    /**
     * Borrado definitivo (no lógico). Solo permitido si el rol ya está
     * Inactivo y no tiene usuarios asignados — mismo patrón de "desactivar
     * primero, borrar definitivo después" que Comité Directivo.
     */
    public function forceDestroy($id)
    {
        $rolActual = collect(DB::select('CALL sp_Rol_Listar()'))->firstWhere('rol_id', (int)$id);

        if (!$rolActual) {
            return back()->with('error', 'El rol no existe.');
        }

        if (in_array(mb_strtolower(trim($rolActual->nombre)), $this->rolesProtegidos)) {
            return back()->with('error', "El rol \"{$rolActual->nombre}\" es necesario para el funcionamiento del sistema: no se puede eliminar.");
        }

        if ($rolActual->estado !== 'I') {
            return back()->with('error', 'Primero debes desactivar el rol antes de eliminarlo definitivamente.');
        }

        DB::statement('SET @resultado = 0, @mensaje = ""');
        DB::statement('CALL sp_Rol_EliminarFisico(?, @resultado, @mensaje)', [(int)$id]);
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');

        $ok = (int)($out[0]->resultado ?? 0) === 1;
        if ($ok) {
            $this->registrarBitacora('roles', "Eliminó permanentemente el rol \"{$rolActual->nombre}\"");
        }
        return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }
}
