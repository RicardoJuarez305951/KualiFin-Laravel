<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $guarded = [];

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
