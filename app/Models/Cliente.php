<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table        = 'clientes';
    protected $primaryKey   = 'id';
    public    $incrementing = true;
    protected $keyType      = 'int';

    // —————————————————————————————————————————————————————————
    // 1) Habilitar timestamps y mapearlos a tus columnas:
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    public  $timestamps  = true;
    // —————————————————————————————————————————————————————————

    // 2) Campos que puedes asignar en masa
    protected $fillable = [
        'nombre',
        'apellido_p',
        'apellido_m',
        'curp',
        'fecha_nac',
        'edad',
        'sexo',
        'estado_civil',
        'validez',
        'activo',
        // No es necesario incluir 'creado_en' ni 'actualizado_en' aquí,
        // pues Eloquent los gestionará automáticamente.
    ];

    // (Opcional) Si quieres que tu campo fecha_nac venga como instancia Carbon:
    protected $casts = [
        'fecha_nac' => 'date',
        'activo'    => 'boolean',
    ];

    // 3) Relación con documentos
    public function documentos()
    {
        // Ajusta el nombre de tu modelo de documento si no es 'DocumentoCliente'
        return $this->hasMany(DocumentoCliente::class, 'cliente_id', 'id');
    }
}
