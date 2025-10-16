<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    // Si la tabla en la base de datos tiene un nombre diferente, defínelo aquí
    protected $table = 'Rol';

    // La clave primaria
    protected $primaryKey = 'rol_id';

    // Si no estás usando timestamps, pon esto en false
    public $timestamps = false;

    // Definir la relación con los usuarios
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'UsuarioRol', 'rol_id', 'usuario_id');
    }
}
