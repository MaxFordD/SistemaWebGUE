<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    use HasFactory;

    protected $table = 'Tipos_Documento';
    protected $primaryKey = 'tipo_id';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    // Relación inversa (un tipo tiene muchos documentos)
    public function documentos()
    {
        return $this->hasMany(MesaParte::class, 'tipo_documento_id', 'tipo_id');
    }

    public function create()
    {
        $tipos = TipoDocumento::orderBy('nombre')->get();
        return view('mesa.create', compact('tipos'));
    }
}
