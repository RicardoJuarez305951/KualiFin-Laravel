<?php

namespace App\Http\Controllers;

class VistaMovilController extends Controller
{
    // GET /movil
    public function index()
    {
        return view('movil.index');
    }

    // GET /movil/venta
    public function venta()
    {
        return view('movil.venta.venta');
    }

    // GET /movil/cartera
    public function cartera()
    {
        return view('movil.cartera.cartera');
    }

    // GET /movil/objetivo
    public function objetivo()
    {
        return view('movil.objetivo');
    }

    public function solicitar_venta()
    {
        return view('movil.venta.solicitar_venta');
    }

    public function ingresar_cliente()
    {
        return view('movil.venta.ingresar_cliente');
    }

    public function cliente_historial()
    {
        return view('movil.cartera.cliente_historial');
    }
}
