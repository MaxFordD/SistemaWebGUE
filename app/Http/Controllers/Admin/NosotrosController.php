<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ArchivoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NosotrosController extends Controller
{
    private function getData(): array
    {
        return DB::table('NosotrosContenido')->get()->pluck('valor', 'clave')->toArray();
    }

    public function edit()
    {
        try {
            $data    = $this->getData();
            $pilares = json_decode($data['pilares'] ?? '[]', true) ?: [];
            $normas  = json_decode($data['normas']  ?? '[]', true) ?: [];

            $historiaImagenes = json_decode($data['historia_imagenes'] ?? '[]', true) ?: [];
            if (empty($historiaImagenes) && !empty($data['historia_imagen'])) {
                $historiaImagenes = [$data['historia_imagen']];
            }

            return view('admin.nosotros.edit', compact('data', 'pilares', 'normas', 'historiaImagenes'));
        } catch (\Exception $e) {
            Log::error('Nosotros edit: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Error al cargar la página Nosotros.');
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'historia_titulo' => 'required|string|max:200',
            'historia_p1'     => 'required|string',
            'historia_p2'     => 'required|string',
            'mision'          => 'required|string',
            'vision'          => 'required|string',
        ]);

        try {
            $actual = $this->getData();

            // Imágenes de "Nuestra Historia" (carrusel): las que el admin dejó
            // marcadas para conservar, más las nuevas que suba en este envío.
            $imagenesAnteriores = json_decode($actual['historia_imagenes'] ?? '[]', true) ?: [];
            if (empty($imagenesAnteriores) && !empty($actual['historia_imagen'])) {
                $imagenesAnteriores = [$actual['historia_imagen']];
            }

            $conservar = array_values(array_filter((array) $request->input('historia_imagenes_actuales', [])));

            $nuevas = [];
            if ($request->hasFile('historia_imagenes_nuevas')) {
                foreach ($request->file('historia_imagenes_nuevas') as $archivo) {
                    $ruta = $archivo->store('nosotros', 'public');
                    app(ArchivoService::class)->optimizarImagen($ruta);
                    $nuevas[] = $ruta;
                }
            }

            $imagenes = array_values(array_unique(array_merge($conservar, $nuevas)));

            // Borrar del disco las que ya no se van a usar
            foreach ($imagenesAnteriores as $img) {
                if (!in_array($img, $imagenes, true) && !str_starts_with($img, 'images/') && Storage::disk('public')->exists($img)) {
                    Storage::disk('public')->delete($img);
                }
            }

            if (empty($imagenes)) {
                $imagenes = ['images/gue.jpg'];
            }
            $imagen = $imagenes[0];

            // Pilares
            $pilarIconos  = $request->input('pilar_icon',   []);
            $pilarTitulos = $request->input('pilar_titulo', []);
            $pilarDescs   = $request->input('pilar_desc',   []);
            $pilarColores = $request->input('pilar_color',  []);
            $pilares = [];
            foreach ($pilarTitulos as $i => $titulo) {
                if (!empty(trim($titulo))) {
                    $pilares[] = [
                        'icon'   => $pilarIconos[$i]  ?? 'star-fill',
                        'titulo' => trim($titulo),
                        'desc'   => trim($pilarDescs[$i]   ?? ''),
                        'color'  => $pilarColores[$i] ?? 'primary',
                    ];
                }
            }

            // Normas
            $normas = array_values(array_filter(
                array_map('trim', $request->input('normas', [])),
                fn($n) => $n !== ''
            ));

            $campos = [
                'historia_titulo'   => $request->input('historia_titulo'),
                'historia_p1'       => $request->input('historia_p1'),
                'historia_p2'       => $request->input('historia_p2'),
                'historia_imagen'   => $imagen,
                'historia_imagenes' => json_encode($imagenes, JSON_UNESCAPED_UNICODE),
                'mision'            => $request->input('mision'),
                'vision'            => $request->input('vision'),
                'pilares'           => json_encode($pilares, JSON_UNESCAPED_UNICODE),
                'normas'            => json_encode($normas,  JSON_UNESCAPED_UNICODE),
            ];

            foreach ($campos as $clave => $valor) {
                DB::table('NosotrosContenido')->updateOrInsert(['clave' => $clave], ['valor' => $valor]);
            }

            return back()->with('success', 'Página "Nosotros" actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Nosotros update: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al guardar los cambios.');
        }
    }
}
