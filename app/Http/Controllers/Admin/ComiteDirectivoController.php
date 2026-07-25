<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ArchivoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ComiteDirectivoController extends Controller
{

    /**
     * Opciones fijas para los selects de Cargo y Grado a Cargo del comité directivo.
     */
    protected array $cargoOpciones = [
        'Director General',
        'Subdirector',
        'Coordinador Académico',
        'Coordinador de Tutoría',
        'Docente',
        'Auxiliar de Educación',
        'Secretaria',
        'Psicólogo(a)',
        'Bibliotecario(a)',
    ];

    protected array $gradoOpciones = [
        'Todos los grados',
        'Nivel Primaria',
        'Nivel Secundaria',
        '1° Primaria', '2° Primaria', '3° Primaria', '4° Primaria', '5° Primaria', '6° Primaria',
        '1° Secundaria', '2° Secundaria', '3° Secundaria', '4° Secundaria', '5° Secundaria',
    ];

    /**
     * Display a listing of the resource (Admin).
     */
    public function index()
    {
        try {
            // Listar solo directivos activos
            $directivos = collect(DB::select('CALL sp_ComiteDirectivo_Listar(?)', [1]));

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
        return view('admin.comite-directivo.create', [
            'cargoOpciones' => $this->cargoOpciones,
            'gradoOpciones' => $this->gradoOpciones,
        ]);
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
                app(ArchivoService::class)->optimizarImagen($rutaFoto, 800);
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Error al subir la foto: ' . $e->getMessage());
            }
        }

        try {
            // Inicializar variables de salida
            DB::statement('SET @resultado = 0, @mensaje = ""');

            // Llamar al procedimiento
            DB::statement('CALL sp_ComiteDirectivo_Insertar(?, ?, ?, ?, ?, ?, ?, @resultado, @mensaje)', [
                $request->input('nombre_completo'),
                $request->input('cargo'),
                $request->input('grado_cargo'),
                $rutaFoto,
                $request->input('biografia'),
                $request->input('orden'),
                $request->input('estado'),
            ]);

            // Obtener resultados
            $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
            $resultado = (int)($out[0]->resultado ?? 0);
            $mensaje   = (string)($out[0]->mensaje ?? 'Sin mensaje');

            if ($resultado <= 0) {
                // Eliminar foto si falló la inserción
                if ($rutaFoto) {
                    Storage::disk('public')->delete($rutaFoto);
                }
                return back()->withInput()->with('error', "No se pudo guardar el directivo: $mensaje");
            }

            $this->registrarBitacora('comite_directivo', "Registró al directivo {$request->input('nombre_completo')} ({$request->input('cargo')})");

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
            $resultado = DB::select('CALL sp_ComiteDirectivo_ObtenerPorId(?)', [(int)$id]);

            if (empty($resultado)) {
                return redirect()->route('admin.comite-directivo.index')->with('error', 'Directivo no encontrado.');
            }

            $directivo = (object)$resultado[0];

            return view('admin.comite-directivo.edit', [
                'directivo'     => $directivo,
                'cargoOpciones' => $this->cargoOpciones,
                'gradoOpciones' => $this->gradoOpciones,
            ]);
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
        $directivoActual = DB::select('CALL sp_ComiteDirectivo_ObtenerPorId(?)', [(int)$id]);
        if (empty($directivoActual)) {
            return redirect()->route('admin.comite-directivo.index')->with('error', 'Directivo no encontrado.');
        }

        $fotoActual = $directivoActual[0]->foto ?? null;
        $nuevaFoto = $fotoActual;

        // Si se sube nueva foto
        if ($request->hasFile('foto')) {
            try {
                $nuevaFoto = $request->file('foto')->store('directivos', 'public');
                app(ArchivoService::class)->optimizarImagen($nuevaFoto, 800);
                // Eliminar foto anterior si existe
                if ($fotoActual && Storage::disk('public')->exists($fotoActual)) {
                    Storage::disk('public')->delete($fotoActual);
                }
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Error al subir la foto: ' . $e->getMessage());
            }
        }

        try {
            // Inicializar variables de salida
            DB::statement('SET @resultado = 0, @mensaje = ""');

            // Llamar al procedimiento
            DB::statement('CALL sp_ComiteDirectivo_Actualizar(?, ?, ?, ?, ?, ?, ?, ?, @resultado, @mensaje)', [
                (int)$id,
                $request->input('nombre_completo'),
                $request->input('cargo'),
                $request->input('grado_cargo'),
                $nuevaFoto,
                $request->input('biografia'),
                $request->input('orden'),
                $request->input('estado'),
            ]);

            // Obtener resultados
            $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
            $resultado = (int)($out[0]->resultado ?? 0);
            $mensaje   = (string)($out[0]->mensaje ?? 'Sin mensaje');

            if ($resultado <= 0) {
                // Si falló y había nueva foto, eliminarla
                if ($request->hasFile('foto') && $nuevaFoto !== $fotoActual) {
                    Storage::disk('public')->delete($nuevaFoto);
                }
                return back()->withInput()->with('error', "Error al actualizar: $mensaje");
            }

            $this->registrarBitacora('comite_directivo', "Actualizó al directivo {$request->input('nombre_completo')} ({$request->input('cargo')})");

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
            $directivoActual = collect(DB::select('CALL sp_ComiteDirectivo_ObtenerPorId(?)', [(int)$id]))->first();

            // Inicializar variables de salida
            DB::statement('SET @resultado = 0, @mensaje = ""');

            // Llamar al procedimiento
            DB::statement('CALL sp_ComiteDirectivo_Eliminar(?, @resultado, @mensaje)', [(int)$id]);

            // Obtener resultados
            $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
            $resultado = (int)($out[0]->resultado ?? 0);
            $mensaje   = (string)($out[0]->mensaje ?? 'Sin mensaje');

            if ($resultado != 1) {
                return redirect()->route('admin.comite-directivo.index')->with('error', "Error al desactivar: $mensaje");
            }

            $nombre = $directivoActual->nombre_completo ?? "directivo #{$id}";
            $this->registrarBitacora('comite_directivo', "Desactivó al directivo {$nombre}");

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
            $inactivos = collect(DB::select('CALL sp_ComiteDirectivo_Listar(?)', [0]))
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
     * Permanently delete an inactive directivo.
     */
    public function forceDelete($id)
    {
        try {
            $directivo = DB::select('CALL sp_ComiteDirectivo_ObtenerPorId(?)', [(int)$id]);

            if (empty($directivo) || $directivo[0]->estado !== 'I') {
                return response()->json(['success' => false, 'message' => 'El directivo no existe o no está inactivo.'], 422);
            }

            $foto   = $directivo[0]->foto ?? null;
            $nombre = $directivo[0]->nombre_completo ?? "directivo #{$id}";

            DB::statement('DELETE FROM Comite_Directivo WHERE directivo_id = ?', [(int)$id]);

            if ($foto && Storage::disk('public')->exists($foto)) {
                Storage::disk('public')->delete($foto);
            }

            $this->registrarBitacora('comite_directivo', "Eliminó permanentemente al directivo {$nombre}");

            return response()->json(['success' => true, 'message' => 'Directivo eliminado permanentemente.']);

        } catch (\Exception $e) {
            Log::error('Error al eliminar permanentemente directivo: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el directivo.'], 500);
        }
    }

    /**
     * Restore an inactive directivo.
     */
    public function restore($id)
    {
        try {
            // Actualizar el estado a 'A' (Activo)
            DB::statement("
                UPDATE Comite_Directivo
                SET estado = 'A'
                WHERE directivo_id = ? AND estado = 'I'
            ", [(int)$id]);

            // Obtener el número de filas afectadas
            $affected = DB::select('SELECT ROW_COUNT() as affected');
            $rowCount = (int)($affected[0]->affected ?? 0);

            if ($rowCount === 0) {
                return redirect()->route('admin.comite-directivo.index')->with('error', 'El directivo no existe o ya está activo.');
            }

            $this->registrarBitacora('comite_directivo', "Reactivó al directivo #{$id}");

            return redirect()->route('admin.comite-directivo.index')->with('success', 'Directivo reactivado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al restaurar directivo: ' . $e->getMessage());
            return redirect()->route('admin.comite-directivo.index')->with('error', 'Error al reactivar el directivo.');
        }
    }
}
