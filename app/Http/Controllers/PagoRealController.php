<?php
namespace App\Http\Controllers;

use App\Models\PagoReal;
use App\Models\PagoProyectado;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
            'monto_pagado'      => 'required|numeric',
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
            'pago_proyectado_ids' => 'required|array',
            'pago_proyectado_ids.*' => 'exists:pagos_proyectados,id',
        ]);

        $pagos = [];

        foreach ($data['pago_proyectado_ids'] as $id) {
            $pagoProyectado = PagoProyectado::findOrFail($id);
            $pagos[] = PagoReal::create([
                'pago_proyectado_id' => $id,
                'monto_pagado' => $pagoProyectado->deuda_total ?? $pagoProyectado->monto_proyectado,
                'tipo' => 'completo',
                'fecha_pago' => Carbon::now()->toDateString(),
            ]);
        }

        return response()->json($pagos, 201);
    }
}
