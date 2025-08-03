<?php
namespace App\Http\Controllers;

use App\Models\Ejercicio;
use Illuminate\Http\Request;

class EjercicioController extends Controller
{
    public function index()
    {
        $ejercicios = Ejercicio::all();
        return view('ejercicios.index', compact('ejercicios'));
    }

    public function create()
    {
        return view('ejercicios.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supervisor_id'=> 'required|exists:supervisores,id',
            'ejecutivo_id' => 'required|exists:ejecutivos,id',
            'fecha_inicio' => 'required|date',
            'fecha_final'  => 'required|date',
            'dinero'       => 'required|numeric',
        ]);

        Ejercicio::create($data);
        return redirect()->route('ejercicios.index');
    }

    public function show(Ejercicio $ejercicio)
    {
        return view('ejercicios.show', compact('ejercicio'));
    }

    public function edit(Ejercicio $ejercicio)
    {
        return view('ejercicios.edit', compact('ejercicio'));
    }

    public function update(Request $request, Ejercicio $ejercicio)
    {
        $data = $request->validate([
            'fecha_final' => 'required|date',
            'dinero'      => 'required|numeric',
        ]);

        $ejercicio->update($data);
        return redirect()->route('ejercicios.index');
    }

    public function destroy(Ejercicio $ejercicio)
    {
        $ejercicio->delete();
        return redirect()->route('ejercicios.index');
    }
}
