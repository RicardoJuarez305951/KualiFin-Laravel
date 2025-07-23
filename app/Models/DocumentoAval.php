<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoAval extends Model
{
    use HasFactory;
    protected $table = 'documentos_avales';
    public $timestamps = false;
    protected $fillable = [
        'aval_id',
        'tipo_doc',
        'url_s3',
        'nombre_arch',
        'creado_en',
    ];
    public function aval() { return $this->belongsTo(Aval::class); }
}
