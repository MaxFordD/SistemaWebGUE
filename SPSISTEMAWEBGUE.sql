-- =============================================
-- STORED PROCEDURES: BDSistemaWebGUE
-- Sistema Web para Institución Educativa
-- =============================================

USE BDSistemaWebGUE;
GO

-- =============================================
-- MÓDULO: ROLES - CRUD COMPLETO
-- =============================================

-- CREATE - Crear Rol
CREATE OR ALTER PROCEDURE sp_Rol_Insertar
    @nombre VARCHAR(50),
    @descripcion VARCHAR(200) = NULL,
    @resultado INT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF EXISTS(SELECT 1 FROM Rol WHERE nombre = @nombre)
        BEGIN
            SET @mensaje = 'El rol ya existe';
            RETURN;
        END

        INSERT INTO Rol (nombre, descripcion, estado)
        VALUES (@nombre, @descripcion, 'A');

        SET @resultado = SCOPE_IDENTITY();
        SET @mensaje = 'Rol creado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- READ - Listar Roles
CREATE OR ALTER PROCEDURE sp_Rol_Listar
AS
BEGIN
    SET NOCOUNT ON;
    SELECT rol_id, nombre, descripcion, estado
    FROM Rol
    ORDER BY nombre;
END
GO

-- READ - Obtener Rol por ID
CREATE OR ALTER PROCEDURE sp_Rol_ObtenerPorId
    @rol_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT rol_id, nombre, descripcion, estado
    FROM Rol
    WHERE rol_id = @rol_id;
END
GO

-- UPDATE - Actualizar Rol
CREATE OR ALTER PROCEDURE sp_Rol_Actualizar
    @rol_id INT,
    @nombre VARCHAR(50),
    @descripcion VARCHAR(200) = NULL,
    @estado CHAR(1),
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Rol WHERE rol_id = @rol_id)
        BEGIN
            SET @mensaje = 'El rol no existe';
            RETURN;
        END

        IF EXISTS(SELECT 1 FROM Rol WHERE nombre = @nombre AND rol_id != @rol_id)
        BEGIN
            SET @mensaje = 'Ya existe otro rol con ese nombre';
            RETURN;
        END

        UPDATE Rol
        SET nombre = @nombre,
            descripcion = @descripcion,
            estado = @estado
        WHERE rol_id = @rol_id;

        SET @resultado = 1;
        SET @mensaje = 'Rol actualizado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- DELETE - Eliminar Rol (Lógico)
CREATE OR ALTER PROCEDURE sp_Rol_Eliminar
    @rol_id INT,
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Rol WHERE rol_id = @rol_id)
        BEGIN
            SET @mensaje = 'El rol no existe';
            RETURN;
        END

        -- Verificar si hay usuarios con este rol
        IF EXISTS(SELECT 1 FROM UsuarioRol WHERE rol_id = @rol_id)
        BEGIN
            SET @mensaje = 'No se puede eliminar el rol porque tiene usuarios asignados';
            RETURN;
        END

        -- Eliminación lógica
        UPDATE Rol SET estado = 'I' WHERE rol_id = @rol_id;

        SET @resultado = 1;
        SET @mensaje = 'Rol eliminado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- =============================================
-- MÓDULO: PERSONAS - CRUD COMPLETO
-- =============================================

-- CREATE - Crear Persona
CREATE OR ALTER PROCEDURE sp_Persona_Insertar
    @nombres NVARCHAR(100),
    @apellidos NVARCHAR(100),
    @dni CHAR(8) = NULL,
    @telefono CHAR(9) = NULL,
    @correo VARCHAR(100) = NULL,
    @resultado INT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF @dni IS NOT NULL AND EXISTS(SELECT 1 FROM Persona WHERE dni = @dni)
        BEGIN
            SET @mensaje = 'El DNI ya está registrado';
            RETURN;
        END

        IF @correo IS NOT NULL AND EXISTS(SELECT 1 FROM Persona WHERE correo = @correo)
        BEGIN
            SET @mensaje = 'El correo ya está registrado';
            RETURN;
        END

        INSERT INTO Persona (nombres, apellidos, dni, telefono, correo, estado)
        VALUES (@nombres, @apellidos, @dni, @telefono, @correo, 'A');

        SET @resultado = SCOPE_IDENTITY();
        SET @mensaje = 'Persona registrada exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- READ - Listar Personas
