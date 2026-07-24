<?php

namespace App\Http\Controllers;

use App\Models\MesaParte;
use App\Services\ArchivoService;
use App\Jobs\EnviarNotificacionMesaPartes;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MesaPartesController extends Controller
{

    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }
    /**
     * Mostrar el formulario de registro de documentos
     */
    public function create()
    {
        $tipos = DB::table('Tipos_Documento')->orderBy('nombre')->get();
        return view('mesa.create', compact('tipos'));
    }

    /**
     * Guardar un nuevo registro en Mesa de Partes
     */
    public function store(Request $request)
    {
        $request->validate([
            'remitente' => 'required|max:150',
            'asunto' => 'required|max:200',
            'detalle' => 'nullable',
            'tipo_documento_id' => 'required|exists:Tipos_Documento,tipo_id',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|mimes:pdf,docx,jpg,png|max:5120',
            'correo' => 'nullable|email'
        ]);

        // Usar servicio para guardar archivos
        $storedPaths = [];
        $originalNames = [];

        if ($request->hasFile('archivos')) {
            [$storedPaths, $originalNames] = $this->archivoService->guardarMultiples(
                $request->file('archivos'),
                'mesa_partes'
            );
        }

        // Guardar en la base de datos
        $mesa = MesaParte::create([
            'remitente' => $request->remitente,
            'dni' => $request->dni,
            'correo' => $request->correo,
            'asunto' => $request->asunto,
            'detalle' => $request->detalle,
            'archivo' => $this->archivoService->concatenarRutas($storedPaths),
            'tipo_documento_id' => $request->tipo_documento_id,
            'estado' => 'Pendiente',
        ]);

        // Obtener nombre del tipo de documento
        $tipoDocumento = DB::table('Tipos_Documento')
            ->where('tipo_id', $request->tipo_documento_id)
            ->value('nombre');

        // Enviar correos de forma asíncrona
        EnviarNotificacionMesaPartes::dispatch(
            $mesa,
            $storedPaths,
            $originalNames,
            $request->correo,
            $tipoDocumento
        );

        return redirect()->route('mesa.create')->with('success', 'Documento enviado correctamente. Las notificaciones serán enviadas por correo.');
    }

    // Mostrar lista de documentos (para el administrador)
    public function index(Request $request)
    {
        $estado  = $request->get('estado', '');
        $perPage = 15;
        $page    = max(1, (int) $request->get('page', 1));

        $todos = collect(DB::select('CALL sp_MesaPartes_Listar()'));

        if ($estado !== '') {
            $todos = $todos->filter(fn($d) => $d->estado === $estado)->values();
        }

        $documentos = new LengthAwarePaginator(
            $todos->forPage($page, $perPage)->values(),
            $todos->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        $estados = ['Pendiente', 'Aceptado', 'Rechazado'];

        return view('admin.mesa.index', compact('documentos', 'estado', 'estados'));
    }

    // Mostrar detalle de un documento
    public function show($id)
    {
        $documento = DB::select('CALL sp_MesaPartes_ObtenerPorId(?)', [$id]);
        if (empty($documento)) {
            abort(404);
        }
        $doc = $documento[0];

        // Obtener nombre del tipo de documento si el SP no lo devuelve
        if (!isset($doc->tipo_documento) && isset($doc->tipo_documento_id)) {
            $tipo = DB::table('Tipos_Documento')
                ->where('tipo_id', $doc->tipo_documento_id)
                ->value('nombre');
            $doc->tipo_documento = $tipo ?? 'Sin tipo';
        }

        return view('admin.mesa.show', compact('doc'));
    }

    // Actualizar estado (Pendiente / Revisado / Aceptado / Rechazado)
    public function updateEstado(Request $request, $id)
    {
        $request->validate(['estado' => 'required|string|max:50']);

        // Una vez que el documento ya tiene una decisión final (Aceptado/Rechazado),
        // solo Director/Administrador pueden volver a cambiarlo — evita que se
        // reabra o se pise una decisión ya tomada sin dejar rastro.
        $actual = DB::select('CALL sp_MesaPartes_ObtenerPorId(?)', [$id]);
        if (!empty($actual)) {
            $estadoActual = $actual[0]->estado ?? null;
            $yaResuelto   = in_array($estadoActual, ['Aceptado', 'Rechazado']);
            if ($yaResuelto && !auth()->user()->isSuperAdmin()) {
                return redirect()->route('admin.mesa.index')
                    ->with('error', 'Este documento ya tiene una decisión final ("' . $estadoActual . '"). Solo Director o Administrador pueden modificarlo.');
            }
        }

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_MesaPartes_CambiarEstado(?, ?, @resultado, @mensaje)', [
            $id, $request->estado
        ]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
        $ok = (int)($out[0]->resultado ?? 0) === 1;

        if ($ok) {
            $asunto = $actual[0]->asunto ?? "documento #{$id}";
            $this->registrarBitacora("Cambió el estado de \"{$asunto}\" a {$request->estado} en Mesa de Partes");
        }

        return redirect()->route('admin.mesa.index')
            ->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada.');
    }

    /**
     * Eliminar un documento de Mesa de Partes
     */
    public function destroy($id)
    {
        try {
            // Intentar obtener el documento para eliminar archivos físicos
            $documento = DB::select('CALL sp_MesaPartes_ObtenerPorId(?)', [$id]);
            $asunto    = $documento[0]->asunto ?? "documento #{$id}";

            if (!empty($documento) && !empty($documento[0]->archivo)) {
                // Usar servicio para eliminar archivos físicos
                $this->archivoService->eliminarArchivos($documento[0]->archivo);
            }

            // Intentar usar stored procedure si existe, sino usar Eloquent
            try {
                // Inicializar variables de salida
                DB::statement('SET @resultado = 0, @mensaje = ""');

                // Llamar al procedimiento
                DB::statement('CALL sp_MesaPartes_Eliminar(?, @resultado, @mensaje)', [$id]);

                // Obtener resultados
                $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
                $resultado = (int)($out[0]->resultado ?? 0);
                $mensaje = $out[0]->mensaje ?? 'Documento eliminado correctamente';

                if ($resultado === 1) {
                    $this->registrarBitacora("Eliminó el documento \"{$asunto}\" de Mesa de Partes");
                    return redirect()->route('admin.mesa.index')->with('success', $mensaje);
                } else {
                    return redirect()->route('admin.mesa.index')->with('error', $mensaje);
                }
            } catch (\Exception $e) {
                // Si no existe el SP, usar Eloquent
                $mesaParte = MesaParte::find($id);
                if ($mesaParte) {
                    $mesaParte->delete();
                    $this->registrarBitacora("Eliminó el documento \"{$asunto}\" de Mesa de Partes");
                    return redirect()->route('admin.mesa.index')->with('success', 'Documento eliminado correctamente');
                } else {
                    return redirect()->route('admin.mesa.index')->with('error', 'Documento no encontrado');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento de Mesa de Partes: ' . $e->getMessage());
            return redirect()->route('admin.mesa.index')->with('error', 'Error al eliminar el documento');
        }
    }
}

