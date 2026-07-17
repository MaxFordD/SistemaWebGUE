<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MesaParte extends Model
{
    protected $table = 'Mesa_Partes';
    protected $primaryKey = 'documento_id';
    public $timestamps = false;

    protected $fillable = [
        'remitente', 'dni', 'correo', 'asunto', 'detalle',
        'archivo', 'tipo_documento_id', 'fecha_envio', 'estado',
    ];

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id', 'tipo_id');
    }
}
