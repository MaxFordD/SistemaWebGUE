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

    // Autenticación personalizada: usa campo 'contrasena' en lugar de 'password'
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function getAuthIdentifierName()
    {
        return 'usuario_id';
    }

    public function getAuthIdentifier()
    {
        return $this->usuario_id;
    }

    // Override para permitir login con nombre_usuario en lugar de email
    public function getAuthPasswordName()
    {
        return 'contrasena';
    }

    // IMPORTANTE: Este mutator está comentado porque los hashes se insertan
    // manualmente desde SQL. Si se descomenta, hasheará el hash (doble hash).
    // public function setContrasenaAttribute($value)
    // {
    //     $this->attributes['contrasena'] = Hash::make($value);
    // }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'persona_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'UsuarioRol', 'usuario_id', 'rol_id');
    }

    public function hasRole($role)
    {
        return $this->roles()->where('nombre', $role)->exists();
    }

    // Para que Auth::attempt funcione con nombre_usuario
    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}
