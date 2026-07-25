<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Bitacora', function (Blueprint $table) {
            $table->string('modulo', 50)->default('general')->after('usuario_id');
        });

        // Backfill best-effort de registros existentes según el texto de la accion,
        // para que las filas viejas no queden todas como "general".
        $mapa = [
            'mesa_partes'      => 'mesa de partes',
            'alumnos'          => 'alumno',
            'roles'            => 'rol "',
            'usuarios'         => 'usuario "',
            'permisos'         => 'permisos del rol',
            'noticias'         => 'noticia',
            'comite_directivo' => 'directivo',
            'historia_legado'  => 'historia y legado',
            'imagenes_inicio'  => ['carrusel', 'talleres'],
            'personas'         => 'persona ',
            'asistencia'       => 'asistencia',
        ];

        foreach ($mapa as $modulo => $patrones) {
            foreach ((array) $patrones as $patron) {
                DB::table('Bitacora')
                    ->where('modulo', 'general')
                    ->where('accion', 'like', "%{$patron}%")
                    ->update(['modulo' => $modulo]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('Bitacora', function (Blueprint $table) {
            $table->dropColumn('modulo');
        });
    }
};