CREATE OR ALTER PROCEDURE sp_Persona_Listar
AS
BEGIN
    SET NOCOUNT ON;
    SELECT persona_id, nombres, apellidos, dni, telefono, correo, estado
    FROM Persona
    ORDER BY apellidos, nombres;
END
GO

-- READ - Obtener Persona por ID
CREATE OR ALTER PROCEDURE sp_Persona_ObtenerPorId
    @persona_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT persona_id, nombres, apellidos, dni, telefono, correo, estado
    FROM Persona
    WHERE persona_id = @persona_id;
END
GO

-- UPDATE - Actualizar Persona
CREATE OR ALTER PROCEDURE sp_Persona_Actualizar
    @persona_id INT,
    @nombres NVARCHAR(100),
    @apellidos NVARCHAR(100),
    @dni CHAR(8) = NULL,
    @telefono CHAR(9) = NULL,
    @correo VARCHAR(100) = NULL,
    @estado CHAR(1),
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Persona WHERE persona_id = @persona_id)
        BEGIN
            SET @mensaje = 'La persona no existe';
            RETURN;
        END

        IF @dni IS NOT NULL AND EXISTS(SELECT 1 FROM Persona WHERE dni = @dni AND persona_id != @persona_id)
        BEGIN
            SET @mensaje = 'El DNI ya está registrado';
            RETURN;
        END

        IF @correo IS NOT NULL AND EXISTS(SELECT 1 FROM Persona WHERE correo = @correo AND persona_id != @persona_id)
        BEGIN
            SET @mensaje = 'El correo ya está registrado';
            RETURN;
        END

        UPDATE Persona
        SET nombres = @nombres,
            apellidos = @apellidos,
            dni = @dni,
            telefono = @telefono,
            correo = @correo,
            estado = @estado
        WHERE persona_id = @persona_id;

        SET @resultado = 1;
        SET @mensaje = 'Persona actualizada exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- DELETE - Eliminar Persona (Lógico)
CREATE OR ALTER PROCEDURE sp_Persona_Eliminar
    @persona_id INT,
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Persona WHERE persona_id = @persona_id)
        BEGIN
            SET @mensaje = 'La persona no existe';
            RETURN;
        END

        -- Verificar si la persona tiene usuario asociado
        IF EXISTS(SELECT 1 FROM Usuario WHERE persona_id = @persona_id)
        BEGIN
            SET @mensaje = 'No se puede eliminar la persona porque tiene un usuario asociado';
            RETURN;
        END

        -- Eliminación lógica
        UPDATE Persona SET estado = 'I' WHERE persona_id = @persona_id;

        SET @resultado = 1;
        SET @mensaje = 'Persona eliminada exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- =============================================
-- MÓDULO: USUARIOS - CRUD COMPLETO
-- =============================================

-- CREATE - Crear Usuario
CREATE OR ALTER PROCEDURE sp_Usuario_Insertar
    @persona_id INT,
    @nombre_usuario VARCHAR(100),
    @contrasena NVARCHAR(200),
    @resultado INT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Persona WHERE persona_id = @persona_id)
        BEGIN
            SET @mensaje = 'La persona no existe';
            RETURN;
        END

        IF EXISTS(SELECT 1 FROM Usuario WHERE nombre_usuario = @nombre_usuario)
        BEGIN
            SET @mensaje = 'El nombre de usuario ya existe';
            RETURN;
        END

        INSERT INTO Usuario (persona_id, nombre_usuario, contrasena, estado)
        VALUES (@persona_id, @nombre_usuario, @contrasena, 'A');

        SET @resultado = SCOPE_IDENTITY();
        SET @mensaje = 'Usuario creado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- READ - Listar Usuarios
