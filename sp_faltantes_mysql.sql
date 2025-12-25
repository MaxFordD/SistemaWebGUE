DELIMITER $$

-- =========================
-- PERSONAS (SP Faltantes)
-- =========================

DROP PROCEDURE IF EXISTS sp_Persona_Actualizar $$
CREATE PROCEDURE sp_Persona_Actualizar(
    IN p_persona_id INT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_dni CHAR(8),
    IN p_telefono CHAR(9),
    IN p_correo VARCHAR(100),
    IN p_estado CHAR(1),
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE v_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al actualizar persona';
    END;

    SELECT COUNT(*) INTO v_count FROM Persona WHERE persona_id = p_persona_id;

    IF v_count = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'La persona no existe';
    ELSE
        UPDATE Persona
        SET nombres = p_nombres,
            apellidos = p_apellidos,
            dni = p_dni,
            telefono = p_telefono,
            correo = p_correo,
            estado = p_estado
        WHERE persona_id = p_persona_id;

        SET p_resultado = 1;
        SET p_mensaje = 'Persona actualizada exitosamente';
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_Persona_Eliminar $$
CREATE PROCEDURE sp_Persona_Eliminar(
    IN p_persona_id INT,
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al eliminar persona';
    END;

    UPDATE Persona
    SET estado = 'I'
    WHERE persona_id = p_persona_id;

    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'La persona no existe';
    ELSE
        SET p_resultado = 1;
        SET p_mensaje = 'Persona eliminada exitosamente';
    END IF;
END$$


-- =========================
-- NOTICIAS (SP Faltantes)
-- =========================

DROP PROCEDURE IF EXISTS sp_Noticia_ListarActivas $$
CREATE PROCEDURE sp_Noticia_ListarActivas()
BEGIN
    SELECT
        n.noticia_id,
        n.titulo,
        LEFT(n.contenido, 300) AS resumen,
        n.contenido,
        n.imagen,
        n.fecha_publicacion,
        CONCAT(p.nombres, ' ', p.apellidos) AS autor
    FROM Noticia n
    INNER JOIN Usuario u ON n.usuario_id = u.usuario_id
    INNER JOIN Persona p ON u.persona_id = p.persona_id
    WHERE n.estado = 'A'
    ORDER BY n.fecha_publicacion DESC;
END$$


-- =========================
-- BITÁCORA (Sistema de Logs)
-- =========================

DROP PROCEDURE IF EXISTS sp_Bitacora_Insertar $$
CREATE PROCEDURE sp_Bitacora_Insertar(
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
END$$


-- =========================
-- ESTADÍSTICAS DEL SISTEMA
-- =========================

DROP PROCEDURE IF EXISTS sp_Sistema_ObtenerEstadisticas $$
CREATE PROCEDURE sp_Sistema_ObtenerEstadisticas()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM Usuario WHERE estado = 'A') AS total_usuarios,
        (SELECT COUNT(*) FROM Persona WHERE estado = 'A') AS total_personas,
        (SELECT COUNT(*) FROM Rol WHERE estado = 'A') AS total_roles,
        (SELECT COUNT(*) FROM Noticia WHERE estado = 'A') AS total_noticias,
        (SELECT COUNT(*) FROM Mesa_Partes WHERE estado = 'Pendiente') AS mesa_pendientes,
        (SELECT COUNT(*) FROM Mesa_Partes WHERE estado = 'Atendido') AS mesa_atendidos;
END$$


-- =========================
-- COMITÉ DIRECTIVO
-- =========================

DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Listar $$
CREATE PROCEDURE sp_ComiteDirectivo_Listar(
    IN p_solo_activos TINYINT
)
BEGIN
    IF p_solo_activos = 1 THEN
        SELECT
            comite_id,
            nombres,
            apellidos,
            cargo,
            foto,
            orden,
            estado
        FROM Comite_Directivo
        WHERE estado = 'A'
        ORDER BY orden ASC, apellidos ASC;
    ELSE
        SELECT
            comite_id,
            nombres,
            apellidos,
            cargo,
            foto,
            orden,
            estado
        FROM Comite_Directivo
        ORDER BY orden ASC, apellidos ASC;
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_ObtenerPorId $$
CREATE PROCEDURE sp_ComiteDirectivo_ObtenerPorId(
    IN p_comite_id INT
)
BEGIN
    SELECT
        comite_id,
        nombres,
        apellidos,
        cargo,
        foto,
        orden,
        estado
    FROM Comite_Directivo
    WHERE comite_id = p_comite_id;
END$$


DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Insertar $$
CREATE PROCEDURE sp_ComiteDirectivo_Insertar(
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_cargo VARCHAR(100),
    IN p_foto VARCHAR(255),
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

    INSERT INTO Comite_Directivo(nombres, apellidos, cargo, foto, orden, estado)
    VALUES(p_nombres, p_apellidos, p_cargo, p_foto, p_orden, 'A');

    SET p_resultado = LAST_INSERT_ID();
    SET p_mensaje = 'Miembro del comité registrado exitosamente';
END$$


DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Actualizar $$
CREATE PROCEDURE sp_ComiteDirectivo_Actualizar(
    IN p_comite_id INT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_cargo VARCHAR(100),
    IN p_foto VARCHAR(255),
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
    SET nombres = p_nombres,
        apellidos = p_apellidos,
        cargo = p_cargo,
        foto = p_foto,
        orden = p_orden,
        estado = p_estado
    WHERE comite_id = p_comite_id;

    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El miembro del comité no existe';
    ELSE
        SET p_resultado = 1;
        SET p_mensaje = 'Miembro del comité actualizado exitosamente';
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Eliminar $$
CREATE PROCEDURE sp_ComiteDirectivo_Eliminar(
    IN p_comite_id INT,
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    UPDATE Comite_Directivo
    SET estado = 'I'
    WHERE comite_id = p_comite_id;

    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El miembro del comité no existe';
    ELSE
        SET p_resultado = 1;
        SET p_mensaje = 'Miembro del comité eliminado exitosamente';
    END IF;
END$$

DELIMITER ;
