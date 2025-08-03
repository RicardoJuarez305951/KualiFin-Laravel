<?php
namespace App\Http\Controllers;

use App\Models\Ocupacion;
use Illuminate\Http\Request;

class OcupacionController extends Controller
{
    public function index()
    {
        $ocupaciones = Ocupacion::all();
        return view('ocupaciones.index', compact('ocupaciones'));
    }

    public function create()
    {
        return view('ocupaciones.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'credito_id'     => 'required|exists:creditos,id',
            'actividad'      => 'required|string',
            'nombre_empresa' => 'required|string',
            'calle'          => 'required|string',
            'numero'         => 'required|string',
            'colonia'        => 'required|string',
            'municipio'      => 'required|string',
            'telefono'       => 'required|string',
            'antiguedad'     => 'required|string',
            'monto_percibido'=> 'required|numeric',
            'periodo_pago'   => 'required|string',
        ]);

        Ocupacion::create($data);
        return redirect()->route('ocupaciones.index');
    }

    public function show(Ocupacion $ocupacion)
    {
        return view('ocupaciones.show', compact('ocupacion'));
    }

    public function edit(Ocupacion $ocupacion)
    {
        return view('ocupaciones.edit', compact('ocupacion'));
    }

    public function update(Request $request, Ocupacion $ocupacion)
    {
        $data = $request->validate([
            'antiguedad'     => 'required|string',
            'monto_percibido'=> 'required|numeric',
        ]);

        $ocupacion->update($data);
        return redirect()->route('ocupaciones.index');
    }

    public function destroy(Ocupacion $ocupacion)
    {
        $ocupacion->delete();
        return redirect()->route('ocupaciones.index');
    }
}
