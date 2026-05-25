<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Grado
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Grado_Listar');
        DB::unprepared('CREATE PROCEDURE sp_Grado_Listar()
        BEGIN
            SELECT grado_id, nombre, nivel, estado
            FROM Grado
            ORDER BY nivel, nombre;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Grado_ListarActivos');
        DB::unprepared('CREATE PROCEDURE sp_Grado_ListarActivos()
        BEGIN
            SELECT grado_id, nombre, nivel
            FROM Grado
            WHERE estado = 1
            ORDER BY nivel, nombre;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Grado_ObtenerPorId');
        DB::unprepared('CREATE PROCEDURE sp_Grado_ObtenerPorId(IN p_grado_id SMALLINT)
        BEGIN
            SELECT grado_id, nombre, nivel, estado
            FROM Grado
            WHERE grado_id = p_grado_id;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Grado_Insertar');
        DB::unprepared("CREATE PROCEDURE sp_Grado_Insertar(
            IN p_nombre VARCHAR(30),
            IN p_nivel  ENUM('Primaria','Secundaria')
        )
        BEGIN
            INSERT INTO Grado (nombre, nivel, estado)
            VALUES (p_nombre, p_nivel, 1);
            SELECT LAST_INSERT_ID() AS grado_id;
        END");

        DB::unprepared("CREATE PROCEDURE sp_Grado_Actualizar(
            IN p_grado_id SMALLINT,
            IN p_nombre   VARCHAR(30),
            IN p_nivel    ENUM('Primaria','Secundaria'),
            IN p_estado   TINYINT
        )
        BEGIN
            UPDATE Grado
            SET nombre = p_nombre, nivel = p_nivel, estado = p_estado
            WHERE grado_id = p_grado_id;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Grado_Eliminar');
        DB::unprepared('CREATE PROCEDURE sp_Grado_Eliminar(IN p_grado_id SMALLINT)
        BEGIN
            UPDATE Grado SET estado = 0 WHERE grado_id = p_grado_id;
        END');

        // Seccion
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Seccion_Listar');
        DB::unprepared('CREATE PROCEDURE sp_Seccion_Listar(IN p_año_lectivo SMALLINT)
        BEGIN
            SELECT s.seccion_id, s.grado_id, g.nombre AS grado, g.nivel,
                   s.nombre AS seccion, s.turno, s.año_lectivo, s.estado
            FROM Seccion s
            INNER JOIN Grado g ON g.grado_id = s.grado_id
            WHERE s.año_lectivo = p_año_lectivo
            ORDER BY g.nivel, g.nombre, s.nombre;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Seccion_ListarActivas');
        DB::unprepared('CREATE PROCEDURE sp_Seccion_ListarActivas(IN p_año_lectivo SMALLINT)
        BEGIN
            SELECT s.seccion_id, s.grado_id, g.nombre AS grado, g.nivel,
                   s.nombre AS seccion, s.turno, s.año_lectivo
            FROM Seccion s
            INNER JOIN Grado g ON g.grado_id = s.grado_id
            WHERE s.año_lectivo = p_año_lectivo AND s.estado = 1
            ORDER BY g.nivel, g.nombre, s.nombre;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Seccion_ObtenerPorId');
        DB::unprepared('CREATE PROCEDURE sp_Seccion_ObtenerPorId(IN p_seccion_id SMALLINT)
        BEGIN
            SELECT s.seccion_id, s.grado_id, g.nombre AS grado, g.nivel,
                   s.nombre AS seccion, s.turno, s.año_lectivo, s.estado
            FROM Seccion s
            INNER JOIN Grado g ON g.grado_id = s.grado_id
            WHERE s.seccion_id = p_seccion_id;
        END');

        DB::unprepared("CREATE PROCEDURE sp_Seccion_Insertar(
            IN p_grado_id     SMALLINT,
            IN p_nombre       VARCHAR(10),
            IN p_turno        ENUM('Mañana','Tarde'),
            IN p_año_lectivo  SMALLINT
        )
        BEGIN
            INSERT INTO Seccion (grado_id, nombre, turno, año_lectivo, estado)
            VALUES (p_grado_id, p_nombre, p_turno, p_año_lectivo, 1);
            SELECT LAST_INSERT_ID() AS seccion_id;
        END");

        DB::unprepared("CREATE PROCEDURE sp_Seccion_Actualizar(
            IN p_seccion_id  SMALLINT,
            IN p_grado_id    SMALLINT,
            IN p_nombre      VARCHAR(10),
            IN p_turno       ENUM('Mañana','Tarde'),
            IN p_año_lectivo SMALLINT,
            IN p_estado      TINYINT
        )
        BEGIN
            UPDATE Seccion
            SET grado_id = p_grado_id, nombre = p_nombre, turno = p_turno,
                año_lectivo = p_año_lectivo, estado = p_estado
            WHERE seccion_id = p_seccion_id;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Seccion_Eliminar');
        DB::unprepared('CREATE PROCEDURE sp_Seccion_Eliminar(IN p_seccion_id SMALLINT)
        BEGIN
            UPDATE Seccion SET estado = 0 WHERE seccion_id = p_seccion_id;
        END');

        // Alumno
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_ListarPorSeccion');
        DB::unprepared('CREATE PROCEDURE sp_Alumno_ListarPorSeccion(IN p_seccion_id SMALLINT)
        BEGIN
            SELECT alumno_id, seccion_id, nombres, apellidos, dni,
                   fecha_nacimiento, sexo, estado
            FROM Alumno
            WHERE seccion_id = p_seccion_id
            ORDER BY apellidos, nombres;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_ListarActivosPorSeccion');
        DB::unprepared('CREATE PROCEDURE sp_Alumno_ListarActivosPorSeccion(IN p_seccion_id SMALLINT)
        BEGIN
            SELECT alumno_id, nombres, apellidos, dni, sexo
            FROM Alumno
            WHERE seccion_id = p_seccion_id AND estado = 1
            ORDER BY apellidos, nombres;
        END');

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

        DB::unprepared("CREATE PROCEDURE sp_Alumno_Actualizar(
            IN p_alumno_id        INT,
            IN p_seccion_id       SMALLINT,
            IN p_nombres          VARCHAR(100),
            IN p_apellidos        VARCHAR(100),
            IN p_dni              VARCHAR(8),
            IN p_fecha_nacimiento DATE,
            IN p_sexo             ENUM('M','F'),
            IN p_estado           TINYINT
        )
        BEGIN
            UPDATE Alumno
            SET seccion_id = p_seccion_id, nombres = p_nombres, apellidos = p_apellidos,
                dni = p_dni, fecha_nacimiento = p_fecha_nacimiento, sexo = p_sexo, estado = p_estado
            WHERE alumno_id = p_alumno_id;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_Eliminar');
        DB::unprepared('CREATE PROCEDURE sp_Alumno_Eliminar(IN p_alumno_id INT)
        BEGIN
            UPDATE Alumno SET estado = 0 WHERE alumno_id = p_alumno_id;
        END');

        // Asistencia
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Asistencia_RegistrarOActualizar');
        DB::unprepared("CREATE PROCEDURE sp_Asistencia_RegistrarOActualizar(
            IN p_alumno_id         INT,
            IN p_usuario_id        INT,
            IN p_fecha             DATE,
            IN p_estado_asistencia ENUM('Asistio','Falta','Tardanza'),
            IN p_observacion       TEXT
        )
        BEGIN
            INSERT INTO Asistencia (alumno_id, usuario_id, fecha, estado_asistencia, observacion)
            VALUES (p_alumno_id, p_usuario_id, p_fecha, p_estado_asistencia, p_observacion)
            ON DUPLICATE KEY UPDATE
                estado_asistencia = p_estado_asistencia,
                observacion       = p_observacion,
                usuario_id        = p_usuario_id;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Asistencia_ObtenerPorSeccionYFecha');
        DB::unprepared('CREATE PROCEDURE sp_Asistencia_ObtenerPorSeccionYFecha(
            IN p_seccion_id SMALLINT,
            IN p_fecha      DATE
        )
        BEGIN
            SELECT a.alumno_id, a.nombres, a.apellidos, a.dni, a.sexo,
                   ast.asistencia_id,
                   COALESCE(ast.estado_asistencia, \'Falta\') AS estado_asistencia,
                   ast.observacion
            FROM Alumno a
            LEFT JOIN Asistencia ast ON ast.alumno_id = a.alumno_id AND ast.fecha = p_fecha
            WHERE a.seccion_id = p_seccion_id AND a.estado = 1
            ORDER BY a.apellidos, a.nombres;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Asistencia_HistorialPorAlumno');
        DB::unprepared('CREATE PROCEDURE sp_Asistencia_HistorialPorAlumno(
            IN p_alumno_id INT,
            IN p_mes       TINYINT,
            IN p_año       SMALLINT
        )
        BEGIN
            SELECT ast.asistencia_id, ast.fecha, ast.estado_asistencia, ast.observacion,
                   u.nombre_usuario AS registrado_por
            FROM Asistencia ast
            INNER JOIN Usuario u ON u.usuario_id = ast.usuario_id
            WHERE ast.alumno_id = p_alumno_id
              AND (p_mes IS NULL OR MONTH(ast.fecha) = p_mes)
              AND (p_año IS NULL OR YEAR(ast.fecha) = p_año)
            ORDER BY ast.fecha DESC;
        END');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Asistencia_ResumenPorSeccion');
        DB::unprepared('CREATE PROCEDURE sp_Asistencia_ResumenPorSeccion(
            IN p_seccion_id SMALLINT,
            IN p_mes        TINYINT,
            IN p_año        SMALLINT
        )
        BEGIN
            SELECT a.alumno_id, a.apellidos, a.nombres,
                   COUNT(CASE WHEN ast.estado_asistencia = \'Asistio\'  THEN 1 END) AS total_asistio,
                   COUNT(CASE WHEN ast.estado_asistencia = \'Falta\'    THEN 1 END) AS total_faltas,
                   COUNT(CASE WHEN ast.estado_asistencia = \'Tardanza\' THEN 1 END) AS total_tardanzas
            FROM Alumno a
            LEFT JOIN Asistencia ast ON ast.alumno_id = a.alumno_id
                AND MONTH(ast.fecha) = p_mes
                AND YEAR(ast.fecha)  = p_año
            WHERE a.seccion_id = p_seccion_id AND a.estado = 1
            GROUP BY a.alumno_id, a.apellidos, a.nombres
            ORDER BY a.apellidos, a.nombres;
        END');

        // Rol Auxiliar
        DB::statement("INSERT IGNORE INTO Rol (nombre, descripcion)
            VALUES ('Auxiliar', 'Gestión de asistencia de alumnos')");
    }

    public function down(): void
    {
        $procedures = [
            'sp_Grado_Listar', 'sp_Grado_ListarActivos', 'sp_Grado_ObtenerPorId',
            'sp_Grado_Insertar', 'sp_Grado_Actualizar', 'sp_Grado_Eliminar',
            'sp_Seccion_Listar', 'sp_Seccion_ListarActivas', 'sp_Seccion_ObtenerPorId',
            'sp_Seccion_Insertar', 'sp_Seccion_Actualizar', 'sp_Seccion_Eliminar',
            'sp_Alumno_ListarPorSeccion', 'sp_Alumno_ListarActivosPorSeccion',
            'sp_Alumno_ObtenerPorId', 'sp_Alumno_Insertar', 'sp_Alumno_Actualizar',
            'sp_Alumno_Eliminar', 'sp_Asistencia_RegistrarOActualizar',
            'sp_Asistencia_ObtenerPorSeccionYFecha', 'sp_Asistencia_HistorialPorAlumno',
            'sp_Asistencia_ResumenPorSeccion',
        ];
        foreach ($procedures as $sp) {
            DB::unprepared("DROP PROCEDURE IF EXISTS {$sp}");
        }
    }
};
