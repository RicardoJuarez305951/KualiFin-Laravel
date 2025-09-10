<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garantia extends Model
{
    use HasFactory;
    protected $guarded = [];
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }
}
