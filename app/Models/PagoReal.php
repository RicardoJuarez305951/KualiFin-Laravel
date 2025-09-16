<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoReal extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'pagos_reales';
    const UPDATED_AT = null;

    public function pagoProyectado()
    {
        return $this->belongsTo(PagoProyectado::class);
    }

    public function pagoDiferido()
    {
        return $this->hasOne(PagoDiferido::class);
    }

    public function pagoCompleto()
    {
        return $this->hasOne(PagoCompleto::class);
    }

    public function pagoAnticipo()
    {
        return $this->hasOne(PagoAnticipo::class);
    }

    // Monto total asociado a este pago real (sumando tipo correspondiente)
    public function getMontoAttribute(): float
    {
        $total = 0.0;
        if ($this->relationLoaded('pagoCompleto') && $this->pagoCompleto) {
            $total += (float) ($this->pagoCompleto->monto_completo ?? 0);
        } elseif ($this->pagoCompleto) {
            $total += (float) ($this->pagoCompleto->monto_completo ?? 0);
        }

        if ($this->relationLoaded('pagoAnticipo') && $this->pagoAnticipo) {
            $total += (float) ($this->pagoAnticipo->monto_anticipo ?? 0);
        } elseif ($this->pagoAnticipo) {
            $total += (float) ($this->pagoAnticipo->monto_anticipo ?? 0);
        }

        if ($this->relationLoaded('pagoDiferido') && $this->pagoDiferido) {
            $total += (float) ($this->pagoDiferido->monto_diferido ?? 0);
        } elseif ($this->pagoDiferido) {
            $total += (float) ($this->pagoDiferido->monto_diferido ?? 0);
        }

        return $total;
    }
}
