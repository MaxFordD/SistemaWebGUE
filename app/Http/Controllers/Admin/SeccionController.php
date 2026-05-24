<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeccionController extends Controller
{
    public function index(Request $request)
    {
        $año = $request->get('año', date('Y'));

        try {
            $secciones = collect(DB::select('CALL sp_Seccion_Listar(?)', [(int) $año]));
            $grados    = collect(DB::select('CALL sp_Grado_ListarActivos()'));
            return view('admin.secciones.index', compact('secciones', 'grados', 'año'));
        } catch (\Exception $e) {
            Log::error('Error al listar secciones: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Error al cargar las secciones.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'grado_id'    => 'required|integer',
            'nombre'      => 'required|string|max:10',
            'turno'       => 'required|in:Mañana,Tarde',
            'año_lectivo' => 'required|integer|min:2020|max:2099',
        ], [
            'grado_id.required'    => 'Seleccione un grado.',
            'nombre.required'      => 'El nombre de la sección es obligatorio.',
            'turno.required'       => 'El turno es obligatorio.',
            'año_lectivo.required' => 'El año lectivo es obligatorio.',
        ]);

        try {
            DB::select('CALL sp_Seccion_Insertar(?, ?, ?, ?)', [
                (int) $request->grado_id,
                $request->nombre,
                $request->turno,
                (int) $request->año_lectivo,
            ]);
            return redirect()->route('admin.secciones.index', ['año' => $request->año_lectivo])
                ->with('success', 'Sección registrada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear sección: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al guardar la sección.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'grado_id'    => 'required|integer',
            'nombre'      => 'required|string|max:10',
            'turno'       => 'required|in:Mañana,Tarde',
            'año_lectivo' => 'required|integer|min:2020|max:2099',
            'estado'      => 'required|in:0,1',
        ], [
            'grado_id.required' => 'Seleccione un grado.',
            'nombre.required'   => 'El nombre de la sección es obligatorio.',
        ]);

        try {
            DB::statement('CALL sp_Seccion_Actualizar(?, ?, ?, ?, ?, ?)', [
                (int) $id,
                (int) $request->grado_id,
                $request->nombre,
                $request->turno,
                (int) $request->año_lectivo,
                (int) $request->estado,
            ]);
            return redirect()->route('admin.secciones.index', ['año' => $request->año_lectivo])
                ->with('success', 'Sección actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar sección: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar la sección.');
        }
    }

    public function destroy($id)
    {
        try {
            DB::statement('CALL sp_Seccion_Eliminar(?)', [(int) $id]);
            return redirect()->route('admin.secciones.index')->with('success', 'Sección desactivada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar sección: ' . $e->getMessage());
            return redirect()->route('admin.secciones.index')->with('error', 'Error al desactivar la sección.');
        }
    }
}
