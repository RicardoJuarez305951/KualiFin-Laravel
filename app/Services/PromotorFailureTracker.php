<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Promotor;
use App\Models\PromotorFailureStreak;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Carbon\CarbonInterface;
use Throwable;

class PromotorFailureTracker
{
    public const FAILURE_THRESHOLD = 8.0;
    public const ALERT_STREAK_LENGTH = 3;

    /**
     * Registra el estado semanal de fallas de un promotor.
     *
     * @return array{failure_rate: float, streak: int, alert: bool, threshold_met: bool}
     */
    public function recordWeek(Promotor $promotor, CarbonInterface $weekStart, CarbonInterface $weekEnd): array
    {
        $metrics = $this->calculateFailureMetrics($promotor, $weekStart, $weekEnd);
        $failureRate = $metrics['failure_rate'];

        if (!$this->streakTableExists()) {
            return [
                'failure_rate' => $failureRate,
                'streak' => 0,
                'alert' => false,
                'threshold_met' => $failureRate >= static::FAILURE_THRESHOLD,
            ];
        }

        $streak = PromotorFailureStreak::firstOrNew(['promotor_id' => $promotor->id]);

        if ($failureRate >= static::FAILURE_THRESHOLD) {
            $this->advanceStreak($streak, $weekStart, $weekEnd, $failureRate);
        } else {
            $this->resetStreak($streak, $failureRate);
        }

        return [
            'failure_rate' => $failureRate,
            'streak' => (int) $streak->streak_count,
            'alert' => (bool) $streak->alert_active,
            'threshold_met' => $failureRate >= static::FAILURE_THRESHOLD,
        ];
    }

    /**
     * Calcula el porcentaje de falla del promotor para el rango indicado.
     *
     * @return array{failure_rate: float, total_projected: float, total_deficit: float}
     */
    public function calculateFailureMetrics(Promotor $promotor, CarbonInterface $weekStart, CarbonInterface $weekEnd): array
    {
        $clientes = Cliente::query()
            ->where('promotor_id', $promotor->id)
            ->with(['credito' => function ($query) use ($weekStart, $weekEnd) {
                $query->select('creditos.id', 'creditos.cliente_id', 'creditos.monto_total');
                $query->with(['pagosProyectados' => function ($pagos) use ($weekStart, $weekEnd) {
                    $pagos->whereBetween('fecha_limite', [
                        $weekStart->copy()->startOfDay(),
                        $weekEnd->copy()->endOfDay(),
                    ])->with(['pagosReales.pagoCompleto', 'pagosReales.pagoAnticipo', 'pagosReales.pagoDiferido']);
                }]);
            }])
            ->get();

        $totalProjected = 0.0;
        $totalDeficit = 0.0;

        foreach ($clientes as $cliente) {
            $credito = $cliente->credito;
            if (!$credito) {
                continue;
            }

            $pagos = $credito->pagosProyectados instanceof Collection
                ? $credito->pagosProyectados
                : collect($credito->pagosProyectados);

            foreach ($pagos as $pago) {
                $proyectado = (float) ($pago->monto_proyectado ?? 0);
                $pagado = (float) collect($pago->pagosReales ?? [])
                    ->sum(fn ($pagoReal) => (float) ($pagoReal->monto ?? 0));

                $totalProjected += $proyectado;
                $deficit = max(0.0, $proyectado - $pagado);
                $totalDeficit += $deficit;
            }
        }

        $failureRate = $totalProjected > 0
            ? round(($totalDeficit / max(1e-6, $totalProjected)) * 100, 2)
            : 0.0;

        return [
            'failure_rate' => $failureRate,
            'total_projected' => round($totalProjected, 2),
            'total_deficit' => round($totalDeficit, 2),
        ];
    }

    private function advanceStreak(
        PromotorFailureStreak $streak,
        CarbonInterface $weekStart,
        CarbonInterface $weekEnd,
        float $failureRate
    ): void {
        $currentWeekStart = Carbon::parse($weekStart)->startOfDay();
        $previousWeekStart = $currentWeekStart->copy()->subWeek();

        if ($streak->last_failure_week_start?->equalTo($currentWeekStart)) {
            // Ya se registró esta semana, sólo actualizamos métricas.
        } elseif ($streak->last_failure_week_start?->equalTo($previousWeekStart)) {
            $streak->streak_count = (int) $streak->streak_count + 1;
        } else {
            $streak->streak_count = 1;
        }

        $streak->last_failure_week_start = $currentWeekStart;
        $streak->last_failure_week_end = Carbon::parse($weekEnd)->endOfDay();
        $streak->last_failure_rate = $failureRate;

        if ($streak->streak_count >= static::ALERT_STREAK_LENGTH) {
            if (!$streak->alert_active) {
                $streak->alert_started_week = $currentWeekStart;
            }
            $streak->alert_active = true;
        } else {
            $streak->alert_active = false;
            $streak->alert_started_week = null;
        }

        $streak->save();
    }

    private function resetStreak(PromotorFailureStreak $streak, float $failureRate): void
    {
        $streak->streak_count = 0;
        $streak->last_failure_rate = $failureRate;
        $streak->last_failure_week_start = null;
        $streak->last_failure_week_end = null;
        $streak->alert_active = false;
        $streak->alert_started_week = null;
        $streak->save();
    }

    private function streakTableExists(): bool
    {
        static $cache;

        if ($cache !== null) {
            return $cache;
        }

        try {
            return $cache = Schema::hasTable('promotor_failure_streaks');
        } catch (Throwable $exception) {
            return $cache = false;
        }
    }
}
