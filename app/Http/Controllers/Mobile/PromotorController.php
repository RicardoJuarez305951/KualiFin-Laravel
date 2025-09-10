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

    public function storeCliente(Request $request)
    {
        $promotor = Promotor::first();
        $data = $request->validate([
            'nombre' => 'required|string',
            'apellido_p' => 'required|string',
            'apellido_m' => 'nullable|string',
            'CURP' => 'required|string|size:18',
            'monto' => 'required|numeric',
        ]);

        $cliente = Cliente::create([
            'promotor_id' => $promotor?->id,
            'CURP' => $data['CURP'],
            'nombre' => $data['nombre'],
            'apellido_p' => $data['apellido_p'],
            'apellido_m' => $data['apellido_m'] ?? '',
            'fecha_nacimiento' => now()->subYears(18),
            'tiene_credito_activo' => true,
            'estatus' => 'activo',
            'monto_maximo' => $data['monto'],
            'activo' => true,
        ]);

        Credito::create([
            'cliente_id' => $cliente->id,
            'monto_total' => $data['monto'],
            'estado' => 'pendiente',
            'interes' => 0,
            'periodicidad' => 'semanal',
            'fecha_inicio' => now(),
            'fecha_final' => now()->addMonths(12),
        ]);

        return redirect()->route('mobile.promotor.ingresar_cliente');
    }

    public function storeRecredito(Request $request)
    {
        $data = $request->validate([
            'CURP' => 'required|string|size:18|exists:clientes,CURP',
            'monto' => 'required|numeric',
        ]);

        $cliente = Cliente::where('CURP', $data['CURP'])->first();

        Credito::create([
            'cliente_id' => $cliente->id,
            'monto_total' => $data['monto'],
            'estado' => 'pendiente',
            'interes' => 0,
            'periodicidad' => 'semanal',
            'fecha_inicio' => now(),
            'fecha_final' => now()->addMonths(12),
        ]);

        return redirect()->route('mobile.promotor.ingresar_cliente');
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
