<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'Alumno';
    protected $primaryKey = 'alumno_id';
    public $timestamps = false;

    protected $fillable = [
        'seccion_id', 'nombres', 'apellidos', 'dni',
        'fecha_nacimiento', 'sexo', 'estado',
    ];

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'seccion_id', 'seccion_id');
    }

    public function getNombreCompletoAttribute()
    {
        return trim($this->apellidos . ', ' . $this->nombres);
    }
}
