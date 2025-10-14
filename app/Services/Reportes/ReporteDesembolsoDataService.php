<?php

namespace App\Services\Reportes;

use App\Http\Controllers\FiltrosController;
use App\Models\Credito;
use App\Models\Ejercicio;
use App\Models\Inversion;
use App\Models\PagoProyectado;
use App\Models\PagoReal;
use App\Models\Promotor;
use App\Services\Recibos\ReciboDesembolsoDataService;
use Carbon\CarbonInterface;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteDesembolsoDataService
{
    private const DEFAULT_FONDO_AHORRO_RATE = 0.10;
    private const PRESTAMO_ESTADOS = ['aprobado', 'desembolsado'];
    private const DESEMBOLSO_ESTADOS = ['desembolsado'];
    private const COBRANZA_DIAS = [
        0 => 'Lunes',
        1 => 'Martes',
2 => 'Miercoles',
        3 => 'Jueves',
        4 => 'Viernes',
        5 => 'Sabado',
    ];

    public function __construct(
        private readonly ReciboDesembolsoDataService $reciboDataService,
    ) {
    }

    public function build(
        Promotor $promotor,
        ?CarbonImmutable $start = null,
        ?CarbonImmutable $end = null,
        ?array $acceptedCreditIds = null,
    ): array {
        [$startDate, $endDate] = $this->resolveRange($start, $end);

        $promotor->loadMissing(['supervisor.ejecutivo']);
        $basePayload = $this->reciboDataService->build($promotor);

        $acceptedIds = collect($acceptedCreditIds ?? [])
            ->map(function ($value) {
                if (is_numeric($value)) {
                    $numeric = (int) $value;
                    return $numeric > 0 ? $numeric : null;
                }

                if (is_string($value)) {
                    $numeric = (int) trim($value);
                    return $numeric > 0 ? $numeric : null;
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values();

        $prestamos = $this->fetchCreditos($promotor, $startDate, $endDate, self::PRESTAMO_ESTADOS, true);
        $desembolsos = $this->fetchCreditos($promotor, $startDate, $endDate, self::DESEMBOLSO_ESTADOS, false);

        if ($acceptedIds->isNotEmpty()) {
            $desembolsos = $desembolsos
                ->filter(fn (Credito $credito) => $acceptedIds->contains((int) $credito->id))
                ->values();
        }

        $fallos = $this->fetchFallos($promotor, $startDate, $endDate);
        $pagosSemana = $this->fetchPagosSemana($promotor, $startDate, $endDate);
        $semanasDebeProyectado = $this->fetchDebeProyectadoSemanal($promotor);

        $carteraReal = $this->calcularCarteraReal($promotor);
        $totalPrestamos = $this->sumMonto($prestamos);
        $totalDesembolsos = $this->sumMonto($desembolsos);
        $totalFallo = $fallos['total'];

        $adelantosTotal = $pagosSemana['totales']['adelantos'];
        $recuperacionTotal = $pagosSemana['totales']['recuperacion'];
        $otrosTotal = $pagosSemana['totales']['diferidos'];
        $cobranzaTotal = $pagosSemana['totales']['cobranza'];

        $recreditos = $this->calcularRecreditos(
            Arr::get($basePayload, 'clientes', []),
            $startDate,
            $endDate,
        );
        $totalRecreditos = $recreditos['total'];

        $fondoAhorro = $this->calcularFondoAhorro($totalPrestamos);
        $comisionPromotor = $this->obtenerComisionPromotor($promotor, $startDate, $endDate);
        $comisionSupervisor = $this->obtenerComisionSupervisor($promotor, $startDate, $endDate);
        $inversion = $this->calcularInversion($promotor, $startDate, $endDate, $totalPrestamos, $carteraReal, $comisionPromotor, $comisionSupervisor);

        $totalIzquierdo = $this->calcularTotalIzquierdo(
            $carteraReal,
            $totalFallo,
            $totalPrestamos,
            $recuperacionTotal,
            $adelantosTotal,
            $totalRecreditos,
        );

        $totalFinal = $this->calcularTotalFinal(
            $totalDesembolsos,
            $totalRecreditos,
            $fondoAhorro,
            $comisionPromotor,
            $comisionSupervisor,
            $otrosTotal,
            $inversion,
        );

        $reportDate = CarbonImmutable::now();
        $semanaVenta = sprintf('%02d/%d', $startDate->isoWeek(), $startDate->isoWeekYear());

        return [
            'contexto' => [
                'fecha_reporte' => $reportDate,
                'semana_venta' => $semanaVenta,
                'rango' => [
                    'inicio' => $startDate,
                    'fin' => $endDate,
                ],
                'ejecutivo' => [
                    'modelo' => $promotor->supervisor?->ejecutivo,
                    'nombre' => Arr::get($basePayload, 'ejecutivoNombre', ''),
                ],
                'supervisor' => [
                    'modelo' => $promotor->supervisor,
                    'nombre' => Arr::get($basePayload, 'supervisorNombre', ''),
                ],
                'promotor' => [
                    'modelo' => $promotor,
                    'nombre' => Arr::get($basePayload, 'promotorNombre', ''),
                ],
            ],
            'listas' => [
                'fallo' => $fallos,
                'prestamos' => $this->mapCreditos($prestamos),
                'desembolsos' => $this->mapCreditos($desembolsos),
                'recreditos' => $recreditos,
                'adelantos' => [
                    'items' => $pagosSemana['adelantos'],
                    'total' => $adelantosTotal,
                ],
                'recuperacion' => [
                    'items' => $pagosSemana['recuperacion'],
                    'total' => $recuperacionTotal,
                ],
            ],
            'cobranza' => [
                'dias' => $pagosSemana['cobranza_dias'],
                'total' => $cobranzaTotal,
            ],
            'debe_proyectado_semanal' => $semanasDebeProyectado,
            'totales' => [
                'cartera_real' => $carteraReal,
                'fallo' => $totalFallo,
                'prestamos' => $totalPrestamos,
                'cobranza' => $cobranzaTotal,
                'adelantos' => $adelantosTotal,
                'recuperacion' => $recuperacionTotal,
                'desembolso' => $totalDesembolsos,
                'recreditos' => $totalRecreditos,
                'fondo_ahorro' => $fondoAhorro,
                'total_izquierdo' => $totalIzquierdo,
                'total_final' => $totalFinal,
            ],
            'cierres' => [
                'fondo_ahorro' => $fondoAhorro,
                'comisiones_prom' => $comisionPromotor,
                'comisiones_superv' => $comisionSupervisor,
                'otros' => $otrosTotal,
                'inversion' => $inversion,
            ],
            'base' => $basePayload,
        ];
    }

    private function fetchDebeProyectadoSemanal(Promotor $promotor): array
    {
        $supervisor = $promotor->supervisor;
        if (!$supervisor) {
            return [];
        }

        $ejercicio = Ejercicio::where('supervisor_id', $supervisor->id)
            ->latest('fecha_inicio')
            ->first();

        if (!$ejercicio) {
            return [];
        }

        $fechaInicio = $this->toCarbonImmutable($ejercicio->fecha_inicio);
        $fechaFin = $this->toCarbonImmutable($ejercicio->fecha_final);

        if (!$fechaInicio || !$fechaFin || $fechaFin->lte($fechaInicio)) {
            return [];
        }

        $nSemanas = $fechaInicio->diffInWeeks($fechaFin);

        if ($nSemanas <= 0) {
            return [];
        }

        $creditosActivos = Credito::query()
            ->whereIn('estado', FiltrosController::CREDIT_ACTIVE_STATES)
            ->whereHas('cliente', function (Builder $query) use ($promotor) {
                $query->where('promotor_id', $promotor->id);
            })
            ->with('pagosProyectados:credito_id,monto_proyectado')
            ->get(['id']);

        $debeProyectadoTotal = $creditosActivos->sum(function (Credito $credito) {
            return $credito->pagosProyectados->sum('monto_proyectado');
        });

        if ($debeProyectadoTotal <= 0) {
            return [];
        }

        $pagoSemanal = $debeProyectadoTotal / $nSemanas;

        $semanas = [];
        for ($i = 1; $i <= $nSemanas; $i++) {
            $semanas[] = [
                'semana' => $i,
                'monto' => round($pagoSemanal, 2),
            ];
        }

        return $semanas;
    }

    private function resolveRange(?CarbonImmutable $start, ?CarbonImmutable $end): array
    {
        $now = CarbonImmutable::now();
        $resolvedStart = $start
            ? $start->startOfDay()
            : $now->startOfWeek(CarbonInterface::MONDAY);

        $defaultEnd = $resolvedStart->addDays(5)->endOfDay();
        $resolvedEnd = $end
            ? $end->endOfDay()
            : $defaultEnd;

        if ($resolvedEnd->lt($resolvedStart)) {
            [$resolvedStart, $resolvedEnd] = [
                $resolvedEnd->startOfDay(),
                $resolvedStart->endOfDay(),
            ];
        }

        return [$resolvedStart, $resolvedEnd];
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Credito>
     */
    private function fetchCreditos(
        Promotor $promotor,
        CarbonImmutable $start,
        CarbonImmutable $end,
        array $estados,
        bool $filtrarPorRango,
    ): Collection {
        $normalizedStates = array_map('strtolower', $estados);

        $query = Credito::query()
            ->select(['id', 'cliente_id', 'monto_total', 'estado', 'fecha_inicio'])
            ->with([
                'cliente:id,promotor_id,nombre,apellido_p,apellido_m',
            ])
            ->whereHas('cliente', function (Builder $query) use ($promotor) {
                $query->where('promotor_id', $promotor->id);
            })
            ->whereIn(DB::raw('LOWER(estado)'), $normalizedStates)
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id');

        if ($filtrarPorRango) {
            $query->whereBetween('fecha_inicio', [$start->toDateString(), $end->toDateString()]);
        }

        return $query->get();
    }

    private function fetchFallos(
        Promotor $promotor,
        CarbonImmutable $start,
        CarbonImmutable $end,
    ): array {
        $pagosProyectados = PagoProyectado::query()
            ->select(['id', 'credito_id', 'monto_proyectado', 'fecha_limite'])
            ->with([
                'credito:id,cliente_id',
                'credito.cliente:id,promotor_id,nombre,apellido_p,apellido_m',
                'pagosReales' => function ($query) {
                    $query->with([
                        'pagoAnticipo:id,pago_real_id,monto_anticipo',
                        'pagoCompleto:id,pago_real_id,monto_completo',
                        'pagoDiferido:id,pago_real_id,monto_diferido',
                    ]);
                },
            ])
            ->whereBetween('fecha_limite', [$start->toDateString(), $end->toDateString()])
            ->whereHas('credito.cliente', function (Builder $query) use ($promotor) {
                $query->where('promotor_id', $promotor->id);
            })
            ->orderBy('fecha_limite')
            ->orderBy('id')
            ->get();

        $items = [];
        $total = 0.0;

        foreach ($pagosProyectados as $pago) {
            $cliente = $pago->credito?->cliente;
            $sumPagos = $pago->pagosReales->sum(function (PagoReal $pagoReal) {
                return $pagoReal->monto;
            });

            $pendiente = max(0.0, (float) $pago->monto_proyectado - $sumPagos);
            if ($pendiente <= 0.0) {
                continue;
            }

            $fechaLimite = $this->toCarbonImmutable($pago->fecha_limite);

            $items[] = [
                'id' => $pago->id,
                'fecha' => $fechaLimite,
                'fecha_texto' => $fechaLimite?->format('d/m/Y'),
                'cliente' => $this->buildFullName($cliente),
                'monto' => round($pendiente, 2),
            ];

            $total += $pendiente;
        }

        return [
            'items' => $items,
            'total' => round($total, 2),
        ];
    }

    private function fetchPagosSemana(
        Promotor $promotor,
        CarbonImmutable $start,
        CarbonImmutable $end,
    ): array {
        $pagoReales = PagoReal::query()
            ->select(['id', 'pago_proyectado_id', 'fecha_pago'])
            ->with([
                'pagoAnticipo:id,pago_real_id,monto_anticipo',
                'pagoCompleto:id,pago_real_id,monto_completo',
                'pagoDiferido:id,pago_real_id,monto_diferido',
                'pagoProyectado:id,credito_id',
                'pagoProyectado.credito:id,cliente_id',
                'pagoProyectado.credito.cliente:id,promotor_id,nombre,apellido_p,apellido_m',
            ])
            ->whereBetween('fecha_pago', [$start->toDateString(), $end->toDateString()])
            ->whereHas('pagoProyectado.credito.cliente', function (Builder $query) use ($promotor) {
                $query->where('promotor_id', $promotor->id);
            })
            ->orderBy('fecha_pago')
            ->orderBy('id')
            ->get();

        $cobranzaDias = [];
        for ($offset = 0; $offset < count(self::COBRANZA_DIAS); $offset++) {
            $dayDate = $start->addDays($offset);
            $cobranzaDias[$offset] = [
                'dia' => self::COBRANZA_DIAS[$offset],
                'fecha' => $dayDate,
                'fecha_texto' => $dayDate->format('d/m/Y'),
                'total' => 0.0,
            ];
        }

        $adelantos = [];
        $recuperacion = [];
        $totales = [
            'adelantos' => 0.0,
            'recuperacion' => 0.0,
            'cobranza' => 0.0,
            'diferidos' => 0.0,
        ];

        foreach ($pagoReales as $pagoReal) {
            $fechaPago = $this->toCarbonImmutable($pagoReal->fecha_pago);
            $cliente = $pagoReal->pagoProyectado?->credito?->cliente;

            $montoAnticipo = (float) ($pagoReal->pagoAnticipo?->monto_anticipo ?? 0.0);
            $montoCompleto = (float) ($pagoReal->pagoCompleto?->monto_completo ?? 0.0);
            $montoDiferido = (float) ($pagoReal->pagoDiferido?->monto_diferido ?? 0.0);

            $montoRecuperacion = $montoCompleto + $montoDiferido;
            $montoCobranza = $montoAnticipo + $montoRecuperacion;

            $totales['adelantos'] += $montoAnticipo;
            $totales['recuperacion'] += $montoRecuperacion;
            $totales['cobranza'] += $montoCobranza;
            $totales['diferidos'] += $montoDiferido;

            $dayIndex = $fechaPago ? $start->diffInDays($fechaPago, false) : null;
            if (is_int($dayIndex) && $dayIndex >= 0 && $dayIndex < count($cobranzaDias)) {
                $cobranzaDias[$dayIndex]['total'] += $montoCobranza;
            }

            if ($montoAnticipo > 0.0) {
                $adelantos[] = [
                    'id' => $pagoReal->id,
                    'fecha' => $fechaPago,
                    'fecha_texto' => $fechaPago?->format('d/m/Y'),
                    'cliente' => $this->buildFullName($cliente),
                    'monto' => round($montoAnticipo, 2),
                ];
            }

            if ($montoRecuperacion > 0.0) {
                $recuperacion[] = [
                    'id' => $pagoReal->id,
                    'fecha' => $fechaPago,
                    'fecha_texto' => $fechaPago?->format('d/m/Y'),
                    'cliente' => $this->buildFullName($cliente),
                    'monto' => round($montoRecuperacion, 2),
                ];
            }
        }

        foreach ($cobranzaDias as &$dia) {
            $dia['total'] = round($dia['total'], 2);
        }
        unset($dia);

        $totales = array_map(
            static fn (float $value) => round($value, 2),
            $totales,
        );

        return [
            'adelantos' => $adelantos,
            'recuperacion' => $recuperacion,
            'cobranza_dias' => array_values($cobranzaDias),
            'totales' => $totales,
        ];
    }

    private function calcularCarteraReal(Promotor $promotor): float
    {
        $creditos = Credito::query()
            ->select(['monto_total', 'estado'])
            ->whereHas('cliente', function (Builder $query) use ($promotor) {
                $query->where('promotor_id', $promotor->id);
            })
            ->get();

        $estadosActivos = array_map('strtolower', FiltrosController::CREDIT_ACTIVE_STATES);

        return round(
            $creditos
                ->filter(function (Credito $credito) use ($estadosActivos) {
                    $estado = strtolower((string) ($credito->estado ?? ''));

                    return in_array($estado, $estadosActivos, true);
                })
                ->sum(function (Credito $credito) {
                    return (float) ($credito->monto_total ?? 0);
                }),
            2
        );
    }

    private function calcularRecreditos(array $clientes, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $items = [];
        $totalNuevo = 0.0;
        $totalAnterior = 0.0;
        $totalSaldo = 0.0;

        foreach ($clientes as $cliente) {
            $ultimo = Arr::get($cliente, 'ultimo_credito');
            $anterior = Arr::get($cliente, 'credito_anterior');

            if (!$ultimo || !$anterior) {
                continue;
            }

            $fechaTexto = Arr::get($ultimo, 'fecha_inicio');
            $fechaInicio = $this->fromFormattedDate($fechaTexto);

            if (!$fechaInicio || $fechaInicio->lt($start) || $fechaInicio->gt($end)) {
                continue;
            }

            $montoNuevo = (float) (Arr::get($ultimo, 'monto_total') ?? 0.0);
            $montoAnterior = (float) (Arr::get($anterior, 'monto_total') ?? 0.0);
            $saldoPost = max(0.0, $montoNuevo - $montoAnterior);

            $items[] = [
                'cliente' => Arr::get($cliente, 'nombre', 'Sin nombre'),
                'fecha' => $fechaInicio,
                'fecha_texto' => $fechaInicio->format('d/m/Y'),
                'monto_nuevo' => round($montoNuevo, 2),
                'monto_anterior' => round($montoAnterior, 2),
                'saldo_post' => round($saldoPost, 2),
            ];

            $totalNuevo += $montoNuevo;
            $totalAnterior += $montoAnterior;
            $totalSaldo += $saldoPost;
        }

        return [
            'items' => $items,
            'total_nuevo' => round($totalNuevo, 2),
            'total_anterior' => round($totalAnterior, 2),
            'total' => round($totalSaldo, 2),
        ];
    }

    private function calcularFondoAhorro(float $totalPrestamos): float
    {
        $rate = (float) config('reports.desembolso.fondo_ahorro_rate', self::DEFAULT_FONDO_AHORRO_RATE);

        return round($totalPrestamos * $rate, 2);
    }

    private function obtenerComisionPromotor(
        Promotor $promotor,
        CarbonImmutable $start,
        CarbonImmutable $end,
    ): float {
        $monto = $promotor->comisiones()
            ->whereBetween('fecha_pago', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('fecha_pago')
            ->value('monto_pago');

        return round((float) ($monto ?? 0.0), 2);
    }

    private function obtenerComisionSupervisor(
        Promotor $promotor,
        CarbonImmutable $start,
        CarbonImmutable $end,
    ): float {
        $supervisor = $promotor->supervisor;
        if (!$supervisor) {
            return 0.0;
        }

        $monto = $supervisor->comisiones()
            ->whereBetween('fecha_pago', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('fecha_pago')
            ->value('monto_pago');

        return round((float) ($monto ?? 0.0), 2);
    }

    private function calcularInversion(
        Promotor $promotor,
        CarbonImmutable $start,
        CarbonImmutable $end,
        float $totalPrestamos,
        float $carteraReal,
        float $comisionPromotor,
        float $comisionSupervisor,
    ): float {
        $monto = Inversion::query()
            ->where('promotor_id', $promotor->id)
            ->whereBetween('fecha_aprobacion', [$start->toDateString(), $end->toDateString()])
            ->sum('monto_aprobado');

        if ($monto > 0) {
            return round((float) $monto, 2);
        }

        $fallback = $comisionPromotor + $comisionSupervisor + $totalPrestamos - $carteraReal;

        return round(max(0.0, $fallback), 2);
    }

    private function calcularTotalIzquierdo(
        float $carteraReal,
        float $totalFallo,
        float $totalPrestamos,
        float $totalRecuperacion,
        float $totalAdelantos,
        float $totalRecreditos,
    ): float {
        $total = $carteraReal
            - $totalFallo
            - $totalPrestamos
            + $totalRecuperacion
            + $totalAdelantos
            + $totalRecreditos;

        return round($total, 2);
    }

    private function calcularTotalFinal(
        float $totalDesembolsos,
        float $totalRecreditos,
        float $fondoAhorro,
        float $comisionPromotor,
        float $comisionSupervisor,
        float $otros,
        float $inversion,
    ): float {
        $total = $totalDesembolsos
            + $totalRecreditos
            + $fondoAhorro
            - $comisionPromotor
            - $comisionSupervisor
            + $otros
            + $inversion;

        return round($total, 2);
    }

    private function sumMonto(Collection $creditos): float
    {
        return round(
            $creditos->sum(function (Credito $credito) {
                return (float) ($credito->monto_total ?? 0.0);
            }),
            2
        );
    }

    private function mapCreditos(Collection $creditos): array
    {
        return $creditos->map(function (Credito $credito) {
            $cliente = $credito->cliente;
            $fechaInicio = $this->toCarbonImmutable($credito->fecha_inicio);

            return [
                'id' => $credito->id,
                'cliente' => $this->buildFullName($cliente),
                'monto' => round((float) ($credito->monto_total ?? 0.0), 2),
                'fecha' => $fechaInicio,
                'fecha_texto' => $fechaInicio?->format('d/m/Y'),
                'estado' => (string) ($credito->estado ?? ''),
            ];
        })->all();
    }

    private function buildFullName($model): string
    {
        if (!$model) {
            return '';
        }

        return collect([
            data_get($model, 'nombre'),
            data_get($model, 'apellido_p'),
            data_get($model, 'apellido_m'),
        ])->filter()->implode(' ');
    }

    private function toCarbonImmutable($value): ?CarbonImmutable
    {
        if ($value instanceof CarbonImmutable) {
            return $value;
        }

        if ($value instanceof CarbonInterface) {
            return CarbonImmutable::instance($value);
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return CarbonImmutable::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function fromFormattedDate(?string $value): ?CarbonImmutable
    {
        if (!$value) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('d/m/Y', $value)->startOfDay();
        } catch (\Throwable) {
            try {
                return CarbonImmutable::parse($value)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }
    }
}
