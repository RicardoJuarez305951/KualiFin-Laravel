<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditoController extends Controller
{
    public function panelRevision()
    {
        // Aquí obtendrías los créditos de la base de datos
        // Por ahora retornamos la vista con datos de ejemplo
        return Inertia::render('PanelRevision');
    }

    public function procesar(Request $request)
    {
        $validated = $request->validate([
            'credito_id' => 'required|string',
            'accion' => 'required|in:aprobar,rechazar,preguntas,liberar',
            'observaciones' => 'nullable|string',
            'preguntas' => 'nullable|string',
        ]);

        // Aquí procesarías la acción en la base de datos
        // Por ejemplo:
        // $credito = Credito::findOrFail($validated['credito_id']);
        // $credito->update(['estado' => $this->mapearEstado($validated['accion'])]);
        
        return back()->with('success', 'Acción procesada correctamente');
    }

    private function mapearEstado($accion)
    {
        return match($accion) {
            'aprobar' => 'aprobado_inicial',
            'rechazar' => 'rechazado',
            'preguntas' => 'pendiente_preguntas',
            'liberar' => 'aprobado_final',
            default => 'pendiente_revision'
        };
    }
}