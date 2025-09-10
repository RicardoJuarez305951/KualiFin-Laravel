<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngresoAdicional extends Model
{
    use HasFactory;
    protected $guarded = [];
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;
    protected $table = 'ingresos_adicionales';

    public function ocupacion()
    {
        return $this->belongsTo(Ocupacion::class);
    }
}
