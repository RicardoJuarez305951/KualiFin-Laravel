<?php
namespace App\Http\Controllers;

use App\Models\PagoReal;
use App\Models\PagoProyectado;
use App\Models\PagoCompleto;
use App\Models\PagoDiferido;
use App\Models\PagoAnticipo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PagoRealController extends Controller
{
    public function index()
    {
        $pagos = PagoReal::all();
        return view('pagos_reales.index', compact('pagos'));
    }

    public function create()
    {
        return view('pagos_reales.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pago_proyectado_id'=> 'required|exists:pagos_proyectados,id',
            'tipo'              => 'required|string',
            'fecha_pago'        => 'required|date',
            'comentario'        => 'nullable|string',
        ]);

        PagoReal::create($data);
        return redirect()->route('pagos_reales.index');
    }

    public function show(PagoReal $pagoReal)
    {
        return view('pagos_reales.show', compact('pagoReal'));
    }

    public function edit(PagoReal $pagoReal)
    {
        return view('pagos_reales.edit', compact('pagoReal'));
    }

    public function update(Request $request, PagoReal $pagoReal)
    {
        $data = $request->validate([
            'comentario' => 'nullable|string',
        ]);

        $pagoReal->update($data);
        return redirect()->route('pagos_reales.index');
    }

    public function destroy(PagoReal $pagoReal)
    {
        $pagoReal->delete();
        return redirect()->route('pagos_reales.index');
    }

    public function storeMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pagos' => ['required', 'array', 'min:1'],
            'pagos.*.pago_proyectado_id' => ['required', 'integer', 'distinct', 'exists:pagos_proyectados,id'],
            'pagos.*.tipo' => ['required', 'string', 'in:completo,diferido,anticipo'],
            'pagos.*.monto' => ['required', 'numeric', 'min:0'],
        ], [
            'pagos.*.monto.min' => 'El monto no puede ser negativo.',
        ]);

        $validator->after(function ($validator) {
            $pagos = $validator->getData()['pagos'] ?? [];
            $saldoDisponiblePorCredito = [];
            $pagoProyectadoCache = [];
            $tolerance = 0.01;

            foreach ($pagos as $index => $pago) {
                $pagoProyectadoId = $pago['pago_proyectado_id'] ?? null;
                if (!$pagoProyectadoId) {
                    continue;
                }

                if (!array_key_exists($pagoProyectadoId, $pagoProyectadoCache)) {
                    $pagoProyectadoCache[$pagoProyectadoId] = PagoProyectado::with([
                        'pagosReales.pagoCompleto',
                        'pagosReales.pagoDiferido',
                        'pagosReales.pagoAnticipo',
                        'credito.pagosProyectados.pagosReales.pagoCompleto',
                        'credito.pagosProyectados.pagosReales.pagoDiferido',
                        'credito.pagosProyectados.pagosReales.pagoAnticipo',
                    ])->find($pagoProyectadoId);
                }

                $pagoProyectado = $pagoProyectadoCache[$pagoProyectadoId];
                if (!$pagoProyectado) {
                    continue;
                }

                $credito = $pagoProyectado->credito;
                $creditoId = $credito?->id;
                if ($creditoId !== null && !array_key_exists($creditoId, $saldoDisponiblePorCredito)) {
                    $saldoDisponiblePorCredito[$creditoId] = $this->calcularSaldoCreditoPendiente($pagoProyectado);
                }

                $tipo = $pago['tipo'] ?? null;

                if ($tipo === 'completo') {
                    if ($creditoId === null) {
                        continue;
                    }

                    $montoCompleto = $this->calcularDeudaPendiente($pagoProyectado);
                    if ($montoCompleto - $saldoDisponiblePorCredito[$creditoId] > $tolerance) {
                        $validator->errors()->add("pagos.$index.monto", 'El monto completo excede la deuda total pendiente.');
                        continue;
                    }

                    $saldoDisponiblePorCredito[$creditoId] = max(0.0, round($saldoDisponiblePorCredito[$creditoId] - $montoCompleto, 2));
                    continue;
                }

                if ($tipo === 'diferido') {
                    $saldoDisponible = $creditoId !== null
                        ? ($saldoDisponiblePorCredito[$creditoId] ?? 0.0)
                        : $this->calcularDeudaPendiente($pagoProyectado);

                    $monto = (float) ($pago['monto'] ?? 0);

                    if ($monto - $saldoDisponible > $tolerance) {
                        $validator->errors()->add("pagos.$index.monto", 'El monto diferido no puede exceder la deuda total pendiente.');
                        continue;
                    }

                    if ($creditoId !== null) {
                        $saldoDisponiblePorCredito[$creditoId] = max(0.0, round($saldoDisponible - $monto, 2));
                    }

                    continue;
                }

                if ($tipo === 'anticipo' && $creditoId !== null) {
                    $saldoDisponible = $saldoDisponiblePorCredito[$creditoId] ?? 0.0;
                    $monto = (float) ($pago['monto'] ?? 0);

                    if ($monto - $saldoDisponible > $tolerance) {
                        $validator->errors()->add("pagos.$index.monto", 'El monto anticipo no puede exceder la deuda total pendiente.');
                        continue;
                    }

                    $saldoDisponiblePorCredito[$creditoId] = max(0.0, round($saldoDisponible - $monto, 2));
                }
            }
        });

        $data = $validator->validate();

        $fechaPago = Carbon::now()->toDateString();

        $saldoDisponiblePorCredito = [];
        $tolerance = 0.01;

        $pagos = DB::transaction(function () use ($data, $fechaPago, &$saldoDisponiblePorCredito, $tolerance) {
            return collect($data['pagos'])->map(function (array $pagoData) use ($fechaPago, &$saldoDisponiblePorCredito, $tolerance) {
                $pagoProyectado = PagoProyectado::with([
                    'pagosReales.pagoCompleto',
                    'pagosReales.pagoDiferido',
                    'pagosReales.pagoAnticipo',
                    'credito.pagosProyectados.pagosReales.pagoCompleto',
                    'credito.pagosProyectados.pagosReales.pagoDiferido',
                    'credito.pagosProyectados.pagosReales.pagoAnticipo',
                ])->lockForUpdate()->findOrFail($pagoData['pago_proyectado_id']);

                $credito = $pagoProyectado->credito;
                $creditoId = $credito?->id;
                $saldoDisponible = null;

                if ($creditoId !== null) {
                    if (!array_key_exists($creditoId, $saldoDisponiblePorCredito)) {
                        $saldoDisponiblePorCredito[$creditoId] = $this->calcularSaldoCreditoPendiente($pagoProyectado);
                    }

                    $saldoDisponible = $saldoDisponiblePorCredito[$creditoId];
                }

                $pagoReal = PagoReal::create([
                    'pago_proyectado_id' => $pagoProyectado->id,
                    'tipo' => $pagoData['tipo'],
                    'fecha_pago' => $fechaPago,
                    'comentario' => null,
                ]);

                if ($pagoData['tipo'] === 'completo') {
                    $monto = $this->calcularDeudaPendiente($pagoProyectado);

                    PagoCompleto::create([
                        'pago_real_id' => $pagoReal->id,
                        'monto_completo' => round(max($monto, 0), 2),
                    ]);

                    if ($creditoId !== null) {
                        $saldoDisponiblePorCredito[$creditoId] = max(0.0, round($saldoDisponiblePorCredito[$creditoId] - $monto, 2));
                    }
                } elseif ($pagoData['tipo'] === 'diferido') {
                    $monto = round((float) $pagoData['monto'], 2);

                    if ($saldoDisponible !== null && $monto - $saldoDisponible > $tolerance) {
                        throw ValidationException::withMessages([
                            "pagos.{$pagoData['pago_proyectado_id']}.monto" => 'El monto diferido no puede exceder la deuda total pendiente.',
                        ]);
                    }

                    PagoDiferido::create([
                        'pago_real_id' => $pagoReal->id,
                        'monto_diferido' => $monto,
                    ]);

                    if ($creditoId !== null) {
                        $saldoDisponiblePorCredito[$creditoId] = max(0.0, round($saldoDisponiblePorCredito[$creditoId] - $monto, 2));
                    }
                } else {
                    $monto = round((float) $pagoData['monto'], 2);

                    PagoAnticipo::create([
                        'pago_real_id' => $pagoReal->id,
                        'monto_anticipo' => $monto,
                    ]);

                    if ($creditoId !== null) {
                        $saldoDisponiblePorCredito[$creditoId] = max(0.0, round($saldoDisponiblePorCredito[$creditoId] - $monto, 2));
                    }
                }

                return $pagoReal->loadMissing(['pagoCompleto', 'pagoDiferido', 'pagoAnticipo']);
            });
        });

        return response()->json($pagos, 201);
    }

    protected function calcularDeudaPendiente(PagoProyectado $pagoProyectado): float
    {
        $total = (float) ($pagoProyectado->deuda_total ?? $pagoProyectado->monto_proyectado ?? 0);

        $pagoProyectado->loadMissing([
            'pagosReales.pagoCompleto',
            'pagosReales.pagoDiferido',
            'pagosReales.pagoAnticipo',
        ]);

        $pagado = $this->sumarMontoPagosReales($pagoProyectado->pagosReales);

        return max(round($total - $pagado, 2), 0);
    }

    protected function calcularSaldoCreditoPendiente(PagoProyectado $pagoProyectado): float
    {
        $pagoProyectado->loadMissing([
            'pagosReales.pagoCompleto',
            'pagosReales.pagoDiferido',
            'pagosReales.pagoAnticipo',
            'credito.pagosProyectados.pagosReales.pagoCompleto',
            'credito.pagosProyectados.pagosReales.pagoDiferido',
            'credito.pagosProyectados.pagosReales.pagoAnticipo',
        ]);

        $credito = $pagoProyectado->credito;
        if (!$credito) {
            return $this->calcularDeudaPendiente($pagoProyectado);
        }

        $totalCredito = $credito->pagosProyectados
            ->reduce(fn (float $carry, $pp) => $carry + (float) ($pp->monto_proyectado ?? 0), 0.0);

        if ($totalCredito <= 0.0) {
            $totalCredito = (float) ($credito->monto_total ?? 0.0);
        }

        $pagado = $credito->pagosProyectados
            ->reduce(fn (float $carry, $pp) => $carry + $this->sumarMontoPagosReales($pp->pagosReales), 0.0);

        return max(round($totalCredito - $pagado, 2), 0.0);
    }

    private function sumarMontoPagosReales($pagosReales): float
    {
        if ($pagosReales instanceof \Illuminate\Support\Collection) {
            $pagos = $pagosReales;
        } else {
            $pagos = collect($pagosReales);
        }

        return $pagos->reduce(function (float $carry, $pagoReal) {
            if (!$pagoReal) {
                return $carry;
            }

            if ($pagoReal->pagoCompleto) {
                $carry += (float) ($pagoReal->pagoCompleto->monto_completo ?? 0);
            }

            if ($pagoReal->pagoDiferido) {
                $carry += (float) ($pagoReal->pagoDiferido->monto_diferido ?? 0);
            }

            if ($pagoReal->pagoAnticipo) {
                $carry += (float) ($pagoReal->pagoAnticipo->monto_anticipo ?? 0);
            }

            return $carry;
        }, 0.0);
    }
}
