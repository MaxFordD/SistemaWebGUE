<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComiteDirectivo extends Model
{
    use HasFactory;

    protected $table = 'Comite_Directivo';
    protected $primaryKey = 'directivo_id';
    public $timestamps = false;

    protected $fillable = [
        'nombre_completo',
        'cargo',
        'grado_cargo',
        'foto',
        'biografia',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    // Scope para obtener solo directivos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para ordenar por campo orden
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc');
    }
}