CREATE OR ALTER PROCEDURE sp_Usuario_Listar
AS
BEGIN
    SET NOCOUNT ON;

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
END
GO

-- READ - Obtener Usuario por ID
CREATE OR ALTER PROCEDURE sp_Usuario_ObtenerPorId
    @usuario_id INT
AS
BEGIN
    SET NOCOUNT ON;

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
    WHERE u.usuario_id = @usuario_id;
END
GO

-- UPDATE - Actualizar Usuario
CREATE OR ALTER PROCEDURE sp_Usuario_Actualizar
    @usuario_id INT,
    @nombre_usuario VARCHAR(100),
    @estado CHAR(1),
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Usuario WHERE usuario_id = @usuario_id)
        BEGIN
            SET @mensaje = 'El usuario no existe';
            RETURN;
        END

        IF EXISTS(SELECT 1 FROM Usuario WHERE nombre_usuario = @nombre_usuario AND usuario_id != @usuario_id)
        BEGIN
            SET @mensaje = 'El nombre de usuario ya existe';
            RETURN;
        END

        UPDATE Usuario
        SET nombre_usuario = @nombre_usuario,
            estado = @estado
        WHERE usuario_id = @usuario_id;

        SET @resultado = 1;
        SET @mensaje = 'Usuario actualizado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- DELETE - Eliminar Usuario (Lógico)
CREATE OR ALTER PROCEDURE sp_Usuario_Eliminar
    @usuario_id INT,
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Usuario WHERE usuario_id = @usuario_id)
        BEGIN
            SET @mensaje = 'El usuario no existe';
            RETURN;
        END

        -- Eliminación lógica
        UPDATE Usuario SET estado = 'I' WHERE usuario_id = @usuario_id;

        SET @resultado = 1;
        SET @mensaje = 'Usuario eliminado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- EXTRA - Validar Login
CREATE OR ALTER PROCEDURE sp_Usuario_ValidarLogin
    @nombre_usuario VARCHAR(100),
    @contrasena NVARCHAR(200)
AS
BEGIN
    SET NOCOUNT ON;

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
    WHERE u.nombre_usuario = @nombre_usuario
      AND u.contrasena = @contrasena
      AND u.estado = 'A'
      AND p.estado = 'A';
END
GO

-- EXTRA - Cambiar Contraseña
-- NOTA: La validación de la contraseña actual se realiza en Laravel usando Hash::check()
-- Este SP solo actualiza la contraseña después de que Laravel validó la autenticidad
CREATE OR ALTER PROCEDURE sp_Usuario_CambiarContrasena
    @usuario_id INT,
    @contrasena_nueva NVARCHAR(200),
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        -- Verificar que el usuario existe
        IF NOT EXISTS(SELECT 1 FROM Usuario WHERE usuario_id = @usuario_id)
        BEGIN
            SET @mensaje = 'Usuario no encontrado';
            RETURN;
        END

        -- Actualizar la contraseña (Laravel ya validó la actual)
        UPDATE Usuario
        SET contrasena = @contrasena_nueva
        WHERE usuario_id = @usuario_id;

        SET @resultado = 1;
        SET @mensaje = 'Contraseña actualizada exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- =============================================
-- MÓDULO: USUARIO-ROL - OPERACIONES
-- =============================================

-- Asignar Rol a Usuario
CREATE OR ALTER PROCEDURE sp_UsuarioRol_Asignar
    @usuario_id INT,
    @rol_id INT,
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF EXISTS(SELECT 1 FROM UsuarioRol WHERE usuario_id = @usuario_id AND rol_id = @rol_id)
        BEGIN
            SET @mensaje = 'El usuario ya tiene asignado este rol';
            RETURN;
        END

        INSERT INTO UsuarioRol (usuario_id, rol_id)
        VALUES (@usuario_id, @rol_id);

        SET @resultado = 1;
        SET @mensaje = 'Rol asignado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- Remover Rol de Usuario
