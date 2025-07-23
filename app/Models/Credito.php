<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    protected $table = 'creditos';
    protected $fillable = [
        'cliente_id',
        'users_id',
        'monto_solic',
        'plazo_meses',
        'tasa_interes',
        'estado',
        'fecha_solicitud',
        'fecha_desembolso',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function user() { return $this->belongsTo(User::class, 'users_id'); }
}
