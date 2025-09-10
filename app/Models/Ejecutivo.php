<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ejecutivo extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supervisors()
    {
        return $this->hasMany(Supervisor::class);
    }

    public function ejercicios()
    {
        return $this->hasMany(Ejercicio::class);
    }

    public function comisiones()
    {
        return $this->morphMany(Comision::class, 'comisionable');
    }
}