CREATE OR ALTER PROCEDURE sp_UsuarioRol_Remover
    @usuario_id INT,
    @rol_id INT,
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        DELETE FROM UsuarioRol
        WHERE usuario_id = @usuario_id AND rol_id = @rol_id;

        SET @resultado = 1;
        SET @mensaje = 'Rol removido exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- Listar Roles de Usuario
CREATE OR ALTER PROCEDURE sp_UsuarioRol_ListarPorUsuario
    @usuario_id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT r.rol_id, r.nombre AS nombre_rol, r.descripcion
    FROM UsuarioRol ur
    INNER JOIN Rol r ON ur.rol_id = r.rol_id
    WHERE ur.usuario_id = @usuario_id AND r.estado = 'A';
END
GO

-- =============================================
-- MÓDULO: NOTICIAS - CRUD COMPLETO
-- =============================================

-- CREATE - Crear Noticia
CREATE OR ALTER PROCEDURE sp_Noticia_Insertar
    @titulo NVARCHAR(200),
    @contenido NVARCHAR(MAX),
    @imagen VARCHAR(255) = NULL,
    @usuario_id INT,
    @resultado INT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        INSERT INTO Noticia (titulo, contenido, imagen, usuario_id, estado, fecha_publicacion)
        VALUES (@titulo, @contenido, @imagen, @usuario_id, 'A', GETDATE());

        SET @resultado = SCOPE_IDENTITY();
        SET @mensaje = 'Noticia publicada exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- READ - Listar Noticias (Admin)
CREATE OR ALTER PROCEDURE sp_Noticia_Listar
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        n.noticia_id,
        n.titulo,
        n.contenido,
        n.imagen,
        n.fecha_publicacion,
        n.estado,
        n.usuario_id,
        u.nombre_usuario,
        ISNULL(p.nombres + ' ' + p.apellidos, 'Redacción GUE') AS autor
    FROM Noticia n
    INNER JOIN Usuario u ON n.usuario_id = u.usuario_id
    LEFT JOIN Persona p ON u.persona_id = p.persona_id
    ORDER BY n.fecha_publicacion DESC;
END
GO

-- READ - Obtener Noticia por ID
CREATE OR ALTER PROCEDURE sp_Noticia_ObtenerPorId
    @noticia_id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        n.noticia_id,
        n.titulo,
        n.contenido,
        n.imagen,
        n.fecha_publicacion,
        n.estado,
        n.usuario_id,
        u.nombre_usuario,
        ISNULL(p.nombres + ' ' + p.apellidos, 'Redacción GUE') AS autor
    FROM Noticia n
    INNER JOIN Usuario u ON n.usuario_id = u.usuario_id
    LEFT JOIN Persona p ON u.persona_id = p.persona_id
    WHERE n.noticia_id = @noticia_id;
END
GO

-- READ - Listar Noticias Activas (Para público)
CREATE OR ALTER PROCEDURE sp_Noticia_ListarActivas
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        n.noticia_id,
        n.titulo,
        n.contenido,
        n.imagen,
        n.fecha_publicacion,
        u.nombre_usuario,
        ISNULL(p.nombres + ' ' + p.apellidos, 'Redacción GUE') AS autor
    FROM Noticia n
    INNER JOIN Usuario u ON n.usuario_id = u.usuario_id
    LEFT JOIN Persona p ON u.persona_id = p.persona_id
    WHERE n.estado = 'A'
    ORDER BY n.fecha_publicacion DESC;
END
GO

-- UPDATE - Actualizar Noticia
CREATE OR ALTER PROCEDURE sp_Noticia_Actualizar
    @noticia_id INT,
    @titulo NVARCHAR(200),
    @contenido NVARCHAR(MAX),
    @imagen VARCHAR(255) = NULL,
    @estado CHAR(1),
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Noticia WHERE noticia_id = @noticia_id)
        BEGIN
            SET @mensaje = 'La noticia no existe';
            RETURN;
        END

        UPDATE Noticia
        SET titulo = @titulo,
            contenido = @contenido,
            imagen = @imagen,
            estado = @estado
        WHERE noticia_id = @noticia_id;

        SET @resultado = 1;
        SET @mensaje = 'Noticia actualizada exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- DELETE - Eliminar Noticia (Lógico)
