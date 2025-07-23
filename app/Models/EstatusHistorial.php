<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstatusHistorial extends Model
{
    use HasFactory;
    protected $table = 'estatus_historial';
    public $timestamps = false;
    protected $fillable = [
        'credito_id',
        'user_id',
        'estado_actualizado',
        'comentario',
        'cambiado_en',
    ];
    public function credito() { return $this->belongsTo(Credito::class); }
    public function user() { return $this->belongsTo(User::class); }
}
