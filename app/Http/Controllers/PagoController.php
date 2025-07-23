<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index() { return response()->json(Pago::all()); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'credito_id' => 'required|exists:creditos,id',
            'monto' => 'required|numeric',
            'fecha_pago' => 'required|date',
            'tipo_pago' => 'required|string|max:20',
            'creado_en' => 'nullable|date',
        ]);
        $pago = Pago::create($validated);
        return response()->json($pago, 201);
    }
    public function show($id) { return response()->json(Pago::findOrFail($id)); }
    public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);
        $validated = $request->validate([
            'credito_id' => 'sometimes|exists:creditos,id',
            'monto' => 'sometimes|numeric',
            'fecha_pago' => 'sometimes|date',
            'tipo_pago' => 'sometimes|string|max:20',
            'creado_en' => 'nullable|date',
        ]);
        $pago->update($validated);
        return response()->json($pago);
    }
    public function destroy($id)
    {
        $pago = Pago::findOrFail($id);
        $pago->delete();
        return response()->json(null, 204);
    }
}
