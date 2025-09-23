<?php
namespace App\Http\Controllers;

use App\Models\PagoReal;
use App\Models\PagoProyectado;
use App\Models\PagoCompleto;
use App\Models\PagoDiferido;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        $data = $request->validate([
            'pagos' => ['required', 'array', 'min:1'],
            'pagos.*.pago_proyectado_id' => ['required', 'exists:pagos_proyectados,id'],
            'pagos.*.tipo' => ['required', Rule::in(['completo', 'diferido'])],
            'pagos.*.monto' => ['required', 'numeric', 'min:0'],
            'pagos.*.comentario' => ['nullable', 'string'],
        ]);

        $fechaPago = Carbon::now()->toDateString();

        $pagos = DB::transaction(function () use ($data, $fechaPago) {
            return collect($data['pagos'])
                ->map(function (array $pago) use ($fechaPago) {
                    $pagoProyectado = PagoProyectado::findOrFail($pago['pago_proyectado_id']);
                    $tipo = $pago['tipo'];
                    $montoIngresado = round(max((float) $pago['monto'], 0), 2);

                    $pagoRealData = [
                        'pago_proyectado_id' => $pagoProyectado->id,
                        'tipo' => $tipo,
                        'fecha_pago' => $fechaPago,
                        'comentario' => $pago['comentario'] ?? null,
                        // 'estado' => $pago['estado'] ?? null, // Espacio reservado para futuros campos
                    ];

                    $pagoReal = PagoReal::create($pagoRealData);

                    if ($tipo === 'completo') {
                        $montoCompleto = $montoIngresado > 0
                            ? $montoIngresado
                            : (float) ($pagoProyectado->deuda_total ?? $pagoProyectado->monto_proyectado ?? 0);

                        PagoCompleto::create([
                            'pago_real_id' => $pagoReal->id,
                            'monto_completo' => round($montoCompleto, 2),
                        ]);
                    } else {
                        PagoDiferido::create([
                            'pago_real_id' => $pagoReal->id,
                            'monto_diferido' => $montoIngresado,
                        ]);
                    }

                    return $pagoReal->loadMissing(['pagoCompleto', 'pagoDiferido']);
                })
                ->values();
        });

        return response()->json($pagos, 201);
    }
}
