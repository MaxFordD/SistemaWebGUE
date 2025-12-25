<?php
// Script para corregir los SPs del Comité Directivo
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$procedures = [
    // sp_ComiteDirectivo_Listar
    "DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Listar",
    "CREATE PROCEDURE sp_ComiteDirectivo_Listar(IN p_solo_activos TINYINT)
    BEGIN
        IF p_solo_activos = 1 THEN
            SELECT directivo_id, nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado
            FROM Comite_Directivo WHERE estado = 'A' ORDER BY orden ASC, nombre_completo ASC;
        ELSE
            SELECT directivo_id, nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado
            FROM Comite_Directivo ORDER BY orden ASC, nombre_completo ASC;
        END IF;
    END",

    // sp_ComiteDirectivo_ObtenerPorId
    "DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_ObtenerPorId",
    "CREATE PROCEDURE sp_ComiteDirectivo_ObtenerPorId(IN p_directivo_id INT)
    BEGIN
        SELECT directivo_id, nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado
        FROM Comite_Directivo WHERE directivo_id = p_directivo_id;
    END",

    // sp_ComiteDirectivo_Insertar
    "DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Insertar",
    "CREATE PROCEDURE sp_ComiteDirectivo_Insertar(
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
        VALUES(p_nombre_completo, p_cargo, p_grado_cargo, p_foto, p_biografia, p_orden, p_estado);
        SET p_resultado = LAST_INSERT_ID();
        SET p_mensaje = 'Miembro del comité registrado exitosamente';
    END",

    // sp_ComiteDirectivo_Actualizar
    "DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Actualizar",
    "CREATE PROCEDURE sp_ComiteDirectivo_Actualizar(
        IN p_directivo_id INT,
        IN p_nombre_completo VARCHAR(200),
        IN p_cargo VARCHAR(100),
        IN p_grado_cargo VARCHAR(100),
        IN p_foto VARCHAR(500),
        IN p_biografia TEXT,
        IN p_orden INT,
        IN p_estado CHAR(1),
        OUT p_resultado TINYINT,
        OUT p_mensaje VARCHAR(200)
    )
    BEGIN
        DECLARE EXIT HANDLER FOR SQLEXCEPTION
        BEGIN
            SET p_resultado = 0;
            SET p_mensaje = 'Error al actualizar miembro del comité';
        END;
        UPDATE Comite_Directivo
        SET nombre_completo = p_nombre_completo, cargo = p_cargo, grado_cargo = p_grado_cargo,
            foto = p_foto, biografia = p_biografia, orden = p_orden, estado = p_estado
        WHERE directivo_id = p_directivo_id;
        IF ROW_COUNT() = 0 THEN
            SET p_resultado = 0;
            SET p_mensaje = 'El miembro del comité no existe';
        ELSE
            SET p_resultado = 1;
            SET p_mensaje = 'Miembro del comité actualizado exitosamente';
        END IF;
    END",

    // sp_ComiteDirectivo_Eliminar
    "DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Eliminar",
    "CREATE PROCEDURE sp_ComiteDirectivo_Eliminar(
        IN p_directivo_id INT,
        OUT p_resultado TINYINT,
        OUT p_mensaje VARCHAR(200)
    )
    BEGIN
        UPDATE Comite_Directivo SET estado = 'I' WHERE directivo_id = p_directivo_id;
        IF ROW_COUNT() = 0 THEN
            SET p_resultado = 0;
            SET p_mensaje = 'El miembro del comité no existe';
        ELSE
            SET p_resultado = 1;
            SET p_mensaje = 'Miembro del comité eliminado exitosamente';
        END IF;
    END"
];

echo "Corrigiendo " . (count($procedures) / 2) . " stored procedures del Comité Directivo...\n";

foreach ($procedures as $i => $sql) {
    try {
        DB::statement($sql);
        if ($i % 2 == 1) {
            echo "✓ SP " . (($i + 1) / 2) . " corregido\n";
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ SPs del Comité Directivo corregidos exitosamente!\n";
