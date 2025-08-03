<?php
namespace App\Http\Controllers;

use App\Models\DatoContacto;
use Illuminate\Http\Request;

class DatoContactoController extends Controller
{
    public function index()
    {
        $datos = DatoContacto::all();
        return view('datos_contacto.index', compact('datos'));
    }

    public function create()
    {
        return view('datos_contacto.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'credito_id'       => 'required|exists:creditos,id',
            'calle'            => 'required|string',
            'numero_ext'       => 'required|string',
            'numero_int'       => 'nullable|string',
            'monto_mensual'    => 'required|integer',
            'colonia'          => 'required|string',
            'municipio'        => 'required|string',
            'estado'           => 'required|string',
            'cp'               => 'required|string',
            'tiempo_residencia'=> 'required|string',
            'tel_fijo'         => 'nullable|string',
            'tel_cel'          => 'required|string',
            'tipo_de_vivienda' => 'required|string',
        ]);

        DatoContacto::create($data);
        return redirect()->route('datos_contacto.index');
    }

    public function show(DatoContacto $datoContacto)
    {
        return view('datos_contacto.show', compact('datoContacto'));
    }

    public function edit(DatoContacto $datoContacto)
    {
        return view('datos_contacto.edit', compact('datoContacto'));
    }

    public function update(Request $request, DatoContacto $datoContacto)
    {
        $data = $request->validate([
            'monto_mensual'    => 'required|integer',
            'tiempo_residencia'=> 'required|string',
            'tel_cel'          => 'required|string',
        ]);

        $datoContacto->update($data);
        return redirect()->route('datos_contacto.index');
    }

    public function destroy(DatoContacto $datoContacto)
    {
        $datoContacto->delete();
        return redirect()->route('datos_contacto.index');
    }
}
