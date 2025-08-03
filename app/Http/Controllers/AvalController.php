<?php
namespace App\Http\Controllers;

use App\Models\Aval;
use Illuminate\Http\Request;

class AvalController extends Controller
{
    public function index()
    {
        $avales = Aval::all();
        return view('avales.index', compact('avales'));
    }

    public function create()
    {
        return view('avales.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'CURP'            => 'required|string|size:18',
            'credito_id'      => 'required|exists:creditos,id',
            'nombre'          => 'required|string',
            'apellido_p'      => 'required|string',
            'apellido_m'      => 'nullable|string',
            'fecha_nacimiento'=> 'required|date',
            'direccion'       => 'required|string',
            'telefono'        => 'required|string',
            'parentesco'      => 'required|string',
        ]);

        Aval::create($data);
        return redirect()->route('avales.index');
    }

    public function show(Aval $aval)
    {
        return view('avales.show', compact('aval'));
    }

    public function edit(Aval $aval)
    {
        return view('avales.edit', compact('aval'));
    }

    public function update(Request $request, Aval $aval)
    {
        $data = $request->validate([
            'CURP'            => 'required|string|size:18',
            'nombre'          => 'required|string',
            'apellido_p'      => 'required|string',
            'apellido_m'      => 'nullable|string',
            'fecha_nacimiento'=> 'required|date',
            'direccion'       => 'required|string',
            'telefono'        => 'required|string',
            'parentesco'      => 'required|string',
        ]);

        $aval->update($data);
        return redirect()->route('avales.index');
    }

    public function destroy(Aval $aval)
    {
        $aval->delete();
        return redirect()->route('avales.index');
    }
}
