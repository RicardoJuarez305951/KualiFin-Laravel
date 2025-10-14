<?php

namespace App\Services\Recibos;

use App\Models\Credito;
use App\Models\Promotor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ReciboDesembolsoDataService
{
    /**
     * Construye el payload para vistas y PDFs del recibo de desembolso.
     */
    public function build(Promotor $promotor): array
    {
        $promotor->loadMissing([
            'clientes.creditos' => function ($query) {
                $query
                    ->with('canceladoPor')
                    ->orderByDesc('fecha_inicio')
                    ->orderByDesc('id');
            },
            'supervisor.ejecutivo',
        ]);

        $clientes = $promotor->clientes instanceof Collection
            ? $promotor->clientes
            : collect($promotor->clientes ?? []);

        $clientesData = collect();
        $carteraActual = 0.0;

        foreach ($clientes as $cliente) {
            $creditos = $cliente->creditos instanceof Collection
                ? $cliente->creditos
                : collect($cliente->creditos ?? []);

            $creditosOrdenados = $creditos
                ->sortByDesc(function (Credito $credito) {
                    if ($credito->fecha_inicio) {
                        return Carbon::parse($credito->fecha_inicio)->timestamp;
                    }

                    return PHP_INT_MIN + (int) $credito->id;
                })
                ->values();

            /** @var \App\Models\Credito|null $creditoActual */
            $creditoActual = $creditosOrdenados->get(0);
            $creditoAnterior = $creditosOrdenados->get(1);

            if ($cliente->tiene_credito_activo && $creditoActual) {
                $carteraActual += (float) ($creditoActual->monto_total ?? 0);
            }

            $ultimoCredito = $creditosOrdenados->first();
            $fechaInicio = $ultimoCredito?->fecha_inicio;
            $fechaFinal = $ultimoCredito?->fecha_final;
            $canceladoEn = $ultimoCredito?->cancelado_en;
            $canceladoPor = $ultimoCredito?->canceladoPor;

            // Guardamos los metadatos de la cancelacion para mostrarlos en la vista movil.
            $canceladoPorNombre = null;
            if ($canceladoPor) {
                $canceladoPorNombre = trim(collect([
                    $canceladoPor->name ?? null,
                    $canceladoPor->apellido_p ?? null,
                    $canceladoPor->apellido_m ?? null,
                ])->filter()->implode(' '));

                if ($canceladoPorNombre === '') {
                    $canceladoPorNombre = $canceladoPor->email ?? null;
                }
            }

            $clientesData->push([
                'id' => $cliente->id,
                'nombre' => trim(collect([
                    $cliente->nombre,
                    $cliente->apellido_p,
                    $cliente->apellido_m,
                ])->filter()->implode(' ')),
                'ultimo_credito' => $ultimoCredito ? [
                    'id' => $ultimoCredito->id,
                    'monto_total' => (float) ($ultimoCredito->monto_total ?? 0),
                    'estado' => (string) ($ultimoCredito->estado ?? ''),
                    'interes' => $ultimoCredito->interes !== null ? (float) $ultimoCredito->interes : null,
                    'periodicidad' => (string) ($ultimoCredito->periodicidad ?? ''),
                    'fecha_inicio' => $fechaInicio instanceof Carbon
                        ? $fechaInicio->format('d/m/Y')
                        : ($fechaInicio ? Carbon::parse($fechaInicio)->format('d/m/Y') : null),
                    'fecha_final' => $fechaFinal instanceof Carbon
                        ? $fechaFinal->format('d/m/Y')
                        : ($fechaFinal ? Carbon::parse($fechaFinal)->format('d/m/Y') : null),
                    'motivo_cancelacion' => (string) ($ultimoCredito->motivo_cancelacion ?? ''),
                    'cancelado_en' => $canceladoEn instanceof Carbon
                        ? $canceladoEn->format('d/m/Y H:i')
                        : ($canceladoEn ? Carbon::parse($canceladoEn)->format('d/m/Y H:i') : null),
                    'cancelado_por' => $canceladoPorNombre,
                ] : null,
                'credito_anterior' => $creditoAnterior ? [
                    'id' => $creditoAnterior->id,
                    'monto_total' => (float) ($creditoAnterior->monto_total ?? 0),
                ] : null,
            ]);
        }

        $clientesOrdenados = $clientesData->sortBy('nombre')->values();

        $clienteRows = $clientesOrdenados
            ->map(function (array $cliente) {
                $ultimoCredito = $cliente['ultimo_credito'] ?? null;
                $creditoAnterior = $cliente['credito_anterior'] ?? null;
                $estadoCredito = $ultimoCredito['estado'] ?? '';
                $puedeCancelar = $ultimoCredito && $estadoCredito !== 'cancelado';

                $prestamoSolicitado = isset($ultimoCredito['monto_total'])
                    ? (float) $ultimoCredito['monto_total']
                    : null;
                $comisionCinco = $prestamoSolicitado !== null
                    ? round($prestamoSolicitado * 0.05, 2)
                    : null;
                $totalPrestamo = $prestamoSolicitado !== null
                    ? $prestamoSolicitado - $comisionCinco
                    : null;

                $prestamoAnterior = isset($creditoAnterior['monto_total'])
                    ? (float) $creditoAnterior['monto_total']
                    : null;

                return [
                    'nombre' => $cliente['nombre'] !== '' ? $cliente['nombre'] : 'Sin nombre',
                    'prestamo_anterior' => $prestamoAnterior,
                    'prestamo_solicitado' => $prestamoSolicitado,
                    'comision_cinco' => $comisionCinco,
                    'total_prestamo' => $totalPrestamo,
                    'recredito_nuevo' => null,
                    'total_recredito' => null,
                    'saldo_post_recredito' => null,
                    'credito_id' => $ultimoCredito['id'] ?? null,
                    'estado' => $estadoCredito,
                    'motivo_cancelacion' => $ultimoCredito['motivo_cancelacion'] ?? '',
                    'cancelado_en' => $ultimoCredito['cancelado_en'] ?? null,
                    'cancelado_por' => $ultimoCredito['cancelado_por'] ?? null,
                    'puede_cancelar' => $puedeCancelar,
                ];
            })
            ->values();

        $totalPrestamoSolicitado = $clienteRows->sum(function ($row) {
            return $row['prestamo_solicitado'] ?? 0;
        });

        $totalesTabla = [
            'prestamo_anterior' => $clienteRows->sum(fn ($row) => $row['prestamo_anterior'] ?? 0),
            'prestamo_solicitado' => $clienteRows->sum(fn ($row) => $row['prestamo_solicitado'] ?? 0),
            'comision_cinco' => $clienteRows->sum(fn ($row) => $row['comision_cinco'] ?? 0),
            'total_prestamo' => $clienteRows->sum(fn ($row) => $row['total_prestamo'] ?? 0),
            'recredito_nuevo' => $clienteRows->sum(fn ($row) => $row['recredito_nuevo'] ?? 0),
            'total_recredito' => $clienteRows->sum(fn ($row) => $row['total_recredito'] ?? 0),
            'saldo_post_recredito' => $clienteRows->sum(fn ($row) => $row['saldo_post_recredito'] ?? 0),
        ];

        $promotorSupervisor = $promotor->supervisor;
        $supervisorNombre = $this->buildFullName($promotorSupervisor);
        $promotorEjecutivo = $promotorSupervisor?->ejecutivo;
        $ejecutivoNombre = $this->buildFullName($promotorEjecutivo);
        $promotorNombre = $this->buildFullName($promotor);

        $ultimaComisionPromotor = $promotor->comisiones()->orderByDesc('fecha_pago')->first();
        $ultimaComisionSupervisor = $promotorSupervisor?->comisiones()->orderByDesc('fecha_pago')->first();

        $comisionPromotor = $ultimaComisionPromotor ? (float) ($ultimaComisionPromotor->monto_pago ?? 0) : 0.0;
        $comisionSupervisor = $ultimaComisionSupervisor ? (float) ($ultimaComisionSupervisor->monto_pago ?? 0) : 0.0;

        $carteraActual = round($carteraActual, 2);
        $inversion = $comisionPromotor + $comisionSupervisor + $totalPrestamoSolicitado - $carteraActual;
        $fechaHoy = now()->format('d/m/Y');
        $reciboDeNombre = $ejecutivoNombre !== '' ? $ejecutivoNombre : $supervisorNombre;

        return [
            'promotor' => $promotor,
            'clientes' => $clientesOrdenados->toArray(),
            'clienteRows' => $clienteRows->toArray(),
            'promotorNombre' => $promotorNombre,
            'supervisorNombre' => $supervisorNombre,
            'ejecutivoNombre' => $ejecutivoNombre,
            'reciboDeNombre' => $reciboDeNombre,
            'fechaHoy' => $fechaHoy,
            'comisionPromotor' => $comisionPromotor,
            'comisionSupervisor' => $comisionSupervisor,
            'carteraActual' => $carteraActual,
            'totalPrestamoSolicitado' => $totalPrestamoSolicitado,
            'inversion' => $inversion,
            'totalesTabla' => $totalesTabla,
            // Valor por defecto para mantener compatibilidad con la vista previa anterior.
            'motivoCancelacion' => '',
            'cancelRouteName' => Route::has('mobile.supervisor.venta.creditos.rechazar')
                ? 'mobile.supervisor.venta.creditos.rechazar'
                : null,
            'puedeCancelarCreditos' => Route::has('mobile.supervisor.venta.creditos.rechazar'),
        ];
    }

    private function buildFullName($model, string $default = ''): string
    {
        if (!$model) {
            return $default;
        }

        $parts = collect([
            data_get($model, 'nombre'),
            data_get($model, 'apellido_p'),
            data_get($model, 'apellido_m'),
        ])->filter(fn ($value) => $value !== null && $value !== '');

        return $parts->implode(' ');
    }
}
