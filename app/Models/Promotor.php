<?php
namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Promotor extends Model
{
    use HasFactory;
    protected $guarded = [];
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    protected $table = 'promotores';
    protected $casts = [
        'dia_de_pago' => 'string',
    ];

    protected $appends = ['horario_pago_resumen'];

    protected function horaDePago(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value instanceof CarbonInterface) {
                    return $value->format('H:i');
                }

                if (is_string($value)) {
                    $value = trim($value);

                    if ($value === '') {
                        return null;
                    }

                    if (preg_match('/^\d{2}:\d{2}/', $value, $matches)) {
                        return $matches[0];
                    }

                    try {
                        return Carbon::parse($value)->format('H:i');
                    } catch (\Throwable) {
                        return null;
                    }
                }

                return null;
            },
            set: function ($value) {
                if ($value instanceof CarbonInterface) {
                    return $value->format('H:i:s');
                }

                if (is_string($value)) {
                    $value = trim($value);

                    if ($value === '') {
                        return null;
                    }

                    if (preg_match('/^\d{2}:\d{2}$/', $value)) {
                        return $value . ':00';
                    }

                    if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                        return $value;
                    }

                    try {
                        return Carbon::parse($value)->format('H:i:s');
                    } catch (\Throwable) {
                        return null;
                    }
                }

                return null;
            }
        );
    }

    public function getHorarioPagoResumenAttribute(): string
    {
        $dia = trim((string) ($this->dia_de_pago ?? ''));
        $hora = $this->hora_de_pago;

        if ($hora instanceof CarbonInterface) {
            $hora = $hora->format('H:i');
        }

        if (is_string($hora)) {
            $hora = trim($hora);
        } else {
            $hora = '';
        }

        if ($dia === '' && $hora === '') {
            return '';
        }

        if ($dia !== '' && $hora !== '') {
            return sprintf('%s • %s', $dia, $hora);
        }

        return $dia !== '' ? $dia : $hora;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function inversiones()
    {
        return $this->hasMany(Inversion::class);
    }

    public function comisiones()
    {
        return $this->morphMany(Comision::class, 'comisionable');
    }
}