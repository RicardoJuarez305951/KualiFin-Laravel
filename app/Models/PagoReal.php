<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoReal extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'pagos_reales';

    public function pagoProyectado()
    {
        return $this->belongsTo(PagoProyectado::class);
    }
}
