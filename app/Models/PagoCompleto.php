<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoCompleto extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'pagos_completos';
    const UPDATED_AT = null;

    public function pagoReal()
    {
        return $this->belongsTo(PagoReal::class);
    }
}

