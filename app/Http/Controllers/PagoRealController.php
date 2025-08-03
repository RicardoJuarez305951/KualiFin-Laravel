<?php
namespace App\Http\Controllers;

use App\Models\PagoReal;
use Illuminate\Http\Request;

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
}
