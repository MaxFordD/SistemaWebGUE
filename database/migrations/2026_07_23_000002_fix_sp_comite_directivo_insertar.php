<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ComiteDirectivoController::store() llama al SP con 7 parametros de entrada
        // (incluye p_estado, tomado del select del formulario), pero el SP original
        // solo declaraba 6 y fijaba el estado en 'A' a mano. El desfase de aridad
        // hacia fallar la llamada con "Incorrect number of arguments".
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Insertar');
        DB::unprepared("CREATE PROCEDURE sp_ComiteDirectivo_Insertar(
            IN p_nombre_completo VARCHAR(200),
            IN p_cargo VARCHAR(100),
            IN p_grado_cargo VARCHAR(100),
            IN p_foto VARCHAR(500),
            IN p_biografia TEXT,
            IN p_orden INT,
            IN p_estado CHAR(1),
            OUT p_resultado INT,
            OUT p_mensaje VARCHAR(200)
        )
        BEGIN
            DECLARE EXIT HANDLER FOR SQLEXCEPTION
            BEGIN
                SET p_resultado = 0;
                SET p_mensaje = 'Error al registrar miembro del comité';
            END;

            INSERT INTO Comite_Directivo(nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado)
            VALUES(p_nombre_completo, p_cargo, p_grado_cargo, p_foto, p_biografia, p_orden, COALESCE(p_estado, 'A'));

            SET p_resultado = LAST_INSERT_ID();
            SET p_mensaje = 'Miembro del comité registrado exitosamente';
        END");
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Insertar');
        DB::unprepared("CREATE PROCEDURE sp_ComiteDirectivo_Insertar(
            IN p_nombre_completo VARCHAR(200),
            IN p_cargo VARCHAR(100),
            IN p_grado_cargo VARCHAR(100),
            IN p_foto VARCHAR(500),
            IN p_biografia TEXT,
            IN p_orden INT,
            OUT p_resultado INT,
            OUT p_mensaje VARCHAR(200)
        )
        BEGIN
            DECLARE EXIT HANDLER FOR SQLEXCEPTION
            BEGIN
                SET p_resultado = 0;
                SET p_mensaje = 'Error al registrar miembro del comité';
            END;

            INSERT INTO Comite_Directivo(nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado)
            VALUES(p_nombre_completo, p_cargo, p_grado_cargo, p_foto, p_biografia, p_orden, 'A');

            SET p_resultado = LAST_INSERT_ID();
            SET p_mensaje = 'Miembro del comité registrado exitosamente';
        END");
    }
};
