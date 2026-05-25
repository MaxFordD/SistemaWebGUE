<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagenes_inicio', function (Blueprint $table) {
            $table->id();
            $table->enum('seccion', ['carousel', 'taller']);
            $table->tinyInteger('orden')->unsigned()->default(1);
            $table->string('ruta', 255);
            $table->string('alt', 255)->default('');
            $table->string('titulo', 100)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->string('icono', 50)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });

        DB::table('imagenes_inicio')->insert([
            // Carrusel hero
            ['seccion'=>'carousel','orden'=>1,'ruta'=>'images/gue.jpg',       'alt'=>'Fachada del colegio',            'titulo'=>null,'descripcion'=>null,'icono'=>null,'activo'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'carousel','orden'=>2,'ruta'=>'images/colegio001.jpg', 'alt'=>'Estudiantes en actividades',     'titulo'=>null,'descripcion'=>null,'icono'=>null,'activo'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'carousel','orden'=>3,'ruta'=>'images/colegio003.jpg', 'alt'=>'Instalaciones del campus',       'titulo'=>null,'descripcion'=>null,'icono'=>null,'activo'=>1,'created_at'=>now(),'updated_at'=>now()],
            // Talleres
            ['seccion'=>'taller','orden'=>1,'ruta'=>'images/talleres/musica.jpg', 'alt'=>'Taller de Música',        'titulo'=>'Música',        'descripcion'=>'Práctica instrumental, ensambles y teoría musical.',   'icono'=>'music-note-beamed','activo'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'taller','orden'=>2,'ruta'=>'images/talleres/deporte.jpg','alt'=>'Taller de Deporte',       'titulo'=>'Deporte',       'descripcion'=>'Fútbol, vóley y atletismo para todas las categorías.', 'icono'=>'trophy',           'activo'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'taller','orden'=>3,'ruta'=>'images/talleres/pintura.jpg','alt'=>'Taller de Artes Plásticas','titulo'=>'Artes Plásticas','descripcion'=>'Dibujo, pintura y técnicas mixtas.',                  'icono'=>'palette',          'activo'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'taller','orden'=>4,'ruta'=>'images/talleres/danza.jpg',  'alt'=>'Taller de Danza',         'titulo'=>'Danza',         'descripcion'=>'Danza moderna y folclore peruano.',                    'icono'=>'person-arms-up',   'activo'=>1,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes_inicio');
    }
};
