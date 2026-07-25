<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Registra una accion en la Bitacora del sistema, asociada al usuario
     * autenticado actual. Disponible para todos los controladores admin
     * ya que todos heredan de esta clase base.
     */
    protected function registrarBitacora(string $modulo, string $accion): void
    {
        try {
            $user      = auth()->user();
            $usuarioId = $user->usuario_id ?? $user->id ?? null;

            if (!$usuarioId) {
                return;
            }

            DB::statement('SET @res = 0, @msg = ""');
            DB::statement('CALL sp_Bitacora_Insertar(?, ?, ?, @res, @msg)', [$usuarioId, $modulo, $accion]);
        } catch (\Exception $e) {
            Log::warning('Error al registrar en bitácora: ' . $e->getMessage());
        }
    }
}