CREATE OR ALTER PROCEDURE sp_Noticia_Eliminar
    @noticia_id INT,
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Noticia WHERE noticia_id = @noticia_id)
        BEGIN
            SET @mensaje = 'La noticia no existe';
            RETURN;
        END

        -- Eliminación lógica
        UPDATE Noticia SET estado = 'I' WHERE noticia_id = @noticia_id;

        SET @resultado = 1;
        SET @mensaje = 'Noticia eliminada exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- =============================================
-- MÓDULO: MESA DE PARTES - CRUD COMPLETO
-- =============================================

-- CREATE - Registrar Documento
CREATE OR ALTER PROCEDURE sp_MesaPartes_Insertar
    @remitente NVARCHAR(150),
    @dni CHAR(8) = NULL,
    @correo VARCHAR(100) = NULL,
    @asunto NVARCHAR(200),
    @detalle NVARCHAR(MAX) = NULL,
    @archivo VARCHAR(255) = NULL,
    @tipo_documento_id INT = 4,
    @resultado INT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS (SELECT 1 FROM Tipos_Documento WHERE tipo_id = @tipo_documento_id)
        BEGIN
            SET @mensaje = 'El tipo de documento especificado no existe';
            RETURN;
        END

        INSERT INTO Mesa_Partes (remitente, dni, correo, asunto, detalle, archivo, tipo_documento_id, estado)
        VALUES (@remitente, @dni, @correo, @asunto, @detalle, @archivo, @tipo_documento_id, N'Pendiente');

        SET @resultado = SCOPE_IDENTITY();
        SET @mensaje = 'Documento registrado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- READ - Listar Documentos
CREATE OR ALTER PROCEDURE sp_MesaPartes_Listar
    @estado NVARCHAR(50) = NULL,
    @tipo_documento_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        mp.documento_id,
        mp.remitente,
        mp.dni,
        mp.correo,
        mp.asunto,
        mp.detalle,
        mp.archivo,
        td.nombre AS tipo_documento,
        mp.fecha_envio,
        mp.estado
    FROM Mesa_Partes mp
    INNER JOIN Tipos_Documento td ON mp.tipo_documento_id = td.tipo_id
    WHERE (@estado IS NULL OR mp.estado = @estado)
      AND (@tipo_documento_id IS NULL OR mp.tipo_documento_id = @tipo_documento_id)
    ORDER BY mp.fecha_envio DESC;
END
GO

-- READ - Obtener Documento por ID
CREATE OR ALTER PROCEDURE sp_MesaPartes_ObtenerPorId
    @documento_id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        mp.documento_id,
        mp.remitente,
        mp.dni,
        mp.correo,
        mp.asunto,
        mp.detalle,
        mp.archivo,
        td.nombre AS tipo_documento,
        mp.fecha_envio,
        mp.estado
    FROM Mesa_Partes mp
    INNER JOIN Tipos_Documento td ON mp.tipo_documento_id = td.tipo_id
    WHERE mp.documento_id = @documento_id;
END
GO

-- UPDATE - Actualizar Estado de Documento
CREATE OR ALTER PROCEDURE sp_MesaPartes_ActualizarEstado
    @documento_id INT,
    @estado NVARCHAR(50),
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS (SELECT 1 FROM Mesa_Partes WHERE documento_id = @documento_id)
        BEGIN
            SET @mensaje = 'El documento no existe';
            RETURN;
        END

        UPDATE Mesa_Partes
        SET estado = @estado
        WHERE documento_id = @documento_id;

        SET @resultado = 1;
        SET @mensaje = 'Estado actualizado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- DELETE - Eliminar Documento (Físico)
