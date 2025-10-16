<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'Persona';
    protected $primaryKey = 'persona_id';
    public $timestamps = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'dni',
        'telefono',
        'correo',
        'estado'
    ];

    // Relación con Usuario (uno a uno)
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'persona_id', 'persona_id');
    }

    // Accessor para obtener nombre completo
    public function getNombreCompletoAttribute()
    {
        return trim($this->apellidos . ' ' . $this->nombres);
    }
}
