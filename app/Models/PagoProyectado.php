<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoProyectado extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'pagos_proyectados';
    public $timestamps = false;

    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }

    public function pagosReales()
    {
        return $this->hasMany(PagoReal::class);
    }
}
