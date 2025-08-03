<?php
namespace App\Http\Controllers;

use App\Models\Comision;
use Illuminate\Http\Request;

class ComisionController extends Controller
{
    public function index()
    {
        $comisiones = Comision::all();
        return view('comisiones.index', compact('comisiones'));
    }

    public function create()
    {
        return view('comisiones.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_beneficiado' => 'required|string',
            'beneficiado_id'   => 'required|integer',
            'porcentaje'       => 'required|numeric',
            'monto_base'       => 'required|numeric',
            'monto_pago'       => 'required|numeric',
            'fecha_pago'       => 'required|date',
        ]);

        Comision::create($data);
        return redirect()->route('comisiones.index');
    }

    public function show(Comision $comision)
    {
        return view('comisiones.show', compact('comision'));
    }

    public function edit(Comision $comision)
    {
        return view('comisiones.edit', compact('comision'));
    }

    public function update(Request $request, Comision $comision)
    {
        $data = $request->validate([
            'porcentaje' => 'required|numeric',
            'monto_base' => 'required|numeric',
            'monto_pago' => 'required|numeric',
            'fecha_pago' => 'required|date',
        ]);

        $comision->update($data);
        return redirect()->route('comisiones.index');
    }

    public function destroy(Comision $comision)
    {
        $comision->delete();
        return redirect()->route('comisiones.index');
    }
}
