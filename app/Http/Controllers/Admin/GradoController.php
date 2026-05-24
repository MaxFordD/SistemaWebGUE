<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GradoController extends Controller
{
    public function index()
    {
        try {
            $grados = collect(DB::select('CALL sp_Grado_Listar()'));
            return view('admin.grados.index', compact('grados'));
        } catch (\Exception $e) {
            Log::error('Error al listar grados: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Error al cargar los grados.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:30',
            'nivel'  => 'required|in:Primaria,Secundaria',
        ], [
            'nombre.required' => 'El nombre del grado es obligatorio.',
            'nivel.required'  => 'El nivel es obligatorio.',
            'nivel.in'        => 'El nivel debe ser Primaria o Secundaria.',
        ]);

        try {
            DB::select('CALL sp_Grado_Insertar(?, ?)', [
                $request->nombre,
                $request->nivel,
            ]);
            return redirect()->route('admin.grados.index')->with('success', 'Grado registrado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear grado: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al guardar el grado.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:30',
            'nivel'  => 'required|in:Primaria,Secundaria',
            'estado' => 'required|in:0,1',
        ], [
            'nombre.required' => 'El nombre del grado es obligatorio.',
            'nivel.required'  => 'El nivel es obligatorio.',
        ]);

        try {
            DB::statement('CALL sp_Grado_Actualizar(?, ?, ?, ?)', [
                (int) $id,
                $request->nombre,
                $request->nivel,
                (int) $request->estado,
            ]);
            return redirect()->route('admin.grados.index')->with('success', 'Grado actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar grado: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar el grado.');
        }
    }

    public function destroy($id)
    {
        try {
            DB::statement('CALL sp_Grado_Eliminar(?)', [(int) $id]);
            return redirect()->route('admin.grados.index')->with('success', 'Grado desactivado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar grado: ' . $e->getMessage());
            return redirect()->route('admin.grados.index')->with('error', 'Error al desactivar el grado.');
        }
    }
}
