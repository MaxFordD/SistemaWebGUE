<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE Asistencia
            MODIFY estado_asistencia ENUM('Asistio','Falta','Tardanza','Justificada') NOT NULL");

        Schema::table('Asistencia', function ($table) {
            $table->time('hora_registro')->nullable()->after('estado_asistencia');
            $table->text('motivo_justificacion')->nullable()->after('observacion');
        });
    }

    public function down(): void
    {
        Schema::table('Asistencia', function ($table) {
            $table->dropColumn(['hora_registro', 'motivo_justificacion']);
        });

        DB::statement("UPDATE Asistencia SET estado_asistencia = 'Falta' WHERE estado_asistencia = 'Justificada'");
        DB::statement("ALTER TABLE Asistencia
            MODIFY estado_asistencia ENUM('Asistio','Falta','Tardanza') NOT NULL");
    }
};
