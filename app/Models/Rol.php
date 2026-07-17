<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'Rol';
    protected $primaryKey = 'rol_id';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'UsuarioRol', 'rol_id', 'usuario_id');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'RolPermiso', 'rol_id', 'permiso_id');
    }
}
