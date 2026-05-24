<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AsistenciaExport;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $año       = $request->get('año', date('Y'));
        $seccionId = $request->get('seccion_id');
        $fecha     = $request->get('fecha', date('Y-m-d'));

        try {
            $secciones = collect(DB::select('CALL sp_Seccion_ListarActivas(?)', [(int) $año]));
            $alumnos   = collect();
            $seccion   = null;

            if ($seccionId) {
                $alumnos = collect(
                    DB::select('CALL sp_Asistencia_ObtenerPorSeccionYFecha(?, ?)', [
                        (int) $seccionId,
                        $fecha,
                    ])
                );
                $seccionData = DB::select('CALL sp_Seccion_ObtenerPorId(?)', [(int) $seccionId]);
                $seccion = $seccionData[0] ?? null;
            }

            return view('admin.asistencia.index', compact(
                'secciones', 'alumnos', 'seccion', 'seccionId', 'fecha', 'año'
            ));
        } catch (\Exception $e) {
            Log::error('Error al cargar asistencia: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Error al cargar el formulario de asistencia.');
        }
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'seccion_id'  => 'required|integer',
            'fecha'       => 'required|date',
            'asistencia'  => 'required|array',
        ], [
            'seccion_id.required' => 'La sección es obligatoria.',
            'fecha.required'      => 'La fecha es obligatoria.',
            'asistencia.required' => 'No hay alumnos para registrar.',
        ]);

        $usuarioId = auth()->user()->usuario_id;
        $fecha     = $request->fecha;
        $guardados = 0;
        $errores   = 0;

        foreach ($request->asistencia as $alumnoId => $datos) {
            $estado      = $datos['estado'] ?? 'Falta';
            $observacion = $datos['observacion'] ?? null;

            if (!in_array($estado, ['Asistio', 'Falta', 'Tardanza'])) {
                continue;
            }

            try {
                DB::statement('CALL sp_Asistencia_RegistrarOActualizar(?, ?, ?, ?, ?)', [
                    (int) $alumnoId,
                    (int) $usuarioId,
                    $fecha,
                    $estado,
                    $observacion ?: null,
                ]);
                $guardados++;
            } catch (\Exception $e) {
                Log::error("Error asistencia alumno {$alumnoId}: " . $e->getMessage());
                $errores++;
            }
        }

        $msg = "Asistencia guardada: {$guardados} alumno(s) registrado(s).";
        if ($errores > 0) {
            $msg .= " {$errores} error(es).";
        }

        return redirect()->route('admin.asistencia.index', [
            'seccion_id' => $request->seccion_id,
            'fecha'      => $fecha,
            'año'        => $request->año ?? date('Y'),
        ])->with('success', $msg);
    }

    public function historialSeccion(Request $request)
    {
        $año       = $request->get('año', date('Y'));
        $mes       = $request->get('mes', date('n'));
        $seccionId = $request->get('seccion_id');

        try {
            $secciones = collect(DB::select('CALL sp_Seccion_ListarActivas(?)', [(int) $año]));
            $resumen   = collect();
            $seccion   = null;

            if ($seccionId) {
                $resumen = collect(DB::select('CALL sp_Asistencia_ResumenPorSeccion(?, ?, ?)', [
                    (int) $seccionId, (int) $mes, (int) $año,
                ]));
                $seccionData = DB::select('CALL sp_Seccion_ObtenerPorId(?)', [(int) $seccionId]);
                $seccion = $seccionData[0] ?? null;
            }

            $meses = [
                1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
                5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
                9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
            ];

            return view('admin.asistencia.historial-seccion', compact(
                'secciones', 'resumen', 'seccion', 'seccionId', 'mes', 'año', 'meses'
            ));
        } catch (\Exception $e) {
            Log::error('Error al cargar historial por sección: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Error al cargar el historial.');
        }
    }

    public function reportePdf(Request $request)
    {
        $seccionId = $request->get('seccion_id');
        $mes       = $request->get('mes', date('n'));
        $año       = $request->get('año', date('Y'));

        if (!$seccionId) {
            return redirect()->route('admin.asistencia.historial-seccion')
                ->with('error', 'Selecciona una sección antes de generar el reporte.');
        }

        $meses = [
            1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
            5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
            9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
        ];

        try {
            $resumen     = collect(DB::select('CALL sp_Asistencia_ResumenPorSeccion(?, ?, ?)', [(int)$seccionId, (int)$mes, (int)$año]));
            $seccionData = DB::select('CALL sp_Seccion_ObtenerPorId(?)', [(int)$seccionId]);
            $seccion     = (object)($seccionData[0] ?? []);

            $pdf = Pdf::loadView('admin.asistencia.reporte-pdf', compact('resumen', 'seccion', 'mes', 'año', 'meses'))
                      ->setPaper('a4', 'portrait');

            $filename = 'asistencia_' . ($seccion->grado ?? '') . '_' . ($seccion->seccion ?? '') . '_' . $meses[(int)$mes] . $año . '.pdf';
            $filename = str_replace(' ', '_', $filename);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el reporte PDF.');
        }
    }

    public function reporteExcel(Request $request)
    {
        $seccionId = $request->get('seccion_id');
        $mes       = $request->get('mes', date('n'));
        $año       = $request->get('año', date('Y'));

        if (!$seccionId) {
            return redirect()->route('admin.asistencia.historial-seccion')
                ->with('error', 'Selecciona una sección antes de generar el reporte.');
        }

        $meses = [
            1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
            5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
            9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
        ];

        try {
            $resumen     = collect(DB::select('CALL sp_Asistencia_ResumenPorSeccion(?, ?, ?)', [(int)$seccionId, (int)$mes, (int)$año]));
            $seccionData = DB::select('CALL sp_Seccion_ObtenerPorId(?)', [(int)$seccionId]);
            $seccion     = (object)($seccionData[0] ?? []);

            $meta = [
                'grado'     => $seccion->grado ?? '',
                'seccion'   => $seccion->seccion ?? '',
                'nivel'     => $seccion->nivel ?? '',
                'mes_nombre'=> $meses[(int)$mes],
                'año'       => $año,
            ];

            $filename = 'asistencia_' . ($seccion->grado ?? '') . '_' . ($seccion->seccion ?? '') . '_' . $meses[(int)$mes] . $año . '.xlsx';
            $filename = str_replace(' ', '_', $filename);

            return Excel::download(new AsistenciaExport($resumen, $meta), $filename);
        } catch (\Exception $e) {
            Log::error('Error al generar Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el reporte Excel.');
        }
    }

    public function historialAlumno(Request $request, $alumnoId)
    {
        $mes = $request->get('mes', date('n'));
        $año = $request->get('año', date('Y'));

        try {
            $alumnoData = DB::select('CALL sp_Alumno_ObtenerPorId(?)', [(int) $alumnoId]);
            if (empty($alumnoData)) {
                return redirect()->route('admin.asistencia.historial-seccion')->with('error', 'Alumno no encontrado.');
            }
            $alumno = $alumnoData[0];

            $historial = collect(DB::select('CALL sp_Asistencia_HistorialPorAlumno(?, ?, ?)', [
                (int) $alumnoId, (int) $mes, (int) $año,
            ]));

            $meses = [
                1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
                5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
                9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
            ];

            $totales = [
                'asistio'  => $historial->where('estado_asistencia', 'Asistio')->count(),
                'falta'    => $historial->where('estado_asistencia', 'Falta')->count(),
                'tardanza' => $historial->where('estado_asistencia', 'Tardanza')->count(),
            ];

            return view('admin.asistencia.historial-alumno', compact(
                'alumno', 'historial', 'totales', 'mes', 'año', 'meses'
            ));
        } catch (\Exception $e) {
            Log::error('Error al cargar historial de alumno: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el historial del alumno.');
        }
    }
}
