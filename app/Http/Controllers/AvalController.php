<?php

namespace App\Http\Controllers;

use App\Models\Aval;
use Illuminate\Http\Request;

class AvalController extends Controller
{
    public function index() { return response()->json(Aval::all()); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'credito_id' => 'required|exists:creditos,id',
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'parentesco' => 'required|string|max:20',
            'curp' => 'required|string|max:18',
            'creado_en' => 'nullable|date',
        ]);
        $aval = Aval::create($validated);
        return response()->json($aval, 201);
    }
    public function show($id) { return response()->json(Aval::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $aval = Aval::findOrFail($id);
        $validated = $request->validate([
            'credito_id' => 'sometimes|exists:creditos,id',
            'nombre' => 'sometimes|string|max:100',
            'direccion' => 'sometimes|string|max:255',
            'telefono' => 'sometimes|string|max:20',
            'parentesco' => 'sometimes|string|max:20',
            'curp' => 'sometimes|string|max:18',
            'creado_en' => 'nullable|date',
        ]);
        $aval->update($validated);
        return response()->json($aval);
    }
    public function destroy($id)
    {
        $aval = Aval::findOrFail($id);
        $aval->delete();
        return response()->json(null, 204);
    }
}
