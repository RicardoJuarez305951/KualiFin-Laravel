<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garantia extends Model
{
    use HasFactory;
    protected $table = 'garantias';
    public $timestamps = false;
    protected $fillable = [
        'credito_id',
        'tipo',
        'marca',
        'modelo',
        'num_serie',
        'antiguedad',
        'foto_url',
        'creado_en',
    ];
    public function credito() { return $this->belongsTo(Credito::class); }
}
