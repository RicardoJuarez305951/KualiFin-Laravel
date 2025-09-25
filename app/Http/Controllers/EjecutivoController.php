<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSupervisorContext;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Services\BusquedaClientesService;
use App\Support\RoleHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EjecutivoController extends Controller
{
    use HandlesSupervisorContext;
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

    public function venta(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $ejecutivo = $this->resolveEjecutivoContext(
            $primaryRole,
            $user?->id,
            (int) $request->query('ejecutivo_id')
        );

        if ($primaryRole === 'ejecutivo') {
            abort_if(!$ejecutivo, 403, 'Perfil de ejecutivo no configurado.');
        }

        $supervisores = $ejecutivo
            ? Supervisor::query()
                ->where('ejecutivo_id', $ejecutivo->id)
                ->with($this->supervisorPromotoresRelationship())
                ->orderBy('nombre')
                ->orderBy('apellido_p')
                ->orderBy('apellido_m')
                ->get()
            : collect();

        $supervisores = $supervisores instanceof Collection ? $supervisores : collect($supervisores);

        [$supervisorSummaries, $totals, $ventaFecha] = $this->buildVentaSupervisorMetrics($supervisores);

        $nombre = $ejecutivo?->nombre ?? ($user?->name ?? 'Ejecutivo');
        $apellido_p = $ejecutivo?->apellido_p;
        $apellido_m = $ejecutivo?->apellido_m;

        $supervisorContextQuery = array_filter([
            'ejecutivo_id' => $request->query('ejecutivo_id'),
        ], fn ($value) => !is_null($value));

        return view('mobile.ejecutivo.venta.venta', [
            'ejecutivo' => $ejecutivo,
            'nombre' => $nombre,
            'apellido_p' => $apellido_p,
            'apellido_m' => $apellido_m,
            'fechaVenta' => $ventaFecha,
            'debeOperativo' => $totals['debeOperativo'],
            'debeProyectado' => $totals['debeProyectado'],
            'fallaReal' => $totals['falla'],
            'cobranza' => $totals['cobranza'],
            'ventaRegistradaTotal' => $totals['ventaRegistrada'],
            'fallaPct' => $totals['fallaPct'],
            'cobranzaPct' => $totals['cobranzaPct'],
            'supervisores' => $supervisorSummaries,
            'supervisorContextQuery' => $supervisorContextQuery,
            'horarios' => collect(),
        ]);
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

    private function supervisorPromotoresRelationship(): array
    {
        return [
            'promotores' => function ($query) {
                $query->select('id', 'supervisor_id', 'nombre', 'apellido_p', 'apellido_m', 'venta_maxima', 'venta_proyectada_objetivo', 'dias_de_pago')
                    ->with(['clientes' => function ($clienteQuery) {
                        $clienteQuery->select('id', 'promotor_id');
                    }])
                    ->orderBy('nombre');
            },
        ];
    }

    private function buildVentaSupervisorMetrics(Collection $supervisores): array
    {
        $supervisores = $supervisores instanceof Collection ? $supervisores : collect($supervisores);

        if ($supervisores->isEmpty()) {
            $totals = [
                'debeOperativo' => 0.0,
                'debeProyectado' => 0.0,
                'ventaRegistrada' => 0.0,
                'cobranza' => 0.0,
                'falla' => 0.0,
                'fallaPct' => 0.0,
                'cobranzaPct' => 0.0,
            ];

            return [collect(), $totals, null];
        }

        $clienteSupervisorMap = [];
        $debeOperativoBySupervisor = [];
        $clienteIds = collect();

        foreach ($supervisores as $supervisor) {
            $promotores = $supervisor->promotores instanceof Collection ? $supervisor->promotores : collect($supervisor->promotores);

            $debeOperativoBySupervisor[$supervisor->id] = (float) $promotores->sum(function ($promotor) {
                return (float) ($promotor->venta_maxima ?? 0);
            });

            $promotores->each(function ($promotor) use ($supervisor, &$clienteSupervisorMap, &$clienteIds) {
                $clientes = $promotor->clientes instanceof Collection ? $promotor->clientes : collect($promotor->clientes);

                $clientes->each(function ($cliente) use ($supervisor, &$clienteSupervisorMap, &$clienteIds) {
                    if ($cliente?->id) {
                        $clienteSupervisorMap[$cliente->id] = $supervisor->id;
                        $clienteIds->push($cliente->id);
                    }
                });
            });
        }

        $clienteIds = $clienteIds->unique()->values();
        $now = Carbon::now();

        $creditos = $clienteIds->isNotEmpty()
            ? Credito::whereIn('cliente_id', $clienteIds)
                ->with([
                    'pagosProyectados' => function ($query) use ($now) {
                        $query->where('fecha_limite', '<=', $now)
                            ->with([
                                'pagosReales.pagoCompleto',
                                'pagosReales.pagoAnticipo',
                                'pagosReales.pagoDiferido',
                            ]);
                    },
                ])
                ->get()
            : collect();

        $creditosBySupervisor = $creditos->groupBy(function (Credito $credito) use ($clienteSupervisorMap) {
            return $clienteSupervisorMap[$credito->cliente_id] ?? null;
        });

        $ventaFecha = null;

        $summaries = $supervisores->map(function (Supervisor $supervisor) use (&$ventaFecha, $debeOperativoBySupervisor, $creditosBySupervisor) {
            $debeOperativo = $debeOperativoBySupervisor[$supervisor->id] ?? 0.0;

            $creditosSupervisor = $creditosBySupervisor->get($supervisor->id, collect());
            $creditosSupervisor = $creditosSupervisor instanceof Collection ? $creditosSupervisor : collect($creditosSupervisor);

            $ventaRegistrada = (float) $creditosSupervisor->sum(function (Credito $credito) {
                return (float) ($credito->monto_total ?? 0);
            });

            $debeProyectado = (float) $creditosSupervisor->sum(function (Credito $credito) {
                return (float) $credito->pagosProyectados->sum(function ($pago) {
                    return (float) ($pago->monto_proyectado ?? 0);
                });
            });

            $cobranza = (float) $creditosSupervisor->sum(function (Credito $credito) {
                return (float) $credito->pagosProyectados->sum(function ($pago) {
                    return (float) $pago->pagosReales->sum(function ($real) {
                        return (float) ($real->monto ?? 0);
                    });
                });
            });

            $falla = max(0.0, $debeProyectado - $cobranza);

            $ultimaFecha = $creditosSupervisor->flatMap(function (Credito $credito) {
                return $credito->pagosProyectados->pluck('fecha_limite');
            })
                ->filter()
                ->map(function ($fecha) {
                    return $fecha instanceof Carbon ? $fecha : Carbon::parse($fecha);
                })
                ->max();

            if ($ultimaFecha && (!$ventaFecha || $ultimaFecha->gt($ventaFecha))) {
                $ventaFecha = $ultimaFecha->copy();
            }

            return [
                'id' => $supervisor->id,
                'nombre' => trim(collect([
                    $supervisor->nombre,
                    $supervisor->apellido_p,
                    $supervisor->apellido_m,
                ])->filter()->implode(' ')),
                'debeOperativo' => round($debeOperativo, 2),
                'debeProyectado' => round($debeProyectado, 2),
                'ventaRegistrada' => round($ventaRegistrada, 2),
                'cobranza' => round($cobranza, 2),
                'falla' => round($falla, 2),
                'cobranzaPct' => $debeProyectado > 0 ? round(($cobranza / $debeProyectado) * 100, 2) : 0.0,
                'fallaPct' => $debeProyectado > 0 ? round(($falla / $debeProyectado) * 100, 2) : 0.0,
                'fecha' => $ultimaFecha ? $ultimaFecha->format('d/m/Y') : null,
                'horario' => null,
            ];
        })->values();

        $totals = [
            'debeOperativo' => round((float) $summaries->sum('debeOperativo'), 2),
            'debeProyectado' => round((float) $summaries->sum('debeProyectado'), 2),
            'ventaRegistrada' => round((float) $summaries->sum('ventaRegistrada'), 2),
            'cobranza' => round((float) $summaries->sum('cobranza'), 2),
            'falla' => round((float) $summaries->sum('falla'), 2),
        ];

        $totals['cobranzaPct'] = $totals['debeProyectado'] > 0
            ? round(($totals['cobranza'] / max(1e-6, $totals['debeProyectado'])) * 100, 2)
            : 0.0;

        $totals['fallaPct'] = $totals['debeProyectado'] > 0
            ? round(($totals['falla'] / max(1e-6, $totals['debeProyectado'])) * 100, 2)
            : 0.0;

        return [$summaries, $totals, $ventaFecha];
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
    
    public function busqueda(Request $request, BusquedaClientesService $busquedaService)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $supervisor = $this->resolveSupervisorContext($request, [
            'promotores' => fn ($query) => $query->select('id', 'supervisor_id'),
        ]);

        $busqueda = $busquedaService->buscar($request, $supervisor);

        $supervisores = $this->buildSupervisorOptionsForBusqueda($request, $primaryRole);
        $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);

        return view('mobile.ejecutivo.busqueda.busqueda', array_merge($busqueda, [
            'role' => $primaryRole,
            'supervisores' => $supervisores,
            'supervisorContextQuery' => $supervisorContextQuery,
        ]));
    }
    
    public function informes()
    {
        return view('mobile.ejecutivo.informes.informes');
    }
    
    public function reportes()
    {
        return view('mobile.ejecutivo.informes.reportes');
    }

    private function buildSupervisorOptionsForBusqueda(Request $request, string $primaryRole): Collection
    {
        $query = Supervisor::query()
            ->select('id', 'nombre', 'apellido_p', 'apellido_m', 'ejecutivo_id')
            ->orderBy('nombre')
            ->orderBy('apellido_p')
            ->orderBy('apellido_m');

        if ($primaryRole === 'ejecutivo') {
            $ejecutivo = Ejecutivo::firstWhere('user_id', $request->user()?->id);
            abort_if(!$ejecutivo, 403, 'Perfil de ejecutivo no configurado.');

            $query->where('ejecutivo_id', $ejecutivo->id);
        }

        return $query->get()->map(function (Supervisor $supervisor) {
            return [
                'id' => $supervisor->id,
                'nombre' => collect([
                    $supervisor->nombre,
                    $supervisor->apellido_p,
                    $supervisor->apellido_m,
                ])->filter()->implode(' '),
            ];
        });
    }

    /*
     * -----------------------------------------------------------------
     * Faltan metodos para Cartera Activa, Falla Actual, Cartera Vencida, Cartera Inactiva
     * -----------------------------------------------------------------
     */
    
}
