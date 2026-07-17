<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class NosotrosController extends Controller
{
    public function index()
    {
        try {
            $rows    = DB::table('NosotrosContenido')->get()->pluck('valor', 'clave')->toArray();
            $pilares = json_decode($rows['pilares'] ?? '[]', true) ?: $this->defaultPilares();
            $normas  = json_decode($rows['normas']  ?? '[]', true) ?: $this->defaultNormas();

            $historiaImagenes = json_decode($rows['historia_imagenes'] ?? '[]', true) ?: [];
            if (empty($historiaImagenes)) {
                $historiaImagenes = [$rows['historia_imagen'] ?? 'images/gue.jpg'];
            }
        } catch (\Exception $e) {
            $rows    = [];
            $pilares = $this->defaultPilares();
            $normas  = $this->defaultNormas();
            $historiaImagenes = ['images/gue.jpg'];
        }

        return view('nosotros', compact('rows', 'pilares', 'normas', 'historiaImagenes'));
    }

    private function defaultPilares(): array
    {
        return [
            ['icon' => 'heart-fill',    'titulo' => 'Valores',              'desc' => 'Respeto, responsabilidad, honestidad y solidaridad son la base de nuestra formación integral.',       'color' => 'danger'],
            ['icon' => 'book-half',     'titulo' => 'Excelencia Académica', 'desc' => 'Comprometidos con los más altos estándares educativos y la mejora continua.',                         'color' => 'primary'],
            ['icon' => 'shield-check',  'titulo' => 'Tradición',            'desc' => 'Más de 190 años de historia formando generaciones de líderes trujillanos.',                          'color' => 'success'],
            ['icon' => 'lightbulb-fill','titulo' => 'Innovación',           'desc' => 'Incorporamos tecnología y metodologías modernas para una educación del siglo XXI.',                  'color' => 'warning'],
            ['icon' => 'people-fill',   'titulo' => 'Inclusión',            'desc' => 'Valoramos la diversidad y garantizamos oportunidades educativas para todos.',                         'color' => 'info'],
            ['icon' => 'star-fill',     'titulo' => 'Liderazgo',            'desc' => 'Formamos líderes con pensamiento crítico y compromiso social.',                                       'color' => 'dark'],
        ];
    }

    private function defaultNormas(): array
    {
        return [
            'Respetar a todos los miembros de la comunidad educativa',
            'Cumplir con puntualidad y responsabilidad nuestros deberes',
            'Cuidar las instalaciones y materiales educativos',
            'Mantener un ambiente de convivencia armónica y pacífica',
            'Practicar la honestidad en todas nuestras acciones',
            'Valorar y respetar la diversidad cultural',
            'Resolver conflictos mediante el diálogo y la mediación',
            'Contribuir al cuidado del medio ambiente',
        ];
    }
}
