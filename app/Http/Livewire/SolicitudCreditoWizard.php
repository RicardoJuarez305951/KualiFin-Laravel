<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SolicitudCreditoWizard extends Component
{
    public $step = 0;

    // Datos iniciales (Paso 0)
    public $initialData = [
        'promotora'          => '',
        'cliente'            => '',
        'clienteValidacion'  => [],
        'avalValidacion'     => [],
    ];

    // Listas para la selección inicial
    public $promotores = [
        ['id'=>'prom1','nombre'=>'María Rodríguez'],
        ['id'=>'prom2','nombre'=>'Carlos Sánchez'],
        ['id'=>'prom3','nombre'=>'Ana Martínez'],
    ];

    public $clientesPorPromotora = [
        'prom1' => ['Juan Pérez López','Ana García Martínez'],
        'prom2' => ['Roberto Silva Torres','Lucía Hernández Ruiz'],
        'prom3' => ['Carlos Gómez Sánchez','Laura Díaz Mendoza'],
    ];

    public $tiposDocs = ['ine','curp','comprobante'];

    public $imagenesCliente = [
        'ine'        => 'https://images.pexels.com/photos/6863183/pexels-photo-6863183.jpeg',
        'curp'       => 'https://images.pexels.com/photos/6863332/pexels-photo-6863332.jpeg',
        'comprobante'=> 'https://images.pexels.com/photos/6863365/pexels-photo-6863365.jpeg',
    ];

    public $imagenesAval = [
        'ine'        => 'https://images.pexels.com/photos/6863400/pexels-photo-6863400.jpeg',
        'curp'       => 'https://images.pexels.com/photos/6863450/pexels-photo-6863450.jpeg',
        'comprobante'=> 'https://images.pexels.com/photos/6863500/pexels-photo-6863500.jpeg',
    ];

    // Paso 1: Domicilio
    public $domicilio = [
        'calle'            => '',
        'numero'           => '',
        'interior'         => '',
        'colonia'          => '',
        'cp'               => '',
        'tipoVivienda'     => '',
        'municipioEstado'  => '',
        'tiempoResidencia' => '',
        'montoMensual'     => '',
        'telFijo'          => '',
        'telCelular'       => '',
    ];

    // Paso 2: Ocupación
    public $ocupacion = [
        'actividad'           => '',
        'empresa'             => '',
        'domSecCalle'         => '',
        'domSecColonia'       => '',
        'domSecMunicipio'     => '',
        'telefono'            => '',
        'antiguedad'          => '',
        'monto'               => '',
        'periodo'             => '',
        'ingresosAdicionales' => false,
        'ingresoConcepto'     => '',
        'ingresoMonto'        => '',
        'ingresoFrecuencia'   => '',
    ];

    // Paso 3: Familiar
    public $infoFamiliar = [
        'nombreConyugue'        => '',
        'viveConUsted'          => false,
        'celularConyugue'       => '',
        'numeroHijos'           => '',
        'actividadConyugue'     => '',
        'ingresosSemanales'     => '',
        'domicilioTrabajo'      => '',
        'personasEnDomicilio'   => '',
        'dependientesEconomicos'=> '',
    ];

    // Paso 4: Avales
    public $avales = [
        'aval1' => ['nombre'=>'','direccion'=>'','telefono'=>'','parentesco'=>''],
        'aval2' => ['nombre'=>'','direccion'=>'','telefono'=>'','parentesco'=>''],
    ];

    // Paso 5: Garantías
    public $garantias = [];

    public function mount()
    {
        // Inicializa 8 garantías vacías
        $this->garantias = array_fill(0, 8, [
            'electrodomestico'=>'',
            'marca'=>'',
            'noSerie'=>'',
            'modelo'=>'',
            'antiguedad'=>'',
            'montoGarantizado'=>'',
        ]);
    }

    public function nextStep()
    {
        // Validar desde el Paso 1 en adelante
        if ($this->step > 0) {
            $this->validateStep($this->step);
        }
        $this->step++;
    }

    public function previousStep()
    {
        if ($this->step > 0) {
            $this->step--;
        }
    }

    protected function validateStep($step)
    {
        return match($step) {
            0 => [],

            1 => [
                'domicilio.calle'            => 'required',
                'domicilio.numero'           => 'required',
                'domicilio.colonia'          => 'required',
                'domicilio.cp'               => 'required',
                'domicilio.tipoVivienda'     => 'required',
                'domicilio.municipioEstado'  => 'required',
                'domicilio.tiempoResidencia' => 'required',
                'domicilio.montoMensual'     => 'required|numeric',
                'domicilio.telCelular'       => 'required',
            ],

            2 => [
                'ocupacion.actividad'       => 'required',
                'ocupacion.domSecCalle'     => 'required',
                'ocupacion.domSecColonia'   => 'required',
                'ocupacion.domSecMunicipio' => 'required',
                'ocupacion.telefono'        => 'required',
                'ocupacion.antiguedad'      => 'required',
                'ocupacion.monto'           => 'required|numeric',
                'ocupacion.periodo'         => 'required',
            ],

            3 => [
                'infoFamiliar.personasEnDomicilio'    => 'required|numeric',
                'infoFamiliar.dependientesEconomicos'  => 'required|numeric',
            ],

            4 => [
                'avales.aval1.nombre'     => 'required',
                'avales.aval1.direccion'  => 'required',
                'avales.aval1.telefono'   => 'required',
                'avales.aval1.parentesco' => 'required',
                'avales.aval2.nombre'     => 'required',
                'avales.aval2.direccion'  => 'required',
                'avales.aval2.telefono'   => 'required',
                'avales.aval2.parentesco' => 'required',
            ],

            5 => [
                'garantias'                      => 'array|min:1',
                'garantias.*.electrodomestico'   => 'required',
                'garantias.*.marca'              => 'required',
                'garantias.*.noSerie'            => 'required',
                'garantias.*.modelo'             => 'required',
                'garantias.*.antiguedad'         => 'required',
                'garantias.*.montoGarantizado'   => 'required|numeric',
            ],

            default => [],
        };
    }

    public function submit()
    {
        // Validaciones finales
        for ($i = 1; $i <= 5; $i++) {
            $this->validateStep($i);
        }

        // Guardar y redirigir
        // Solicitud::create([...]);

        session()->flash('success', 'Solicitud enviada correctamente.');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.solicitud-credito-wizard');
    }
}
