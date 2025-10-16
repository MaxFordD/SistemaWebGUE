<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class NoticiaController extends Controller
{
    // LISTAR (usa sp_Noticia_Listar y pagina en PHP para no pelear con OUTPUT de SQLSRV)
    public function index(Request $request)
    {
        // Trae todas las activas ordenadas desc por fecha (ya lo hace el SP)
        $rows = collect(DB::select('EXEC sp_Noticia_Listar'));

        // Paginación en memoria (simple, efectiva)
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
    }

    // VER DETALLE (sp_Noticia_ObtenerPorId)
    public function show($id)
    {
        $resultado = DB::select('EXEC sp_Noticia_ObtenerPorId ?', [(int)$id]);

        if (empty($resultado)) {
            return redirect()->route('noticias.index')->with('error', 'Noticia no encontrada.');
        }

        // Normalizamos fecha con Carbon (viene como string)
        $noticia = (object)$resultado[0];
        if (!empty($noticia->fecha_publicacion)) {
            $noticia->fecha_publicacion = Carbon::parse($noticia->fecha_publicacion);
        }

        return view('noticias.show', compact('noticia'));
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
            'titulo'    => 'required|string|max:200',
            'contenido' => 'required|string',
            'imagen'    => 'nullable|image|max:2048',
        ], [
            'titulo.required'    => 'El título es obligatorio.',
            'contenido.required' => 'El contenido es obligatorio.',
            'imagen.image'       => 'El archivo debe ser una imagen.',
            'imagen.max'         => 'La imagen no debe superar 2MB.',
        ]);

        $user = auth()->user();
        $usuarioId = $user->usuario_id ?? $user->id ?? null;
        if (!$usuarioId) {
            return back()->withInput()->with('error', 'No se pudo identificar al usuario autenticado.');
        }

        // Subida de imagen (opcional) a storage/app/public/noticias
        $rutaImagen = null;
        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('noticias', 'public');
        }

        // Ejecutamos el SP con OUTPUT (técnica: DECLARE -> EXEC -> SELECT)
        $titulo    = $request->input('titulo');
        $contenido = $request->input('contenido');

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

        $out = DB::select($sql, [$titulo, $contenido, $rutaImagen, $usuarioId]);
        $resultado = (int)($out[0]->resultado ?? 0);
        $mensaje   = (string)($out[0]->mensaje ?? 'Sin mensaje');

        if ($resultado <= 0) {
            return back()->withInput()->with('error', "No se pudo guardar la noticia: $mensaje");
        }

        $noticiaId = $resultado;

        // Registrar en Bitácora
        $sqlBit = "
            DECLARE @res INT, @msg VARCHAR(200);
            EXEC sp_Bitacora_Insertar
                @usuario_id = ?,
                @accion = ?,
                @resultado = @res OUTPUT,
                @mensaje   = @msg OUTPUT;
            SELECT res = @res, msg = @msg;
        ";
        $accion = "Creó la noticia ID {$noticiaId}";
        DB::select($sqlBit, [$usuarioId, $accion]);

        return redirect()
            ->route('noticias.show', $noticiaId)
            ->with('success', 'Noticia creada correctamente.');
    }
}
