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
        $currentStep = $request->session()->get('current_step', 1);
        $solicitud = $request->session()->get('solicitud', []);
        $promotores = $this->getPromotoresData();
        
        return view('credito.form', compact('currentStep', 'solicitud', 'promotores'));
    }

    /**
     * Procesa la información enviada desde el formulario.
     */
    public function store(Request $request)
    {
        $currentStep = $request->session()->get('current_step', 1);
        $solicitud = $request->session()->get('solicitud', []);
        $validatedData = [];

        // Valida y guarda los datos según el paso actual
        if ($currentStep == 1) {
            $validatedData = $request->validate([
                'promotor_id' => 'required|numeric',
                'cliente_id' => 'required|numeric',
                'ine_cliente_status' => 'required|in:aprobado',
                'domicilio_cliente_status' => 'required|in:aprobado',
                'ine_aval_status' => 'required|in:aprobado',
                'domicilio_aval_status' => 'required|in:aprobado',
            ], [
                // Mensajes de error personalizados para guiar al usuario
                'promotor_id.required' => 'Debes seleccionar una promotor.',
                'cliente_id.required' => 'Debes seleccionar un cliente.',
                'ine_cliente_status.in' => 'El INE del cliente debe estar aprobado para continuar.',
                'domicilio_cliente_status.in' => 'El comprobante de domicilio del cliente debe estar aprobado.',
                'ine_aval_status.in' => 'El INE del aval debe estar aprobado.',
                'domicilio_aval_status.in' => 'El comprobante de domicilio del aval debe estar aprobado.',
                '*.required' => 'Es necesario evaluar todos los documentos antes de continuar.',
            ]);
            $selectionDetails = $this->getSelectionDetails($validatedData['promotor_id'], $validatedData['cliente_id']);
            $solicitud = array_merge($selectionDetails, $validatedData);

        } elseif ($currentStep == 2) {
            $validatedData = $request->validate(['nombre_completo' => 'required|string|min:3', 'email' => 'required|email']);
            $solicitud = array_merge($solicitud, $validatedData);

        } elseif ($currentStep == 3) {
            $validatedData = $request->validate(['empresa' => 'required|string', 'ingreso_mensual' => 'required|numeric|min:0']);
            $solicitud = array_merge($solicitud, $validatedData);
        }
        
        $request->session()->put('solicitud', $solicitud);
        
        // Lógica para avanzar al siguiente paso o finalizar
        if ($currentStep < $this->totalSteps) {
            $request->session()->put('current_step', $currentStep + 1);
        } else {
            // Lógica final para guardar en la base de datos
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
        if ($request->session()->get('current_step', 1) > 1) {
            $request->session()->decrement('current_step');
        }
        return redirect()->route('credito.create');
    }

    /**
     * Devuelve datos estáticos de promotores y sus clientes.
     */
    private function getPromotoresData()
    {
        return [
            [
                'id' => 1, 'nombre' => 'Ana Sofía Rodríguez', 'clientes' => [
                    [
                        'id' => 101, 'nombre' => 'Javier Morales López', 'curp' => 'MOLJ850101HDFXXX01',
                        'docs' => [
                            'ine_cliente' => 'https://placehold.co/600x400/E2E8F0/4A5568?text=INE+Cliente',
                            'domicilio_cliente' => 'https://placehold.co/600x400/E2E8F0/4A5568?text=Domicilio+Cliente',
                            'ine_aval' => 'https://placehold.co/600x400/E2E8F0/4A5568?text=INE+Aval',
                            'domicilio_aval' => 'https://placehold.co/600x400/E2E8F0/4A5568?text=Domicilio+Aval'
                        ]
                    ],
                    [
                        'id' => 102, 'nombre' => 'Laura Campos Solís', 'curp' => 'CASL920510MDFXXX02',
                        'docs' => [
                            'ine_cliente' => 'https://placehold.co/600x400/CBD5E0/2D3748?text=INE+Cliente',
                            'domicilio_cliente' => 'https://placehold.co/600x400/CBD5E0/2D3748?text=Domicilio+Cliente',
                            'ine_aval' => 'https://placehold.co/600x400/CBD5E0/2D3748?text=INE+Aval',
                            'domicilio_aval' => 'https://placehold.co/600x400/CBD5E0/2D3748?text=Domicilio+Aval'
                        ]
                    ],
                ]
            ],
            [
                'id' => 2, 'nombre' => 'Miguel Ángel Torres', 'clientes' => [
                    ['id' => 201, 'nombre' => 'Roberto Jiménez Silva', 'curp' => 'JISR881120HDFXXX03', 
                    'docs' => 
                    ['ine_cliente' => 'https://placehold.co/600x400/A0AEC0/1A202C?text=INE+Cliente', 'domicilio_cliente' => 'https://placehold.co/600x400/A0AEC0/1A202C?text=Domicilio+Cliente', 
                    'ine_aval' => 'https://placehold.co/600x400/A0AEC0/1A202C?text=INE+Aval', 'domicilio_aval' => 'https://placehold.co/600x400/A0AEC0/1A202C?text=Domicilio+Aval']],
                ]
            ],
        ];
    }

    /**
     * Obtiene los detalles de la promotor y cliente seleccionados.
     */
    private function getSelectionDetails($promotorId, $clienteId)
    {
        $promotores = $this->getPromotoresData();
        $selectedPromotor = collect($promotores)->firstWhere('id', $promotorId);
        $selectedCliente = collect($selectedPromotor['clientes'])->firstWhere('id', $clienteId);

        return [
            'promotor_info' => ['id' => $selectedPromotor['id'], 'nombre' => $selectedPromotor['nombre']],
            'cliente_info' => ['id' => $selectedCliente['id'], 'nombre' => $selectedCliente['nombre'], 'curp' => $selectedCliente['curp']],
        ];
    }
}