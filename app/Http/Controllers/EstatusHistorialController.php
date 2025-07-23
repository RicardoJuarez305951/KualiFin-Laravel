<?php

namespace App\Http\Controllers;

use App\Models\EstatusHistorial;
use Illuminate\Http\Request;

class EstatusHistorialController extends Controller
{
    public function index() { return response()->json(EstatusHistorial::all()); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'credito_id' => 'required|exists:creditos,id',
            'user_id' => 'required|exists:users,id',
            'estado_actualizado' => 'required|string|max:20',
            'comentario' => 'nullable|string',
            'cambiado_en' => 'nullable|date',
        ]);
        $historial = EstatusHistorial::create($validated);
        return response()->json($historial, 201);
    }
    public function show($id) { return response()->json(EstatusHistorial::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $historial = EstatusHistorial::findOrFail($id);
        $validated = $request->validate([
            'credito_id' => 'sometimes|exists:creditos,id',
            'user_id' => 'sometimes|exists:users,id',
            'estado_actualizado' => 'sometimes|string|max:20',
            'comentario' => 'nullable|string',
            'cambiado_en' => 'nullable|date',
        ]);
        $historial->update($validated);
        return response()->json($historial);
    }
    public function destroy($id)
    {
        $historial = EstatusHistorial::findOrFail($id);
        $historial->delete();
        return response()->json(null, 204);
    }
}
