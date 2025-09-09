<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoAnticipo extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'pagos_anticipo';

    public function pagoReal()
    {
        return $this->belongsTo(PagoReal::class);
    }
}

