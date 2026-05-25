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

    public function store(Request $request)
    {
        $rules = [
            'seccion' => 'required|in:carousel,taller',
            'foto'    => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'alt'     => 'required|string|max:255',
        ];

        if ($request->seccion === 'taller') {
            $rules['titulo']      = 'required|string|max:100';
            $rules['descripcion'] = 'required|string|max:255';
            $rules['icono']       = 'required|string|max:50';
        }

        $request->validate($rules, [
            'foto.required'        => 'La imagen es obligatoria.',
            'foto.image'           => 'El archivo debe ser una imagen.',
            'foto.mimes'           => 'Solo se aceptan JPG, PNG o WEBP.',
            'foto.max'             => 'La imagen no debe superar 2 MB.',
            'alt.required'         => 'El texto alternativo es obligatorio.',
            'titulo.required'      => 'El título es obligatorio.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'icono.required'       => 'El ícono es obligatorio.',
        ]);

        $path  = $request->file('foto')->store('imagenes_inicio', 'public');
        $orden = (ImagenInicio::where('seccion', $request->seccion)->max('orden') ?? 0) + 1;

        ImagenInicio::create([
            'seccion'     => $request->seccion,
            'orden'       => $orden,
            'ruta'        => 'storage/' . $path,
            'alt'         => $request->alt,
            'titulo'      => $request->seccion === 'taller' ? $request->titulo      : null,
            'descripcion' => $request->seccion === 'taller' ? $request->descripcion : null,
            'icono'       => $request->seccion === 'taller' ? $request->icono       : null,
            'activo'      => 1,
        ]);

        return redirect()->route('admin.imagenes-inicio.index')
            ->with('success', 'Imagen agregada correctamente.');
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

    public function destroy($id)
    {
        $imagen = ImagenInicio::findOrFail($id);

        if (str_starts_with($imagen->ruta, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $imagen->ruta));
        }

        $imagen->delete();

        return redirect()->route('admin.imagenes-inicio.index')
            ->with('success', 'Imagen eliminada correctamente.');
    }
}
