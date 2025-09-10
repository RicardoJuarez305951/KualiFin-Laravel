<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ocupacion extends Model
{
    use HasFactory;
    protected $guarded = [];
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;
    protected $table = 'ocupaciones';

    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }

    public function ingresosAdicionales()
    {
        return $this->hasMany(IngresoAdicional::class);
    }
}
