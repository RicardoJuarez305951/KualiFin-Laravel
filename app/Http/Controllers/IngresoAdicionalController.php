<?php
namespace App\Http\Controllers;

use App\Models\IngresoAdicional;
use Illuminate\Http\Request;

class IngresoAdicionalController extends Controller
{
    public function index()
    {
        $ingresos = IngresoAdicional::all();
        return view('ingresos_adicionales.index', compact('ingresos'));
    }

    public function create()
    {
        return view('ingresos_adicionales.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ocupacion_id'=> 'required|exists:ocupaciones,id',
            'concepto'    => 'required|string',
            'monto'       => 'required|numeric',
            'frecuencia'  => 'required|string',
        ]);

        IngresoAdicional::create($data);
        return redirect()->route('ingresos_adicionales.index');
    }

    public function show(IngresoAdicional $ingresoAdicional)
    {
        return view('ingresos_adicionales.show', compact('ingresoAdicional'));
    }

    public function edit(IngresoAdicional $ingresoAdicional)
    {
        return view('ingresos_adicionales.edit', compact('ingresoAdicional'));
    }

    public function update(Request $request, IngresoAdicional $ingresoAdicional)
    {
        $data = $request->validate([
            'concepto'   => 'required|string',
            'monto'      => 'required|numeric',
            'frecuencia' => 'required|string',
        ]);

        $ingresoAdicional->update($data);
        return redirect()->route('ingresos_adicionales.index');
    }

    public function destroy(IngresoAdicional $ingresoAdicional)
    {
        $ingresoAdicional->delete();
        return redirect()->route('ingresos_adicionales.index');
    }
}
