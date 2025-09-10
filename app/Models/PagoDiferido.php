<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoDiferido extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'pagos_diferidos';

    public function pagoReal()
    {
        return $this->belongsTo(PagoReal::class);
    }
}

