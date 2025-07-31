<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SolicitudCreditoController extends Controller
{
    // Total de pasos en el formulario
    private $totalSteps = 4;

    /**
     * Muestra la vista principal del wizard, renderizando el paso actual.
     */
    public function create(Request $request)
    {
        // Obtiene el paso actual de la sesión, si no existe, empieza en 1.
        $currentStep = $request->session()->get('current_step', 1);
        // Obtiene los datos del formulario guardados en la sesión.
        $solicitud = $request->session()->get('solicitud', []);

        // --- Datos estáticos para la selección ---
        $promotoras = $this->getPromotorasData();
        
        return view('credito.inicial', compact('currentStep', 'solicitud', 'promotoras'));
    }

    /**
     * Procesa la información enviada desde el formulario.
     */
    public function store(Request $request)
    {
        $currentStep = $request->session()->get('current_step', 1);
        $solicitud = $request->session()->get('solicitud', []);

        // Valida y guarda los datos según el paso actual
        if ($currentStep == 1) {
            $validatedData = $request->validate([
                'promotora_id' => 'required|numeric',
                'cliente_id' => 'required|numeric',
            ]);
            // Guarda la información seleccionada en la sesión
            $solicitud = $this->getSelectionDetails($validatedData['promotora_id'], $validatedData['cliente_id']);

        } elseif ($currentStep == 2) {
            $validatedData = $request->validate([
                'nombre_completo' => 'required|string|min:3',
                'email' => 'required|email',
            ]);
            $solicitud = array_merge($solicitud, $validatedData);

        } elseif ($currentStep == 3) {
            $validatedData = $request->validate([
                'empresa' => 'required|string',
                'ingreso_mensual' => 'required|numeric|min:0',
            ]);
            $solicitud = array_merge($solicitud, $validatedData);
        }
        
        $request->session()->put('solicitud', $solicitud);
        
        // Lógica para avanzar al siguiente paso o finalizar
        if ($currentStep < $this->totalSteps) {
            $request->session()->put('current_step', $currentStep + 1);
        } else {
            // --- LÓGICA FINAL ---
            // Aquí guardas los datos en la base de datos, por ejemplo:
            // Solicitud::create($solicitud);

            // Limpias la sesión para una nueva solicitud
            $request->session()->forget(['solicitud', 'current_step']);
            
            return redirect()->route('credito.create')->with('success', '¡Tu solicitud ha sido enviada con éxito!');
        }

        return redirect()->route('credito.create');
    }

    /**
     * Permite al usuario retroceder un paso.
     */
    public function back(Request $request)
    {
        $currentStep = $request->session()->get('current_step', 1);

        if ($currentStep > 1) {
            $request->session()->put('current_step', $currentStep - 1);
        }

        return redirect()->route('credito.create');
    }

    /**
     * Devuelve datos estáticos de promotoras y sus clientes.
     */
    private function getPromotorasData()
    {
        return [
            [
                'id' => 1, 'nombre' => 'Ana Sofía Rodríguez', 'clientes' => [
                    ['id' => 101, 'nombre' => 'Javier Morales López'],
                    ['id' => 102, 'nombre' => 'Laura Campos Solís'],
                ]
            ],
            [
                'id' => 2, 'nombre' => 'Miguel Ángel Torres', 'clientes' => [
                    ['id' => 201, 'nombre' => 'Roberto Jiménez Silva'],
                    ['id' => 202, 'nombre' => 'Fernanda Castillo Cruz'],
                ]
            ],
        ];
    }

    /**
     * Obtiene los detalles de la promotora y cliente seleccionados.
     */
    private function getSelectionDetails($promotoraId, $clienteId)
    {
        $promotoras = $this->getPromotorasData();
        $selectedPromotora = collect($promotoras)->firstWhere('id', $promotoraId);
        $selectedCliente = collect($selectedPromotora['clientes'])->firstWhere('id', $clienteId);

        return [
            'promotora_info' => [
                'id' => $selectedPromotora['id'],
                'nombre' => $selectedPromotora['nombre'],
            ],
            'cliente_info' => [
                'id' => $selectedCliente['id'],
                'nombre' => $selectedCliente['nombre'],
            ],
        ];
    }
}
