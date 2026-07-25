<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Bitacora_Insertar');
        DB::unprepared("CREATE PROCEDURE sp_Bitacora_Insertar(
            IN p_usuario_id INT,
            IN p_modulo VARCHAR(50),
            IN p_accion LONGTEXT,
            OUT p_resultado INT,
            OUT p_mensaje VARCHAR(200)
        )
        BEGIN
            DECLARE EXIT HANDLER FOR SQLEXCEPTION
            BEGIN
                SET p_resultado = 0;
                SET p_mensaje = 'Error al registrar en bitácora';
            END;

            INSERT INTO Bitacora(usuario_id, modulo, accion, fecha)
            VALUES(p_usuario_id, COALESCE(p_modulo, 'general'), p_accion, UTC_TIMESTAMP());

            SET p_resultado = LAST_INSERT_ID();
            SET p_mensaje = 'Registro exitoso en bitácora';
        END");
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Bitacora_Insertar');
        DB::unprepared("CREATE PROCEDURE sp_Bitacora_Insertar(
            IN p_usuario_id INT,
            IN p_accion LONGTEXT,
            OUT p_resultado INT,
            OUT p_mensaje VARCHAR(200)
        )
        BEGIN
            DECLARE EXIT HANDLER FOR SQLEXCEPTION
            BEGIN
                SET p_resultado = 0;
                SET p_mensaje = 'Error al registrar en bitácora';
            END;

            INSERT INTO Bitacora(usuario_id, accion, fecha)
            VALUES(p_usuario_id, p_accion, UTC_TIMESTAMP());

            SET p_resultado = LAST_INSERT_ID();
            SET p_mensaje = 'Registro exitoso en bitácora';
        END");
    }
};
