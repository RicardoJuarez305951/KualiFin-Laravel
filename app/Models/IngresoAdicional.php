<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngresoAdicional extends Model
{
    use HasFactory;
    protected $table = 'ingresos_adicionales';
    public $timestamps = false;
    protected $fillable = [
        'ocupacion_id',
        'concepto',
        'monto',
        'frecuencia',
        'creado_en',
    ];
    public function ocupacion() { return $this->belongsTo(Ocupacion::class); }
}
