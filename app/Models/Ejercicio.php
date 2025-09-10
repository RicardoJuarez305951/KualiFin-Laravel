<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ejercicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'supervisor_id',
        'ejecutivo_id',
        'fecha_inicio',
        'fecha_final',
        'venta_objetivo',
        'dinero_autorizado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_final' => 'date',
        'venta_objetivo' => 'decimal:2',
        'dinero_autorizado' => 'decimal:2',
    ];

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class);
    }

    public function ejecutivo()
    {
        return $this->belongsTo(Ejecutivo::class);
    }
}
