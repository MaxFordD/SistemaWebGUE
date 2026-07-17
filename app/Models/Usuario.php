<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'Usuario';
    protected $primaryKey = 'usuario_id';
    public $timestamps = false;

    protected $fillable = ['persona_id', 'nombre_usuario', 'contrasena', 'estado'];
    protected $hidden = ['contrasena'];

    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function getAuthIdentifierName()
    {
        return 'usuario_id';
    }

    public function getAuthIdentifier()
    {
        return $this->usuario_id;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

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

    public function hasPermission(string $slug): bool
    {
        return $this->roles()
            ->whereHas('permisos', fn($q) => $q->where('slug', $slug)->where('estado', 'A'))
            ->exists();
    }
}
