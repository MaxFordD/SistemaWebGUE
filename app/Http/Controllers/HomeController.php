<?php
namespace App\Http\Controllers;

use App\Models\Noticia;

class HomeController extends Controller
{
    public function index()
    {
        // Obtener las últimas 3 noticias activas
        $ultimas = Noticia::where('estado', 'A')
            ->orderByDesc('fecha_publicacion')
            ->limit(3)
            ->get();

        // Pasar las noticias a la vista
        return view('welcome', compact('ultimas')); // Esta es la vista que se debe mostrar
    }
}
