<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    protected $table = 'Grado';
    protected $primaryKey = 'grado_id';
    public $timestamps = false;

    protected $fillable = ['nombre', 'nivel', 'estado'];

    public function secciones()
    {
        return $this->hasMany(Seccion::class, 'grado_id', 'grado_id');
    }
}