CREATE OR ALTER PROCEDURE sp_MesaPartes_Eliminar
    @documento_id INT,
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS (SELECT 1 FROM Mesa_Partes WHERE documento_id = @documento_id)
        BEGIN
            SET @mensaje = 'El documento no existe';
            RETURN;
        END

        DELETE FROM Mesa_Partes WHERE documento_id = @documento_id;

        SET @resultado = 1;
        SET @mensaje = 'Documento eliminado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- =============================================
-- MÓDULO: COMITÉ DIRECTIVO - CRUD COMPLETO
-- =============================================

-- CREATE - Insertar Directivo
CREATE OR ALTER PROCEDURE sp_ComiteDirectivo_Insertar
    @nombre_completo NVARCHAR(200),
    @cargo NVARCHAR(100),
    @grado_cargo NVARCHAR(100) = NULL,
    @foto VARCHAR(500) = NULL,
    @biografia NVARCHAR(MAX) = NULL,
    @orden INT = 0,
    @estado CHAR(1) = 'A',
    @resultado INT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF LTRIM(RTRIM(@nombre_completo)) = ''
        BEGIN
            SET @mensaje = 'El nombre completo es obligatorio';
            RETURN;
        END

        IF LTRIM(RTRIM(@cargo)) = ''
        BEGIN
            SET @mensaje = 'El cargo es obligatorio';
            RETURN;
        END

        INSERT INTO Comite_Directivo (nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado)
        VALUES (@nombre_completo, @cargo, @grado_cargo, @foto, @biografia, @orden, @estado);

        SET @resultado = SCOPE_IDENTITY();
        SET @mensaje = 'Directivo registrado correctamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = 'Error: ' + ERROR_MESSAGE();
    END CATCH
END
GO

-- READ - Listar Comité Directivo
CREATE OR ALTER PROCEDURE sp_ComiteDirectivo_Listar
    @solo_activos BIT = 1
AS
BEGIN
    SET NOCOUNT ON;

    IF @solo_activos = 1
    BEGIN
        SELECT directivo_id, nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado
        FROM Comite_Directivo
        WHERE estado = 'A'
        ORDER BY orden ASC, nombre_completo ASC;
    END
    ELSE
    BEGIN
        SELECT directivo_id, nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado
        FROM Comite_Directivo
        ORDER BY orden ASC, nombre_completo ASC;
    END
END
GO

-- READ - Obtener Directivo por ID
CREATE OR ALTER PROCEDURE sp_ComiteDirectivo_ObtenerPorId
    @directivo_id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT directivo_id, nombre_completo, cargo, grado_cargo, foto, biografia, orden, estado
    FROM Comite_Directivo
    WHERE directivo_id = @directivo_id;
END
GO

-- UPDATE - Actualizar Directivo
CREATE OR ALTER PROCEDURE sp_ComiteDirectivo_Actualizar
    @directivo_id INT,
    @nombre_completo NVARCHAR(200),
    @cargo NVARCHAR(100),
    @grado_cargo NVARCHAR(100) = NULL,
    @foto VARCHAR(500) = NULL,
    @biografia NVARCHAR(MAX) = NULL,
    @orden INT = 0,
    @estado CHAR(1) = 'A',
    @resultado INT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS (SELECT 1 FROM Comite_Directivo WHERE directivo_id = @directivo_id)
        BEGIN
            SET @mensaje = 'El directivo no existe';
            RETURN;
        END

        IF LTRIM(RTRIM(@nombre_completo)) = ''
        BEGIN
            SET @mensaje = 'El nombre completo es obligatorio';
            RETURN;
        END

        UPDATE Comite_Directivo
        SET nombre_completo = @nombre_completo,
            cargo = @cargo,
            grado_cargo = @grado_cargo,
            foto = @foto,
            biografia = @biografia,
            orden = @orden,
            estado = @estado
        WHERE directivo_id = @directivo_id;

        SET @resultado = @directivo_id;
        SET @mensaje = 'Directivo actualizado correctamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = 'Error: ' + ERROR_MESSAGE();
    END CATCH
END
GO

