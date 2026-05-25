<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagenInicio extends Model
{
    protected $table = 'imagenes_inicio';

    protected $fillable = ['seccion', 'orden', 'ruta', 'alt', 'titulo', 'descripcion', 'icono', 'activo'];
}
