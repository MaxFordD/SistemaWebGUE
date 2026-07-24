<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AsistenciaAuditoria', function (Blueprint $table) {
            $table->increments('auditoria_id');
            $table->unsignedInteger('alumno_id');
            $table->integer('usuario_id');
            $table->date('fecha_asistencia');
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20);
            $table->text('observacion_anterior')->nullable();
            $table->text('observacion_nueva')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('alumno_id')->references('alumno_id')->on('Alumno');
            $table->foreign('usuario_id')->references('usuario_id')->on('Usuario');
            $table->index(['alumno_id', 'fecha_asistencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AsistenciaAuditoria');
    }
};
