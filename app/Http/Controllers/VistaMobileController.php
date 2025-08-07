<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class VistaMobileController extends Controller
{
    // GET /mobile
    public function index()
    {
        return view('mobile.index');
    }

    // GET /mobile/objetivo
    public function objetivo()
    {
        return $this->viewByRole('objetivo','objetivo');
    }

    // GET /mobile/venta
    public function venta()
    {
        return $this->viewByRole('venta', 'venta');
    }

    // GET /mobile/solicitar-venta
    public function solicitar_venta()
    {
        return $this->viewByRole('venta', 'solicitar_venta');
    }

    // GET /mobile/ingresar-cliente
    public function ingresar_cliente()
    {
        return $this->viewByRole('venta', 'ingresar_cliente');
    }

    // GET /mobile/cartera
    public function cartera()
    {
        return $this->viewByRole('cartera', 'cartera');
    }    

    // GET /mobile/cliente-historial
    public function cliente_historial()
    {
        return $this->viewByRole('cartera', 'cliente_historial');
    }

    // SUPERVISOR
    public function cartera_vigente()
    {
        return $this->viewByRole('cartera', 'vigente');
    }

    public function cartera_vencida()
    {
        return $this->viewByRole('cartera', 'vencida');
    }

    public function cartera_inactiva()
    {
        return $this->viewByRole('cartera', 'inactiva');
    
    }
    public function cartera_historial_promotora()
    {
        return $this->viewByRole('cartera', 'historial_promotora');
    }

    /**
     * Devuelve la vista según rol del usuario.
     *
     * @param  string  $section Carpeta principal: 'venta' o 'cartera'
     * @param  string  $view    Nombre de la vista dentro de esa carpeta
     */
    protected function viewByRole(string $section, string $view)
    {
        $role = Auth::user()->rol;

        $folder = match ($role) {
            'promotor'   => 'promotor',
            'ejecutivo'  => 'ejecutivo',
            'supervisor' => 'supervisor',
            default      => abort(403),
        };

        // ej. 'mobile.promotor.venta.venta'
        return view("mobile.{$folder}.{$section}.{$view}");
    }
}
