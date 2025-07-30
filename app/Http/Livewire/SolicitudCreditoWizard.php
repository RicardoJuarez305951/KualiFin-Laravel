<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SolicitudCreditoWizard extends Component
{
    public $step = 0;

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
        if ($this->step > 0) {
            $this->validateStep($this->step);
        }
        $this->step++;
    }

    public function previousStep()
    {
        if ($this->step > 1) $this->step--;
    }

    protected function validateStep($step)
    {
        $rules = match($step) {
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
        $this->validate($rules);
    }

    public function submit()
    {
        // validamos todo de nuevo
        for ($i = 1; $i <= 5; $i++) {
            $this->validateStep($i);
        }

        // aquí guardas tu solicitud con todos los datos
        // Solicitud::create([...]);

        session()->flash('success', 'Solicitud enviada correctamente.');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.solicitud-credito-wizard');
    }
}
