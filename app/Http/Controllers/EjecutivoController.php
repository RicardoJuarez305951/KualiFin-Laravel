<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Support\RoleHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EjecutivoController extends Controller
{
    /*
     * -----------------------------------------------------------------
     * Métodos administrativos
     * -----------------------------------------------------------------
     */

    public function adminIndex()
    {
        $ejecutivos = Ejecutivo::all();
        return view('ejecutivos.index', compact('ejecutivos'));
    }

    public function create()
    {
        return view('ejecutivos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'nombre'     => 'required|string',
            'apellido_p' => 'required|string',
            'apellido_m' => 'nullable|string',
        ]);

        Ejecutivo::create($data);
        return redirect()->route('ejecutivos.index');
    }

    public function show(Ejecutivo $ejecutivo)
    {
        return view('ejecutivos.show', compact('ejecutivo'));
    }

    public function edit(Ejecutivo $ejecutivo)
    {
        return view('ejecutivos.edit', compact('ejecutivo'));
    }

    public function update(Request $request, Ejecutivo $ejecutivo)
    {
        $data = $request->validate([
            'nombre'     => 'required|string',
            'apellido_p' => 'required|string',
            'apellido_m' => 'nullable|string',
        ]);

        $ejecutivo->update($data);
        return redirect()->route('ejecutivos.index');
    }

    public function destroy(Ejecutivo $ejecutivo)
    {
        $ejecutivo->delete();
        return redirect()->route('ejecutivos.index');
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

    public function cartera(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $ejecutivo = $this->resolveEjecutivoContext($primaryRole, $user?->id, (int) $request->query('ejecutivo_id'));
        $nombre = $ejecutivo?->nombre;
        $apellido_p = $ejecutivo?->apellido_p;
        $apellido_m = $ejecutivo?->apellido_m;

        $supervisores = $ejecutivo
            ? $ejecutivo->supervisors()
                ->select('id', 'nombre', 'apellido_p', 'apellido_m')
                ->orderBy('nombre')
                ->get()
            : collect();

        [$cartera_activa, $cartera_vencida, $cartera_falla, $cartera_inactivaP] =
            $this->buildCarteraMetrics($supervisores);

        return view('mobile.ejecutivo.cartera.cartera', compact(
            'ejecutivo',
            'nombre',
            'apellido_p',
            'apellido_m',
            'supervisores',
            'cartera_activa',
            'cartera_vencida',
            'cartera_falla',
            'cartera_inactivaP'
        ));
    }

    private function resolveEjecutivoContext(?string $primaryRole, ?int $userId, int $overrideEjecutivoId = 0): ?Ejecutivo
    {
        if ($primaryRole === 'ejecutivo' && $userId) {
            return Ejecutivo::firstWhere('user_id', $userId);
        }

        if (in_array($primaryRole, ['administrativo', 'superadmin'], true)) {
            if ($overrideEjecutivoId > 0) {
                return Ejecutivo::find($overrideEjecutivoId);
            }

            return Ejecutivo::first();
        }

        return null;
    }

    /**
     * Calcula los totales básicos de cartera a partir de la lista de supervisores.
     */
    private function buildCarteraMetrics(Collection $supervisores): array
    {
        if ($supervisores->isEmpty()) {
            return [0.0, 0.0, 0.0, 0.0];
        }

        $supervisorIds = $supervisores->pluck('id')->filter();
        if ($supervisorIds->isEmpty()) {
            return [0.0, 0.0, 0.0, 0.0];
        }

        $promotorIds = Promotor::whereIn('supervisor_id', $supervisorIds)->pluck('id');
        if ($promotorIds->isEmpty()) {
            return [0.0, 0.0, 0.0, 0.0];
        }

        $clienteIds = Cliente::whereIn('promotor_id', $promotorIds)->pluck('id');
        if ($clienteIds->isEmpty()) {
            return [0.0, 0.0, 0.0, 0.0];
        }

        $totalClientes = $clienteIds->count();
        $inactivos = Cliente::whereIn('id', $clienteIds)
            ->where(function ($query) {
                $query->where('activo', false)->orWhereNull('activo');
            })
            ->count();
        $cartera_inactivaP = $totalClientes > 0
            ? round(($inactivos / max(1, (float) $totalClientes)) * 100, 2)
            : 0.0;

        $cartera_activa = (float) Credito::whereIn('cliente_id', $clienteIds)
            ->where('estado', 'activo')
            ->sum('monto_total');

        $cartera_vencida = (float) Credito::whereIn('cliente_id', $clienteIds)
            ->where('estado', 'vencido')
            ->sum('monto_total');

        $creditos = Credito::whereIn('cliente_id', $clienteIds)
            ->with([
                'pagosProyectados' => function ($query) {
                    $query->where('fecha_limite', '<', now())
                        ->with([
                            'pagosReales.pagoCompleto',
                            'pagosReales.pagoAnticipo',
                            'pagosReales.pagoDiferido',
                        ]);
                },
            ])
            ->get();

        $cartera_falla = 0.0;
        foreach ($creditos as $credito) {
            foreach ($credito->pagosProyectados as $pago) {
                $proyectado = (float) ($pago->monto_proyectado ?? 0);
                $pagado = (float) $pago->pagosReales->sum(fn ($real) => (float) ($real->monto ?? 0));
                $cartera_falla += max(0, $proyectado - $pagado);
            }
        }

        return [
            round($cartera_activa, 2),
            round($cartera_vencida, 2),
            round($cartera_falla, 2),
            $cartera_inactivaP,
        ];
    }

    public function cliente_historial(Cliente $cliente)
    {
        return view('mobile.ejecutivo.cartera.cliente_historial', compact('cliente'));
    }

    public function venta_supervisor()
    {
        return view('mobile.ejecutivo.venta.venta_supervisor');
    }
    
    public function desembolso()
    {
        return view('mobile.ejecutivo.venta.desembolso');
    }
    
    public function busqueda()
    {
        return view('mobile.ejecutivo.busqueda.busqueda');
    }
    
    public function informes()
    {
        return view('mobile.ejecutivo.informes.informes');
    }
    
    public function reportes()
    {
        return view('mobile.ejecutivo.informes.reportes');
    }
}
