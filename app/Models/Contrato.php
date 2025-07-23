<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;
    protected $table = 'contratos';
    public $timestamps = false;
    protected $fillable = [
        'credito_id',
        'nombre_plantilla',
        'url_doc',
        'generado_en',
    ];
    public function credito() { return $this->belongsTo(Credito::class); }
}
