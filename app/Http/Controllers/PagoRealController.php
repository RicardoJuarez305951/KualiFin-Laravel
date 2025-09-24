<?php
namespace App\Http\Controllers;

use App\Models\PagoReal;
use App\Models\PagoProyectado;
use App\Models\PagoCompleto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
            'pago_proyectado_ids' => ['required', 'array', 'min:1'],
            'pago_proyectado_ids.*' => ['required', 'integer', 'distinct', 'exists:pagos_proyectados,id'],
        ]);

        $fechaPago = Carbon::now()->toDateString();

        $pagos = DB::transaction(function () use ($data, $fechaPago) {
            return array_map(function (int $pagoProyectadoId) use ($fechaPago) {
                $pagoProyectado = PagoProyectado::findOrFail($pagoProyectadoId);

                $pagoReal = PagoReal::create([
                    'pago_proyectado_id' => $pagoProyectado->id,
                    'tipo' => 'completo',
                    'fecha_pago' => $fechaPago,
                    'comentario' => null,
                ]);

                $monto = (float) ($pagoProyectado->deuda_total ?? $pagoProyectado->monto_proyectado ?? 0);

                PagoCompleto::create([
                    'pago_real_id' => $pagoReal->id,
                    'monto_completo' => round(max($monto, 0), 2),
                ]);

                return $pagoReal->loadMissing('pagoCompleto');
            }, $data['pago_proyectado_ids']);
        });

        return response()->json($pagos, 201);
    }
}
