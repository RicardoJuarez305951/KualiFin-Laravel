<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'promotora_id'      => 'required|exists:promotoras,id',
            'CURP'              => 'required|string|size:18',
            'nombre'            => 'required|string',
            'apellido_p'        => 'required|string',
            'apellido_m'        => 'nullable|string',
            'fecha_nacimiento'  => 'required|date',
            'tiene_credito_activo' => 'required|boolean',
            'estatus'           => 'required|string',
            'monto_maximo'      => 'required|numeric',
            'activo'            => 'required|boolean',
        ]);

        Cliente::create($data);
        return redirect()->route('clientes.index');
    }

    public function show(Cliente $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre'            => 'required|string',
            'apellido_p'        => 'required|string',
            'apellido_m'        => 'nullable|string',
            'tiene_credito_activo' => 'required|boolean',
            'estatus'           => 'required|string',
            'monto_maximo'      => 'required|numeric',
            'activo'            => 'required|boolean',
        ]);

        $cliente->update($data);
        return redirect()->route('clientes.index');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index');
    }
}
