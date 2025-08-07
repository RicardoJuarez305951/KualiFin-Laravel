<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;

class PromotorController extends Controller
{
    public function index()
    {
        return view('mobile.index');
    }

    public function objetivo()
    {
        return view('mobile.promotor.objetivo.objetivo');
    }

    public function venta()
    {
        return view('mobile.promotor.venta.venta');
    }

    public function solicitar_venta()
    {
        return view('mobile.promotor.venta.solicitar_venta');
    }

    public function ingresar_cliente()
    {
        return view('mobile.promotor.venta.ingresar_cliente');
    }

    public function cartera()
    {
        return view('mobile.promotor.cartera.cartera');
    }

    public function cliente_historial()
    {
        return view('mobile.promotor.cartera.cliente_historial');
    }
}
