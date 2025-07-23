<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformacionFamiliar extends Model
{
    use HasFactory;
    protected $table = 'informacion_familiares';
    public $timestamps = false;
    protected $fillable = [
        'credito_id',
        'nombre_conyuge',
        'celular_conyuge',
        'actividad_conyuge',
        'ingresos_semanales_conyuge',
        'domicilio_trabajo_conyuge',
        'numero_hijos',
        'personas_en_domicilio',
        'dependientes_economicos',
        'conyuge_vive_con_cliente',
        'creado_en',
    ];
    public function credito() { return $this->belongsTo(Credito::class); }
}
