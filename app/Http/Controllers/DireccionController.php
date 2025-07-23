<?php

namespace App\Http\Controllers;

use App\Models\Direccion;
use Illuminate\Http\Request;

class DireccionController extends Controller
{
    public function index() { return response()->json(Direccion::all()); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'credito_id' => 'required|exists:creditos,id',
            'calle' => 'required|string|max:150',
            'numero_ext' => 'required|string|max:10',
            'numero_int' => 'nullable|string|max:10',
            'monto_mensual' => 'required|integer',
            'colonia' => 'required|string|max:100',
            'municipio' => 'required|string|max:100',
            'estado' => 'required|string|max:100',
            'cp' => 'required|string|max:10',
            'tiempo_residencia' => 'required|string|max:20',
            'tel_fijo' => 'nullable|string|max:20',
            'tel_cel' => 'nullable|string|max:20',
            'tipo_de_vivienda' => 'required|string|max:100',
            'creado_en' => 'nullable|date',
        ]);
        $direccion = Direccion::create($validated);
        return response()->json($direccion, 201);
    }
    public function show($id) { return response()->json(Direccion::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $direccion = Direccion::findOrFail($id);
        $validated = $request->validate([
            'credito_id' => 'sometimes|exists:creditos,id',
            'calle' => 'sometimes|string|max:150',
            'numero_ext' => 'sometimes|string|max:10',
            'numero_int' => 'nullable|string|max:10',
            'monto_mensual' => 'sometimes|integer',
            'colonia' => 'sometimes|string|max:100',
            'municipio' => 'sometimes|string|max:100',
            'estado' => 'sometimes|string|max:100',
            'cp' => 'sometimes|string|max:10',
            'tiempo_residencia' => 'sometimes|string|max:20',
            'tel_fijo' => 'nullable|string|max:20',
            'tel_cel' => 'nullable|string|max:20',
            'tipo_de_vivienda' => 'sometimes|string|max:100',
            'creado_en' => 'nullable|date',
        ]);
        $direccion->update($validated);
        return response()->json($direccion);
    }
    public function destroy($id)
    {
        $direccion = Direccion::findOrFail($id);
        $direccion->delete();
        return response()->json(null, 204);
    }
}
