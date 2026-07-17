DELIMITER $$

-- =========================
-- ROLES
-- =========================

DROP PROCEDURE IF EXISTS sp_Rol_Insertar $$
CREATE PROCEDURE sp_Rol_Insertar(
    IN p_nombre VARCHAR(50),
    IN p_descripcion VARCHAR(200),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE v_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al crear rol';
    END;

    SELECT COUNT(*) INTO v_count FROM Rol WHERE nombre = p_nombre;

    IF v_count > 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El rol ya existe';
    ELSE
        INSERT INTO Rol(nombre, descripcion, estado)
        VALUES(p_nombre, p_descripcion, 'A');

        SET p_resultado = LAST_INSERT_ID();
        SET p_mensaje = 'Rol creado exitosamente';
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_Rol_Listar $$
CREATE PROCEDURE sp_Rol_Listar()
BEGIN
    SELECT rol_id, nombre, descripcion, estado
    FROM Rol
    ORDER BY nombre;
END$$


DROP PROCEDURE IF EXISTS sp_Rol_ObtenerPorId $$
CREATE PROCEDURE sp_Rol_ObtenerPorId(IN p_rol_id INT)
BEGIN
    SELECT rol_id, nombre, descripcion, estado
    FROM Rol
    WHERE rol_id = p_rol_id;
END$$


DROP PROCEDURE IF EXISTS sp_Rol_Actualizar $$
CREATE PROCEDURE sp_Rol_Actualizar(
    IN p_rol_id INT,
    IN p_nombre VARCHAR(50),
    IN p_descripcion VARCHAR(200),
    IN p_estado CHAR(1),
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE v_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al actualizar rol';
    END;

    SELECT COUNT(*) INTO v_count FROM Rol WHERE rol_id = p_rol_id;

    IF v_count = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El rol no existe';
    ELSE
        UPDATE Rol
        SET nombre = p_nombre,
            descripcion = p_descripcion,
            estado = p_estado
        WHERE rol_id = p_rol_id;

        SET p_resultado = 1;
        SET p_mensaje = 'Rol actualizado exitosamente';
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_Rol_Eliminar $$
CREATE PROCEDURE sp_Rol_Eliminar(
    IN p_rol_id INT,
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE v_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al eliminar rol';
    END;

    SELECT COUNT(*) INTO v_count FROM UsuarioRol WHERE rol_id = p_rol_id;

    IF v_count > 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'No se puede eliminar el rol porque tiene usuarios asignados';
    ELSE
        UPDATE Rol SET estado = 'I' WHERE rol_id = p_rol_id;
        SET p_resultado = 1;
        SET p_mensaje = 'Rol eliminado exitosamente';
    END IF;
END$$


-- =========================
-- PERSONAS
-- =========================

DROP PROCEDURE IF EXISTS sp_Persona_Insertar $$
CREATE PROCEDURE sp_Persona_Insertar(
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_dni CHAR(8),
    IN p_telefono CHAR(9),
    IN p_correo VARCHAR(100),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE v_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al registrar persona';
    END;

    SELECT COUNT(*) INTO v_count FROM Persona WHERE dni = p_dni;

    IF p_dni IS NOT NULL AND v_count > 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El DNI ya está registrado';
    ELSE
        INSERT INTO Persona(nombres, apellidos, dni, telefono, correo, estado)
        VALUES(p_nombres, p_apellidos, p_dni, p_telefono, p_correo, 'A');

        SET p_resultado = LAST_INSERT_ID();
        SET p_mensaje = 'Persona registrada exitosamente';
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_Persona_Listar $$
CREATE PROCEDURE sp_Persona_Listar()
BEGIN
    SELECT persona_id, nombres, apellidos, dni, telefono, correo, estado
    FROM Persona
    ORDER BY apellidos, nombres;
END$$


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
-- USUARIOS
-- =========================

DROP PROCEDURE IF EXISTS sp_Usuario_Insertar $$
CREATE PROCEDURE sp_Usuario_Insertar(
    IN p_persona_id INT,
    IN p_nombre_usuario VARCHAR(100),
    IN p_contrasena VARCHAR(200),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE v_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al crear usuario';
    END;

    SELECT COUNT(*) INTO v_count FROM Persona WHERE persona_id = p_persona_id;
    IF v_count = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'La persona no existe';
    ELSE
        SELECT COUNT(*) INTO v_count FROM Usuario WHERE nombre_usuario = p_nombre_usuario;
        IF v_count > 0 THEN
            SET p_resultado = 0;
            SET p_mensaje = 'El nombre de usuario ya existe';
        ELSE
            INSERT INTO Usuario(persona_id, nombre_usuario, contrasena, estado)
            VALUES(p_persona_id, p_nombre_usuario, p_contrasena, 'A');

            SET p_resultado = LAST_INSERT_ID();
            SET p_mensaje = 'Usuario creado exitosamente';
        END IF;
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_Usuario_Listar $$
CREATE PROCEDURE sp_Usuario_Listar()
BEGIN
    SELECT
        u.usuario_id,
        u.persona_id,
        u.nombre_usuario,
        p.nombres,
        p.apellidos,
        p.dni,
        p.telefono,
        p.correo,
        u.estado
    FROM Usuario u
    INNER JOIN Persona p ON u.persona_id = p.persona_id
    ORDER BY p.apellidos, p.nombres;
END$$


DROP PROCEDURE IF EXISTS sp_Usuario_ObtenerPorId $$
CREATE PROCEDURE sp_Usuario_ObtenerPorId(IN p_usuario_id INT)
BEGIN
    SELECT
        u.usuario_id,
        u.persona_id,
        u.nombre_usuario,
        p.nombres,
        p.apellidos,
        p.dni,
        p.telefono,
        p.correo,
        u.estado
    FROM Usuario u
    INNER JOIN Persona p ON u.persona_id = p.persona_id
    WHERE u.usuario_id = p_usuario_id;
END$$


DROP PROCEDURE IF EXISTS sp_Usuario_Actualizar $$
CREATE PROCEDURE sp_Usuario_Actualizar(
    IN p_usuario_id INT,
    IN p_nombre_usuario VARCHAR(100),
    IN p_estado CHAR(1),
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE v_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al actualizar usuario';
    END;

    SELECT COUNT(*) INTO v_count FROM Usuario WHERE usuario_id = p_usuario_id;
    IF v_count = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El usuario no existe';
    ELSE
        SELECT COUNT(*) INTO v_count
        FROM Usuario
        WHERE nombre_usuario = p_nombre_usuario
          AND usuario_id <> p_usuario_id;

        IF v_count > 0 THEN
            SET p_resultado = 0;
            SET p_mensaje = 'El nombre de usuario ya existe';
        ELSE
            UPDATE Usuario
            SET nombre_usuario = p_nombre_usuario,
                estado = p_estado
            WHERE usuario_id = p_usuario_id;

            SET p_resultado = 1;
            SET p_mensaje = 'Usuario actualizado exitosamente';
        END IF;
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_Usuario_Eliminar $$
CREATE PROCEDURE sp_Usuario_Eliminar(
    IN p_usuario_id INT,
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al eliminar usuario';
    END;

    UPDATE Usuario
    SET estado = 'I'
    WHERE usuario_id = p_usuario_id;

    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El usuario no existe';
    ELSE
        SET p_resultado = 1;
        SET p_mensaje = 'Usuario eliminado exitosamente';
    END IF;
END$$


-- =========================
-- LOGIN Y CONTRASEÑA
-- =========================

DROP PROCEDURE IF EXISTS sp_Usuario_ValidarLogin $$
CREATE PROCEDURE sp_Usuario_ValidarLogin(
    IN p_usuario VARCHAR(100),
    IN p_contrasena VARCHAR(200)
)
BEGIN
    SELECT
        u.usuario_id,
        u.persona_id,
        u.nombre_usuario,
        p.nombres,
        p.apellidos,
        p.dni,
        p.correo,
        u.estado
    FROM Usuario u
    INNER JOIN Persona p ON u.persona_id = p.persona_id
    WHERE u.nombre_usuario = p_usuario
      AND u.contrasena = p_contrasena
      AND u.estado = 'A'
      AND p.estado = 'A';
END$$


DROP PROCEDURE IF EXISTS sp_Usuario_CambiarContrasena $$
CREATE PROCEDURE sp_Usuario_CambiarContrasena(
    IN p_usuario_id INT,
    IN p_contrasena_actual VARCHAR(200),
    IN p_contrasena_nueva VARCHAR(200),
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE v_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al cambiar contraseña';
    END;

    SELECT COUNT(*) INTO v_count
    FROM Usuario
    WHERE usuario_id = p_usuario_id
      AND contrasena = p_contrasena_actual;

    IF v_count = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'Contraseña actual incorrecta';
    ELSE
        UPDATE Usuario
        SET contrasena = p_contrasena_nueva
        WHERE usuario_id = p_usuario_id;

        SET p_resultado = 1;
        SET p_mensaje = 'Contraseña actualizada exitosamente';
    END IF;
END$$


-- =========================
-- USUARIO - ROL
-- =========================

DROP PROCEDURE IF EXISTS sp_UsuarioRol_Asignar $$
CREATE PROCEDURE sp_UsuarioRol_Asignar(
    IN p_usuario_id INT,
    IN p_rol_id INT,
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE v_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al asignar rol';
    END;

    SELECT COUNT(*) INTO v_count
    FROM UsuarioRol
    WHERE usuario_id = p_usuario_id AND rol_id = p_rol_id;

    IF v_count > 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El usuario ya tiene asignado este rol';
    ELSE
        INSERT INTO UsuarioRol(usuario_id, rol_id)
        VALUES(p_usuario_id, p_rol_id);

        SET p_resultado = 1;
        SET p_mensaje = 'Rol asignado exitosamente';
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_UsuarioRol_Remover $$
CREATE PROCEDURE sp_UsuarioRol_Remover(
    IN p_usuario_id INT,
    IN p_rol_id INT,
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al remover rol';
    END;

    DELETE FROM UsuarioRol
    WHERE usuario_id = p_usuario_id AND rol_id = p_rol_id;

    SET p_resultado = 1;
    SET p_mensaje = 'Rol removido exitosamente';
END$$


DROP PROCEDURE IF EXISTS sp_UsuarioRol_ListarPorUsuario $$
CREATE PROCEDURE sp_UsuarioRol_ListarPorUsuario(IN p_usuario_id INT)
BEGIN
    SELECT r.rol_id, r.nombre, r.descripcion
    FROM UsuarioRol ur
    INNER JOIN Rol r ON ur.rol_id = r.rol_id
    WHERE ur.usuario_id = p_usuario_id
      AND r.estado = 'A';
END$$


-- =========================
-- NOTICIAS
-- =========================

DROP PROCEDURE IF EXISTS sp_Noticia_Insertar $$
CREATE PROCEDURE sp_Noticia_Insertar(
    IN p_titulo VARCHAR(200),
    IN p_contenido LONGTEXT,
    IN p_imagen VARCHAR(255),
    IN p_usuario_id INT,
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al registrar la noticia';
    END;

    INSERT INTO Noticia(titulo, contenido, imagen, usuario_id, fecha_publicacion, estado)
    VALUES(p_titulo, p_contenido, p_imagen, p_usuario_id, UTC_TIMESTAMP(), 'A');

    SET p_resultado = LAST_INSERT_ID();
    SET p_mensaje = 'Noticia registrada correctamente';
END$$


DROP PROCEDURE IF EXISTS sp_Noticia_Listar $$
CREATE PROCEDURE sp_Noticia_Listar()
BEGIN
    SELECT
        n.noticia_id,
        n.titulo,
        LEFT(n.contenido, 300) AS resumen,
        n.imagen,
        n.fecha_publicacion,
        CONCAT(p.nombres, ' ', p.apellidos) AS autor
    FROM Noticia n
    INNER JOIN Usuario u ON n.usuario_id = u.usuario_id
    INNER JOIN Persona p ON u.persona_id = p.persona_id
    WHERE n.estado = 'A'
    ORDER BY n.fecha_publicacion DESC;
END$$


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


DROP PROCEDURE IF EXISTS sp_Noticia_Listar_Paginado $$
CREATE PROCEDURE sp_Noticia_Listar_Paginado(
    IN p_pagina INT,
    IN p_registros INT
)
BEGIN
    DECLARE v_offset INT;
    SET v_offset = (p_pagina - 1) * p_registros;

    SELECT
        n.noticia_id,
        n.titulo,
        LEFT(n.contenido, 300) AS resumen,
        n.imagen,
        n.fecha_publicacion,
        CONCAT(p.nombres, ' ', p.apellidos) AS autor
    FROM Noticia n
    INNER JOIN Usuario u ON n.usuario_id = u.usuario_id
    INNER JOIN Persona p ON u.persona_id = p.persona_id
    WHERE n.estado = 'A'
    ORDER BY n.fecha_publicacion DESC
    LIMIT v_offset, p_registros;
END$$


DROP PROCEDURE IF EXISTS sp_Noticia_ObtenerPorId $$
CREATE PROCEDURE sp_Noticia_ObtenerPorId(IN p_noticia_id INT)
BEGIN
    SELECT
        n.noticia_id,
        n.titulo,
        n.contenido,
        n.imagen,
        n.estado,
        n.fecha_publicacion,
        CONCAT(p.nombres, ' ', p.apellidos) AS autor
    FROM Noticia n
    INNER JOIN Usuario u ON n.usuario_id = u.usuario_id
    INNER JOIN Persona p ON u.persona_id = p.persona_id
    WHERE n.noticia_id = p_noticia_id;
END$$


DROP PROCEDURE IF EXISTS sp_Noticia_Actualizar $$
CREATE PROCEDURE sp_Noticia_Actualizar(
    IN p_noticia_id INT,
    IN p_titulo VARCHAR(200),
    IN p_contenido LONGTEXT,
    IN p_imagen TEXT,
    IN p_estado CHAR(1),
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al actualizar la noticia';
    END;

    UPDATE Noticia
    SET titulo    = p_titulo,
        contenido = p_contenido,
        imagen    = p_imagen,
        estado    = p_estado
    WHERE noticia_id = p_noticia_id;

    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'La noticia no existe';
    ELSE
        SET p_resultado = 1;
        SET p_mensaje = 'Noticia actualizada correctamente';
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_Noticia_Eliminar $$
CREATE PROCEDURE sp_Noticia_Eliminar(
    IN p_noticia_id INT,
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    UPDATE Noticia
    SET estado = 'I'
    WHERE noticia_id = p_noticia_id;

    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'La noticia no existe';
    ELSE
        SET p_resultado = 1;
        SET p_mensaje = 'Noticia eliminada correctamente';
    END IF;
END$$


-- =========================
-- TIPOS DE DOCUMENTO
-- =========================

DROP PROCEDURE IF EXISTS sp_TipoDocumento_Insertar $$
CREATE PROCEDURE sp_TipoDocumento_Insertar(
    IN p_nombre VARCHAR(50),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE v_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al registrar tipo de documento';
    END;

    SELECT COUNT(*) INTO v_count FROM Tipos_Documento WHERE nombre = p_nombre;

    IF v_count > 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El tipo de documento ya existe';
    ELSE
        INSERT INTO Tipos_Documento(nombre) VALUES(p_nombre);

        SET p_resultado = LAST_INSERT_ID();
        SET p_mensaje = 'Tipo de documento registrado correctamente';
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_TipoDocumento_Listar $$
CREATE PROCEDURE sp_TipoDocumento_Listar()
BEGIN
    SELECT tipo_id, nombre
    FROM Tipos_Documento
    ORDER BY nombre;
END$$


-- =========================
-- MESA DE PARTES
-- =========================

DROP PROCEDURE IF EXISTS sp_MesaPartes_Insertar $$
CREATE PROCEDURE sp_MesaPartes_Insertar(
    IN p_remitente VARCHAR(150),
    IN p_dni CHAR(8),
    IN p_correo VARCHAR(100),
    IN p_asunto VARCHAR(200),
    IN p_detalle LONGTEXT,
    IN p_archivo VARCHAR(255),
    IN p_tipo_documento_id INT,
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al registrar documento';
    END;

    INSERT INTO Mesa_Partes(remitente, dni, correo, asunto, detalle, archivo, tipo_documento_id, fecha_envio, estado)
    VALUES(p_remitente, p_dni, p_correo, p_asunto, p_detalle, p_archivo, p_tipo_documento_id, UTC_TIMESTAMP(), 'Pendiente');

    SET p_resultado = LAST_INSERT_ID();
    SET p_mensaje = 'Documento enviado correctamente';
END$$


DROP PROCEDURE IF EXISTS sp_MesaPartes_Listar $$
CREATE PROCEDURE sp_MesaPartes_Listar()
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
END$$


DROP PROCEDURE IF EXISTS sp_MesaPartes_ObtenerPorId $$
CREATE PROCEDURE sp_MesaPartes_ObtenerPorId(IN p_documento_id INT)
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
END$$


DROP PROCEDURE IF EXISTS sp_MesaPartes_CambiarEstado $$
CREATE PROCEDURE sp_MesaPartes_CambiarEstado(
    IN p_documento_id INT,
    IN p_estado VARCHAR(50),
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    UPDATE Mesa_Partes
    SET estado = p_estado
    WHERE documento_id = p_documento_id;

    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'Documento no encontrado';
    ELSE
        SET p_resultado = 1;
        SET p_mensaje = 'Estado actualizado correctamente';
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_MesaPartes_Eliminar $$
CREATE PROCEDURE sp_MesaPartes_Eliminar(
    IN p_documento_id INT,
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    DELETE FROM Mesa_Partes WHERE documento_id = p_documento_id;

    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'Documento no encontrado';
    ELSE
        SET p_resultado = 1;
        SET p_mensaje = 'Documento eliminado correctamente';
    END IF;
END$$


-- =========================
-- COMITÉ DIRECTIVO
-- =========================

DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Listar $$
CREATE PROCEDURE sp_ComiteDirectivo_Listar(IN p_solo_activos TINYINT)
BEGIN
    IF p_solo_activos = 1 THEN
        SELECT directivo_id, nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado
        FROM Comite_Directivo
        WHERE estado = 'A'
        ORDER BY orden ASC, nombre_completo ASC;
    ELSE
        SELECT directivo_id, nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado
        FROM Comite_Directivo
        ORDER BY orden ASC, nombre_completo ASC;
    END IF;
END$$


DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_ObtenerPorId $$
CREATE PROCEDURE sp_ComiteDirectivo_ObtenerPorId(IN p_directivo_id INT)
BEGIN
    SELECT directivo_id, nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado
    FROM Comite_Directivo
    WHERE directivo_id = p_directivo_id;
END$$


DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Insertar $$
CREATE PROCEDURE sp_ComiteDirectivo_Insertar(
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
END$$


DROP PROCEDURE IF EXISTS sp_ComiteDirectivo_Actualizar $$
CREATE PROCEDURE sp_ComiteDirectivo_Actualizar(
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
    SET nombre_completo = p_nombre_completo,
        cargo = p_cargo,
        grado_cargo = p_grado_cargo,
        foto = p_foto,
        biografia = p_biografia,
        orden = p_orden,
        estado = p_estado
    WHERE directivo_id = p_directivo_id;

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
    IN p_directivo_id INT,
    OUT p_resultado TINYINT,
    OUT p_mensaje VARCHAR(200)
)
BEGIN
    UPDATE Comite_Directivo
    SET estado = 'I'
    WHERE directivo_id = p_directivo_id;

    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El miembro del comité no existe';
    ELSE
        SET p_resultado = 1;
        SET p_mensaje = 'Miembro del comité eliminado exitosamente';
    END IF;
END$$


-- =========================
-- BITÁCORA
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
        (SELECT COUNT(*) FROM Usuario WHERE estado = 'A')             AS total_usuarios,
        (SELECT COUNT(*) FROM Persona WHERE estado = 'A')             AS total_personas,
        (SELECT COUNT(*) FROM Rol WHERE estado = 'A')                 AS total_roles,
        (SELECT COUNT(*) FROM Noticia WHERE estado = 'A')             AS total_noticias,
        (SELECT COUNT(*) FROM Mesa_Partes WHERE estado = 'Pendiente') AS mesa_pendientes,
        (SELECT COUNT(*) FROM Mesa_Partes WHERE estado = 'Revisado')  AS mesa_revisados;
END$$


-- =========================
-- GRADO
-- =========================

DROP PROCEDURE IF EXISTS sp_Grado_Listar $$
CREATE PROCEDURE sp_Grado_Listar()
BEGIN
    SELECT grado_id, nombre, nivel, estado
    FROM Grado
    ORDER BY nivel, nombre;
END$$

DROP PROCEDURE IF EXISTS sp_Grado_ListarActivos $$
CREATE PROCEDURE sp_Grado_ListarActivos()
BEGIN
    SELECT grado_id, nombre, nivel
    FROM Grado
    WHERE estado = 1
    ORDER BY nivel, nombre;
END$$

DROP PROCEDURE IF EXISTS sp_Grado_ObtenerPorId $$
CREATE PROCEDURE sp_Grado_ObtenerPorId(IN p_grado_id SMALLINT)
BEGIN
    SELECT grado_id, nombre, nivel, estado
    FROM Grado
    WHERE grado_id = p_grado_id;
END$$

DROP PROCEDURE IF EXISTS sp_Grado_Insertar $$
CREATE PROCEDURE sp_Grado_Insertar(
    IN p_nombre VARCHAR(30),
    IN p_nivel  ENUM('Primaria','Secundaria')
)
BEGIN
    INSERT INTO Grado (nombre, nivel, estado)
    VALUES (p_nombre, p_nivel, 1);
    SELECT LAST_INSERT_ID() AS grado_id;
END$$

DROP PROCEDURE IF EXISTS sp_Grado_Actualizar $$
CREATE PROCEDURE sp_Grado_Actualizar(
    IN p_grado_id SMALLINT,
    IN p_nombre   VARCHAR(30),
    IN p_nivel    ENUM('Primaria','Secundaria'),
    IN p_estado   TINYINT
)
BEGIN
    UPDATE Grado
    SET nombre = p_nombre, nivel = p_nivel, estado = p_estado
    WHERE grado_id = p_grado_id;
END$$

DROP PROCEDURE IF EXISTS sp_Grado_Eliminar $$
CREATE PROCEDURE sp_Grado_Eliminar(IN p_grado_id SMALLINT)
BEGIN
    UPDATE Grado SET estado = 0 WHERE grado_id = p_grado_id;
END$$


-- =========================
-- SECCION
-- =========================

DROP PROCEDURE IF EXISTS sp_Seccion_Listar $$
CREATE PROCEDURE sp_Seccion_Listar(IN p_año_lectivo SMALLINT)
BEGIN
    SELECT s.seccion_id, s.grado_id, g.nombre AS grado, g.nivel,
           s.nombre AS seccion, s.turno, s.año_lectivo, s.estado
    FROM Seccion s
    INNER JOIN Grado g ON g.grado_id = s.grado_id
    WHERE s.año_lectivo = p_año_lectivo
    ORDER BY g.nivel, g.nombre, s.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_Seccion_ListarActivas $$
CREATE PROCEDURE sp_Seccion_ListarActivas(IN p_año_lectivo SMALLINT)
BEGIN
    SELECT s.seccion_id, s.grado_id, g.nombre AS grado, g.nivel,
           s.nombre AS seccion, s.turno, s.año_lectivo
    FROM Seccion s
    INNER JOIN Grado g ON g.grado_id = s.grado_id
    WHERE s.año_lectivo = p_año_lectivo AND s.estado = 1
    ORDER BY g.nivel, g.nombre, s.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_Seccion_ObtenerPorId $$
CREATE PROCEDURE sp_Seccion_ObtenerPorId(IN p_seccion_id SMALLINT)
BEGIN
    SELECT s.seccion_id, s.grado_id, g.nombre AS grado, g.nivel,
           s.nombre AS seccion, s.turno, s.año_lectivo, s.estado
    FROM Seccion s
    INNER JOIN Grado g ON g.grado_id = s.grado_id
    WHERE s.seccion_id = p_seccion_id;
END$$

DROP PROCEDURE IF EXISTS sp_Seccion_Insertar $$
CREATE PROCEDURE sp_Seccion_Insertar(
    IN p_grado_id     SMALLINT,
    IN p_nombre       VARCHAR(10),
    IN p_turno        ENUM('Mañana','Tarde'),
    IN p_año_lectivo  SMALLINT
)
BEGIN
    INSERT INTO Seccion (grado_id, nombre, turno, año_lectivo, estado)
    VALUES (p_grado_id, p_nombre, p_turno, p_año_lectivo, 1);
    SELECT LAST_INSERT_ID() AS seccion_id;
END$$

DROP PROCEDURE IF EXISTS sp_Seccion_Actualizar $$
CREATE PROCEDURE sp_Seccion_Actualizar(
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
END$$

DROP PROCEDURE IF EXISTS sp_Seccion_Eliminar $$
CREATE PROCEDURE sp_Seccion_Eliminar(IN p_seccion_id SMALLINT)
BEGIN
    UPDATE Seccion SET estado = 0 WHERE seccion_id = p_seccion_id;
END$$


-- =========================
-- ALUMNO
-- =========================

DROP PROCEDURE IF EXISTS sp_Alumno_ListarPorSeccion $$
CREATE PROCEDURE sp_Alumno_ListarPorSeccion(IN p_seccion_id SMALLINT)
BEGIN
    SELECT alumno_id, seccion_id, nombres, apellidos, dni,
           fecha_nacimiento, sexo, estado
    FROM Alumno
    WHERE seccion_id = p_seccion_id
    ORDER BY apellidos, nombres;
END$$

DROP PROCEDURE IF EXISTS sp_Alumno_ListarActivosPorSeccion $$
CREATE PROCEDURE sp_Alumno_ListarActivosPorSeccion(IN p_seccion_id SMALLINT)
BEGIN
    SELECT alumno_id, nombres, apellidos, dni, sexo
    FROM Alumno
    WHERE seccion_id = p_seccion_id AND estado = 1
    ORDER BY apellidos, nombres;
END$$

DROP PROCEDURE IF EXISTS sp_Alumno_ObtenerPorId $$
CREATE PROCEDURE sp_Alumno_ObtenerPorId(IN p_alumno_id INT)
BEGIN
    SELECT a.alumno_id, a.seccion_id, a.nombres, a.apellidos, a.dni,
           a.fecha_nacimiento, a.sexo, a.estado,
           s.nombre AS seccion, g.nombre AS grado, g.nivel
    FROM Alumno a
    INNER JOIN Seccion s ON s.seccion_id = a.seccion_id
    INNER JOIN Grado g ON g.grado_id = s.grado_id
    WHERE a.alumno_id = p_alumno_id;
END$$

DROP PROCEDURE IF EXISTS sp_Alumno_Insertar $$
CREATE PROCEDURE sp_Alumno_Insertar(
    IN p_seccion_id      SMALLINT,
    IN p_nombres         VARCHAR(100),
    IN p_apellidos       VARCHAR(100),
    IN p_dni             VARCHAR(8),
    IN p_fecha_nacimiento DATE,
    IN p_sexo            ENUM('M','F')
)
BEGIN
    INSERT INTO Alumno (seccion_id, nombres, apellidos, dni, fecha_nacimiento, sexo, estado)
    VALUES (p_seccion_id, p_nombres, p_apellidos, p_dni, p_fecha_nacimiento, p_sexo, 1);
    SELECT LAST_INSERT_ID() AS alumno_id;
END$$

DROP PROCEDURE IF EXISTS sp_Alumno_Actualizar $$
CREATE PROCEDURE sp_Alumno_Actualizar(
    IN p_alumno_id       INT,
    IN p_seccion_id      SMALLINT,
    IN p_nombres         VARCHAR(100),
    IN p_apellidos       VARCHAR(100),
    IN p_dni             VARCHAR(8),
    IN p_fecha_nacimiento DATE,
    IN p_sexo            ENUM('M','F'),
    IN p_estado          TINYINT
)
BEGIN
    UPDATE Alumno
    SET seccion_id = p_seccion_id, nombres = p_nombres, apellidos = p_apellidos,
        dni = p_dni, fecha_nacimiento = p_fecha_nacimiento, sexo = p_sexo, estado = p_estado
    WHERE alumno_id = p_alumno_id;
END$$

DROP PROCEDURE IF EXISTS sp_Alumno_Eliminar $$
CREATE PROCEDURE sp_Alumno_Eliminar(IN p_alumno_id INT)
BEGIN
    UPDATE Alumno SET estado = 0 WHERE alumno_id = p_alumno_id;
END$$

DROP PROCEDURE IF EXISTS sp_Alumno_BorrarFisico $$
CREATE PROCEDURE sp_Alumno_BorrarFisico(IN p_alumno_id INT)
BEGIN
    DELETE FROM Asistencia WHERE alumno_id = p_alumno_id;
    DELETE FROM Alumno WHERE alumno_id = p_alumno_id;
END$$


-- =========================
-- ASISTENCIA
-- =========================

DROP PROCEDURE IF EXISTS sp_Asistencia_RegistrarOActualizar $$
CREATE PROCEDURE sp_Asistencia_RegistrarOActualizar(
    IN p_alumno_id          INT,
    IN p_usuario_id         INT,
    IN p_fecha              DATE,
    IN p_estado_asistencia  ENUM('Asistio','Falta','Tardanza'),
    IN p_observacion        TEXT
)
BEGIN
    INSERT INTO Asistencia (alumno_id, usuario_id, fecha, estado_asistencia, observacion)
    VALUES (p_alumno_id, p_usuario_id, p_fecha, p_estado_asistencia, p_observacion)
    ON DUPLICATE KEY UPDATE
        estado_asistencia = p_estado_asistencia,
        observacion       = p_observacion,
        usuario_id        = p_usuario_id;
END$$

DROP PROCEDURE IF EXISTS sp_Asistencia_ObtenerPorSeccionYFecha $$
CREATE PROCEDURE sp_Asistencia_ObtenerPorSeccionYFecha(
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
END$$

DROP PROCEDURE IF EXISTS sp_Asistencia_HistorialPorAlumno $$
CREATE PROCEDURE sp_Asistencia_HistorialPorAlumno(
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
END$$

DROP PROCEDURE IF EXISTS sp_Asistencia_ResumenPorSeccion $$
CREATE PROCEDURE sp_Asistencia_ResumenPorSeccion(
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
END$$


-- =========================
-- HISTORIA Y LEGADO
-- =========================

DROP PROCEDURE IF EXISTS sp_HistoriaLegado_Listar $$
CREATE PROCEDURE sp_HistoriaLegado_Listar()
BEGIN
    SELECT item_id, tipo, titulo, contenido, archivo, url_video, orden, estado, created_at
    FROM Historia_Legado
    WHERE estado = 'A'
    ORDER BY orden ASC, created_at ASC;
END$$

DROP PROCEDURE IF EXISTS sp_HistoriaLegado_ObtenerPorId $$
CREATE PROCEDURE sp_HistoriaLegado_ObtenerPorId(IN p_item_id INT)
BEGIN
    SELECT item_id, tipo, titulo, contenido, archivo, url_video, orden, estado
    FROM Historia_Legado
    WHERE item_id = p_item_id;
END$$

DROP PROCEDURE IF EXISTS sp_HistoriaLegado_Insertar $$
CREATE PROCEDURE sp_HistoriaLegado_Insertar(
    IN p_tipo      VARCHAR(10),
    IN p_titulo    VARCHAR(200),
    IN p_contenido TEXT,
    IN p_archivo   VARCHAR(500),
    IN p_url_video VARCHAR(500),
    IN p_orden     SMALLINT,
    OUT p_resultado INT,
    OUT p_mensaje   VARCHAR(200)
)
BEGIN
    INSERT INTO Historia_Legado(tipo, titulo, contenido, archivo, url_video, orden)
    VALUES(p_tipo, p_titulo, p_contenido, p_archivo, p_url_video, p_orden);
    SET p_resultado = LAST_INSERT_ID();
    SET p_mensaje   = 'Elemento registrado correctamente';
END$$

DROP PROCEDURE IF EXISTS sp_HistoriaLegado_Actualizar $$
CREATE PROCEDURE sp_HistoriaLegado_Actualizar(
    IN p_item_id   INT,
    IN p_tipo      VARCHAR(10),
    IN p_titulo    VARCHAR(200),
    IN p_contenido TEXT,
    IN p_archivo   VARCHAR(500),
    IN p_url_video VARCHAR(500),
    IN p_orden     SMALLINT,
    IN p_estado    CHAR(1),
    OUT p_resultado INT,
    OUT p_mensaje   VARCHAR(200)
)
BEGIN
    UPDATE Historia_Legado
    SET tipo=p_tipo, titulo=p_titulo, contenido=p_contenido,
        archivo=p_archivo, url_video=p_url_video, orden=p_orden, estado=p_estado
    WHERE item_id = p_item_id;
    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0; SET p_mensaje = 'Elemento no encontrado';
    ELSE
        SET p_resultado = 1; SET p_mensaje = 'Elemento actualizado correctamente';
    END IF;
END$$

DROP PROCEDURE IF EXISTS sp_HistoriaLegado_Eliminar $$
CREATE PROCEDURE sp_HistoriaLegado_Eliminar(
    IN p_item_id   INT,
    OUT p_resultado INT,
    OUT p_mensaje   VARCHAR(200)
)
BEGIN
    UPDATE Historia_Legado SET estado='I' WHERE item_id = p_item_id;
    IF ROW_COUNT() = 0 THEN
        SET p_resultado = 0; SET p_mensaje = 'Elemento no encontrado';
    ELSE
        SET p_resultado = 1; SET p_mensaje = 'Elemento eliminado correctamente';
    END IF;
END$$

DELIMITER ;

-- =========================
-- DATOS / AJUSTES ADICIONALES
-- =========================

-- Rol Auxiliar para gestión de asistencia (módulo de asistencia)
INSERT IGNORE INTO Rol (nombre, descripcion) VALUES ('Auxiliar', 'Gestión de asistencia de alumnos');
