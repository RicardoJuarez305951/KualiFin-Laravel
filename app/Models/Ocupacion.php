<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ocupacion extends Model
{
    use HasFactory;
    protected $table = 'ocupaciones';
    public $timestamps = false;
    protected $fillable = [
        'credito_id',
        'actividad',
        'nombre_empresa',
        'calle',
        'numero',
        'colonia',
        'municipio',
        'telefono',
        'antiguedad',
        'monto_percibido',
        'periodo_pago',
        'creado_en',
    ];
    public function credito() { return $this->belongsTo(Credito::class); }
}
