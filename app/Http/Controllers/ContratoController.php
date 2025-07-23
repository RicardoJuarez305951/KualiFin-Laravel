<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    public function index() { return response()->json(Contrato::all()); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'credito_id' => 'required|exists:creditos,id',
            'nombre_plantilla' => 'required|string|max:50',
            'url_doc' => 'required|string|max:255|unique:contratos,url_doc',
            'generado_en' => 'nullable|date',
        ]);
        $contrato = Contrato::create($validated);
        return response()->json($contrato, 201);
    }
    public function show($id) { return response()->json(Contrato::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $contrato = Contrato::findOrFail($id);
        $validated = $request->validate([
            'credito_id' => 'sometimes|exists:creditos,id',
            'nombre_plantilla' => 'sometimes|string|max:50',
            'url_doc' => 'sometimes|string|max:255|unique:contratos,url_doc,' . $id,
            'generado_en' => 'nullable|date',
        ]);
        $contrato->update($validated);
        return response()->json($contrato);
    }
    public function destroy($id)
    {
        $contrato = Contrato::findOrFail($id);
        $contrato->delete();
        return response()->json(null, 204);
    }
}
