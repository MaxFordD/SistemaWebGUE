<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ComiteDirectivoController extends Controller
{
    /**
     * Display a listing of the resource (Admin).
     */
    public function index()
    {
        try {
            // Listar solo directivos activos
            $directivos = collect(DB::select('EXEC sp_ComiteDirectivo_Listar @solo_activos = 1'));

            return view('admin.comite-directivo.index', compact('directivos'));
        } catch (\Exception $e) {
            Log::error('Error al listar directivos (admin): ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Error al cargar los directivos.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.comite-directivo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:200',
            'cargo'           => 'required|string|max:100',
            'grado_cargo'     => 'nullable|string|max:100',
            'foto'            => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'biografia'       => 'nullable|string',
            'orden'           => 'required|integer|min:0',
            'estado'          => 'required|in:A,I',
        ], [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'cargo.required'           => 'El cargo es obligatorio.',
            'orden.required'           => 'El orden es obligatorio.',
            'orden.integer'            => 'El orden debe ser un número.',
            'foto.image'               => 'El archivo debe ser una imagen.',
            'foto.mimes'               => 'Solo se aceptan imágenes JPG, JPEG o PNG.',
            'foto.max'                 => 'La imagen no debe superar 2MB.',
        ]);

        // Subida de foto
        $rutaFoto = null;
        if ($request->hasFile('foto')) {
            try {
                $rutaFoto = $request->file('foto')->store('directivos', 'public');
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Error al subir la foto: ' . $e->getMessage());
            }
        }

        try {
            $sql = "
                DECLARE @resultado INT, @mensaje VARCHAR(200);
                EXEC sp_ComiteDirectivo_Insertar
                    @nombre_completo = ?,
                    @cargo = ?,
                    @grado_cargo = ?,
                    @foto = ?,
                    @biografia = ?,
                    @orden = ?,
                    @estado = ?,
                    @resultado = @resultado OUTPUT,
                    @mensaje = @mensaje OUTPUT;
                SELECT resultado = @resultado, mensaje = @mensaje;
            ";

            $out = DB::select($sql, [
                $request->input('nombre_completo'),
                $request->input('cargo'),
                $request->input('grado_cargo'),
                $rutaFoto,
                $request->input('biografia'),
                $request->input('orden'),
                $request->input('estado'),
            ]);

            $resultado = (int)($out[0]->resultado ?? 0);
            $mensaje   = (string)($out[0]->mensaje ?? 'Sin mensaje');

            if ($resultado <= 0) {
                // Eliminar foto si falló la inserción
                if ($rutaFoto) {
                    Storage::disk('public')->delete($rutaFoto);
                }
                return back()->withInput()->with('error', "No se pudo guardar el directivo: $mensaje");
            }

            return redirect()
                ->route('admin.comite-directivo.index')
                ->with('success', 'Directivo registrado correctamente.');

        } catch (\Exception $e) {
            // Eliminar foto si falló todo
            if ($rutaFoto) {
                Storage::disk('public')->delete($rutaFoto);
            }
            Log::error('Error al crear directivo: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al guardar el directivo.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $resultado = DB::select('EXEC sp_ComiteDirectivo_ObtenerPorId ?', [(int)$id]);

            if (empty($resultado)) {
                return redirect()->route('admin.comite-directivo.index')->with('error', 'Directivo no encontrado.');
            }

            $directivo = (object)$resultado[0];

            return view('admin.comite-directivo.edit', compact('directivo'));
        } catch (\Exception $e) {
            Log::error('Error al cargar directivo para editar: ' . $e->getMessage());
            return redirect()->route('admin.comite-directivo.index')->with('error', 'Error al cargar el directivo.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:200',
            'cargo'           => 'required|string|max:100',
            'grado_cargo'     => 'nullable|string|max:100',
            'foto'            => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'biografia'       => 'nullable|string',
            'orden'           => 'required|integer|min:0',
            'estado'          => 'required|in:A,I',
        ], [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'cargo.required'           => 'El cargo es obligatorio.',
            'orden.required'           => 'El orden es obligatorio.',
            'foto.image'               => 'El archivo debe ser una imagen.',
            'foto.mimes'               => 'Solo se aceptan imágenes JPG, JPEG o PNG.',
            'foto.max'                 => 'La imagen no debe superar 2MB.',
        ]);

        // Obtener directivo actual para manejar foto
        $directivoActual = DB::select('EXEC sp_ComiteDirectivo_ObtenerPorId ?', [(int)$id]);
        if (empty($directivoActual)) {
            return redirect()->route('admin.comite-directivo.index')->with('error', 'Directivo no encontrado.');
        }

        $fotoActual = $directivoActual[0]->foto ?? null;
        $nuevaFoto = $fotoActual;

        // Si se sube nueva foto
        if ($request->hasFile('foto')) {
            try {
                $nuevaFoto = $request->file('foto')->store('directivos', 'public');
                // Eliminar foto anterior si existe
                if ($fotoActual && Storage::disk('public')->exists($fotoActual)) {
                    Storage::disk('public')->delete($fotoActual);
                }
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Error al subir la foto: ' . $e->getMessage());
            }
        }

        try {
            $sql = "
                DECLARE @resultado INT, @mensaje VARCHAR(200);
                EXEC sp_ComiteDirectivo_Actualizar
                    @directivo_id = ?,
                    @nombre_completo = ?,
                    @cargo = ?,
                    @grado_cargo = ?,
                    @foto = ?,
                    @biografia = ?,
                    @orden = ?,
                    @estado = ?,
                    @resultado = @resultado OUTPUT,
                    @mensaje = @mensaje OUTPUT;
                SELECT resultado = @resultado, mensaje = @mensaje;
            ";

            $out = DB::select($sql, [
                (int)$id,
                $request->input('nombre_completo'),
                $request->input('cargo'),
                $request->input('grado_cargo'),
                $nuevaFoto,
                $request->input('biografia'),
                $request->input('orden'),
                $request->input('estado'),
            ]);

            $resultado = (int)($out[0]->resultado ?? 0);
            $mensaje   = (string)($out[0]->mensaje ?? 'Sin mensaje');

            if ($resultado <= 0) {
                // Si falló y había nueva foto, eliminarla
                if ($request->hasFile('foto') && $nuevaFoto !== $fotoActual) {
                    Storage::disk('public')->delete($nuevaFoto);
                }
                return back()->withInput()->with('error', "Error al actualizar: $mensaje");
            }

            return redirect()
                ->route('admin.comite-directivo.index')
                ->with('success', 'Directivo actualizado correctamente.');

        } catch (\Exception $e) {
            // Si falló y había nueva foto, eliminarla
            if ($request->hasFile('foto') && $nuevaFoto !== $fotoActual) {
                Storage::disk('public')->delete($nuevaFoto);
            }
            Log::error('Error al actualizar directivo: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar el directivo.');
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy($id)
    {
        try {
            $sql = "
                DECLARE @resultado INT, @mensaje VARCHAR(200);
                EXEC sp_ComiteDirectivo_Eliminar
                    @directivo_id = ?,
                    @resultado = @resultado OUTPUT,
                    @mensaje = @mensaje OUTPUT;
                SELECT resultado = @resultado, mensaje = @mensaje;
            ";

            $out = DB::select($sql, [(int)$id]);
            $resultado = (int)($out[0]->resultado ?? 0);
            $mensaje   = (string)($out[0]->mensaje ?? 'Sin mensaje');

            if ($resultado != 1) {
                return redirect()->route('admin.comite-directivo.index')->with('error', "Error al desactivar: $mensaje");
            }

            return redirect()->route('admin.comite-directivo.index')->with('success', 'Directivo desactivado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar directivo: ' . $e->getMessage());
            return redirect()->route('admin.comite-directivo.index')->with('error', 'Error al desactivar el directivo.');
        }
    }

    /**
     * Get inactive directivos (for modal).
     */
    public function inactivos()
    {
        try {
            // Listar solo directivos inactivos
            $inactivos = collect(DB::select('EXEC sp_ComiteDirectivo_Listar @solo_activos = 0'))
                ->filter(function ($directivo) {
                    return $directivo->estado === 'I';
                });

            return response()->json([
                'success' => true,
                'data' => $inactivos->values()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar directivos inactivos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los directivos inactivos.'
            ], 500);
        }
    }

    /**
     * Restore an inactive directivo.
     */
    public function restore($id)
    {
        try {
            // Actualizar el estado a 'A' (Activo)
            $sql = "
                UPDATE Comite_Directivo
                SET estado = 'A'
                WHERE directivo_id = ? AND estado = 'I';

                SELECT @@ROWCOUNT as affected;
            ";

            $result = DB::select($sql, [(int)$id]);
            $affected = (int)($result[0]->affected ?? 0);

            if ($affected === 0) {
                return redirect()->route('admin.comite-directivo.index')->with('error', 'El directivo no existe o ya está activo.');
            }

            return redirect()->route('admin.comite-directivo.index')->with('success', 'Directivo reactivado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al restaurar directivo: ' . $e->getMessage());
            return redirect()->route('admin.comite-directivo.index')->with('error', 'Error al reactivar el directivo.');
        }
    }
}
