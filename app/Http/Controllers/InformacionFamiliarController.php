<?php
namespace App\Http\Controllers;

use App\Models\InformacionFamiliar;
use Illuminate\Http\Request;

class InformacionFamiliarController extends Controller
{
    public function index()
    {
        $info = InformacionFamiliar::all();
        return view('informacion_familiar.index', compact('info'));
    }

    public function create()
    {
        return view('informacion_familiar.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'credito_id'                   => 'required|exists:creditos,id',
            'nombre_conyuge'               => 'required|string',
            'celular_conyuge'              => 'required|string',
            'actividad_conyuge'            => 'required|string',
            'ingresos_semanales_conyuge'   => 'required|numeric',
            'domicilio_trabajo_conyuge'    => 'required|string',
            'personas_en_domicilio'        => 'required|integer',
            'dependientes_economicos'      => 'required|integer',
            'conyuge_vive_con_cliente'     => 'required|boolean',
        ]);

        InformacionFamiliar::create($data);
        return redirect()->route('informacion_familiar.index');
    }

    public function show(InformacionFamiliar $informacionFamiliar)
    {
        return view('informacion_familiar.show', compact('informacionFamiliar'));
    }

    public function edit(InformacionFamiliar $informacionFamiliar)
    {
        return view('informacion_familiar.edit', compact('informacionFamiliar'));
    }

    public function update(Request $request, InformacionFamiliar $informacionFamiliar)
    {
        $data = $request->validate([
            'personas_en_domicilio'        => 'required|integer',
            'dependientes_economicos'      => 'required|integer',
            'conyuge_vive_con_cliente'     => 'required|boolean',
        ]);

        $informacionFamiliar->update($data);
        return redirect()->route('informacion_familiar.index');
    }

    public function destroy(InformacionFamiliar $informacionFamiliar)
    {
        $informacionFamiliar->delete();
        return redirect()->route('informacion_familiar.index');
    }
}
