<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Mesa_Partes', function (Blueprint $table) {
            $table->string('notificacion_estado', 20)->default('Pendiente')->after('estado');
            $table->text('notificacion_error')->nullable()->after('notificacion_estado');
        });

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_MesaPartes_Listar');
        DB::unprepared("CREATE PROCEDURE sp_MesaPartes_Listar()
        BEGIN
            SELECT
                mp.documento_id,
                mp.remitente,
                mp.dni,
                mp.correo,
                mp.asunto,
                td.nombre AS tipo_documento,
                mp.fecha_envio,
                mp.estado,
                mp.notificacion_estado,
                mp.notificacion_error
            FROM Mesa_Partes mp
            INNER JOIN Tipos_Documento td ON mp.tipo_documento_id = td.tipo_id
            ORDER BY mp.fecha_envio DESC;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_MesaPartes_ObtenerPorId');
        DB::unprepared("CREATE PROCEDURE sp_MesaPartes_ObtenerPorId(IN p_documento_id INT)
        BEGIN
            SELECT
                mp.documento_id,
                mp.remitente,
                mp.dni,
                mp.correo,
                mp.asunto,
                mp.detalle,
                mp.archivo,
                mp.tipo_documento_id,
                td.nombre AS tipo_documento,
                mp.fecha_envio,
                mp.estado,
                mp.notificacion_estado,
                mp.notificacion_error
            FROM Mesa_Partes mp
            LEFT JOIN Tipos_Documento td ON mp.tipo_documento_id = td.tipo_id
            WHERE mp.documento_id = p_documento_id;
        END");
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_MesaPartes_Listar');
        DB::unprepared("CREATE PROCEDURE sp_MesaPartes_Listar()
        BEGIN
            SELECT
                mp.documento_id,
                mp.remitente,
                mp.dni,
                mp.correo,
                mp.asunto,
                td.nombre AS tipo_documento,
                mp.fecha_envio,
                mp.estado
            FROM Mesa_Partes mp
            INNER JOIN Tipos_Documento td ON mp.tipo_documento_id = td.tipo_id
            ORDER BY mp.fecha_envio DESC;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_MesaPartes_ObtenerPorId');
        DB::unprepared("CREATE PROCEDURE sp_MesaPartes_ObtenerPorId(IN p_documento_id INT)
        BEGIN
            SELECT
                mp.documento_id,
                mp.remitente,
                mp.dni,
                mp.correo,
                mp.asunto,
                mp.detalle,
                mp.archivo,
                mp.tipo_documento_id,
                td.nombre AS tipo_documento,
                mp.fecha_envio,
                mp.estado
            FROM Mesa_Partes mp
            LEFT JOIN Tipos_Documento td ON mp.tipo_documento_id = td.tipo_id
            WHERE mp.documento_id = p_documento_id;
        END");

        Schema::table('Mesa_Partes', function (Blueprint $table) {
            $table->dropColumn(['notificacion_estado', 'notificacion_error']);
        });
    }
};
