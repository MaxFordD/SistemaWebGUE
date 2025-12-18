<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'Usuario';
    protected $primaryKey = 'usuario_id';
    public $timestamps = false;

    protected $fillable = ['persona_id', 'nombre_usuario', 'contrasena', 'estado'];
    protected $hidden = ['contrasena'];

    // Especificar el campo de password para autenticación
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    // Especificar el campo usado para el username (para Laravel)
    public function getAuthIdentifierName()
    {
        return 'nombre_usuario';
    }

    public function getAuthIdentifier()
    {
        return $this->nombre_usuario;
    }

    // COMENTAR ESTE MUTATOR SI INSERTAMOS HASHES MANUALMENTE EN LA BD
    // public function setContrasenaAttribute($value)
    // {
    //     $this->attributes['contrasena'] = Hash::make($value);
    // }

    // Relación con Persona
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'persona_id');
    }

    // Relación con Rol
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'UsuarioRol', 'usuario_id', 'rol_id');
    }

    // Verifica si el usuario tiene un rol específico
    public function hasRole($role)
    {
        return $this->roles()->where('nombre', $role)->exists();
    }
}
