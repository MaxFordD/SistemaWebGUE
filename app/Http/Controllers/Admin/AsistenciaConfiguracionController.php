<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsistenciaConfiguracionController extends Controller
{
    public function index()
    {
        $config = collect(DB::select('CALL sp_AsistenciaConfiguracion_Obtener()'))->first();

        return view('admin.asistencia.configuracion', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'hora_apertura'        => 'required|date_format:H:i',
            'hora_cierre'          => 'required|date_format:H:i|after:hora_apertura',
            'hora_limite_tardanza' => 'required|date_format:H:i|after_or_equal:hora_apertura|before_or_equal:hora_cierre',
            'umbral_alertas_mes'   => 'required|integer|min:1|max:99',
            'dias_limite_edicion'  => 'required|integer|min:0|max:365',
        ], [
            'hora_cierre.after'                    => 'La hora de cierre debe ser posterior a la hora de apertura.',
            'hora_limite_tardanza.after_or_equal'   => 'La hora límite de tardanza debe estar dentro de la ventana de registro.',
            'hora_limite_tardanza.before_or_equal'  => 'La hora límite de tardanza debe estar dentro de la ventana de registro.',
        ]);

        $usuarioId = auth()->user()->usuario_id;

        try {
            DB::statement('CALL sp_AsistenciaConfiguracion_Actualizar(?, ?, ?, ?, ?, ?)', [
                $request->hora_apertura,
                $request->hora_cierre,
                $request->hora_limite_tardanza,
                (int) $request->umbral_alertas_mes,
                (int) $request->dias_limite_edicion,
                (int) $usuarioId,
            ]);

            try {
                DB::statement('SET @res = 0, @msg = ""');
                DB::statement('CALL sp_Bitacora_Insertar(?, ?, @res, @msg)', [
                    $usuarioId,
                    'Actualizó la configuración de horarios de asistencia',
                ]);
            } catch (\Exception $e) {
                Log::warning('Bitácora error: ' . $e->getMessage());
            }

            return redirect()->route('admin.asistencia.configuracion.index')
                ->with('success', 'Configuración de asistencia actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar configuración de asistencia: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al guardar la configuración.');
        }
    }
}
