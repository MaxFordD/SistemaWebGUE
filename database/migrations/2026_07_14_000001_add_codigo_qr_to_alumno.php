<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Alumno', function (Blueprint $table) {
            $table->string('codigo_qr', 40)->nullable()->unique()->after('dni');
        });

        // Backfill de alumnos existentes que aún no tienen código QR
        $alumnos = DB::table('Alumno')->whereNull('codigo_qr')->pluck('alumno_id');
        foreach ($alumnos as $alumnoId) {
            DB::table('Alumno')
                ->where('alumno_id', $alumnoId)
                ->update(['codigo_qr' => bin2hex(random_bytes(16))]);
        }
    }

    public function down(): void
    {
        Schema::table('Alumno', function (Blueprint $table) {
            $table->dropColumn('codigo_qr');
        });
    }
};
