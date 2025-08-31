<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;

class SupervisorController extends Controller
{
    public function index()
    {
        return view('mobile.index');
    }

    public function objetivo()
    {
        return view('mobile.supervisor.objetivo.objetivo');
    }

    public function venta()
    {
        return view('mobile.supervisor.venta.venta');
    }

    public function solicitar_venta()
    {
        return view('mobile.supervisor.venta.solicitar_venta');
    }

    public function ingresar_cliente()
    {
        return view('mobile.supervisor.venta.ingresar_cliente');
    }

    
    public function clientes_prospectados()
    {
        return view('mobile.supervisor.venta.clientes_prospectados');
    }

    public function clientes_supervisados()
    {
        return view('mobile.supervisor.venta.clientes_supervisados');
    }

    public function cartera()
    {
        return view('mobile.supervisor.cartera.cartera');
    }
    
    public function reporte()
    {
        return view('mobile.supervisor.cartera.reporte');
    }

    public function cliente_historial()
    {
        return view('mobile.supervisor.cartera.cliente_historial');
    }

    public function cartera_activa()
    {
        return view('mobile.supervisor.cartera.cartera_activa');
    }

    public function cartera_vencida()
    {
        return view('mobile.supervisor.cartera.cartera_vencida');
    }

    public function cartera_inactiva()
    {
        return view('mobile.supervisor.cartera.cartera_inactiva');
    }

    public function cartera_falla()
    {
        return view('mobile.supervisor.cartera.cartera_falla');
    }

    public function cartera_historial_promotor()
    {
        return view('mobile.supervisor.cartera.historial_promotor');
    }

    public function cartera_reacreditacion()
    {
        return view('mobile.supervisor.cartera.reacreditacion');
    }

    public function busqueda()
    {
        return view('mobile.supervisor.busqueda.busqueda');
    }

}
