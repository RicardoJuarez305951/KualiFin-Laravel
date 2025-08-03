<?php
namespace App\Http\Controllers;

use App\Models\Credito;
use Illuminate\Http\Request;

class CreditoController extends Controller
{
    public function index()
    {
        $creditos = Credito::all();
        return view('creditos.index', compact('creditos'));
    }

    public function create()
    {
        return view('creditos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'   => 'required|exists:clientes,id',
            'monto_total'  => 'required|numeric',
            'estado'       => 'required|string',
            'interes'      => 'required|numeric',
            'periodo_pago' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_final'  => 'required|date',
        ]);

        Credito::create($data);
        return redirect()->route('creditos.index');
    }

    public function show(Credito $credito)
    {
        return view('creditos.show', compact('credito'));
    }

    public function edit(Credito $credito)
    {
        return view('creditos.edit', compact('credito'));
    }

    public function update(Request $request, Credito $credito)
    {
        $data = $request->validate([
            'estado'       => 'required|string',
            'monto_total'  => 'required|numeric',
            'interes'      => 'required|numeric',
        ]);

        $credito->update($data);
        return redirect()->route('creditos.index');
    }

    public function destroy(Credito $credito)
    {
        $credito->delete();
        return redirect()->route('creditos.index');
    }
}
