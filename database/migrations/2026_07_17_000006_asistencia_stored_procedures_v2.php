<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- Asistencia: registrar/actualizar con hora_registro + justificación ---
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Asistencia_RegistrarOActualizar');
        DB::unprepared("CREATE PROCEDURE sp_Asistencia_RegistrarOActualizar(
            IN p_alumno_id             INT,
            IN p_usuario_id            INT,
            IN p_fecha                 DATE,
            IN p_estado_asistencia     ENUM('Asistio','Falta','Tardanza','Justificada'),
            IN p_observacion           TEXT,
            IN p_hora_registro         TIME,
            IN p_motivo_justificacion  TEXT
        )
        BEGIN
            INSERT INTO Asistencia
                (alumno_id, usuario_id, fecha, estado_asistencia, observacion, hora_registro, motivo_justificacion)
            VALUES
                (p_alumno_id, p_usuario_id, p_fecha, p_estado_asistencia, p_observacion, p_hora_registro, p_motivo_justificacion)
            ON DUPLICATE KEY UPDATE
                estado_asistencia    = p_estado_asistencia,
                observacion          = p_observacion,
                usuario_id           = p_usuario_id,
                hora_registro        = COALESCE(p_hora_registro, hora_registro),
                motivo_justificacion = p_motivo_justificacion;
        END");

        // --- Asistencia: obtener por sección y fecha (con hora_registro + motivo) ---
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Asistencia_ObtenerPorSeccionYFecha');
        DB::unprepared("CREATE PROCEDURE sp_Asistencia_ObtenerPorSeccionYFecha(
            IN p_seccion_id SMALLINT,
            IN p_fecha      DATE
        )
        BEGIN
            SELECT a.alumno_id, a.nombres, a.apellidos, a.dni, a.sexo,
                   ast.asistencia_id,
                   COALESCE(ast.estado_asistencia, 'Falta') AS estado_asistencia,
                   ast.observacion,
                   ast.hora_registro,
                   ast.motivo_justificacion
            FROM Alumno a
            LEFT JOIN Asistencia ast ON ast.alumno_id = a.alumno_id AND ast.fecha = p_fecha
            WHERE a.seccion_id = p_seccion_id AND a.estado = 1
            ORDER BY a.apellidos, a.nombres;
        END");

        // --- Asistencia: historial por alumno (con hora_registro + motivo) ---
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Asistencia_HistorialPorAlumno');
        DB::unprepared("CREATE PROCEDURE sp_Asistencia_HistorialPorAlumno(
            IN p_alumno_id INT,
            IN p_mes       TINYINT,
            IN p_año       SMALLINT
        )
        BEGIN
            SELECT ast.asistencia_id, ast.fecha, ast.estado_asistencia, ast.observacion,
                   ast.hora_registro, ast.motivo_justificacion,
                   u.nombre_usuario AS registrado_por
            FROM Asistencia ast
            INNER JOIN Usuario u ON u.usuario_id = ast.usuario_id
            WHERE ast.alumno_id = p_alumno_id
              AND (p_mes IS NULL OR MONTH(ast.fecha) = p_mes)
              AND (p_año IS NULL OR YEAR(ast.fecha) = p_año)
            ORDER BY ast.fecha DESC;
        END");

        // --- Asistencia: resumen por sección (incluye justificadas) ---
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Asistencia_ResumenPorSeccion');
        DB::unprepared("CREATE PROCEDURE sp_Asistencia_ResumenPorSeccion(
            IN p_seccion_id SMALLINT,
            IN p_mes        TINYINT,
            IN p_año        SMALLINT
        )
        BEGIN
            SELECT a.alumno_id, a.apellidos, a.nombres,
                   COUNT(CASE WHEN ast.estado_asistencia = 'Asistio'    THEN 1 END) AS total_asistio,
                   COUNT(CASE WHEN ast.estado_asistencia = 'Falta'      THEN 1 END) AS total_faltas,
                   COUNT(CASE WHEN ast.estado_asistencia = 'Tardanza'   THEN 1 END) AS total_tardanzas,
                   COUNT(CASE WHEN ast.estado_asistencia = 'Justificada' THEN 1 END) AS total_justificadas
            FROM Alumno a
            LEFT JOIN Asistencia ast ON ast.alumno_id = a.alumno_id
                AND MONTH(ast.fecha) = p_mes
                AND YEAR(ast.fecha)  = p_año
            WHERE a.seccion_id = p_seccion_id AND a.estado = 1
            GROUP BY a.alumno_id, a.apellidos, a.nombres
            ORDER BY a.apellidos, a.nombres;
        END");

        // --- Configuración de asistencia ---
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_AsistenciaConfiguracion_Obtener');
        DB::unprepared("CREATE PROCEDURE sp_AsistenciaConfiguracion_Obtener()
        BEGIN
            SELECT config_id, hora_apertura, hora_cierre, hora_limite_tardanza,
                   umbral_alertas_mes, dias_limite_edicion, actualizado_por, actualizado_en
            FROM AsistenciaConfiguracion
            LIMIT 1;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_AsistenciaConfiguracion_Actualizar');
        DB::unprepared("CREATE PROCEDURE sp_AsistenciaConfiguracion_Actualizar(
            IN p_hora_apertura         TIME,
            IN p_hora_cierre           TIME,
            IN p_hora_limite_tardanza  TIME,
            IN p_umbral_alertas_mes    TINYINT UNSIGNED,
            IN p_dias_limite_edicion   TINYINT UNSIGNED,
            IN p_usuario_id            INT
        )
        BEGIN
            UPDATE AsistenciaConfiguracion
            SET hora_apertura        = p_hora_apertura,
                hora_cierre          = p_hora_cierre,
                hora_limite_tardanza = p_hora_limite_tardanza,
                umbral_alertas_mes   = p_umbral_alertas_mes,
                dias_limite_edicion  = p_dias_limite_edicion,
                actualizado_por      = p_usuario_id,
                actualizado_en       = NOW();
        END");

        // --- Auditoría de ediciones de asistencia ---
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_AsistenciaAuditoria_Registrar');
        DB::unprepared("CREATE PROCEDURE sp_AsistenciaAuditoria_Registrar(
            IN p_alumno_id             INT,
            IN p_usuario_id            INT,
            IN p_fecha_asistencia      DATE,
            IN p_estado_anterior       VARCHAR(20),
            IN p_estado_nuevo          VARCHAR(20),
            IN p_observacion_anterior  TEXT,
            IN p_observacion_nueva     TEXT
        )
        BEGIN
            INSERT INTO AsistenciaAuditoria
                (alumno_id, usuario_id, fecha_asistencia, estado_anterior, estado_nuevo,
                 observacion_anterior, observacion_nueva)
            VALUES
                (p_alumno_id, p_usuario_id, p_fecha_asistencia, p_estado_anterior, p_estado_nuevo,
                 p_observacion_anterior, p_observacion_nueva);
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_AsistenciaAuditoria_ListarPorAlumno');
        DB::unprepared("CREATE PROCEDURE sp_AsistenciaAuditoria_ListarPorAlumno(IN p_alumno_id INT)
        BEGIN
            SELECT au.auditoria_id, au.fecha_asistencia, au.estado_anterior, au.estado_nuevo,
                   au.observacion_anterior, au.observacion_nueva, au.created_at,
                   u.nombre_usuario AS editado_por
            FROM AsistenciaAuditoria au
            INNER JOIN Usuario u ON u.usuario_id = au.usuario_id
            WHERE au.alumno_id = p_alumno_id
            ORDER BY au.created_at DESC
            LIMIT 100;
        END");

        // --- Días no hábiles ---
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_DiaNoHabil_Listar');
        DB::unprepared("CREATE PROCEDURE sp_DiaNoHabil_Listar(IN p_año SMALLINT)
        BEGIN
            SELECT dia_no_habil_id, fecha, motivo, usuario_id, created_at
            FROM DiaNoHabil
            WHERE p_año IS NULL OR YEAR(fecha) = p_año
            ORDER BY fecha;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_DiaNoHabil_Insertar');
        DB::unprepared("CREATE PROCEDURE sp_DiaNoHabil_Insertar(
            IN p_fecha      DATE,
            IN p_motivo     VARCHAR(150),
            IN p_usuario_id INT
        )
        BEGIN
            INSERT INTO DiaNoHabil (fecha, motivo, usuario_id) VALUES (p_fecha, p_motivo, p_usuario_id);
            SELECT LAST_INSERT_ID() AS dia_no_habil_id;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_DiaNoHabil_Eliminar');
        DB::unprepared("CREATE PROCEDURE sp_DiaNoHabil_Eliminar(IN p_dia_no_habil_id INT)
        BEGIN
            DELETE FROM DiaNoHabil WHERE dia_no_habil_id = p_dia_no_habil_id;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_DiaNoHabil_ExistePorFecha');
        DB::unprepared("CREATE PROCEDURE sp_DiaNoHabil_ExistePorFecha(IN p_fecha DATE)
        BEGIN
            SELECT COUNT(*) AS existe, MAX(motivo) AS motivo FROM DiaNoHabil WHERE fecha = p_fecha;
        END");
    }

    public function down(): void
    {
        $procedures = [
            'sp_AsistenciaConfiguracion_Obtener', 'sp_AsistenciaConfiguracion_Actualizar',
            'sp_AsistenciaAuditoria_Registrar', 'sp_AsistenciaAuditoria_ListarPorAlumno',
            'sp_DiaNoHabil_Listar', 'sp_DiaNoHabil_Insertar', 'sp_DiaNoHabil_Eliminar',
            'sp_DiaNoHabil_ExistePorFecha',
        ];
        foreach ($procedures as $sp) {
            DB::unprepared("DROP PROCEDURE IF EXISTS {$sp}");
        }

        // Restaurar versiones previas (sin hora_registro/justificación) de los SP compartidos
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
        DB::unprepared("CREATE PROCEDURE sp_Asistencia_ObtenerPorSeccionYFecha(
            IN p_seccion_id SMALLINT,
            IN p_fecha      DATE
        )
        BEGIN
            SELECT a.alumno_id, a.nombres, a.apellidos, a.dni, a.sexo,
                   ast.asistencia_id,
                   COALESCE(ast.estado_asistencia, 'Falta') AS estado_asistencia,
                   ast.observacion
            FROM Alumno a
            LEFT JOIN Asistencia ast ON ast.alumno_id = a.alumno_id AND ast.fecha = p_fecha
            WHERE a.seccion_id = p_seccion_id AND a.estado = 1
            ORDER BY a.apellidos, a.nombres;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Asistencia_HistorialPorAlumno');
        DB::unprepared("CREATE PROCEDURE sp_Asistencia_HistorialPorAlumno(
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
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Asistencia_ResumenPorSeccion');
        DB::unprepared("CREATE PROCEDURE sp_Asistencia_ResumenPorSeccion(
            IN p_seccion_id SMALLINT,
            IN p_mes        TINYINT,
            IN p_año        SMALLINT
        )
        BEGIN
            SELECT a.alumno_id, a.apellidos, a.nombres,
                   COUNT(CASE WHEN ast.estado_asistencia = 'Asistio'  THEN 1 END) AS total_asistio,
                   COUNT(CASE WHEN ast.estado_asistencia = 'Falta'    THEN 1 END) AS total_faltas,
                   COUNT(CASE WHEN ast.estado_asistencia = 'Tardanza' THEN 1 END) AS total_tardanzas
            FROM Alumno a
            LEFT JOIN Asistencia ast ON ast.alumno_id = a.alumno_id
                AND MONTH(ast.fecha) = p_mes
                AND YEAR(ast.fecha)  = p_año
            WHERE a.seccion_id = p_seccion_id AND a.estado = 1
            GROUP BY a.alumno_id, a.apellidos, a.nombres
            ORDER BY a.apellidos, a.nombres;
        END");
    }
};
