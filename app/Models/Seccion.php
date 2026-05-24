<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'Seccion';
    protected $primaryKey = 'seccion_id';
    public $timestamps = false;

    protected $fillable = ['grado_id', 'nombre', 'turno', 'año_lectivo', 'estado'];

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id', 'grado_id');
    }

    public function alumnos()
    {
        return $this->hasMany(Alumno::class, 'seccion_id', 'seccion_id');
    }
}
