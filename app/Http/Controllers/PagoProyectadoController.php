<?php
namespace App\Http\Controllers;

use App\Models\PagoProyectado;
use Illuminate\Http\Request;

class PagoProyectadoController extends Controller
{
    public function index()
    {
        $pagos = PagoProyectado::all();
        return view('pagos_proyectados.index', compact('pagos'));
    }

    public function create()
    {
        return view('pagos_proyectados.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'credito_id'       => 'required|exists:creditos,id',
            'semana'           => 'required|integer',
            'monto_proyectado' => 'required|numeric',
            'fecha_limite'     => 'required|date',
            'estado'           => 'required|string',
        ]);

        PagoProyectado::create($data);
        return redirect()->route('pagos_proyectados.index');
    }

    public function show(PagoProyectado $pagoProyectado)
    {
        return view('pagos_proyectados.show', compact('pagoProyectado'));
    }

    public function edit(PagoProyectado $pagoProyectado)
    {
        return view('pagos_proyectados.edit', compact('pagoProyectado'));
    }

    public function update(Request $request, PagoProyectado $pagoProyectado)
    {
        $data = $request->validate([
            'estado'           => 'required|string',
        ]);

        $pagoProyectado->update($data);
        return redirect()->route('pagos_proyectados.index');
    }

    public function destroy(PagoProyectado $pagoProyectado)
    {
        $pagoProyectado->delete();
        return redirect()->route('pagos_proyectados.index');
    }
}
