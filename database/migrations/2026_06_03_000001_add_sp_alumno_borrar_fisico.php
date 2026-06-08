<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_BorrarFisico');
        DB::unprepared('CREATE PROCEDURE sp_Alumno_BorrarFisico(IN p_alumno_id INT)
        BEGIN
            DELETE FROM Asistencia WHERE alumno_id = p_alumno_id;
            DELETE FROM Alumno WHERE alumno_id = p_alumno_id;
        END');
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_Alumno_BorrarFisico');
    }
};
