<?php
namespace App\Http\Controllers;

use App\Models\Contrato;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    public function index()
    {
        $contratos = Contrato::all();
        return view('contratos.index', compact('contratos'));
    }

    public function create()
    {
        return view('contratos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'credito_id'     => 'required|exists:creditos,id',
            'tipo_contrato'  => 'required|string',
            'fecha_generacion'=> 'required|date',
            'url_s3'         => 'required|url',
        ]);

        Contrato::create($data);
        return redirect()->route('contratos.index');
    }

    public function show(Contrato $contrato)
    {
        return view('contratos.show', compact('contrato'));
    }

    public function edit(Contrato $contrato)
    {
        return view('contratos.edit', compact('contrato'));
    }

    public function update(Request $request, Contrato $contrato)
    {
        $data = $request->validate([
            'tipo_contrato'  => 'required|string',
            'url_s3'         => 'required|url',
        ]);

        $contrato->update($data);
        return redirect()->route('contratos.index');
    }

    public function destroy(Contrato $contrato)
    {
        $contrato->delete();
        return redirect()->route('contratos.index');
    }
}
