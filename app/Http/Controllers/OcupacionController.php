<?php

namespace App\Http\Controllers;

use App\Models\Ocupacion;
use Illuminate\Http\Request;

class OcupacionController extends Controller
{
    public function index() { return response()->json(Ocupacion::all()); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'credito_id' => 'required|exists:creditos,id',
            'actividad' => 'required|string|max:100',
            'nombre_empresa' => 'nullable|string|max:100',
            'calle' => 'nullable|string|max:100',
            'numero' => 'nullable|string|max:10',
            'colonia' => 'nullable|string|max:100',
            'municipio' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'antiguedad' => 'nullable|string|max:20',
            'monto_percibido' => 'nullable|numeric',
            'periodo_pago' => 'nullable|string|max:20',
            'creado_en' => 'nullable|date',
        ]);
        $ocupacion = Ocupacion::create($validated);
        return response()->json($ocupacion, 201);
    }
    public function show($id) { return response()->json(Ocupacion::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $ocupacion = Ocupacion::findOrFail($id);
        $validated = $request->validate([
            'credito_id' => 'sometimes|exists:creditos,id',
            'actividad' => 'sometimes|string|max:100',
            'nombre_empresa' => 'nullable|string|max:100',
            'calle' => 'nullable|string|max:100',
            'numero' => 'nullable|string|max:10',
            'colonia' => 'nullable|string|max:100',
            'municipio' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'antiguedad' => 'nullable|string|max:20',
            'monto_percibido' => 'nullable|numeric',
            'periodo_pago' => 'nullable|string|max:20',
            'creado_en' => 'nullable|date',
        ]);
        $ocupacion->update($validated);
        return response()->json($ocupacion);
    }
    public function destroy($id)
    {
        $ocupacion = Ocupacion::findOrFail($id);
        $ocupacion->delete();
        return response()->json(null, 204);
    }
}
