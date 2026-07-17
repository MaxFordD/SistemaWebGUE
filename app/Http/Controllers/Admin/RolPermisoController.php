<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolPermisoController extends Controller
{
    public function index()
    {
        $roles    = Rol::where('estado', 'A')->orderBy('rol_id')->get();
        $permisos = Permiso::where('estado', 'A')->orderBy('modulo')->orderBy('nombre')->get()
                    ->groupBy('modulo');

        // Cargar los permiso_ids de cada rol
        $rolPermisos = [];
        foreach ($roles as $rol) {
            $rolPermisos[$rol->rol_id] = $rol->permisos->pluck('permiso_id')->toArray();
        }

        return view('admin.roles-permisos.index', compact('roles', 'permisos', 'rolPermisos'));
    }

    public function update(Request $request, $rolId)
    {
        $rol = Rol::findOrFail($rolId);

        $permisosSeleccionados = array_map('intval', (array)$request->input('permisos', []));

        try {
            $rol->permisos()->sync($permisosSeleccionados);

            $user      = auth()->user();
            $usuarioId = $user->usuario_id ?? $user->id ?? null;
            if ($usuarioId) {
                try {
                    DB::statement('SET @res = 0, @msg = ""');
                    DB::statement('CALL sp_Bitacora_Insertar(?, ?, @res, @msg)', [
                        $usuarioId,
                        "Actualizó permisos del rol: {$rol->nombre}"
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Bitácora error: ' . $e->getMessage());
                }
            }

            return back()->with('success', "Permisos del rol \"{$rol->nombre}\" actualizados correctamente.");
        } catch (\Exception $e) {
            Log::error('Error al actualizar permisos: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar los permisos.');
        }
    }
}
