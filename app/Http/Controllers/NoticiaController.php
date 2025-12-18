<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NoticiaController extends Controller
{
    // LISTAR (usa sp_Noticia_ListarActivas para solo noticias activas)
    public function index(Request $request)
    {
        try {
            // Trae solo noticias activas ordenadas desc por fecha
            $rows = collect(DB::select('EXEC sp_Noticia_ListarActivas'));

            // Convertir stdClass a instancias del modelo Noticia para usar accessors
            $rows = $rows->map(function($row) {
                $noticia = new Noticia();
                foreach (get_object_vars($row) as $key => $value) {
                    $noticia->$key = $value;
                }
                $noticia->exists = true; // Marcar como existente en BD
                return $noticia;
            });

            // Paginación en memoria
            $perPage = 10;
            $currentPage = max(1, (int)$request->get('page', 1));
            $items = $rows->forPage($currentPage, $perPage)->values();

            $noticias = new LengthAwarePaginator(
                $items,
                $rows->count(),
                $perPage,
                $currentPage,
                ['path' => url()->current(), 'query' => $request->query()]
            );

            return view('noticias.index', compact('noticias'));
        } catch (\Exception $e) {
            Log::error('Error al listar noticias: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Error al cargar las noticias.');
        }
    }

    // VER DETALLE (sp_Noticia_ObtenerPorId)
    public function show($id)
    {
        try {
            $resultado = DB::select('EXEC sp_Noticia_ObtenerPorId ?', [(int)$id]);

            if (empty($resultado)) {
                return redirect()->route('noticias.index')->with('error', 'Noticia no encontrada.');
            }

            // Convertir stdClass a instancia del modelo Noticia para usar accessors
            $noticia = new Noticia();
            foreach (get_object_vars($resultado[0]) as $key => $value) {
                $noticia->$key = $value;
            }
            $noticia->exists = true; // Marcar como existente en BD

            // Asegurar que el autor esté disponible
            if (empty($noticia->autor) && !empty($noticia->nombre_usuario)) {
                $noticia->autor = $noticia->nombre_usuario;
            }

            return response()
                ->view('noticias.show', compact('noticia'))
                ->header('Content-Type', 'text/html; charset=UTF-8');
        } catch (\Exception $e) {
            Log::error('Error al mostrar noticia ID ' . $id . ': ' . $e->getMessage());
            return redirect()->route('noticias.index')->with('error', 'Error al cargar la noticia.');
        }
    }

    // FORM CREAR
    public function create()
    {
        return view('noticias.create');
    }

    // GUARDAR (sp_Noticia_Insertar + sp_Bitacora_Insertar)
    public function store(Request $request)
    {
        $request->validate([
            'titulo'      => 'required|string|max:200',
            'contenido'   => 'required|string',
            'archivos'    => 'nullable|array',
            'archivos.*'  => 'file|mimes:jpeg,jpg,png,gif,pdf,doc,docx,xls,xlsx|max:2048',
        ], [
            'titulo.required'      => 'El título es obligatorio.',
            'contenido.required'   => 'El contenido es obligatorio.',
            'archivos.*.file'      => 'Cada archivo debe ser válido.',
            'archivos.*.mimes'     => 'Formato no permitido. Solo se aceptan imágenes, PDF, Word y Excel.',
            'archivos.*.max'       => 'Cada archivo no debe superar 2MB.',
        ]);

        $user = auth()->user();
        $usuarioId = $user->usuario_id ?? $user->id ?? null;
        if (!$usuarioId) {
            return back()->withInput()->with('error', 'No se pudo identificar al usuario autenticado.');
        }

        // Subida de múltiples archivos a storage/app/public/noticias
        $rutasArchivos = [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                try {
                    $ruta = $archivo->store('noticias', 'public');

                    // Verificar que el archivo realmente se guardó
                    if (!Storage::disk('public')->exists($ruta)) {
                        throw new \Exception("El archivo no se guardó correctamente: $ruta");
                    }

                    $rutasArchivos[] = $ruta;
                    Log::info("Archivo guardado exitosamente: $ruta");
                } catch (\Exception $e) {
                    // Si falla la subida, eliminar archivos ya subidos
                    foreach ($rutasArchivos as $rutaEliminar) {
                        Storage::disk('public')->delete($rutaEliminar);
                    }
                    Log::error('Error al subir archivo: ' . $e->getMessage());
                    return back()->withInput()->with('error', 'Error al subir archivo: ' . $e->getMessage());
                }
            }
        }

        // Concatenar rutas con separador ';' (sin espacios para consistencia)
        $rutaImagenConcatenada = !empty($rutasArchivos) ? implode(';', $rutasArchivos) : null;

        // Ejecutamos el SP con OUTPUT
        $titulo    = $request->input('titulo');
        $contenido = $request->input('contenido');

        try {
            $sql = "
                DECLARE @resultado INT, @mensaje VARCHAR(200);
                EXEC sp_Noticia_Insertar
                    @titulo = ?,
                    @contenido = ?,
                    @imagen = ?,
                    @usuario_id = ?,
                    @resultado = @resultado OUTPUT,
                    @mensaje = @mensaje OUTPUT;
                SELECT resultado = @resultado, mensaje = @mensaje;
            ";

            $out = DB::select($sql, [$titulo, $contenido, $rutaImagenConcatenada, $usuarioId]);
            $resultado = (int)($out[0]->resultado ?? 0);
            $mensaje   = (string)($out[0]->mensaje ?? 'Sin mensaje');

            if ($resultado <= 0) {
                // Eliminar archivos subidos si falló la inserción
                foreach ($rutasArchivos as $ruta) {
                    Storage::disk('public')->delete($ruta);
                }
                return back()->withInput()->with('error', "No se pudo guardar la noticia: $mensaje");
            }

            $noticiaId = $resultado;

            // Registrar en Bitácora
            try {
                $sqlBit = "
                    DECLARE @res INT, @msg VARCHAR(200);
                    EXEC sp_Bitacora_Insertar
                        @usuario_id = ?,
                        @accion = ?,
                        @resultado = @res OUTPUT,
                        @mensaje   = @msg OUTPUT;
                    SELECT res = @res, msg = @msg;
                ";
                $cantidadArchivos = count($rutasArchivos);
                $accion = "Creó la noticia ID {$noticiaId}" . ($cantidadArchivos > 0 ? " con {$cantidadArchivos} archivo(s)" : "");
                DB::select($sqlBit, [$usuarioId, $accion]);
            } catch (\Exception $e) {
                // Si falla la bitácora, solo loguear pero continuar
                Log::warning('Error al registrar en bitácora: ' . $e->getMessage());
            }

            return redirect()
                ->route('noticias.show', $noticiaId)
                ->with('success', 'Noticia creada correctamente' . (count($rutasArchivos) > 0 ? " con " . count($rutasArchivos) . " archivo(s) adjunto(s)." : '.'));
                
        } catch (\Exception $e) {
            // Si falla todo, eliminar archivos subidos
            foreach ($rutasArchivos as $ruta) {
                Storage::disk('public')->delete($ruta);
            }
            Log::error('Error al crear noticia: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al guardar la noticia: ' . $e->getMessage());
        }
    }

    // EDITAR FORM
    public function edit($id)
    {
        try {
            $resultado = DB::select('EXEC sp_Noticia_ObtenerPorId ?', [(int)$id]);

            if (empty($resultado)) {
                return redirect()->route('noticias.index')->with('error', 'Noticia no encontrada.');
            }

            $noticia = (object)$resultado[0];
            
            return view('noticias.edit', compact('noticia'));
        } catch (\Exception $e) {
            Log::error('Error al cargar noticia para editar: ' . $e->getMessage());
            return redirect()->route('noticias.index')->with('error', 'Error al cargar la noticia.');
        }
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo'      => 'required|string|max:200',
            'contenido'   => 'required|string',
            'estado'      => 'required|in:A,I',
            'archivos'    => 'nullable|array',
            'archivos.*'  => 'file|mimes:jpeg,jpg,png,gif,pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        // Obtener noticia actual para manejar archivos existentes
        $noticiaActual = DB::select('EXEC sp_Noticia_ObtenerPorId ?', [(int)$id]);
        if (empty($noticiaActual)) {
            return redirect()->route('noticias.index')->with('error', 'Noticia no encontrada.');
        }

        $archivosExistentes = $noticiaActual[0]->imagen ?? '';
        
        // Si se suben nuevos archivos
        $rutasArchivos = [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                try {
                    $ruta = $archivo->store('noticias', 'public');

                    // Verificar que el archivo realmente se guardó
                    if (!Storage::disk('public')->exists($ruta)) {
                        throw new \Exception("El archivo no se guardó correctamente: $ruta");
                    }

                    $rutasArchivos[] = $ruta;
                    Log::info("Archivo actualizado exitosamente: $ruta");
                } catch (\Exception $e) {
                    foreach ($rutasArchivos as $rutaEliminar) {
                        Storage::disk('public')->delete($rutaEliminar);
                    }
                    Log::error('Error al subir archivo en update: ' . $e->getMessage());
                    return back()->withInput()->with('error', 'Error al subir archivo: ' . $e->getMessage());
                }
            }
        }

        // Combinar archivos existentes con nuevos
        $todasLasRutas = $archivosExistentes;
        if (!empty($rutasArchivos)) {
            $todasLasRutas = $archivosExistentes 
                ? $archivosExistentes . ';' . implode(';', $rutasArchivos)
                : implode(';', $rutasArchivos);
        }

        try {
            $sql = "
                DECLARE @resultado BIT, @mensaje VARCHAR(200);
                EXEC sp_Noticia_Actualizar
                    @noticia_id = ?,
                    @titulo = ?,
                    @contenido = ?,
                    @imagen = ?,
                    @estado = ?,
                    @resultado = @resultado OUTPUT,
                    @mensaje = @mensaje OUTPUT;
                SELECT resultado = @resultado, mensaje = @mensaje;
            ";

            $out = DB::select($sql, [
                (int)$id,
                $request->input('titulo'),
                $request->input('contenido'),
                $todasLasRutas,
                $request->input('estado')
            ]);

            $resultado = (int)($out[0]->resultado ?? 0);
            $mensaje = (string)($out[0]->mensaje ?? 'Sin mensaje');

            if ($resultado != 1) {
                // Eliminar archivos nuevos si falló
                foreach ($rutasArchivos as $ruta) {
                    Storage::disk('public')->delete($ruta);
                }
                return back()->withInput()->with('error', "Error al actualizar: $mensaje");
            }

            // Registrar en bitácora
            $user = auth()->user();
            $usuarioId = $user->usuario_id ?? $user->id ?? null;
            if ($usuarioId) {
                try {
                    $accion = "Actualizó la noticia ID {$id}";
                    DB::statement('EXEC sp_Bitacora_Insertar ?, ?, @resultado OUTPUT, @mensaje OUTPUT', 
                        [$usuarioId, $accion]);
                } catch (\Exception $e) {
                    Log::warning('Error al registrar en bitácora: ' . $e->getMessage());
                }
            }

            return redirect()
                ->route('noticias.show', $id)
                ->with('success', 'Noticia actualizada correctamente.');
                
        } catch (\Exception $e) {
            foreach ($rutasArchivos as $ruta) {
                Storage::disk('public')->delete($ruta);
            }
            Log::error('Error al actualizar noticia: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar la noticia.');
        }
    }

    // ELIMINAR (Lógico)
    public function destroy($id)
    {
        try {
            $sql = "
                DECLARE @resultado BIT, @mensaje VARCHAR(200);
                EXEC sp_Noticia_Eliminar
                    @noticia_id = ?,
                    @resultado = @resultado OUTPUT,
                    @mensaje = @mensaje OUTPUT;
                SELECT resultado = @resultado, mensaje = @mensaje;
            ";

            $out = DB::select($sql, [(int)$id]);
            $resultado = (int)($out[0]->resultado ?? 0);
            $mensaje = (string)($out[0]->mensaje ?? 'Sin mensaje');

            if ($resultado != 1) {
                return redirect()->route('noticias.index')->with('error', "Error al eliminar: $mensaje");
            }

            // Registrar en bitácora
            $user = auth()->user();
            $usuarioId = $user->usuario_id ?? $user->id ?? null;
            if ($usuarioId) {
                try {
                    $accion = "Eliminó la noticia ID {$id}";
                    DB::statement('EXEC sp_Bitacora_Insertar ?, ?, @resultado OUTPUT, @mensaje OUTPUT', 
                        [$usuarioId, $accion]);
                } catch (\Exception $e) {
                    Log::warning('Error al registrar en bitácora: ' . $e->getMessage());
                }
            }

            return redirect()->route('noticias.index')->with('success', 'Noticia eliminada correctamente.');
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar noticia: ' . $e->getMessage());
            return redirect()->route('noticias.index')->with('error', 'Error al eliminar la noticia.');
        }
    }
}