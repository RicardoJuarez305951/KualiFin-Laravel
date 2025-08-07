<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;

class EjecutivoController extends Controller
{
    public function index()
    {
        return view('mobile.index');
    }

    public function objetivo()
    {
        return view('mobile.ejecutivo.objetivo.objetivo');
    }

    public function venta()
    {
        return view('mobile.ejecutivo.venta.venta');
    }

    public function solicitar_venta()
    {
        return view('mobile.ejecutivo.venta.solicitar_venta');
    }

    public function ingresar_cliente()
    {
        return view('mobile.ejecutivo.venta.ingresar_cliente');
    }

    public function cartera()
    {
        return view('mobile.ejecutivo.cartera.cartera');
    }

    public function cliente_historial()
    {
        return view('mobile.ejecutivo.cartera.cliente_historial');
    }
}
