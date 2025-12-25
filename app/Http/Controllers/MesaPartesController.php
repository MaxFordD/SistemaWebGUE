<?php

namespace App\Http\Controllers;

use App\Models\MesaParte;
use App\Services\ArchivoService;
use App\Jobs\EnviarNotificacionMesaPartes;
use Illuminate\Http\Request;
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
    public function index()
    {
        $documentos = DB::select('CALL sp_MesaPartes_Listar()');
        return view('admin.mesa.index', compact('documentos'));
    }

    // Mostrar detalle de un documento
    public function show($id)
    {
        $documento = DB::select('CALL sp_MesaPartes_ObtenerPorId(?)', [$id]);
        if (empty($documento)) {
            abort(404);
        }
        return view('admin.mesa.show', ['doc' => $documento[0]]);
    }

    // Actualizar estado (Pendiente / Revisado / Aceptado / Rechazado)
    public function updateEstado(Request $request, $id)
    {
        $request->validate(['estado' => 'required|string|max:50']);

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_MesaPartes_ActualizarEstado(?, ?, @resultado, @mensaje)', [
            $id, $request->estado
        ]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
        $ok = (int)($out[0]->resultado ?? 0) === 1;

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
                    return redirect()->route('admin.mesa.index')->with('success', $mensaje);
                } else {
                    return redirect()->route('admin.mesa.index')->with('error', $mensaje);
                }
            } catch (\Exception $e) {
                // Si no existe el SP, usar Eloquent
                $mesaParte = MesaParte::find($id);
                if ($mesaParte) {
                    $mesaParte->delete();
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

