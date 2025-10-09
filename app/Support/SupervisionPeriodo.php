<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class SupervisionPeriodo
{
    /**
     * Obtiene la información del periodo actual de supervisión.
     *
     * @param  int         $diasPeriodo  Número de días que cubre el periodo. Para sábado a sábado se consideran 8 días (ambos inclusive).
     * @param  Carbon|null $referencia   Fecha de referencia para ubicar el periodo.
     * @return array{inicio: Carbon, limite: Carbon, dias: int}
     */
    public static function periodoActual(int $diasPeriodo = 8, ?Carbon $referencia = null): array
    {
        $referencia = $referencia?->copy()->startOfDay() ?? Carbon::today();

        $cortes = self::cortesProgramados();

        $corteActual = $cortes->first(fn (Carbon $corte) => $referencia->lessThanOrEqualTo($corte));

        if (!$corteActual) {
            $corteActual = self::calcularSiguienteCorte($cortes, $referencia);
        }

        $inicio = $corteActual->copy()->subDays($diasPeriodo - 1);

        return [
            'inicio' => $inicio,
            'limite' => $corteActual,
            'dias'   => $diasPeriodo,
        ];
    }

    /**
     * Lee el archivo de cortes programados (sábados).
     */
    protected static function cortesProgramados(): Collection
    {
        $rutaArchivo = resource_path('data/cortes_supervision.bit');

        if (!file_exists($rutaArchivo)) {
            return collect();
        }

        return collect(file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))
            ->map(fn ($linea) => trim($linea))
            ->filter()
            ->map(function ($linea) {
                try {
                    return Carbon::createFromFormat('Y-m-d', $linea)->startOfDay();
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter()
            ->sort()
            ->values();
    }

    /**
     * Calcula el siguiente corte tomando como base la fecha de referencia y el último corte conocido.
     */
    protected static function calcularSiguienteCorte(Collection $cortes, Carbon $referencia): Carbon
    {
        $ultimoCorte = $cortes->last();

        if ($ultimoCorte) {
            $proximo = $ultimoCorte->copy();
            while ($proximo->lessThan($referencia)) {
                $proximo->addWeek();
            }

            return $proximo;
        }

        return $referencia->copy()->nextOrSame(Carbon::SATURDAY);
    }
}
