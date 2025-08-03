<?php
namespace App\Http\Controllers;

use App\Models\Garantia;
use Illuminate\Http\Request;

class GarantiaController extends Controller
{
    public function index()
    {
        $garantias = Garantia::all();
        return view('garantias.index', compact('garantias'));
    }

    public function create()
    {
        return view('garantias.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'credito_id'     => 'required|exists:creditos,id',
            'propietario'    => 'required|string',
            'tipo'           => 'required|string',
            'marca'          => 'required|string',
            'modelo'         => 'required|string',
            'num_serie'      => 'required|string',
            'antiguedad'     => 'required|string',
            'monto_garantizado'=> 'required|numeric',
            'foto_url'       => 'required|url',
        ]);

        Garantia::create($data);
        return redirect()->route('garantias.index');
    }

    public function show(Garantia $garantia)
    {
        return view('garantias.show', compact('garantia'));
    }

    public function edit(Garantia $garantia)
    {
        return view('garantias.edit', compact('garantia'));
    }

    public function update(Request $request, Garantia $garantia)
    {
        $data = $request->validate([
            'tipo'           => 'required|string',
            'marca'          => 'required|string',
            'modelo'         => 'required|string',
            'monto_garantizado'=> 'required|numeric',
        ]);

        $garantia->update($data);
        return redirect()->route('garantias.index');
    }

    public function destroy(Garantia $garantia)
    {
        $garantia->delete();
        return redirect()->route('garantias.index');
    }
}
