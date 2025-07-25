<?php

namespace App\Http\Controllers;

class PromotoraController extends Controller
{
    // GET /promotora
    public function index()
    {
        return view('promotora.promotora_index');
    }

    // GET /promotora/venta
    public function venta()
    {
        return view('promotora.venta');
    }

    // GET /promotora/cartera
    public function cartera()
    {
        return view('promotora.cartera');
    }

    // GET /promotora/objetivo
    public function objetivo()
    {
        return view('promotora.objetivo');
    }

    public function solicitar_venta()
    {
        return view('promotora.solicitar_venta');
    }

    public function ingresar_cliente()
    {
        return view('promotora.ingresar_cliente');
    }

    public function cliente_historial()
    {
        return view('promotora.cliente_historial');
    }
}
