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

    // Agregar accessors automáticos
    protected $appends = ['primera_imagen', 'imagenes', 'fecha_formateada', 'archivos'];

    // Método para obtener archivos como array
    public function getArchivosAttribute()
    {
        if (empty($this->imagen)) {
            return [];
        }

        return array_filter(array_map('trim', explode(';', $this->imagen)));
    }

    // Método para obtener solo imágenes (filtra por extensión)
    public function getImagenesAttribute()
    {
        if (empty($this->imagen)) {
            return [];
        }

        $archivos = array_filter(array_map('trim', explode(';', $this->imagen)));
        return array_filter($archivos, function($archivo) {
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        });
    }

    // Método para obtener la primera imagen disponible
    public function getPrimeraImagenAttribute()
    {
        $imagenes = $this->imagenes;
        return count($imagenes) > 0 ? reset($imagenes) : null;
    }

    // Método para obtener fecha formateada en español
    public function getFechaFormateadaAttribute()
    {
        if (!$this->fecha_publicacion) {
            return null;
        }

        // Parsear si viene como string desde stored procedure
        $fecha = is_string($this->fecha_publicacion)
            ? \Carbon\Carbon::parse($this->fecha_publicacion)
            : $this->fecha_publicacion;

        return $fecha->format('d/m/Y');
    }

    // Método para verificar si tiene archivos
    public function tieneArchivos()
    {
        return !empty($this->imagen);
    }
}