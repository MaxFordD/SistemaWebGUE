<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Asistencia', function (Blueprint $table) {
            $table->increments('asistencia_id');
            $table->unsignedInteger('alumno_id');
            $table->integer('usuario_id');
            $table->date('fecha');
            $table->enum('estado_asistencia', ['Asistio', 'Falta', 'Tardanza']);
            $table->text('observacion')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['alumno_id', 'fecha'], 'uq_asistencia_alumno_fecha');
            $table->foreign('alumno_id')->references('alumno_id')->on('Alumno');
            $table->foreign('usuario_id')->references('usuario_id')->on('Usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Asistencia');
    }
};
