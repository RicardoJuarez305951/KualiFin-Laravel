<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ejercicio extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class);
    }

    public function ejecutivo()
    {
        return $this->belongsTo(Ejecutivo::class);
    }
}
