<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // 1) KPIs del sistema
        $statsRow = DB::select('EXEC sp_Sistema_ObtenerEstadisticas');
        $stats = (object) ($statsRow[0] ?? []);

        // Helper para recortar datetime2(7) a 6 decimales
        $fixDate = function ($fecha) {
            if (isset($fecha) && is_string($fecha)) {
                if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.(\d{7,})$/', $fecha)) {
                    return substr($fecha, 0, 26); // yyyy-mm-dd hh:mm:ss.ffffff
                }
            }
            return $fecha;
        };

        // 2) Últimas 5 noticias
        $ultimasNoticias = collect(DB::select('EXEC sp_Noticia_Listar'))
            ->map(function ($r) use ($fixDate) {
                $r->fecha_publicacion = $fixDate($r->fecha_publicacion ?? null);
                return $r;
            })
            ->take(5)
            ->values();
        // 4) Mesa de partes pendientes (máx 5)
        $mpPendientes = collect(DB::select("EXEC sp_MesaPartes_Listar @estado = N'Pendiente'"))
            ->map(function ($r) use ($fixDate) {
                $r->fecha_envio = $fixDate($r->fecha_envio ?? null);
                return $r;
            })
            ->take(5)
            ->values();

        return view('admin.dashboard', compact(
            'stats',
            'ultimasNoticias',
            'mpPendientes'
        ));
    }
}
