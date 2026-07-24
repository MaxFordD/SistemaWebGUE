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
    private function obtenerConfiguracion()
    {
        $config = collect(DB::select('CALL sp_AsistenciaConfiguracion_Obtener()'))->first();

        return $config ?: (object) [
            'hora_apertura'        => '05:00:00',
            'hora_cierre'          => '19:00:00',
            'hora_limite_tardanza' => '08:00:00',
            'umbral_alertas_mes'   => 3,
            'dias_limite_edicion'  => 7,
        ];
    }

    public function index(Request $request)
    {
        $año       = $request->get('año', date('Y'));
        $seccionId = $request->get('seccion_id');
        $fecha     = $request->get('fecha', date('Y-m-d'));

        try {
            $config    = $this->obtenerConfiguracion();
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

            $diaNoHabil = collect(DB::select('CALL sp_DiaNoHabil_ExistePorFecha(?)', [$fecha]))->first();
            $esDiaNoHabil = $diaNoHabil && $diaNoHabil->existe > 0;

            return view('admin.asistencia.index', compact(
                'secciones', 'alumnos', 'seccion', 'seccionId', 'fecha', 'año', 'config', 'esDiaNoHabil', 'diaNoHabil'
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

        $usuario   = auth()->user();
        $usuarioId = $usuario->usuario_id;
        $fecha     = $request->fecha;

        // Día no hábil: no se permite registrar/editar asistencia
        $diaNoHabil = collect(DB::select('CALL sp_DiaNoHabil_ExistePorFecha(?)', [$fecha]))->first();
        if ($diaNoHabil && $diaNoHabil->existe > 0) {
            return redirect()->back()->withInput()->with(
                'error',
                "No se puede registrar asistencia: {$fecha} está marcado como día no hábil ({$diaNoHabil->motivo})."
            );
        }

        // Límite de días para editar registros ya pasados
        $config       = $this->obtenerConfiguracion();
        $diasAtras    = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($fecha)->startOfDay());
        $fechaPasada  = \Carbon\Carbon::parse($fecha)->lt(now()->startOfDay());
        if ($fechaPasada && $diasAtras > $config->dias_limite_edicion && !$usuario->hasPermission('asistencia.editar_vencido')) {
            return redirect()->back()->withInput()->with(
                'error',
                "No puedes registrar/editar asistencia de hace más de {$config->dias_limite_edicion} día(s). Pide autorización a un administrador."
            );
        }

        // Estado previo de cada alumno (para auditar cambios sobre registros existentes)
        $previos = collect(DB::select('CALL sp_Asistencia_ObtenerPorSeccionYFecha(?, ?)', [
            (int) $request->seccion_id, $fecha,
        ]))->keyBy('alumno_id');

        $guardados = 0;
        $errores   = 0;

        foreach ($request->asistencia as $alumnoId => $datos) {
            $estado      = $datos['estado'] ?? 'Falta';
            $observacion = $datos['observacion'] ?? null;
            $motivo      = $estado === 'Justificada' ? ($datos['motivo_justificacion'] ?? null) : null;

            if (!in_array($estado, ['Asistio', 'Falta', 'Tardanza', 'Justificada'])) {
                continue;
            }

            try {
                $previo   = $previos->get((int) $alumnoId);
                $existia  = $previo && $previo->asistencia_id;

                DB::statement('CALL sp_Asistencia_RegistrarOActualizar(?, ?, ?, ?, ?, ?, ?)', [
                    (int) $alumnoId,
                    (int) $usuarioId,
                    $fecha,
                    $estado,
                    $observacion ?: null,
                    now()->format('H:i:s'),
                    $motivo ?: null,
                ]);

                if ($existia && ($previo->estado_asistencia !== $estado || (string) ($previo->observacion ?? '') !== (string) ($observacion ?? ''))) {
                    DB::statement('CALL sp_AsistenciaAuditoria_Registrar(?, ?, ?, ?, ?, ?, ?)', [
                        (int) $alumnoId,
                        (int) $usuarioId,
                        $fecha,
                        $previo->estado_asistencia,
                        $estado,
                        $previo->observacion,
                        $observacion ?: null,
                    ]);
                }

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
            $config    = $this->obtenerConfiguracion();
            $secciones = collect(DB::select('CALL sp_Seccion_ListarActivas(?)', [(int) $año]));
            $resumen   = collect();
            $seccion   = null;

            if ($seccionId) {
                $resumen = collect(DB::select('CALL sp_Asistencia_ResumenPorSeccion(?, ?, ?)', [
                    (int) $seccionId, (int) $mes, (int) $año,
                ]))->map(function ($r) use ($config) {
                    $r->alerta_reincidencia = (($r->total_faltas ?? 0) + ($r->total_tardanzas ?? 0)) >= $config->umbral_alertas_mes;
                    return $r;
                });
                $seccionData = DB::select('CALL sp_Seccion_ObtenerPorId(?)', [(int) $seccionId]);
                $seccion = $seccionData[0] ?? null;
            }

            $meses = [
                1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
                5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
                9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
            ];

            return view('admin.asistencia.historial-seccion', compact(
                'secciones', 'resumen', 'seccion', 'seccionId', 'mes', 'año', 'meses', 'config'
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

    public function reporteAlumnoPdf(Request $request, $alumnoId)
    {
        $mes = $request->get('mes', date('n'));
        $año = $request->get('año', date('Y'));

        $meses = [
            1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
            5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
            9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
        ];

        try {
            $alumnoData = DB::select('CALL sp_Alumno_ObtenerPorId(?)', [(int)$alumnoId]);
            if (empty($alumnoData)) abort(404);
            $alumno = $alumnoData[0];

            $historial = collect(DB::select('CALL sp_Asistencia_HistorialPorAlumno(?, ?, ?)', [
                (int)$alumnoId, (int)$mes, (int)$año,
            ]));

            $totales = [
                'asistio'  => $historial->where('estado_asistencia', 'Asistio')->count(),
                'falta'    => $historial->where('estado_asistencia', 'Falta')->count(),
                'tardanza' => $historial->where('estado_asistencia', 'Tardanza')->count(),
            ];

            $pdf = Pdf::loadView('admin.asistencia.reporte-alumno-pdf', compact(
                'alumno', 'historial', 'totales', 'mes', 'año', 'meses'
            ))->setPaper('a4', 'portrait');

            $nombre = str_replace(' ', '_', $alumno->apellidos . '_' . $alumno->nombres);
            return $pdf->download("asistencia_{$nombre}_{$meses[(int)$mes]}{$año}.pdf");

        } catch (\Exception $e) {
            Log::error('Error PDF alumno: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el PDF.');
        }
    }

    public function reporteAlumnoExcel(Request $request, $alumnoId)
    {
        $mes = $request->get('mes', date('n'));
        $año = $request->get('año', date('Y'));

        $meses = [
            1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
            5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
            9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
        ];

        try {
            $alumnoData = DB::select('CALL sp_Alumno_ObtenerPorId(?)', [(int)$alumnoId]);
            if (empty($alumnoData)) abort(404);
            $alumno = $alumnoData[0];

            $historial = collect(DB::select('CALL sp_Asistencia_HistorialPorAlumno(?, ?, ?)', [
                (int)$alumnoId, (int)$mes, (int)$año,
            ]));

            $meta = [
                'alumno'     => $alumno->apellidos . ', ' . $alumno->nombres,
                'dni'        => $alumno->dni ?? '',
                'grado'      => ($alumno->grado ?? '') . ' — Sección ' . ($alumno->seccion ?? ''),
                'mes_nombre' => $meses[(int)$mes],
                'año'        => $año,
            ];

            $nombre = str_replace(' ', '_', $alumno->apellidos . '_' . $alumno->nombres);
            return Excel::download(
                new \App\Exports\AsistenciaAlumnoExport($historial, $meta),
                "asistencia_{$nombre}_{$meses[(int)$mes]}{$año}.xlsx"
            );

        } catch (\Exception $e) {
            Log::error('Error Excel alumno: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el Excel.');
        }
    }

    public function escanear(Request $request)
    {
        $request->validate([
            'codigo_qr' => 'required|string|max:40',
        ]);

        try {
            $config     = $this->obtenerConfiguracion();
            $ahora      = now();
            $horaActual = $ahora->format('H:i:s');

            if ($horaActual < $config->hora_apertura || $horaActual > $config->hora_cierre) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Fuera del horario de registro (' . substr($config->hora_apertura, 0, 5)
                             . ' a ' . substr($config->hora_cierre, 0, 5) . ').',
                ], 422);
            }

            $diaNoHabil = collect(DB::select('CALL sp_DiaNoHabil_ExistePorFecha(?)', [$ahora->format('Y-m-d')]))->first();
            if ($diaNoHabil && $diaNoHabil->existe > 0) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Hoy no hay clases: ' . $diaNoHabil->motivo,
                ], 422);
            }

            $alumnoData = DB::select('CALL sp_Alumno_ObtenerPorCodigoQR(?)', [$request->codigo_qr]);

            if (empty($alumnoData)) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Código no reconocido o alumno inactivo.',
                ], 404);
            }

            $alumno    = $alumnoData[0];
            $usuarioId = auth()->user()->usuario_id;
            $fecha     = $ahora->format('Y-m-d');
            $estado    = $horaActual <= $config->hora_limite_tardanza ? 'Asistio' : 'Tardanza';

            DB::statement('CALL sp_Asistencia_RegistrarOActualizar(?, ?, ?, ?, ?, ?, ?)', [
                (int) $alumno->alumno_id,
                (int) $usuarioId,
                $fecha,
                $estado,
                null,
                $horaActual,
                null,
            ]);

            return response()->json([
                'ok'      => true,
                'alumno'  => [
                    'nombres'   => $alumno->nombres,
                    'apellidos' => $alumno->apellidos,
                    'grado'     => $alumno->grado,
                    'seccion'   => $alumno->seccion,
                ],
                'estado' => $estado,
                'hora'   => $ahora->format('H:i:s'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al escanear QR de asistencia: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => 'Error al registrar la asistencia.'], 500);
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
                'asistio'     => $historial->where('estado_asistencia', 'Asistio')->count(),
                'falta'       => $historial->where('estado_asistencia', 'Falta')->count(),
                'tardanza'    => $historial->where('estado_asistencia', 'Tardanza')->count(),
                'justificada' => $historial->where('estado_asistencia', 'Justificada')->count(),
            ];

            $auditoria = collect(DB::select('CALL sp_AsistenciaAuditoria_ListarPorAlumno(?)', [(int) $alumnoId]));

            return view('admin.asistencia.historial-alumno', compact(
                'alumno', 'historial', 'totales', 'mes', 'año', 'meses', 'auditoria'
            ));
        } catch (\Exception $e) {
            Log::error('Error al cargar historial de alumno: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el historial del alumno.');
        }
    }
}
