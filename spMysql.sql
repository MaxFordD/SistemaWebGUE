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

    INSERT INTO Noticia(
        titulo,
        contenido,
        imagen,
        usuario_id,
        fecha_publicacion,
        estado
    ) VALUES (
        p_titulo,
        p_contenido,
        p_imagen,
        p_usuario_id,
        UTC_TIMESTAMP(),
        'A'
    );

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
        n.fecha_publicacion,
        CONCAT(p.nombres, ' ', p.apellidos) AS autor
    FROM Noticia n
    INNER JOIN Usuario u ON n.usuario_id = u.usuario_id
    INNER JOIN Persona p ON u.persona_id = p.persona_id
    WHERE n.noticia_id = p_noticia_id
      AND n.estado = 'A';
END$$


DROP PROCEDURE IF EXISTS sp_Noticia_Actualizar $$
CREATE PROCEDURE sp_Noticia_Actualizar(
    IN p_noticia_id INT,
    IN p_titulo VARCHAR(200),
    IN p_contenido LONGTEXT,
    IN p_imagen VARCHAR(255),
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
    SET
        titulo = p_titulo,
        contenido = p_contenido,
        imagen = p_imagen
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

    SELECT COUNT(*) INTO v_count
    FROM Tipos_Documento
    WHERE nombre = p_nombre;

    IF v_count > 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El tipo de documento ya existe';
    ELSE
        INSERT INTO Tipos_Documento(nombre)
        VALUES(p_nombre);

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

    INSERT INTO Mesa_Partes(
        remitente,
        dni,
        correo,
        asunto,
        detalle,
        archivo,
        tipo_documento_id,
        fecha_envio,
        estado
    ) VALUES (
        p_remitente,
        p_dni,
        p_correo,
        p_asunto,
        p_detalle,
        p_archivo,
        p_tipo_documento_id,
        UTC_TIMESTAMP(),
        'Pendiente'
    );

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
    INNER JOIN Tipos_Documento td
        ON mp.tipo_documento_id = td.tipo_id
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
        mp.fecha_envio,
        mp.estado
    FROM Mesa_Partes mp
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

DELIMITER ;
