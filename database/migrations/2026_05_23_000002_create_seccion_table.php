<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Seccion', function (Blueprint $table) {
            $table->smallIncrements('seccion_id');
            $table->unsignedSmallInteger('grado_id');
            $table->string('nombre', 10);
            $table->enum('turno', ['Mañana', 'Tarde'])->default('Mañana');
            $table->smallInteger('año_lectivo');
            $table->tinyInteger('estado')->default(1);

            $table->foreign('grado_id')->references('grado_id')->on('Grado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Seccion');
    }
};
