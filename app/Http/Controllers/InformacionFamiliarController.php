<?php

namespace App\Http\Controllers;

use App\Models\InformacionFamiliar;
use Illuminate\Http\Request;

class InformacionFamiliarController extends Controller
{
    public function index() { return response()->json(InformacionFamiliar::all()); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'credito_id' => 'required|exists:creditos,id',
            'nombre_conyuge' => 'nullable|string|max:100',
            'celular_conyuge' => 'nullable|string|max:20',
            'actividad_conyuge' => 'nullable|string|max:100',
            'ingresos_semanales_conyuge' => 'nullable|numeric',
            'domicilio_trabajo_conyuge' => 'nullable|string|max:255',
            'numero_hijos' => 'nullable|integer',
            'personas_en_domicilio' => 'nullable|integer',
            'dependientes_economicos' => 'nullable|integer',
            'conyuge_vive_con_cliente' => 'nullable|boolean',
            'creado_en' => 'nullable|date',
        ]);
        $info = InformacionFamiliar::create($validated);
        return response()->json($info, 201);
    }
    public function show($id) { return response()->json(InformacionFamiliar::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $info = InformacionFamiliar::findOrFail($id);
        $validated = $request->validate([
            'credito_id' => 'sometimes|exists:creditos,id',
            'nombre_conyuge' => 'nullable|string|max:100',
            'celular_conyuge' => 'nullable|string|max:20',
            'actividad_conyuge' => 'nullable|string|max:100',
            'ingresos_semanales_conyuge' => 'nullable|numeric',
            'domicilio_trabajo_conyuge' => 'nullable|string|max:255',
            'numero_hijos' => 'nullable|integer',
            'personas_en_domicilio' => 'nullable|integer',
            'dependientes_economicos' => 'nullable|integer',
            'conyuge_vive_con_cliente' => 'nullable|boolean',
            'creado_en' => 'nullable|date',
        ]);
        $info->update($validated);
        return response()->json($info);
    }
    public function destroy($id)
    {
        $info = InformacionFamiliar::findOrFail($id);
        $info->delete();
        return response()->json(null, 204);
    }
}
