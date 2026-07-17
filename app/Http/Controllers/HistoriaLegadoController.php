<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HistoriaLegadoController extends Controller
{
    public function index()
    {
        $items = collect(DB::select('CALL sp_HistoriaLegado_Listar()'));
        return view('historia-legado', compact('items'));
    }
}
