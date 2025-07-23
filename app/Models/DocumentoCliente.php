<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoCliente extends Model
{
    use HasFactory;
    protected $table = 'documentos_clientes';
    public $timestamps = false;
    protected $fillable = [
        'cliente_id',
        'credito_id',
        'tipo_doc',
        'url_s3',
        'nombre_arch',
        'creado_en',
    ];
    public function cliente() { return $this->belongsTo(Cliente::class); }
}
