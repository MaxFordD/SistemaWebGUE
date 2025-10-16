<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    use HasFactory;

    protected $table = 'Noticia'; // Tabla en la base de datos
    protected $primaryKey = 'noticia_id'; // Clave primaria
    public $timestamps = false; // Si no usas campos 'created_at' y 'updated_at'

    protected $fillable = [
        'titulo',
        'contenido',
        'imagen',
        'usuario_id',
        'fecha_publicacion',
        'estado',
    ];

    protected $casts = [
        'fecha_publicacion' => 'datetime',
    ];

    // Método para obtener archivos como array
    public function getArchivosAttribute()
    {
        if (empty($this->imagen)) {
            return [];
        }
        
        return array_filter(array_map('trim', explode(';', $this->imagen)));
    }

    // Método para verificar si tiene archivos
    public function tieneArchivos()
    {
        return !empty($this->imagen);
    }
}