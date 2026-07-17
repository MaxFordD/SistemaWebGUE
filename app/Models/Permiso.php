<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'Permiso';
    protected $primaryKey = 'permiso_id';
    public $timestamps = false;

    protected $fillable = ['slug', 'nombre', 'modulo', 'descripcion', 'estado'];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'RolPermiso', 'permiso_id', 'rol_id');
    }
}
