<?php

namespace App\Http\Controllers;

use App\Models\IngresoAdicional;
use Illuminate\Http\Request;

class IngresoAdicionalController extends Controller
{
    public function index() { return response()->json(IngresoAdicional::all()); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ocupacion_id' => 'required|exists:ocupaciones,id',
            'concepto' => 'required|string|max:100',
            'monto' => 'required|numeric',
            'frecuencia' => 'required|string|max:20',
            'creado_en' => 'nullable|date',
        ]);
        $ingreso = IngresoAdicional::create($validated);
        return response()->json($ingreso, 201);
    }
    public function show($id) { return response()->json(IngresoAdicional::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $ingreso = IngresoAdicional::findOrFail($id);
        $validated = $request->validate([
            'ocupacion_id' => 'sometimes|exists:ocupaciones,id',
            'concepto' => 'sometimes|string|max:100',
            'monto' => 'sometimes|numeric',
            'frecuencia' => 'sometimes|string|max:20',
            'creado_en' => 'nullable|date',
        ]);
        $ingreso->update($validated);
        return response()->json($ingreso);
    }
    public function destroy($id)
    {
        $ingreso = IngresoAdicional::findOrFail($id);
        $ingreso->delete();
        return response()->json(null, 204);
    }
}
