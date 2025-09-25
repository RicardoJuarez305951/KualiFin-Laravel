<?php
namespace App\Http\Controllers;

use App\Models\PagoReal;
use App\Models\PagoProyectado;
use App\Models\PagoCompleto;
use App\Models\PagoDiferido;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            'pagos.*.tipo' => ['required', 'string', 'in:completo,diferido'],
            'pagos.*.monto' => ['required', 'numeric', 'min:0'],
        ], [
            'pagos.*.monto.min' => 'El monto no puede ser negativo.',
        ]);

        $validator->after(function ($validator) {
            $pagos = $validator->getData()['pagos'] ?? [];

            foreach ($pagos as $index => $pago) {
                if (($pago['tipo'] ?? null) !== 'diferido') {
                    continue;
                }

                $pagoProyectadoId = $pago['pago_proyectado_id'] ?? null;
                if (!$pagoProyectadoId) {
                    continue;
                }

                $pagoProyectado = PagoProyectado::with([
                    'pagosReales.pagoCompleto',
                    'pagosReales.pagoDiferido',
                    'pagosReales.pagoAnticipo',
                ])->find($pagoProyectadoId);

                if (!$pagoProyectado) {
                    continue;
                }

                $deudaPendiente = $this->calcularDeudaPendiente($pagoProyectado);
                if ((float) ($pago['monto'] ?? 0) > $deudaPendiente) {
                    $validator->errors()->add("pagos.$index.monto", 'El monto diferido no puede exceder la deuda pendiente.');
                }
            }
        });

        $data = $validator->validate();

        $fechaPago = Carbon::now()->toDateString();

        $pagos = DB::transaction(function () use ($data, $fechaPago) {
            return collect($data['pagos'])->map(function (array $pagoData) use ($fechaPago) {
                $pagoProyectado = PagoProyectado::with([
                    'pagosReales.pagoCompleto',
                    'pagosReales.pagoDiferido',
                    'pagosReales.pagoAnticipo',
                ])->lockForUpdate()->findOrFail($pagoData['pago_proyectado_id']);

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
                } else {
                    $monto = round((float) $pagoData['monto'], 2);

                    PagoDiferido::create([
                        'pago_real_id' => $pagoReal->id,
                        'monto_diferido' => $monto,
                    ]);
                }

                return $pagoReal->loadMissing(['pagoCompleto', 'pagoDiferido']);
            });
        });

        return response()->json($pagos, 201);
    }

    protected function calcularDeudaPendiente(PagoProyectado $pagoProyectado): float
    {
        $total = (float) ($pagoProyectado->deuda_total ?? $pagoProyectado->monto_proyectado ?? 0);
        $pagado = 0.0;

        $pagoProyectado->loadMissing([
            'pagosReales.pagoCompleto',
            'pagosReales.pagoDiferido',
            'pagosReales.pagoAnticipo',
        ]);

        foreach ($pagoProyectado->pagosReales as $pagoReal) {
            if ($pagoReal->pagoCompleto) {
                $pagado += (float) ($pagoReal->pagoCompleto->monto_completo ?? 0);
            }

            if ($pagoReal->pagoDiferido) {
                $pagado += (float) ($pagoReal->pagoDiferido->monto_diferido ?? 0);
            }

            if ($pagoReal->pagoAnticipo) {
                $pagado += (float) ($pagoReal->pagoAnticipo->monto_anticipo ?? 0);
            }
        }

        return max(round($total - $pagado, 2), 0);
    }
}
