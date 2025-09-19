<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Ejecutivo;
use Illuminate\Http\Request;

class EjecutivoController extends Controller
{
    /*
     * -----------------------------------------------------------------
     * Métodos administrativos
     * -----------------------------------------------------------------
     */

    public function adminIndex()
    {
        $ejecutivos = Ejecutivo::all();
        return view('ejecutivos.index', compact('ejecutivos'));
    }

    public function create()
    {
        return view('ejecutivos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'nombre'     => 'required|string',
            'apellido_p' => 'required|string',
            'apellido_m' => 'nullable|string',
        ]);

        Ejecutivo::create($data);
        return redirect()->route('ejecutivos.index');
    }

    public function show(Ejecutivo $ejecutivo)
    {
        return view('ejecutivos.show', compact('ejecutivo'));
    }

    public function edit(Ejecutivo $ejecutivo)
    {
        return view('ejecutivos.edit', compact('ejecutivo'));
    }

    public function update(Request $request, Ejecutivo $ejecutivo)
    {
        $data = $request->validate([
            'nombre'     => 'required|string',
            'apellido_p' => 'required|string',
            'apellido_m' => 'nullable|string',
        ]);

        $ejecutivo->update($data);
        return redirect()->route('ejecutivos.index');
    }

    public function destroy(Ejecutivo $ejecutivo)
    {
        $ejecutivo->delete();
        return redirect()->route('ejecutivos.index');
    }

    /*
     * -----------------------------------------------------------------
     * Métodos para vista mobile
     * -----------------------------------------------------------------
     */

    public function index()
    {
        return view('mobile.index');
    }

    public function objetivo()
    {
        return view('mobile.ejecutivo.objetivo.objetivo');
    }

    public function venta()
    {
        return view('mobile.ejecutivo.venta.venta');
    }

    public function solicitar_venta()
    {
        return view('mobile.ejecutivo.venta.solicitar_venta');
    }

    public function ingresar_cliente()
    {
        return view('mobile.ejecutivo.venta.ingresar_cliente');
    }

    public function cartera()
    {
        return view('mobile.ejecutivo.cartera.cartera');
    }

    public function cliente_historial(Cliente $cliente)
    {
        return view('mobile.ejecutivo.cartera.cliente_historial', compact('cliente'));
    }

    public function venta_supervisor()
    {
        return view('mobile.ejecutivo.venta.venta_supervisor');
    }
    
    public function desembolso()
    {
        return view('mobile.ejecutivo.venta.desembolso');
    }
    
    public function busqueda()
    {
        return view('mobile.ejecutivo.busqueda.busqueda');
    }
    
    public function informes()
    {
        return view('mobile.ejecutivo.informes.informes');
    }
    
    public function reportes()
    {
        return view('mobile.ejecutivo.informes.reportes');
    }
}
