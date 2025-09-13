<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Cliente;

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

    public function cliente_historial(Cliente $cliente)
    {
        return view('mobile.ejecutivo.cartera.cliente_historial', compact('cliente'));
    }
}
