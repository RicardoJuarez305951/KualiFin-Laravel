<?php
namespace App\View\Components\Solicitud;

use Illuminate\View\Component;

class DomicilioForm extends Component
{
    public $tiposVivienda = ['Propia','Rentada','Familiar'];

    public function render()
    {
        return view('components.solicitud.domicilio-form');
    }
}
