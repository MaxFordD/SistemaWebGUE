<?php
namespace App\Http\Controllers;

use App\Models\ImagenInicio;
use App\Models\Noticia;

class HomeController extends Controller
{
    public function index()
    {
        $ultimas = Noticia::where('estado', 'A')
            ->orderByDesc('fecha_publicacion')
            ->limit(3)
            ->get();

        $slides  = ImagenInicio::where('seccion', 'carousel')->where('activo', 1)->orderBy('orden')->get();
        $talleres = ImagenInicio::where('seccion', 'taller')->where('activo', 1)->orderBy('orden')->get();

        return view('welcome', compact('ultimas', 'slides', 'talleres'));
    }
}
