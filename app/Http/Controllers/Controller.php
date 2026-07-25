<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    /**
     * Normaliza campos de nombres propios a Title Case (ej. "MARIA JOSE" -> "Maria Jose")
     * y los reescribe en el propio $request, para que tanto la validacion como el resto
     * del controlador (SPs, bitacora) usen ya el valor normalizado.
     */
    protected function normalizarNombrePropio(Request $request, array $campos): void
    {
        $valores = [];
        foreach ($campos as $campo) {
            if ($request->filled($campo)) {
                $valores[$campo] = Str::title((string) $request->input($campo));
            }
        }
        $request->merge($valores);
    }
}
