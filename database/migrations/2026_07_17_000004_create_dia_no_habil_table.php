<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DiaNoHabil', function (Blueprint $table) {
            $table->increments('dia_no_habil_id');
            $table->date('fecha')->unique();
            $table->string('motivo', 150);
            $table->integer('usuario_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('usuario_id')->references('usuario_id')->on('Usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DiaNoHabil');
    }
};
