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

    public function cartera()
    {
        return view('mobile.supervisor.cartera.cartera');
    }

    public function cliente_historial()
    {
        return view('mobile.supervisor.cartera.cliente_historial');
    }

    public function cartera_vigente()
    {
        return view('mobile.supervisor.cartera.vigente');
    }

    public function cartera_vencida()
    {
        return view('mobile.supervisor.cartera.vencida');
    }

    public function cartera_inactiva()
    {
        return view('mobile.supervisor.cartera.inactiva');
    }

    public function cartera_historial_promotor()
    {
        return view('mobile.supervisor.cartera.historial_promotor');
    }
}
