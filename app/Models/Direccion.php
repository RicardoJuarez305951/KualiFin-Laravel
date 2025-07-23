<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory;
    protected $table = 'direcciones';
    protected $fillable = [
        'credito_id',
        'calle',
        'numero_ext',
        'numero_int',
        'monto_mensual',
        'colonia',
        'municipio',
        'estado',
        'cp',
        'tiempo_residencia',
        'tel_fijo',
        'tel_cel',
        'tipo_de_vivienda',
        'creado_en',
    ];
    public $timestamps = false;
    public function credito() { return $this->belongsTo(Credito::class); }
}