-- DELETE - Eliminar Directivo (Lógico)
CREATE OR ALTER PROCEDURE sp_ComiteDirectivo_Eliminar
    @directivo_id INT,
    @resultado INT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS (SELECT 1 FROM Comite_Directivo WHERE directivo_id = @directivo_id)
        BEGIN
            SET @mensaje = 'El directivo no existe';
            RETURN;
        END

        UPDATE Comite_Directivo
        SET estado = 'I'
        WHERE directivo_id = @directivo_id;

        SET @resultado = 1;
        SET @mensaje = 'Directivo desactivado correctamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = 'Error: ' + ERROR_MESSAGE();
    END CATCH
END
GO

-- =============================================
-- MÓDULO: BITÁCORA - CRUD COMPLETO
-- =============================================

-- CREATE - Registrar Acción
CREATE OR ALTER PROCEDURE sp_Bitacora_Insertar
    @usuario_id INT,
    @accion NVARCHAR(200),
    @resultado INT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        INSERT INTO Bitacora (usuario_id, accion)
        VALUES (@usuario_id, @accion);

        SET @resultado = SCOPE_IDENTITY();
        SET @mensaje = 'Acción registrada exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- READ - Listar Bitácora
CREATE OR ALTER PROCEDURE sp_Bitacora_Listar
    @fecha_inicio DATETIME2 = NULL,
    @fecha_fin DATETIME2 = NULL,
    @usuario_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        b.bitacora_id,
        b.accion,
        b.fecha,
        b.usuario_id,
        u.nombre_usuario,
        p.nombres + ' ' + p.apellidos AS usuario
    FROM Bitacora b
    INNER JOIN Usuario u ON b.usuario_id = u.usuario_id
    INNER JOIN Persona p ON u.persona_id = p.persona_id
    WHERE (@fecha_inicio IS NULL OR b.fecha >= @fecha_inicio)
      AND (@fecha_fin IS NULL OR b.fecha <= @fecha_fin)
      AND (@usuario_id IS NULL OR b.usuario_id = @usuario_id)
    ORDER BY b.fecha DESC;
END
GO

-- =============================================
-- MÓDULO: MENSAJES - CRUD COMPLETO
-- =============================================

-- CREATE - Enviar Mensaje
CREATE OR ALTER PROCEDURE sp_Mensaje_Insertar
    @remitente_usuario_id INT,
    @destinatario_usuario_id INT,
    @asunto NVARCHAR(200) = NULL,
    @cuerpo NVARCHAR(MAX),
    @resultado INT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Usuario WHERE usuario_id = @remitente_usuario_id)
        BEGIN
            SET @mensaje = 'El usuario remitente no existe';
            RETURN;
        END

        IF NOT EXISTS(SELECT 1 FROM Usuario WHERE usuario_id = @destinatario_usuario_id)
        BEGIN
            SET @mensaje = 'El usuario destinatario no existe';
            RETURN;
        END

        INSERT INTO Mensaje (remitente_usuario_id, destinatario_usuario_id, asunto, cuerpo)
        VALUES (@remitente_usuario_id, @destinatario_usuario_id, @asunto, @cuerpo);

        SET @resultado = SCOPE_IDENTITY();
        SET @mensaje = 'Mensaje enviado exitosamente';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- READ - Listar Mensajes Recibidos
CREATE OR ALTER PROCEDURE sp_Mensaje_ListarRecibidos
    @usuario_id INT,
    @solo_no_leidos BIT = 0
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        m.mensaje_id,
        m.asunto,
        m.cuerpo,
        m.creado_en,
        m.leido_en,
        m.remitente_usuario_id,
        u.nombre_usuario AS remitente_usuario,
        p.nombres + ' ' + p.apellidos AS remitente_nombre
    FROM Mensaje m
    INNER JOIN Usuario u ON m.remitente_usuario_id = u.usuario_id
    INNER JOIN Persona p ON u.persona_id = p.persona_id
    WHERE m.destinatario_usuario_id = @usuario_id
      AND (@solo_no_leidos = 0 OR m.leido_en IS NULL)
    ORDER BY m.creado_en DESC;
END
GO

