<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Rol_EliminarFisico');
        DB::unprepared("CREATE PROCEDURE sp_Rol_EliminarFisico(
            IN p_rol_id INT,
            OUT p_resultado TINYINT,
            OUT p_mensaje VARCHAR(200)
        )
        BEGIN
            DECLARE v_count  INT;
            DECLARE v_estado CHAR(1);

            DECLARE EXIT HANDLER FOR SQLEXCEPTION
            BEGIN
                SET p_resultado = 0;
                SET p_mensaje = 'Error al eliminar rol';
            END;

            SELECT estado INTO v_estado FROM Rol WHERE rol_id = p_rol_id;

            IF v_estado IS NULL THEN
                SET p_resultado = 0;
                SET p_mensaje = 'El rol no existe';
            ELSEIF v_estado <> 'I' THEN
                SET p_resultado = 0;
                SET p_mensaje = 'El rol debe estar inactivo antes de eliminarlo definitivamente';
            ELSE
                SELECT COUNT(*) INTO v_count FROM UsuarioRol WHERE rol_id = p_rol_id;
                IF v_count > 0 THEN
                    SET p_resultado = 0;
                    SET p_mensaje = 'No se puede eliminar: el rol tiene usuarios asignados';
                ELSE
                    DELETE FROM RolPermiso WHERE rol_id = p_rol_id;
                    DELETE FROM Rol WHERE rol_id = p_rol_id;
                    SET p_resultado = 1;
                    SET p_mensaje = 'Rol eliminado permanentemente';
                END IF;
            END IF;
        END");

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Rol_Listar');
        DB::unprepared("CREATE PROCEDURE sp_Rol_Listar()
        BEGIN
            SELECT rol_id, nombre, descripcion, estado
            FROM Rol
            ORDER BY rol_id;
        END");
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Rol_EliminarFisico');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Rol_Listar');
        DB::unprepared("CREATE PROCEDURE sp_Rol_Listar()
        BEGIN
            SELECT rol_id, nombre, descripcion, estado
            FROM Rol
            ORDER BY nombre;
        END");
    }
};
