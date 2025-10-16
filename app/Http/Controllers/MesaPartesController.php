<?php

namespace App\Http\Controllers;

use App\Models\MesaParte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MesaPartesController extends Controller
{
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

        $storedPaths = [];
        $originalNames = [];

        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                if (!$archivo) { continue; }
                $nombreOriginal = $archivo->getClientOriginalName();
                $path = $archivo->storeAs('mesa_partes', $nombreOriginal, 'public');
                $storedPaths[] = $path; // relativo dentro de storage/app/public
                $originalNames[] = $nombreOriginal;
            }
        }

        // Guardar en la base de datos
        $mesa = MesaParte::create([
            'remitente' => $request->remitente,
            'dni' => $request->dni,
            'correo' => $request->correo,
            'asunto' => $request->asunto,
            'detalle' => $request->detalle,
            // Si existe una sola columna 'archivo', concatenamos rutas separadas por ';'
            'archivo' => count($storedPaths) ? ('/storage/' . implode('; /storage/', $storedPaths)) : null,
            'tipo_documento_id' => $request->tipo_documento_id,
            'estado' => 'Pendiente',
        ]);

        try {
            // ✅ 1. Correo al remitente
            if ($request->filled('correo')) {
                Mail::html("\n                <p>Estimado/a <b>{$request->remitente}</b>,</p>\n                <p>Su documento con asunto <b>'{$request->asunto}'</b> fue recibido correctamente en la Mesa de Partes.</p>\n                <p>Gracias por su envío.<br><br>IE JFSC</p>\n            ", function ($msg) use ($request, $storedPaths, $originalNames) {
                    $msg->to($request->correo)
                        ->subject('Confirmación de recepción - Mesa de Partes IE JFSC');

                    foreach ($storedPaths as $i => $path) {
                        $msg->attach(storage_path('app/public/' . $path), [
                            'as' => $originalNames[$i] ?? basename($path),
                        ]);
                    }
                });
            }

            // ✅ 2. Correo al administrador
            Mail::html("\n            <p><b>Nuevo documento recibido en Mesa de Partes:</b></p>\n            <p>\n                <b>Remitente:</b> {$request->remitente}<br>\n                <b>Asunto:</b> {$request->asunto}<br>\n                <b>Detalle:</b> {$request->detalle}<br>\n                <b>Tipo de documento:</b> {$request->nombre}<br>\n                <b>Fecha:</b> " . now()->format('d/m/Y H:i:s') . "\n            </p>\n        ", function ($msg) use ($request, $storedPaths, $originalNames) {
                $msg->to('oscarrojas24200@gmail.com')
                    ->subject('📬 Nuevo documento recibido - Mesa de Partes');

                foreach ($storedPaths as $i => $path) {
                    $msg->attach(storage_path('app/public/' . $path), [
                        'as' => $originalNames[$i] ?? basename($path),
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Error al enviar correos de Mesa de Partes: ' . $e->getMessage());
        }

        return redirect()->route('mesa.create')->with('success', 'Documento enviado y correos notificados correctamente.');
    }

    // Mostrar lista de documentos (para el administrador)
    public function index()
    {
        $documentos = DB::select('EXEC sp_MesaPartes_Listar');
        return view('admin.mesa.index', compact('documentos'));
    }

    // Mostrar detalle de un documento
    public function show($id)
    {
        $documento = DB::select('EXEC sp_MesaPartes_ObtenerPorId ?', [$id]);
        if (empty($documento)) {
            abort(404);
        }
        return view('admin.mesa.show', ['doc' => $documento[0]]);
    }

    // Actualizar estado (Pendiente / Revisado / Aceptado / Rechazado)
    public function updateEstado(Request $request, $id)
    {
        $request->validate(['estado' => 'required|string|max:50']);

        $sql = "
            DECLARE @resultado BIT, @mensaje VARCHAR(200);
            EXEC sp_MesaPartes_ActualizarEstado
                @documento_id = ?,
                @estado = ?,
                @resultado = @resultado OUTPUT,
                @mensaje = @mensaje OUTPUT;
            SELECT resultado=@resultado, mensaje=@mensaje;
        ";

        $out = DB::select($sql, [$id, $request->estado]);
        $ok = (int)($out[0]->resultado ?? 0) === 1;

        return redirect()->route('admin.mesa.index')
            ->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada.');
    }
}

