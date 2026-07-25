<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $slug = 'bitacora.ver_mesa_partes';

        if (!DB::table('Permiso')->where('slug', $slug)->exists()) {
            DB::table('Permiso')->insert([
                'slug'        => $slug,
                'nombre'      => 'Ver bitácora de Mesa de Partes',
                'modulo'      => 'Mesa de Partes',
                'descripcion' => 'Ver únicamente el historial de acciones de Mesa de Partes, sin ver el resto de la bitácora del sistema.',
                'estado'      => 'A',
            ]);
        }

        $permisoId = DB::table('Permiso')->where('slug', $slug)->value('permiso_id');
        $rolId = DB::table('Rol')->whereRaw('LOWER(TRIM(nombre)) = ?', ['mesapartes'])->value('rol_id');

        if ($permisoId && $rolId) {
            $yaAsignado = DB::table('RolPermiso')
                ->where('rol_id', $rolId)
                ->where('permiso_id', $permisoId)
                ->exists();

            if (!$yaAsignado) {
                DB::table('RolPermiso')->insert([
                    'rol_id'     => $rolId,
                    'permiso_id' => $permisoId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permisoId = DB::table('Permiso')->where('slug', 'bitacora.ver_mesa_partes')->value('permiso_id');
        if ($permisoId) {
            DB::table('RolPermiso')->where('permiso_id', $permisoId)->delete();
            DB::table('Permiso')->where('permiso_id', $permisoId)->delete();
        }
    }
};
