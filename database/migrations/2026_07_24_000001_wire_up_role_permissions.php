<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- 1) Permisos nuevos que faltaban ---
        $nuevosPermisos = [
            ['slug' => 'bitacora.ver',      'nombre' => 'Ver bitácora del sistema',        'modulo' => 'Usuarios',   'descripcion' => 'Ver el registro de acciones de todos los usuarios.'],
            ['slug' => 'alumnos.eliminar',  'nombre' => 'Eliminar alumnos permanentemente', 'modulo' => 'Asistencia', 'descripcion' => 'Borrar alumnos de forma irreversible (individual o en lote), a diferencia de solo desactivarlos.'],
        ];
        foreach ($nuevosPermisos as $p) {
            $existe = DB::table('Permiso')->where('slug', $p['slug'])->exists();
            if (!$existe) {
                DB::table('Permiso')->insert(array_merge($p, ['estado' => 'A']));
            }
        }

        // --- 2) Roles que usan las rutas pero no existían en los datos base ---
        foreach (['Director', 'Editor', 'MesaPartes'] as $nombreRol) {
            $existe = DB::table('Rol')->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreRol)])->exists();
            if (!$existe) {
                DB::table('Rol')->insert([
                    'nombre'      => $nombreRol,
                    'descripcion' => 'Creado automáticamente para igualar los roles ya usados por el sistema.',
                    'estado'      => 'A',
                ]);
            }
        }

        // --- 3) Asignación por defecto de permisos por rol ---
        // Director y Administrador ya tienen acceso total como superadmin (Usuario::isSuperAdmin()),
        // pero se les marcan todos los permisos igual para que la pantalla "Permisos por Rol" los
        // muestre correctamente y quede como referencia/documentación de lo que ya pueden hacer.
        $todosLosSlugs = DB::table('Permiso')->pluck('slug')->all();

        $asignacionesPorRol = [
            'administrador' => $todosLosSlugs,
            'director'      => $todosLosSlugs,
            'auxiliar'      => ['asistencia.registrar', 'asistencia.reportes', 'alumnos.admin'],
            'secretaria'    => ['noticias.admin'],
            'editor'        => ['noticias.admin'],
            'mesapartes'    => ['mesa.admin'],
            // Docente y Usuario no tenían acceso a ninguna sección administrativa antes de este
            // cambio (ningún grupo de rutas los mencionaba), así que se quedan sin permisos por
            // defecto para no darles acceso nuevo sin que tú lo decidas desde Permisos por Rol.
        ];

        $rolesPorNombre = [];
        foreach (DB::table('Rol')->select('rol_id', 'nombre')->get() as $rol) {
            $rolesPorNombre[mb_strtolower(trim($rol->nombre))] = $rol->rol_id;
        }

        $permisoIdPorSlug = DB::table('Permiso')->pluck('permiso_id', 'slug')->toArray();

        foreach ($asignacionesPorRol as $nombreRol => $slugs) {
            $rolId = $rolesPorNombre[$nombreRol] ?? null;
            if (!$rolId) {
                continue;
            }
            foreach ($slugs as $slug) {
                $permisoId = $permisoIdPorSlug[$slug] ?? null;
                if (!$permisoId) {
                    continue;
                }
                $yaAsignado = DB::table('RolPermiso')
                    ->where('rol_id', $rolId)
                    ->where('permiso_id', $permisoId)
                    ->exists();
                if (!$yaAsignado) {
                    DB::table('RolPermiso')->insert([
                        'rol_id'     => $rolId,
                        'permiso_id' => $permisoId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['Director', 'Editor', 'MesaPartes'] as $nombreRol) {
            $rol = DB::table('Rol')->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreRol)])->first();
            if ($rol) {
                DB::table('RolPermiso')->where('rol_id', $rol->rol_id)->delete();
                DB::table('Rol')->where('rol_id', $rol->rol_id)->delete();
            }
        }

        DB::table('Permiso')->whereIn('slug', ['bitacora.ver', 'alumnos.eliminar'])->delete();
    }
};
