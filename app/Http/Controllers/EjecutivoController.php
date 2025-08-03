<?php
namespace App\Http\Controllers;

use App\Models\Ejecutivo;
use Illuminate\Http\Request;

class EjecutivoController extends Controller
{
    public function index()
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
}
