<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\tipoDocumento;

class MesaParte extends Model
{
    use HasFactory;

    // Nombre exacto de la tabla en tu BD
    protected $table = 'Mesa_Partes';
    protected $primaryKey = 'documento_id';
    public $timestamps = false; // ya tienes campo fecha_envio manejado por SQL

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'remitente',
        'dni',
        'correo',
        'asunto',
        'detalle',
        'archivo',
        'tipo_documento_id',
        'estado',
    ];

    // Relación con Tipos_Documento
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id', 'tipo_id');
    }
}
