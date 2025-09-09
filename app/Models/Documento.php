<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }

    public function promotor()
    {
        return $this->belongsTo(Promotor::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class);
    }

    public function ejecutivo()
    {
        return $this->belongsTo(Ejecutivo::class);
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class);
    }
}
