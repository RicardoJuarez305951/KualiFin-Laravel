<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Ejercicio;
use App\Models\Promotor;

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
        $clientesProspectados = Cliente::count();
        $clientesPorSupervisar = Cliente::where('estatus', 'inactivo')->count();

        $ejercicio = Ejercicio::latest('fecha_inicio')->first();
        $moneyWeeklyNow = $ejercicio->dinero_autorizado ?? 0;
        $moneyWeeklyTarget = $ejercicio->venta_objetivo ?? 0;
        $fechaLimite = $ejercicio?->fecha_final?->format('d/m/Y');
        $moneyProgress = $moneyWeeklyTarget > 0
            ? min(100, ($moneyWeeklyNow / $moneyWeeklyTarget) * 100)
            : 0;

        $promotoresSupervisados = Promotor::with('clientes')
            ->get()
            ->map(function ($p) {
                $debe = (float) $p->venta_maxima;
                $falla = max(0, $debe - (float) $p->venta_proyectada_objetivo);
                return [
                    'nombre' => trim($p->nombre . ' ' . $p->apellido_p),
                    'debe' => $debe,
                    'falla' => $falla,
                    'porcentajeFalla' => $debe > 0 ? ($falla / $debe) * 100 : 0,
                    'ventaRegistrada' => (float) $p->venta_proyectada_objetivo,
                    'prospectados' => $p->clientes->pluck('nombre'),
                    'porSupervisar' => $p->clientes
                        ->where('estatus', 'inactivo')
                        ->pluck('nombre'),
                ];
            });

        return view('mobile.supervisor.venta.venta', compact(
            'clientesProspectados',
            'clientesPorSupervisar',
            'moneyWeeklyNow',
            'moneyWeeklyTarget',
            'fechaLimite',
            'moneyProgress',
            'promotoresSupervisados'
        ));
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

      public function apertura()
    {
        return view('mobile.supervisor.apertura.apertura');
    }

}
