<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alinea los alias de columnas con los que espera resources/views/admin/dashboard.blade.php
        // (usuarios_activos, personas_activas, roles_activos, noticias_activas), que nunca coincidian
        // con los nombres originales (total_usuarios, total_personas, ...) y hacian que los KPIs
        // siempre mostraran 0.
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Sistema_ObtenerEstadisticas');
        DB::unprepared("CREATE PROCEDURE sp_Sistema_ObtenerEstadisticas()
        BEGIN
            SELECT
                (SELECT COUNT(*) FROM Usuario WHERE estado = 'A')             AS usuarios_activos,
                (SELECT COUNT(*) FROM Persona WHERE estado = 'A')             AS personas_activas,
                (SELECT COUNT(*) FROM Rol WHERE estado = 'A')                 AS roles_activos,
                (SELECT COUNT(*) FROM Noticia WHERE estado = 'A')             AS noticias_activas,
                (SELECT COUNT(*) FROM Mesa_Partes WHERE estado = 'Pendiente') AS mesa_pendientes,
                (SELECT COUNT(*) FROM Mesa_Partes WHERE estado = 'Revisado')  AS mesa_revisados;
        END");
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Sistema_ObtenerEstadisticas');
        DB::unprepared("CREATE PROCEDURE sp_Sistema_ObtenerEstadisticas()
        BEGIN
            SELECT
                (SELECT COUNT(*) FROM Usuario WHERE estado = 'A')             AS total_usuarios,
                (SELECT COUNT(*) FROM Persona WHERE estado = 'A')             AS total_personas,
                (SELECT COUNT(*) FROM Rol WHERE estado = 'A')                 AS total_roles,
                (SELECT COUNT(*) FROM Noticia WHERE estado = 'A')             AS total_noticias,
                (SELECT COUNT(*) FROM Mesa_Partes WHERE estado = 'Pendiente') AS mesa_pendientes,
                (SELECT COUNT(*) FROM Mesa_Partes WHERE estado = 'Revisado')  AS mesa_revisados;
        END");
    }
};
