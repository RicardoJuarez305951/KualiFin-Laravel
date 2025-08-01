<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aval extends Model
{
    use HasFactory;
    protected $table = 'avales';
    public $timestamps = false;
    protected $fillable = [
        'credito_id',
        'nombre',
        'apellido_p',
        'apellido_m',
        'curp',
        'direccion',
        'telefono',
        'parentesco',
        'creado_en',
    ];
    public function credito() { return $this->belongsTo(Credito::class); }
}
