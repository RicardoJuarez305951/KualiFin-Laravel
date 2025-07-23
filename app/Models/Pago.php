<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;
    protected $table = 'pagos';
    public $timestamps = false;
    protected $fillable = [
        'credito_id',
        'monto',
        'fecha_pago',
        'tipo_pago',
        'creado_en',
    ];
    public function credito() { return $this->belongsTo(Credito::class); }
}
