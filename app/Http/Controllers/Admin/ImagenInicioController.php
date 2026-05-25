<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImagenInicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImagenInicioController extends Controller
{
    public function index()
    {
        $carousel = ImagenInicio::where('seccion', 'carousel')->orderBy('orden')->get();
        $talleres = ImagenInicio::where('seccion', 'taller')->orderBy('orden')->get();

        return view('admin.imagenes-inicio.index', compact('carousel', 'talleres'));
    }

    public function update(Request $request, $id)
    {
        $imagen = ImagenInicio::findOrFail($id);

        $rules = ['alt' => 'required|string|max:255'];

        if ($imagen->seccion === 'taller') {
            $rules['titulo']      = 'required|string|max:100';
            $rules['descripcion'] = 'required|string|max:255';
            $rules['icono']       = 'required|string|max:50';
        }

        if ($request->hasFile('foto')) {
            $rules['foto'] = 'image|mimes:jpg,jpeg,png,webp|max:2048';
        }

        $request->validate($rules, [
            'alt.required'         => 'El texto alternativo es obligatorio.',
            'titulo.required'      => 'El título es obligatorio.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'icono.required'       => 'El ícono es obligatorio.',
            'foto.image'           => 'El archivo debe ser una imagen.',
            'foto.mimes'           => 'Solo se aceptan JPG, PNG o WEBP.',
            'foto.max'             => 'La imagen no debe superar 2 MB.',
        ]);

        if ($request->hasFile('foto')) {
            // Eliminar imagen anterior si fue subida (está en storage)
            if (str_starts_with($imagen->ruta, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $imagen->ruta));
            }

            $path = $request->file('foto')->store('imagenes_inicio', 'public');
            $imagen->ruta = 'storage/' . $path;
        }

        $imagen->alt = $request->alt;

        if ($imagen->seccion === 'taller') {
            $imagen->titulo      = $request->titulo;
            $imagen->descripcion = $request->descripcion;
            $imagen->icono       = $request->icono;
        }

        $imagen->save();

        return redirect()->route('admin.imagenes-inicio.index')
            ->with('success', 'Imagen actualizada correctamente.');
    }
}
