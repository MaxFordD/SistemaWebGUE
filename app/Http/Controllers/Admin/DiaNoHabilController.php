<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiaNoHabilController extends Controller
{
    public function index(Request $request)
    {
        $año = $request->get('año', date('Y'));

        try {
            $dias = collect(DB::select('CALL sp_DiaNoHabil_Listar(?)', [(int) $año]));
        } catch (\Exception $e) {
            Log::error('Error al listar días no hábiles: ' . $e->getMessage());
            $dias = collect();
        }

        return view('admin.asistencia.dias-no-habiles', compact('dias', 'año'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha'  => 'required|date',
            'motivo' => 'required|string|max:150',
        ], [
            'fecha.required'  => 'La fecha es obligatoria.',
            'motivo.required' => 'El motivo es obligatorio.',
        ]);

        $usuarioId = auth()->user()->usuario_id;

        try {
            DB::statement('CALL sp_DiaNoHabil_Insertar(?, ?, ?)', [
                $request->fecha,
                $request->motivo,
                (int) $usuarioId,
            ]);

            return redirect()->route('admin.asistencia.dias-no-habiles.index', ['año' => date('Y', strtotime($request->fecha))])
                ->with('success', 'Día no hábil registrado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al registrar día no hábil: ' . $e->getMessage());
            $mensaje = str_contains($e->getMessage(), 'Duplicate entry')
                ? 'Esa fecha ya está registrada como día no hábil.'
                : 'Error al registrar el día no hábil.';
            return redirect()->back()->withInput()->with('error', $mensaje);
        }
    }

    public function destroy($id)
    {
        try {
            DB::statement('CALL sp_DiaNoHabil_Eliminar(?)', [(int) $id]);
            return redirect()->back()->with('success', 'Día no hábil eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar día no hábil: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar el día no hábil.');
        }
    }
}