-- UPDATE - Marcar Mensaje como Leído
CREATE OR ALTER PROCEDURE sp_Mensaje_MarcarLeido
    @mensaje_id INT,
    @resultado BIT OUTPUT,
    @mensaje VARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET @resultado = 0;

    BEGIN TRY
        IF NOT EXISTS(SELECT 1 FROM Mensaje WHERE mensaje_id = @mensaje_id)
        BEGIN
            SET @mensaje = 'El mensaje no existe';
            RETURN;
        END

        UPDATE Mensaje
        SET leido_en = SYSUTCDATETIME()
        WHERE mensaje_id = @mensaje_id AND leido_en IS NULL;

        SET @resultado = 1;
        SET @mensaje = 'Mensaje marcado como leído';
    END TRY
    BEGIN CATCH
        SET @mensaje = ERROR_MESSAGE();
    END CATCH
END
GO

-- =============================================
-- PROCEDIMIENTOS ADICIONALES
-- =============================================

-- Obtener Estadísticas del Sistema
CREATE OR ALTER PROCEDURE sp_Sistema_ObtenerEstadisticas
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        (SELECT COUNT(*) FROM Usuario WHERE estado = 'A') AS usuarios_activos,
        (SELECT COUNT(*) FROM Persona WHERE estado = 'A') AS personas_activas,
        (SELECT COUNT(*) FROM Rol WHERE estado = 'A') AS roles_activos,
        (SELECT COUNT(*) FROM Noticia WHERE estado = 'A') AS noticias_activas,
        (SELECT COUNT(*) FROM Mesa_Partes WHERE estado = N'Pendiente') AS documentos_pendientes,
        (SELECT COUNT(*) FROM Mensaje WHERE leido_en IS NULL) AS mensajes_no_leidos,
        (SELECT COUNT(*) FROM Comite_Directivo WHERE estado = 'A') AS directivos_activos;
END
GO

-- Buscar Usuarios
CREATE OR ALTER PROCEDURE sp_Usuario_Buscar
    @criterio NVARCHAR(100)
AS
BEGIN
    SET NOCOUNT ON;

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
    WHERE u.nombre_usuario LIKE '%' + @criterio + '%'
       OR p.nombres LIKE '%' + @criterio + '%'
       OR p.apellidos LIKE '%' + @criterio + '%'
       OR p.dni LIKE '%' + @criterio + '%'
       OR p.correo LIKE '%' + @criterio + '%'
    ORDER BY p.apellidos, p.nombres;
END
GO

-- Buscar Noticias
CREATE OR ALTER PROCEDURE sp_Noticia_Buscar
    @criterio NVARCHAR(200)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        n.noticia_id,
        n.titulo,
        n.contenido,
        n.imagen,
        n.fecha_publicacion,
        n.estado,
        u.nombre_usuario,
        ISNULL(p.nombres + ' ' + p.apellidos, 'Redacción GUE') AS autor
    FROM Noticia n
    INNER JOIN Usuario u ON n.usuario_id = u.usuario_id
    LEFT JOIN Persona p ON u.persona_id = p.persona_id
    WHERE n.estado = 'A'
      AND (n.titulo LIKE '%' + @criterio + '%' OR n.contenido LIKE '%' + @criterio + '%')
    ORDER BY n.fecha_publicacion DESC;
END
GO

PRINT '=============================================';
PRINT 'Stored Procedures creados exitosamente';
PRINT '=============================================';
PRINT 'Total de SPs: 44';
PRINT '- Roles: 5 SPs';
PRINT '- Personas: 5 SPs';
PRINT '- Usuarios: 7 SPs';
PRINT '- Usuario-Rol: 3 SPs';
PRINT '- Noticias: 5 SPs + 1 búsqueda';
PRINT '- Mesa de Partes: 5 SPs';
PRINT '- Comité Directivo: 5 SPs';
PRINT '- Bitácora: 2 SPs';
PRINT '- Mensajes: 4 SPs';
PRINT '- Adicionales: 2 SPs';
PRINT '=============================================';
