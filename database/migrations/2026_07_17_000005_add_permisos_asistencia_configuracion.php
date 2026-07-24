<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permisos = [
            ['slug' => 'asistencia.configurar',     'nombre' => 'Configurar horarios de asistencia', 'modulo' => 'Asistencia', 'descripcion' => 'Editar horarios de registro, umbral de reincidencia, límite de días de edición y días no hábiles.'],
            ['slug' => 'asistencia.editar_vencido',  'nombre' => 'Editar asistencia fuera de plazo',   'modulo' => 'Asistencia', 'descripcion' => 'Corregir un registro de asistencia más allá del límite de días permitido.'],
        ];

        foreach ($permisos as $p) {
            DB::table('Permiso')->insert($p + ['estado' => 'A']);
        }
    }

    public function down(): void
    {
        DB::table('Permiso')->whereIn('slug', ['asistencia.configurar', 'asistencia.editar_vencido'])->delete();
    }
};
