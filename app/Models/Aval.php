<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aval extends Model
{
    use HasFactory;
    protected $guarded = [];
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;
    protected $table = 'avales';

    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoAval::class);
    }

    public static function ultimoCreditoActivo(string $curp)
    {
        return static::where('CURP', $curp)
            ->with('credito')
            ->latest('creado_en')
            ->first();
    }
}
