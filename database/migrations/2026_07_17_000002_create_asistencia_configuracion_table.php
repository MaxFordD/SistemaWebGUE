<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AsistenciaConfiguracion', function (Blueprint $table) {
            $table->tinyIncrements('config_id');
            $table->time('hora_apertura');
            $table->time('hora_cierre');
            $table->time('hora_limite_tardanza');
            $table->unsignedTinyInteger('umbral_alertas_mes');
            $table->unsignedTinyInteger('dias_limite_edicion');
            $table->integer('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();

            $table->foreign('actualizado_por')->references('usuario_id')->on('Usuario');
        });

        DB::table('AsistenciaConfiguracion')->insert([
            'hora_apertura'        => '05:00:00',
            'hora_cierre'          => '19:00:00',
            'hora_limite_tardanza' => '08:00:00',
            'umbral_alertas_mes'   => 3,
            'dias_limite_edicion'  => 7,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('AsistenciaConfiguracion');
    }
};
