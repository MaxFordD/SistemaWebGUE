<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Alumno', function (Blueprint $table) {
            $table->increments('alumno_id');
            $table->unsignedSmallInteger('seccion_id');
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('dni', 8)->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['M', 'F']);
            $table->tinyInteger('estado')->default(1);

            $table->foreign('seccion_id')->references('seccion_id')->on('Seccion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Alumno');
    }
};
