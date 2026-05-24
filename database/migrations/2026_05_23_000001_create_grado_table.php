<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Grado', function (Blueprint $table) {
            $table->smallIncrements('grado_id');
            $table->string('nombre', 30);
            $table->enum('nivel', ['Primaria', 'Secundaria']);
            $table->tinyInteger('estado')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Grado');
    }
};
