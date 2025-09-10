<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Promotor;
use App\Models\Cliente;
use App\Models\Credito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $promotor = Promotor::with(['supervisor.ejecutivo.user', 'supervisor.user', 'clientes.credito'])
            ->first();

        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $supervisor = $promotor?->supervisor?->user?->name;
        $ejecutivo = $promotor?->supervisor?->ejecutivo?->user?->name;

        $clientes = $promotor?->clientes->map(function ($c) {
            $monto = $c->credito->monto_total ?? $c->monto_maximo;
            return [
                'nombre' => trim($c->nombre . ' ' . $c->apellido_p),
                'monto' => (float) $monto,
            ];
        }) ?? collect();

        $total = $clientes->sum('monto');

        return view('mobile.promotor.venta.venta', compact(
            'fecha',
            'supervisor',
            'ejecutivo',
            'clientes',
            'total'
        ));
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
