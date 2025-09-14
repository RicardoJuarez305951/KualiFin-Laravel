<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Ejercicio;
use App\Models\Promotor;
use App\Models\Supervisor;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    /*
     * -----------------------------------------------------------------
     * Métodos administrativos
     * -----------------------------------------------------------------
     */

    public function adminIndex()
    {
        $supers = Supervisor::all();
        return view('supervisores.index', compact('supers'));
    }

    public function create()
    {
        return view('supervisores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'ejecutivo_id'=> 'required|exists:ejecutivos,id',
            'nombre'      => 'required|string',
            'apellido_p'  => 'required|string',
            'apellido_m'  => 'nullable|string',
        ]);

        Supervisor::create($data);
        return redirect()->route('supervisores.index');
    }

    public function show(Supervisor $supervisor)
    {
        return view('supervisores.show', compact('supervisor'));
    }

    public function edit(Supervisor $supervisor)
    {
        return view('supervisores.edit', compact('supervisor'));
    }

    public function update(Request $request, Supervisor $supervisor)
    {
        $data = $request->validate([
            'nombre'     => 'required|string',
            'apellido_p' => 'required|string',
            'apellido_m' => 'nullable|string',
        ]);

        $supervisor->update($data);
        return redirect()->route('supervisores.index');
    }

    public function destroy(Supervisor $supervisor)
    {
        $supervisor->delete();
        return redirect()->route('supervisores.index');
    }
    /*
     * -----------------------------------------------------------------
     * Métodos para vista mobile
     * -----------------------------------------------------------------
     */

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
        $clientesProspectados   = Cliente::count();
        $clientesPorSupervisar  = Cliente::where('estatus', 'inactivo')->count();

        $ejercicio        = Ejercicio::latest('fecha_inicio')->first();
        $moneyWeeklyNow   = $ejercicio?->dinero_autorizado ?? 0;
        $moneyWeeklyTarget= $ejercicio?->venta_objetivo ?? 0;
        $fechaLimite      = $ejercicio?->fecha_final?->format('d/m/Y');

        $moneyProgress = $moneyWeeklyTarget > 0
            ? min(100, ($moneyWeeklyNow / $moneyWeeklyTarget) * 100)
            : 0;

        $promotoresSupervisados = Promotor::with('clientes')
            ->get()
            ->map(function ($p) {
                $debe  = (float) $p->venta_maxima;
                $falla = max(0, $debe - (float) $p->venta_proyectada_objetivo);

                return [
                    'nombre'          => trim($p->nombre . ' ' . $p->apellido_p),
                    'debe'            => $debe,
                    'falla'           => $falla,
                    'porcentajeFalla' => $debe > 0 ? ($falla / $debe) * 100 : 0,
                    'ventaRegistrada' => (float) $p->venta_proyectada_objetivo,
                    'prospectados'    => $p->clientes->pluck('nombre'),
                    'porSupervisar'   => $p->clientes
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
        $supervisor = auth()->user()->supervisor;

        $promotores = $supervisor
            ? Promotor::where('supervisor_id', $supervisor->id)
                ->select('id', 'nombre', 'apellido_p', 'apellido_m')
                ->get()
            : collect();
        return view('mobile.supervisor.cartera.cartera', compact('promotores'));
    }

    public function carteraPromotor(Promotor $promotor)
    {
        $supervisor = auth()->user()->supervisor;

        abort_if(!$supervisor, 403);
        abort_unless($promotor->supervisor_id === $supervisor->id, 403);

        $clientes = Cliente::where('promotor_id', $promotor->id)
            ->with('credito')
            ->get();

        return view('mobile.supervisor.cartera.promotor', compact('promotor', 'clientes'));
    }

    public function reporte()
    {
        return view('mobile.supervisor.cartera.reporte');
    }

    public function cliente_historial(Cliente $cliente)
    {
        return view('mobile.supervisor.cartera.cliente_historial', compact('cliente'));
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
