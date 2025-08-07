<?php
namespace App\Http\Controllers;

use App\Models\Inversion;
use Illuminate\Http\Request;

class InversionController extends Controller
{
    public function index()
    {
        $inversiones = Inversion::all();
        return view('inversiones.index', compact('inversiones'));
    }

    public function create()
    {
        return view('inversiones.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'promotor_id'  => 'required|exists:promotores,id',
            'monto_solicitado'=> 'required|numeric',
            'monto_aprobado'=> 'required|numeric',
            'fecha_solicitud'=> 'required|date',
            'fecha_aprobacion'=> 'required|date',
        ]);

        Inversion::create($data);
        return redirect()->route('inversiones.index');
    }

    public function show(Inversion $inversion)
    {
        return view('inversiones.show', compact('inversion'));
    }

    public function edit(Inversion $inversion)
    {
        return view('inversiones.edit', compact('inversion'));
    }

    public function update(Request $request, Inversion $inversion)
    {
        $data = $request->validate([
            'monto_aprobado'=> 'required|numeric',
        ]);

        $inversion->update($data);
        return redirect()->route('inversiones.index');
    }

    public function destroy(Inversion $inversion)
    {
        $inversion->delete();
        return redirect()->route('inversiones.index');
    }
}
