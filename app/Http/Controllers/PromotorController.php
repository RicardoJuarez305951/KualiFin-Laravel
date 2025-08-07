<?php

namespace App\Http\Controllers;

class PromotorController extends Controller
{
    // GET /promotor
    public function index()
    {
        return view('promotor.promotor_index');
    }

    // GET /promotor/venta
    public function venta()
    {
        return view('promotor.venta');
    }

    // GET /promotor/cartera
    public function cartera()
    {
        return view('promotor.cartera');
    }

    // GET /promotor/objetivo
    public function objetivo()
    {
        return view('promotor.objetivo');
    }

    public function solicitar_venta()
    {
        return view('promotor.solicitar_venta');
    }

    public function ingresar_cliente()
    {
        return view('promotor.ingresar_cliente');
    }

    public function cliente_historial()
    {
        return view('promotor.cliente_historial');
    }
}
