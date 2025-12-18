<?php

namespace App\Jobs;

use App\Models\MesaParte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EnviarNotificacionMesaPartes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $mesa;
    public $storedPaths;
    public $originalNames;
    public $correoRemitente;
    public $tipoDocumento;

    /**
     * Create a new job instance.
     *
     * @param MesaParte $mesa
     * @param array $storedPaths
     * @param array $originalNames
     * @param string|null $correoRemitente
     * @param string|null $tipoDocumento
     */
    public function __construct(
        MesaParte $mesa,
        array $storedPaths,
        array $originalNames,
        ?string $correoRemitente = null,
        ?string $tipoDocumento = null
    ) {
        $this->mesa = $mesa;
        $this->storedPaths = $storedPaths;
        $this->originalNames = $originalNames;
        $this->correoRemitente = $correoRemitente;
        $this->tipoDocumento = $tipoDocumento;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // 1. Enviar correo al remitente (si proporcionó email)
            if (!empty($this->correoRemitente)) {
                $this->enviarCorreoRemitente();
            }

            // 2. Enviar correo al administrador
            $this->enviarCorreoAdministrador();

        } catch (\Exception $e) {
            Log::error('Error al enviar correos de Mesa de Partes: ' . $e->getMessage());
            throw $e; // Re-lanzar para que el job sea reintentado
        }
    }

    /**
     * Enviar correo de confirmación al remitente
     */
    protected function enviarCorreoRemitente()
    {
        Mail::html("
            <p>Estimado/a <b>{$this->mesa->remitente}</b>,</p>
            <p>Su documento con asunto <b>'{$this->mesa->asunto}'</b> fue recibido correctamente en la Mesa de Partes.</p>
            <p>Gracias por su envío.<br><br>IE JFSC</p>
        ", function ($msg) {
            $msg->to($this->correoRemitente)
                ->subject('Confirmación de recepción - Mesa de Partes IE JFSC');

            // Adjuntar archivos
            foreach ($this->storedPaths as $i => $path) {
                $msg->attach(storage_path('app/public/' . $path), [
                    'as' => $this->originalNames[$i] ?? basename($path),
                ]);
            }
        });
    }

    /**
     * Enviar correo al administrador
     */
    protected function enviarCorreoAdministrador()
    {
        Mail::html("
            <p><b>Nuevo documento recibido en Mesa de Partes:</b></p>
            <p>
                <b>Remitente:</b> {$this->mesa->remitente}<br>
                <b>Asunto:</b> {$this->mesa->asunto}<br>
                <b>Detalle:</b> {$this->mesa->detalle}<br>
                <b>Tipo de documento:</b> {$this->tipoDocumento}<br>
                <b>Fecha:</b> " . now()->format('d/m/Y H:i:s') . "
            </p>
        ", function ($msg) {
            $msg->to(env('ADMIN_EMAIL', 'oscarrojas24200@gmail.com'))
                ->subject('📬 Nuevo documento recibido - Mesa de Partes');

            // Adjuntar archivos
            foreach ($this->storedPaths as $i => $path) {
                $msg->attach(storage_path('app/public/' . $path), [
                    'as' => $this->originalNames[$i] ?? basename($path),
                ]);
            }
        });
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Job EnviarNotificacionMesaPartes falló para documento ID: ' . $this->mesa->documento_id, [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
