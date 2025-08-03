<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function pagosProyectados()
    {
        return $this->hasMany(PagoProyectado::class);
    }

    public function pagosReales()
    {
        return $this->hasMany(PagoReal::class);
    }

    public function ocupacion()
    {
        return $this->hasOne(Ocupacion::class);
    }

    public function informacionFamiliar()
    {
        return $this->hasOne(InformacionFamiliar::class);
    }

    public function avales()
    {
        return $this->hasMany(Aval::class);
    }

    public function garantias()
    {
        return $this->hasMany(Garantia::class);
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }
}
