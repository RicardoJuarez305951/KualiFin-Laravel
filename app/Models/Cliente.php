<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    public $timestamps = false; 

    protected $fillable = [
        'promotor_id',
        'CURP',
        'nombre',
        'apellido_p',
        'apellido_m',
        'fecha_nacimiento',
        'tiene_credito_activo',
        'estatus',
        'monto_maximo',
        'creado_en',
        'actualizado_en',
        'activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'tiene_credito_activo' => 'boolean',
        'activo' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];
    
    public function promotor()
    {
        return $this->belongsTo(Promotor::class, 'promotor_id');
    }

    public function creditos()
    {
        return $this->hasMany(Credito::class, 'cliente_id');
    }

    public function credito()
    {
        return $this->hasOne(Credito::class, 'cliente_id')->latestOfMany();
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoCliente::class, 'cliente_id');
    }
}
