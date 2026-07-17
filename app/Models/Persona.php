<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'Persona';
    protected $primaryKey = 'persona_id';
    public $timestamps = false;

    protected $fillable = ['nombres', 'apellidos', 'dni', 'telefono', 'correo', 'estado'];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'persona_id', 'persona_id');
    }
}
