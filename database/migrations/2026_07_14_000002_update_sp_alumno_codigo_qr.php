<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_ObtenerPorId');
        DB::unprepared('CREATE PROCEDURE sp_Alumno_ObtenerPorId(IN p_alumno_id INT)
        BEGIN
            SELECT a.alumno_id, a.seccion_id, a.nombres, a.apellidos, a.dni, a.codigo_qr,
                   a.fecha_nacimiento, a.sexo, a.estado,
                   s.nombre AS seccion, g.nombre AS grado, g.nivel
            FROM Alumno a
            INNER JOIN Seccion s ON s.seccion_id = a.seccion_id
            INNER JOIN Grado g ON g.grado_id = s.grado_id
            WHERE a.alumno_id = p_alumno_id;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_ObtenerPorCodigoQR');
        DB::unprepared('CREATE PROCEDURE sp_Alumno_ObtenerPorCodigoQR(IN p_codigo_qr VARCHAR(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci)
        BEGIN
            SELECT a.alumno_id, a.seccion_id, a.nombres, a.apellidos, a.dni, a.codigo_qr,
                   a.fecha_nacimiento, a.sexo, a.estado,
                   s.nombre AS seccion, g.nombre AS grado, g.nivel
            FROM Alumno a
            INNER JOIN Seccion s ON s.seccion_id = a.seccion_id
            INNER JOIN Grado g ON g.grado_id = s.grado_id
            WHERE a.codigo_qr = p_codigo_qr AND a.estado = 1;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_Insertar');
        DB::unprepared("CREATE PROCEDURE sp_Alumno_Insertar(
            IN p_seccion_id       SMALLINT,
            IN p_nombres          VARCHAR(100),
            IN p_apellidos        VARCHAR(100),
            IN p_dni              VARCHAR(8),
            IN p_fecha_nacimiento DATE,
            IN p_sexo             ENUM('M','F'),
            IN p_codigo_qr        VARCHAR(40)
        )
        BEGIN
            INSERT INTO Alumno (seccion_id, nombres, apellidos, dni, fecha_nacimiento, sexo, codigo_qr, estado)
            VALUES (p_seccion_id, p_nombres, p_apellidos, p_dni, p_fecha_nacimiento, p_sexo, p_codigo_qr, 1);
            SELECT LAST_INSERT_ID() AS alumno_id;
        END");
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_ObtenerPorCodigoQR');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_ObtenerPorId');
        DB::unprepared('CREATE PROCEDURE sp_Alumno_ObtenerPorId(IN p_alumno_id INT)
        BEGIN
            SELECT a.alumno_id, a.seccion_id, a.nombres, a.apellidos, a.dni,
                   a.fecha_nacimiento, a.sexo, a.estado,
                   s.nombre AS seccion, g.nombre AS grado, g.nivel
            FROM Alumno a
            INNER JOIN Seccion s ON s.seccion_id = a.seccion_id
            INNER JOIN Grado g ON g.grado_id = s.grado_id
            WHERE a.alumno_id = p_alumno_id;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_Insertar');
        DB::unprepared("CREATE PROCEDURE sp_Alumno_Insertar(
            IN p_seccion_id       SMALLINT,
            IN p_nombres          VARCHAR(100),
            IN p_apellidos        VARCHAR(100),
            IN p_dni              VARCHAR(8),
            IN p_fecha_nacimiento DATE,
            IN p_sexo             ENUM('M','F')
        )
        BEGIN
            INSERT INTO Alumno (seccion_id, nombres, apellidos, dni, fecha_nacimiento, sexo, estado)
            VALUES (p_seccion_id, p_nombres, p_apellidos, p_dni, p_fecha_nacimiento, p_sexo, 1);
            SELECT LAST_INSERT_ID() AS alumno_id;
        END");
    }
};
