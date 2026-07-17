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
    protected $appends = ['primera_imagen', 'imagenes', 'fecha_formateada', 'archivos', 'videos'];

    // Todos los valores del campo imagen que NO son URLs (rutas de archivo)
    public function getArchivosAttribute()
    {
        if (empty($this->imagen)) return [];
        $all = array_filter(array_map('trim', explode(';', $this->imagen)));
        return array_values(array_filter($all, fn($a) => !str_starts_with($a, 'http')));
    }

    // Solo archivos de imagen (jpg, png, gif, webp)
    public function getImagenesAttribute()
    {
        return array_values(array_filter($this->archivos, function ($archivo) {
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        }));
    }

    // Solo URLs de video (YouTube, Facebook, etc.)
    public function getVideosAttribute()
    {
        if (empty($this->imagen)) return [];
        $all = array_filter(array_map('trim', explode(';', $this->imagen)));
        return array_values(array_filter($all, fn($a) => str_starts_with($a, 'http')));
    }

    // Convierte URL pública a URL de embed
    public static function embedUrl(string $url): string
    {
        // YouTube: watch?v= o youtu.be/
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        // Facebook video
        if (str_contains($url, 'facebook.com') || str_contains($url, 'fb.watch')) {
            return 'https://www.facebook.com/plugins/video.php?href=' . urlencode($url) . '&show_text=false&width=auto';
        }
        return $url;
    }

    // Detecta la plataforma del video
    public static function plataforma(string $url): string
    {
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) return 'youtube';
        if (str_contains($url, 'facebook.com') || str_contains($url, 'fb.watch')) return 'facebook';
        return 'otro';
    }

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