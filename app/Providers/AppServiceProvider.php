<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Directiva Blade: @role('Director','Administrador') ... @endrole
        Blade::if('role', function (...$roles) {
            $user = auth()->user();
            if (!$user) {
                return false;
            }

            $usuarioId = $user->usuario_id ?? $user->id ?? null;
            if (!$usuarioId) {
                return false;
            }

            $rolesUsuario = collect(DB::select('EXEC sp_UsuarioRol_ListarPorUsuario ?', [(int)$usuarioId]))
                ->pluck('nombre')
                ->map(fn($n) => mb_strtolower(trim($n)));

            $requeridos = collect($roles)->map(fn($n) => mb_strtolower(trim($n)));

            return $rolesUsuario->intersect($requeridos)->isNotEmpty();
        });
    }
}
