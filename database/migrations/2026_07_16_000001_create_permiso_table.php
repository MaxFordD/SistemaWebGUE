<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Permiso', function (Blueprint $table) {
            // integer($column, $autoIncrement, $unsigned) — signed, como Rol.rol_id
            // (no usamos increments()/id() porque esos son UNSIGNED y romperían
            // la FK con Rol.rol_id, que en esta base de datos es INT con signo).
            $table->integer('permiso_id', true, false);
            $table->string('slug', 100)->unique();
            $table->string('nombre', 150);
            $table->string('modulo', 100);
            $table->string('descripcion', 255)->nullable();
            $table->char('estado', 1)->default('A');
        });

        Schema::create('RolPermiso', function (Blueprint $table) {
            $table->integer('rol_id');
            $table->integer('permiso_id');
            $table->primary(['rol_id', 'permiso_id']);
            $table->foreign('rol_id')->references('rol_id')->on('Rol')->onDelete('cascade');
            $table->foreign('permiso_id')->references('permiso_id')->on('Permiso')->onDelete('cascade');
        });

        $permisos = [
            ['slug' => 'personas.admin',       'nombre' => 'Ver personas',                 'modulo' => 'Usuarios',      'descripcion' => 'Ver el listado de personas registradas.'],
            ['slug' => 'usuarios.admin',       'nombre' => 'Gestionar usuarios',            'modulo' => 'Usuarios',      'descripcion' => 'Crear, editar y desactivar usuarios.'],
            ['slug' => 'roles.asignar',        'nombre' => 'Asignar roles',                 'modulo' => 'Usuarios',      'descripcion' => 'Asignar roles a los usuarios.'],
            ['slug' => 'roles.admin',          'nombre' => 'Gestionar roles',               'modulo' => 'Usuarios',      'descripcion' => 'Crear y editar roles del sistema.'],
            ['slug' => 'permisos.admin',       'nombre' => 'Gestionar permisos por rol',    'modulo' => 'Usuarios',      'descripcion' => 'Asignar permisos a cada rol.'],
            ['slug' => 'noticias.admin',       'nombre' => 'Publicar noticias',             'modulo' => 'Contenidos',    'descripcion' => 'Crear, editar y eliminar noticias.'],
            ['slug' => 'comite.admin',         'nombre' => 'Gestionar comité directivo',    'modulo' => 'Contenidos',    'descripcion' => 'Administrar el comité directivo.'],
            ['slug' => 'historia.admin',       'nombre' => 'Gestionar historia y legado',   'modulo' => 'Contenidos',    'descripcion' => 'Administrar la página Historia y Legado.'],
            ['slug' => 'imagenes.admin',       'nombre' => 'Gestionar imágenes del inicio', 'modulo' => 'Contenidos',    'descripcion' => 'Administrar carrusel y talleres del inicio.'],
            ['slug' => 'nosotros.admin',       'nombre' => 'Gestionar página Nosotros',     'modulo' => 'Contenidos',    'descripcion' => 'Administrar el contenido de la página Nosotros.'],
            ['slug' => 'mesa.admin',           'nombre' => 'Ver documentos de mesa de partes', 'modulo' => 'Mesa de Partes', 'descripcion' => 'Ver y gestionar documentos recibidos.'],
            ['slug' => 'alumnos.admin',        'nombre' => 'Gestionar alumnos',             'modulo' => 'Asistencia',    'descripcion' => 'Administrar el registro de alumnos.'],
            ['slug' => 'grados.admin',         'nombre' => 'Gestionar grados',              'modulo' => 'Asistencia',    'descripcion' => 'Administrar los grados.'],
            ['slug' => 'secciones.admin',      'nombre' => 'Gestionar secciones',           'modulo' => 'Asistencia',    'descripcion' => 'Administrar las secciones.'],
            ['slug' => 'asistencia.registrar', 'nombre' => 'Registrar asistencia',          'modulo' => 'Asistencia',    'descripcion' => 'Registrar la asistencia diaria de alumnos.'],
            ['slug' => 'asistencia.reportes',  'nombre' => 'Ver reportes de asistencia',    'modulo' => 'Asistencia',    'descripcion' => 'Ver historial y reportes de asistencia.'],
        ];

        foreach ($permisos as $p) {
            DB::table('Permiso')->insert($p + ['estado' => 'A']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('RolPermiso');
        Schema::dropIfExists('Permiso');
    }
};
