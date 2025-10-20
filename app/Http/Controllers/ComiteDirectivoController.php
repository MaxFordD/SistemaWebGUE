<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComiteDirectivoController extends Controller
{
    /**
     * Mostrar la vista pública del comité directivo
     */
    public function index()
    {
        try {
            // Usar el SP para listar solo directivos activos
            $directivos = collect(DB::select('EXEC sp_ComiteDirectivo_Listar @solo_activos = 1'));

            return view('comite-directivo.index', compact('directivos'));
        } catch (\Exception $e) {
            \Log::error('Error al listar directivos: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Error al cargar el comité directivo.');
        }
    }
}
