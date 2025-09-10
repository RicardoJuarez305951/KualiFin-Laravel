<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $guarded = [];
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    public function promotor()
    {
        return $this->belongsTo(Promotor::class);
    }

    public function credito()
    {
        return $this->hasOne(Credito::class);
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoCliente::class);
    }
}
