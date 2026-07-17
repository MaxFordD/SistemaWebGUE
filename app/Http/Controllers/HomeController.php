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

        $slides   = ImagenInicio::where('seccion', 'carousel')->where('activo', 1)->orderBy('orden')->get();
        $talleres = ImagenInicio::where('seccion', 'taller')->where('activo', 1)->orderBy('orden')->get();

        // TODO: reemplazar con los logros reales de la institución
        $logros = [
            ['icon' => 'bi-patch-check-fill', 'titulo' => 'Colegio Emblemático', 'descripcion' => 'Reconocido como institución educativa emblemática de la región.'],
            ['icon' => 'bi-graph-up-arrow',   'titulo' => 'Excelencia académica', 'descripcion' => 'Resultados destacados en evaluaciones y concursos académicos.'],
            ['icon' => 'bi-people-fill',      'titulo' => 'Formación integral',  'descripcion' => 'Programas de valores, deporte y arte junto a la formación académica.'],
            ['icon' => 'bi-building-check',   'titulo' => 'Infraestructura moderna', 'descripcion' => 'Ambientes renovados para un aprendizaje de calidad.'],
        ];

        return view('welcome', compact('ultimas', 'slides', 'talleres', 'logros'));
    }
}
