<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AsignarRolUsuario extends Command
{
    protected $signature = 'usuario:asignar-rol {usuario_id} {rol_id}';
    protected $description = 'Asignar un rol a un usuario';

    public function handle()
    {
        $usuarioId = $this->argument('usuario_id');
        $rolId = $this->argument('rol_id');

        $this->info("Asignando rol {$rolId} al usuario {$usuarioId}...");

        try {
            // Verificar si ya existe
            $existe = DB::select(
                'SELECT * FROM UsuarioRol WHERE usuario_id = ? AND rol_id = ?',
                [$usuarioId, $rolId]
            );

            if (!empty($existe)) {
                $this->warn('El usuario ya tiene este rol asignado.');
                return 0;
            }

            // Insertar
            DB::insert(
                'INSERT INTO UsuarioRol (usuario_id, rol_id) VALUES (?, ?)',
                [$usuarioId, $rolId]
            );

            $this->info('✅ Rol asignado correctamente!');

            // Mostrar roles actuales
            $roles = DB::select('CALL sp_UsuarioRol_ListarPorUsuario(?)', [$usuarioId]);

            $this->info("\nRoles actuales del usuario {$usuarioId}:");
            foreach ($roles as $rol) {
                $this->line("  - {$rol->nombre}");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
