<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $buscar  = trim($request->get('buscar', ''));
        $perPage = 20;
        $page    = max(1, (int) $request->get('page', 1));

        $query = DB::table('Bitacora as b')
            ->join('Usuario as u', 'u.usuario_id', '=', 'b.usuario_id')
            ->select('b.bitacora_id', 'b.accion', 'b.fecha', 'u.nombre_usuario')
            ->orderByDesc('b.fecha');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('u.nombre_usuario', 'like', "%{$buscar}%")
                  ->orWhere('b.accion', 'like', "%{$buscar}%");
            });
        }

        $total    = $query->count();
        $registros = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $bitacora = new LengthAwarePaginator(
            $registros, $total, $perPage, $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        return view('admin.bitacora.index', compact('bitacora', 'buscar'));
    }
}
