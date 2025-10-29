-- =============================================
-- Script de Actualización: sp_Usuario_CambiarContrasena
-- Fecha: 2025-10-29
-- Descripción: Simplifica el SP eliminando validación redundante
--              La validación de contraseña actual ahora se hace en Laravel
-- =============================================

USE BDSistemaWebGUE;
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

-- Verificación: Mostrar la definición del procedimiento
PRINT 'Stored Procedure sp_Usuario_CambiarContrasena actualizado exitosamente';
PRINT 'Parámetros actuales:';
PRINT '  - @usuario_id INT';
PRINT '  - @contrasena_nueva NVARCHAR(200)';
PRINT '  - @resultado BIT OUTPUT';
PRINT '  - @mensaje VARCHAR(200) OUTPUT';
PRINT '';
PRINT 'NOTA: El parámetro @contrasena_actual fue eliminado.';
PRINT 'La validación de la contraseña actual ahora se realiza en Laravel.';
GO
