<?php

namespace App\Http\Controllers;

use App\Models\Garantia;
use Illuminate\Http\Request;

class GarantiaController extends Controller
{
    public function index() { return response()->json(Garantia::all()); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'credito_id' => 'required|exists:creditos,id',
            'tipo' => 'required|string|max:100',
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'num_serie' => 'required|string|max:100',
            'antiguedad' => 'required|string|max:20',
            'foto_url' => 'nullable|string|max:255',
            'creado_en' => 'nullable|date',
        ]);
        $garantia = Garantia::create($validated);
        return response()->json($garantia, 201);
    }
    public function show($id) { return response()->json(Garantia::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $garantia = Garantia::findOrFail($id);
        $validated = $request->validate([
            'credito_id' => 'sometimes|exists:creditos,id',
            'tipo' => 'sometimes|string|max:100',
            'marca' => 'sometimes|string|max:100',
            'modelo' => 'sometimes|string|max:100',
            'num_serie' => 'sometimes|string|max:100',
            'antiguedad' => 'sometimes|string|max:20',
            'foto_url' => 'nullable|string|max:255',
            'creado_en' => 'nullable|date',
        ]);
        $garantia->update($validated);
        return response()->json($garantia);
    }
    public function destroy($id)
    {
        $garantia = Garantia::findOrFail($id);
        $garantia->delete();
        return response()->json(null, 204);
    }
}
