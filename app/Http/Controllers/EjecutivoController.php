<?php
namespace App\Http\Controllers;

use App\Enums\ClienteEstado;
use App\Http\Controllers\Concerns\HandlesSupervisorContext;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Ejecutivo;
use App\Models\Ejercicio;
use App\Models\PagoCompleto;
use App\Models\PagoDiferido;
use App\Models\PagoProyectado;
use App\Models\PagoReal;
use App\Models\Promotor;
use App\Models\Supervisor;
use App\Services\BusquedaClientesService;
use App\Services\Reportes\ReporteDesembolsoDataService;
use App\Support\RoleHierarchy;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EjecutivoController extends Controller
{
    use HandlesSupervisorContext;
    /*
     * -----------------------------------------------------------------
     * MÃ©todos administrativos
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
     * MÃ©todos para vista mobile
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

    public function cartera_activa(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $this->resolveEjecutivoContext($primaryRole, $user?->id, (int) $request->query('ejecutivo_id'));

        $supervisor = $this->resolveSupervisorContext($request);

        if (!$supervisor) {
            $message = $primaryRole === 'ejecutivo'
                ? 'Perfil de ejecutivo sin supervisores configurados.'
                : 'Supervisor fuera de tu alcance.';

            abort(403, $message);
        }

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m', 'dias_de_pago')
            ->orderBy('nombre')
            ->orderBy('apellido_p')
            ->orderBy('apellido_m')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function (Promotor $promotor) {
            $clientes = Cliente::where('promotor_id', $promotor->id)
                ->whereHas('credito', fn ($query) => $query->whereIn('estado', FiltrosController::CREDIT_ACTIVE_STATES))
                ->with([
                    'credito.pagosProyectados.pagosReales.pagoCompleto',
                    'credito.pagosProyectados.pagosReales.pagoAnticipo',
                    'credito.pagosProyectados.pagosReales.pagoDiferido',
                ])
                ->get();

            $dinero = 0.0;

            $items = $clientes->map(function (Cliente $cliente) use (&$dinero) {
                $credito = $cliente->credito;

                if (!$credito) {
                    return null;
                }

                $dinero += (float) ($credito->monto_total ?? 0);

                $pagos = $credito->pagosProyectados ?? collect();
                $pagos = $pagos instanceof Collection ? $pagos : collect($pagos);

                $totalWeeks = $pagos->count();
                $fechaInicio = $credito->fecha_inicio ? Carbon::parse($credito->fecha_inicio) : null;
                $currentWeek = ($totalWeeks > 0 && $fechaInicio)
                    ? min(now()->diffInWeeks($fechaInicio) + 1, $totalWeeks)
                    : 0;

                $pago = $pagos->firstWhere('semana', $currentWeek);
                $pagoSemanal = (float) ($pago->monto_proyectado ?? 0);
                $status = '!';

                if ($pago) {
                    $fechaLimite = $pago->fecha_limite ? Carbon::parse($pago->fecha_limite) : null;
                    $primerPago = $pago->pagosReales instanceof Collection
                        ? $pago->pagosReales->sortBy('fecha_pago')->first()
                        : null;

                    if ($primerPago && $fechaLimite) {
                        $fechaPago = Carbon::parse($primerPago->fecha_pago);

                        if ($fechaPago->lt($fechaLimite)) {
                            $status = 'Ad';
                        } elseif ($fechaPago->equalTo($fechaLimite)) {
                            $status = 'V';
                        } else {
                            $status = 'F';
                        }
                    } elseif ($fechaLimite) {
                        $status = $fechaLimite->isPast() ? 'F' : '!';
                    }
                }

                return [
                    'id' => $cliente->id,
                    'nombre' => trim(collect([
                        $cliente->nombre,
                        $cliente->apellido_p,
                        $cliente->apellido_m,
                    ])->filter()->implode(' ')),
                    'monto' => (float) ($credito->monto_total ?? 0),
                    'semana' => $currentWeek,
                    'pago_semanal' => $pagoSemanal,
                    'status' => $status,
                ];
            })->filter()->values();

            return [
                'nombre' => trim(collect([
                    $promotor->nombre,
                    $promotor->apellido_p,
                    $promotor->apellido_m,
                ])->filter()->implode(' ')),
                'dias_de_pago' => trim((string) ($promotor->dias_de_pago ?? '')),
                'dinero' => $dinero,
                'clientes' => $items,
            ];
        });

        $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);

        return view('mobile.ejecutivo.cartera.cartera_activa', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
            'role' => $primaryRole,
            'supervisorContextQuery' => $supervisorContextQuery,
        ]);
    }

    public function cartera_vencida(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $this->resolveEjecutivoContext($primaryRole, $user?->id, (int) $request->query('ejecutivo_id'));

        $supervisor = $this->resolveSupervisorContext($request);

        if (!$supervisor) {
            $message = $primaryRole === 'ejecutivo'
                ? 'Perfil de ejecutivo sin supervisores configurados.'
                : 'Supervisor fuera de tu alcance.';

            abort(403, $message);
        }

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m', 'dias_de_pago')
            ->orderBy('nombre')
            ->orderBy('apellido_p')
            ->orderBy('apellido_m')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function (Promotor $promotor) {
            $clientes = Cliente::where('promotor_id', $promotor->id)
                ->whereHas('credito', fn ($query) => $query->whereIn('estado', FiltrosController::CREDIT_FAILURE_STATES))
                ->with([
                    'credito.pagosProyectados.pagosReales.pagoCompleto',
                    'credito.pagosProyectados.pagosReales.pagoAnticipo',
                    'credito.pagosProyectados.pagosReales.pagoDiferido',
                ])
                ->get();

            $items = collect();
            $dineroVencido = 0.0;
            $baseCreditos = 0.0;

            foreach ($clientes as $cliente) {
                $credito = $cliente->credito;

                if (!$credito) {
                    continue;
                }

                $pagos = $credito->pagosProyectados instanceof Collection
                    ? $credito->pagosProyectados
                    : collect($credito->pagosProyectados);

                $pagado = (float) $pagos->flatMap(function ($pago) {
                    return $pago->pagosReales instanceof Collection
                        ? $pago->pagosReales
                        : collect($pago->pagosReales);
                })->sum(fn ($pago) => (float) ($pago->monto ?? 0));

                $proyectado = (float) $pagos->sum(function ($pago) {
                    return (float) ($pago->monto_proyectado ?? 0);
                });

                $baseCreditos += (float) ($credito->monto_total ?? 0);
                $deficit = max(0.0, $proyectado - $pagado);

                if ($deficit > 0) {
                    $dineroVencido += $deficit;
                    $estatus = $pagado <= 0 ? 'total' : 'parcial';

                    $items->push([
                        'id' => $cliente->id,
                        'nombre' => trim(collect([
                            $cliente->nombre,
                            $cliente->apellido_p,
                            $cliente->apellido_m,
                        ])->filter()->implode(' ')),
                        'monto' => $deficit,
                        'estatus' => $estatus,
                    ]);
                }
            }

            $porcentajeVencido = $baseCreditos > 0
                ? round(($dineroVencido / max(1e-6, $baseCreditos)) * 100)
                : 0;

            return [
                'nombre' => trim(collect([
                    $promotor->nombre,
                    $promotor->apellido_p,
                    $promotor->apellido_m,
                ])->filter()->implode(' ')),
                'dias_de_pago' => trim((string) ($promotor->dias_de_pago ?? '')),
                'dinero' => $dineroVencido,
                'vencido' => $porcentajeVencido,
                'clientes' => $items->values(),
            ];
        });

        $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);

        return view('mobile.ejecutivo.cartera.cartera_vencida', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
            'role' => $primaryRole,
            'supervisorContextQuery' => $supervisorContextQuery,
        ]);
    }

    public function cartera_inactiva(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $this->resolveEjecutivoContext($primaryRole, $user?->id, (int) $request->query('ejecutivo_id'));

        $supervisor = $this->resolveSupervisorContext($request);

        if (!$supervisor) {
            $message = $primaryRole === 'ejecutivo'
                ? 'Perfil de ejecutivo sin supervisores configurados.'
                : 'Supervisor fuera de tu alcance.';

            abort(403, $message);
        }

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m', 'dias_de_pago')
            ->orderBy('nombre')
            ->orderBy('apellido_p')
            ->orderBy('apellido_m')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function (Promotor $promotor) {
            $clientes = Cliente::where('promotor_id', $promotor->id)
                ->where(function ($query) {
                    $query->where('activo', false)
                        ->orWhereNull('activo')
                        ->orWhereIn('cliente_estado', $this->inactiveClientStates());
                })
                ->with([
                    'credito.pagosProyectados.pagosReales.pagoCompleto',
                    'credito.pagosProyectados.pagosReales.pagoAnticipo',
                    'credito.pagosProyectados.pagosReales.pagoDiferido',
                    'credito.datoContacto',
                ])
                ->get();

            $items = $clientes->map(function (Cliente $cliente) {
                $credito = $cliente->credito;
                $dato = $credito?->datoContacto;

                $pagos = $credito?->pagosProyectados instanceof Collection
                    ? $credito->pagosProyectados
                    : collect($credito?->pagosProyectados);

                $vencidos = $pagos->filter(function ($pago) {
                    if (!$pago->fecha_limite) {
                        return false;
                    }

                    $fecha = $pago->fecha_limite instanceof Carbon
                        ? $pago->fecha_limite
                        : Carbon::parse($pago->fecha_limite);

                    return $fecha->isPast();
                });

                $proyectado = (float) $vencidos->sum(fn ($pago) => (float) ($pago->monto_proyectado ?? 0));
                $pagado = (float) $vencidos->flatMap(function ($pago) {
                    return $pago->pagosReales instanceof Collection
                        ? $pago->pagosReales
                        : collect($pago->pagosReales);
                })->sum(fn ($pago) => (float) ($pago->monto ?? 0));

                $direccion = $dato ? collect([
                    trim(($dato->calle ?? '') . ' ' . ($dato->numero_ext ?? '')),
                    $dato->numero_int ? 'Int. ' . $dato->numero_int : null,
                    $dato->colonia ?? null,
                    $dato->municipio ?? null,
                    $dato->estado ?? null,
                    $dato->cp ? 'CP ' . $dato->cp : null,
                ])->filter()->implode(', ') : null;

                return [
                    'nombre' => trim(collect([
                        $cliente->nombre,
                        $cliente->apellido_p,
                        $cliente->apellido_m,
                    ])->filter()->implode(' ')),
                    'curp' => $cliente->CURP,
                    'fecha_nac' => $cliente->fecha_nacimiento
                        ? Carbon::parse($cliente->fecha_nacimiento)->format('Y-m-d')
                        : null,
                    'direccion' => $direccion,
                    'ultimo_credito' => $credito?->fecha_inicio
                        ? Carbon::parse($credito->fecha_inicio)->format('Y-m-d')
                        : null,
                    'monto_credito' => $credito?->monto_total ? (float) $credito->monto_total : 0.0,
                    'telefono' => $dato->tel_cel ?? $dato->tel_fijo ?? null,
                    'fallas' => $proyectado > $pagado
                        ? $vencidos->count()
                        : 0,
                ];
            })->values();

            return [
                'nombre' => trim(collect([
                    $promotor->nombre,
                    $promotor->apellido_p,
                    $promotor->apellido_m,
                ])->filter()->implode(' ')),
                'dias_de_pago' => trim((string) ($promotor->dias_de_pago ?? '')),
                'clientes' => $items,
            ];
        });

        $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);

        return view('mobile.ejecutivo.cartera.cartera_inactiva', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
            'role' => $primaryRole,
            'supervisorContextQuery' => $supervisorContextQuery,
        ]);
    }

    public function cartera_falla(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $this->resolveEjecutivoContext($primaryRole, $user?->id, (int) $request->query('ejecutivo_id'));

        $supervisor = $this->resolveSupervisorContext($request);

        if (!$supervisor) {
            $message = $primaryRole === 'ejecutivo'
                ? 'Perfil de ejecutivo sin supervisores configurados.'
                : 'Supervisor fuera de tu alcance.';

            abort(403, $message);
        }

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m', 'dias_de_pago')
            ->orderBy('nombre')
            ->orderBy('apellido_p')
            ->orderBy('apellido_m')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function (Promotor $promotor) {
            $clientes = Cliente::where('promotor_id', $promotor->id)
                ->with([
                    'credito.pagosProyectados.pagosReales.pagoCompleto',
                    'credito.pagosProyectados.pagosReales.pagoAnticipo',
                    'credito.pagosProyectados.pagosReales.pagoDiferido',
                ])
                ->get();

            $items = collect();
            $dineroFalla = 0.0;
            $baseCreditos = 0.0;

            foreach ($clientes as $cliente) {
                $credito = $cliente->credito;

                if (!$credito) {
                    continue;
                }

                $pagos = $credito->pagosProyectados instanceof Collection
                    ? $credito->pagosProyectados
                    : collect($credito->pagosProyectados);

                $pagado = (float) $pagos->flatMap(function ($pago) {
                    return $pago->pagosReales instanceof Collection
                        ? $pago->pagosReales
                        : collect($pago->pagosReales);
                })->sum(fn ($pago) => (float) ($pago->monto ?? 0));

                $proyectado = (float) $pagos->sum(function ($pago) {
                    return (float) ($pago->monto_proyectado ?? 0);
                });

                $baseCreditos += (float) ($credito->monto_total ?? 0);
                $deficit = max(0.0, $proyectado - $pagado);

                if ($deficit <= 0) {
                    continue;
                }

                $dineroFalla += $deficit;

                $items->push([
                    'id' => $cliente->id,
                    'nombre' => trim(collect([
                        $cliente->nombre,
                        $cliente->apellido_p,
                        $cliente->apellido_m,
                    ])->filter()->implode(' ')),
                    'monto' => $deficit,
                ]);
            }

            $porcentajeFalla = $baseCreditos > 0
                ? round(($dineroFalla / max(1e-6, $baseCreditos)) * 100)
                : 0;

            return [
                'nombre' => trim(collect([
                    $promotor->nombre,
                    $promotor->apellido_p,
                    $promotor->apellido_m,
                ])->filter()->implode(' ')),
                'dias_de_pago' => trim((string) ($promotor->dias_de_pago ?? '')),
                'dinero' => $dineroFalla,
                'falla' => $porcentajeFalla,
                'clientes' => $items->values(),
            ];
        });

        $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);

        return view('mobile.ejecutivo.cartera.cartera_falla', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
            'role' => $primaryRole,
            'supervisorContextQuery' => $supervisorContextQuery,
        ]);
    }

    public function horarios(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $ejecutivo = $this->resolveEjecutivoContext($primaryRole, $user?->id, (int) $request->query('ejecutivo_id'));

        if ($primaryRole === 'ejecutivo') {
            abort_if(!$ejecutivo, 403, 'Perfil de ejecutivo no configurado.');
        }

        $supervisor = $this->resolveSupervisorContext($request, [
            'promotores' => function ($query) {
                $query->select('id', 'supervisor_id', 'nombre', 'apellido_p', 'apellido_m', 'dias_de_pago', 'venta_maxima', 'venta_proyectada_objetivo')
                    ->orderBy('nombre')
                    ->orderBy('apellido_p')
                    ->orderBy('apellido_m');
            },
        ]);

        $promotores = $supervisor?->promotores ?? collect();
        $promotores = $promotores instanceof Collection ? $promotores : collect($promotores);

        $promotores = $promotores->map(function (Promotor $promotor) {
            $promotor->nombre_completo = trim(collect([
                $promotor->nombre,
                $promotor->apellido_p,
                $promotor->apellido_m,
            ])->filter()->implode(' '));

            return $promotor;
        })->values();

        $ventaFecha = $supervisor?->id
            ? Ejercicio::where('supervisor_id', $supervisor->id)
                ->orderByDesc('fecha_inicio')
                ->value('fecha_inicio')
            : null;

        $ventaFecha = $ventaFecha ? Carbon::parse($ventaFecha) : now();

        $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);

        $definirRoute = Route::has('mobile.supervisor.horarios.definir')
            ? fn ($promotorId) => route('mobile.supervisor.horarios.definir', array_merge($supervisorContextQuery, ['promotor' => $promotorId]))
            : fn ($promotorId = null) => '#';

        return view('mobile.ejecutivo.venta.horarios', [
            'venta_fecha' => $ventaFecha,
            'promotores' => $promotores,
            'definirRoute' => $definirRoute,
            'role' => $primaryRole,
            'supervisorContextQuery' => $supervisorContextQuery,
        ]);
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
     * Calcula los totales bÃ¡sicos de cartera a partir de la lista de supervisores.
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
                $query->where('activo', false)
                    ->orWhereNull('activo')
                    ->orWhereIn('cliente_estado', $this->inactiveClientStates());
            })
            ->count();
        $cartera_inactivaP = $totalClientes > 0
            ? round(($inactivos / max(1, (float) $totalClientes)) * 100, 2)
            : 0.0;

        $cartera_activa = (float) Credito::whereIn('cliente_id', $clienteIds)
            ->whereIn('estado', FiltrosController::CREDIT_ACTIVE_STATES)
            ->sum('monto_total');

        $cartera_vencida = (float) Credito::whereIn('cliente_id', $clienteIds)
            ->whereIn('estado', FiltrosController::CREDIT_FAILURE_STATES)
            ->sum('monto_total');

        $creditos = Credito::whereIn('cliente_id', $clienteIds)
            ->whereIn('estado', FiltrosController::CREDIT_FAILURE_STATES)
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
                $query->select('id', 'supervisor_id', 'nombre', 'apellido_p', 'apellido_m', 'venta_maxima', 'venta_proyectada_objetivo', 'dia_de_pago', 'hora_de_pago')
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
                ->whereIn('estado', FiltrosController::CREDIT_ACTIVE_STATES)
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

    public function cliente_historial(Request $request, Cliente $cliente)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $ejecutivo = $this->resolveEjecutivoContext($primaryRole, $user?->id, (int) $request->query('ejecutivo_id'));

        if ($primaryRole === 'ejecutivo') {
            abort_if(!$ejecutivo, 403, 'Perfil de ejecutivo no configurado.');
        }

        $cliente->load([
            'promotor.supervisor',
            'credito.pagosProyectados.pagosReales',
            'credito.garantias',
            'credito.avales.documentos',
            'credito.datoContacto',
            'documentos',
        ]);

        $promotor = $cliente->promotor;
        $supervisor = $promotor?->supervisor;

        abort_unless($supervisor, 404, 'Cliente sin supervisor asignado.');

        if ($primaryRole === 'ejecutivo') {
            abort_if($supervisor->ejecutivo_id !== $ejecutivo?->id, 403, 'Cliente fuera de tu alcance.');
        } elseif ($primaryRole === 'supervisor') {
            abort_if($supervisor->user_id !== $user?->id, 403, 'Cliente fuera de tu alcance.');
        } elseif (!in_array($primaryRole, ['administrativo', 'superadmin'], true)) {
            abort(403, 'Cliente fuera de tu alcance.');
        }

        $request->session()->put('mobile.supervisor_context', $supervisor->id);
        $this->shareSupervisorContext($request, $supervisor);

        $credito = $cliente->credito;

        abort_unless($credito, 404, 'El cliente no cuenta con crédito activo.');

        $pagosProyectados = $credito->pagosProyectados instanceof Collection
            ? $credito->pagosProyectados
            : collect($credito->pagosProyectados ?? []);

        $totalWeeks = $pagosProyectados->count();
        $fechaCredito = $credito->fecha_inicio ? Carbon::parse($credito->fecha_inicio) : null;

        $currentWeek = 0;
        if ($totalWeeks > 0 && $fechaCredito) {
            $currentWeek = min(now()->diffInWeeks($fechaCredito) + 1, $totalWeeks);
        }

        $semanas = $pagosProyectados
            ->sortBy('semana')
            ->map(function ($pago) {
                $fechaLimite = $pago->fecha_limite ? Carbon::parse($pago->fecha_limite) : null;
                $primerPago = $pago->pagosReales instanceof Collection
                    ? $pago->pagosReales->sortBy('fecha_pago')->first()
                    : null;

                if ($primerPago && $fechaLimite) {
                    $fechaPago = Carbon::parse($primerPago->fecha_pago);

                    if ($fechaPago->lt($fechaLimite)) {
                        $estado = 'Adelantado';
                    } elseif ($fechaPago->gt($fechaLimite)) {
                        $estado = 'Atrasado';
                    } else {
                        $estado = 'Pagado';
                    }
                } elseif ($fechaLimite) {
                    $estado = $fechaLimite->isPast() ? 'Atrasado' : 'Por pagar';
                } else {
                    $estado = 'Sin fecha';
                }

                return [
                    'semana' => $pago->semana,
                    'monto' => (float) ($pago->monto_proyectado ?? 0),
                    'estado' => $estado,
                ];
            })
            ->values();

        $datoContacto = $credito->datoContacto;
        $clienteDireccion = $datoContacto
            ? collect([
                trim(($datoContacto->calle ?? '') . ' ' . ($datoContacto->numero_ext ?? '')),
                $datoContacto->numero_int ? 'Int. ' . $datoContacto->numero_int : null,
                $datoContacto->colonia,
                $datoContacto->municipio,
                $datoContacto->estado,
                $datoContacto->cp ? 'CP ' . $datoContacto->cp : null,
            ])->filter()->implode(', ')
            : null;

        $clienteTelefonos = $datoContacto
            ? collect([$datoContacto->tel_cel, $datoContacto->tel_fijo])->filter()->unique()->values()
            : collect();

        $garantias = $credito->garantias instanceof Collection ? $credito->garantias : collect($credito->garantias ?? []);

        $garantiasCliente = $garantias
            ->filter(fn ($garantia) => Str::lower((string) $garantia->propietario) === 'cliente')
            ->map(function ($garantia) {
                $descripcion = collect([
                    $garantia->tipo,
                    $garantia->marca,
                    $garantia->modelo,
                    $garantia->num_serie,
                ])->filter()->implode(' - ');

                return [
                    'descripcion' => $descripcion !== '' ? $descripcion : ($garantia->tipo ?? 'Garantia'),
                    'monto' => (float) ($garantia->monto_garantizado ?? 0),
                    'foto_url' => $garantia->foto_url,
                ];
            })
            ->values();

        $garantiasAval = $garantias
            ->filter(fn ($garantia) => Str::lower((string) $garantia->propietario) === 'aval')
            ->map(function ($garantia) {
                $descripcion = collect([
                    $garantia->tipo,
                    $garantia->marca,
                    $garantia->modelo,
                    $garantia->num_serie,
                ])->filter()->implode(' - ');

                return [
                    'descripcion' => $descripcion !== '' ? $descripcion : ($garantia->tipo ?? 'Garantia'),
                    'monto' => (float) ($garantia->monto_garantizado ?? 0),
                    'foto_url' => $garantia->foto_url,
                ];
            })
            ->values();

        $documentosCliente = $cliente->documentos instanceof Collection
            ? $cliente->documentos
            : collect($cliente->documentos ?? []);

        $documentosCliente = $documentosCliente
            ->map(fn ($documento) => [
                'titulo' => (string) Str::of($documento->tipo_doc ?? 'documento')->replace('_', ' ')->title(),
                'url' => $documento->url_s3,
            ])
            ->values();

        $avales = $credito->avales instanceof Collection ? $credito->avales : collect($credito->avales ?? []);
        $aval = $avales->first();

        $documentosAval = $avales
            ->flatMap(function ($aval) {
                $avalNombre = collect([$aval->nombre, $aval->apellido_p, $aval->apellido_m])->filter()->implode(' ');

                $documentos = $aval->documentos instanceof Collection
                    ? $aval->documentos
                    : collect($aval->documentos ?? []);

                return $documentos->map(function ($documento) use ($avalNombre) {
                    $tituloDocumento = (string) Str::of($documento->tipo_doc ?? 'documento')->replace('_', ' ')->title();

                    return [
                        'titulo' => $avalNombre
                            ? trim($avalNombre . ' - ' . $tituloDocumento)
                            : $tituloDocumento,
                        'url' => $documento->url_s3,
                    ];
                });
            })
            ->values();

        $avalTelefonos = $aval
            ? collect([$aval->telefono])->filter()->unique()->values()
            : collect();

        $clienteNombre = collect([$cliente->nombre, $cliente->apellido_p, $cliente->apellido_m])->filter()->implode(' ');
        $promotorNombre = $promotor
            ? collect([$promotor->nombre, $promotor->apellido_p, $promotor->apellido_m])->filter()->implode(' ')
            : '';
        $supervisorNombre = collect([$supervisor->nombre, $supervisor->apellido_p, $supervisor->apellido_m])->filter()->implode(' ');
        $avalNombre = $aval
            ? collect([$aval->nombre, $aval->apellido_p, $aval->apellido_m])->filter()->implode(' ')
            : '';

        $avalDireccion = $aval?->direccion;

        $fechaCreditoTexto = $fechaCredito
            ? $fechaCredito->clone()->locale('es')->translatedFormat('j \\de F \\de Y')
            : null;

        $montoCredito = (float) ($credito->monto_total ?? 0);

        return view('mobile.ejecutivo.cartera.cliente_historial', compact(
            'clienteNombre',
            'supervisorNombre',
            'promotorNombre',
            'totalWeeks',
            'currentWeek',
            'fechaCreditoTexto',
            'montoCredito',
            'clienteDireccion',
            'clienteTelefonos',
            'garantiasCliente',
            'documentosCliente',
            'avalNombre',
            'avalDireccion',
            'avalTelefonos',
            'garantiasAval',
            'documentosAval',
            'semanas'
        ));
    }
public function venta_supervisor()
    {
        return view('mobile.ejecutivo.venta.venta_supervisor');
    }
    
    public function desembolso(
        Request $request,
        ReporteDesembolsoDataService $dataService,
    ) {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $ejecutivo = $this->resolveEjecutivoContext(
            $primaryRole,
            $user?->id,
            (int) $request->query('ejecutivo_id'),
        );

        if ($primaryRole === 'ejecutivo') {
            abort_if(!$ejecutivo, 403, 'Perfil de ejecutivo no configurado.');
        }

        $supervisor = $this->resolveSupervisorContext($request, [
            'promotores' => fn ($query) => $query
                ->select('id', 'supervisor_id', 'nombre', 'apellido_p', 'apellido_m')
                ->orderBy('nombre')
                ->orderBy('apellido_p')
                ->orderBy('apellido_m'),
        ]);

        $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);
        $promotorSessionKey = 'mobile.reportes.desembolso.promotor';
        $promotorSeleccionado = null;
        $promotoresCollection = collect($supervisor?->promotores ?? []);

        $nombreCompleto = static function ($model): string {
            return collect([
                data_get($model, 'nombre'),
                data_get($model, 'apellido_p'),
                data_get($model, 'apellido_m'),
            ])->filter()->implode(' ');
        };

        $promotorIdQuery = $request->query('promotor');
        if ($promotorIdQuery !== null) {
            $promotorId = (int) $promotorIdQuery;

            if ($promotorId > 0) {
                $promotorSeleccionado = $this->loadPromotorForDesembolso($promotorId);
                abort_if(!$promotorSeleccionado, 404, 'Promotor no encontrado.');

                $this->ensurePromotorBelongsToContext($supervisor, $promotorSeleccionado, $primaryRole ?? '');

                $request->session()->put($promotorSessionKey, $promotorSeleccionado->id);
            } else {
                $request->session()->forget($promotorSessionKey);
            }
        }

        if (!$promotorSeleccionado) {
            $storedPromotorId = (int) $request->session()->get($promotorSessionKey);

            if ($storedPromotorId > 0) {
                $candidate = $this->loadPromotorForDesembolso($storedPromotorId);

                if ($this->promotorDisponibleEnContexto($candidate, $supervisor, $primaryRole)) {
                    $promotorSeleccionado = $candidate;
                } else {
                    $request->session()->forget($promotorSessionKey);
                }
            }
        }

        if (!$promotorSeleccionado && $promotoresCollection->isNotEmpty()) {
            $defaultPromotor = $promotoresCollection->first();

            if ($defaultPromotor) {
                $promotorSeleccionado = $this->loadPromotorForDesembolso($defaultPromotor->id);

                if ($promotorSeleccionado) {
                    $request->session()->put($promotorSessionKey, $promotorSeleccionado->id);
                }
            }
        }

        if ($promotorSeleccionado && !$this->promotorDisponibleEnContexto($promotorSeleccionado, $supervisor, $primaryRole)) {
            $request->session()->forget($promotorSessionKey);
            $promotorSeleccionado = null;
        }

        [$fechaInicio, $fechaFin] = $this->resolveDesembolsoRange();

        $payload = null;
        if ($promotorSeleccionado) {
            $payload = $dataService->build($promotorSeleccionado, $fechaInicio, $fechaFin);
        }

        $promotoresDisponibles = $promotoresCollection
            ->map(function (Promotor $promotor) use ($nombreCompleto) {
                return [
                    'id' => $promotor->id,
                    'nombre' => $nombreCompleto($promotor),
                ];
            })
            ->values();

        return view('mobile.ejecutivo.venta.desembolso', [
            'payload' => $payload,
            'ejecutivo' => $ejecutivo,
            'supervisorSeleccionado' => $supervisor,
            'promotorSeleccionado' => $promotorSeleccionado,
            'promotoresDisponibles' => $promotoresDisponibles,
            'supervisorContextQuery' => $supervisorContextQuery,
            'primaryRole' => $primaryRole,
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
        ]);
    }

    public function getPromotorFailureRate(Request $request, Promotor $promotor)
    {
        $failureRate = $this->calculatePromotorFailure($promotor);
        return response()->json(['failure_rate' => $failureRate]);
    }

    private function calculatePromotorFailure(Promotor $promotor)
    {
        $clientes = Cliente::where('promotor_id', $promotor->id)
            ->with([
                'credito.pagosProyectados.pagosReales',
            ])
            ->get();

        $dineroFalla = 0.0;
        $baseCreditos = 0.0;

        foreach ($clientes as $cliente) {
            $credito = $cliente->credito;

            if (!$credito) {
                continue;
            }

            $pagos = $credito->pagosProyectados instanceof \Illuminate\Support\Collection
                ? $credito->pagosProyectados
                : collect($credito->pagosProyectados);

            $pagado = (float) $pagos->flatMap(function ($pago) {
                return $pago->pagosReales instanceof \Illuminate\Support\Collection
                    ? $pago->pagosReales
                    : collect($pago->pagosReales);
            })->sum(fn ($pagoReal) => (float) ($pagoReal->monto ?? 0));

            $proyectado = (float) $pagos->sum('monto_proyectado');

            $baseCreditos += (float) ($credito->monto_total ?? 0);
            $deficit = max(0.0, $proyectado - $pagado);

            if ($deficit > 0) {
                $dineroFalla += $deficit;
            }
        }

        return $baseCreditos > 0
            ? round(($dineroFalla / max(1e-6, $baseCreditos)) * 100)
            : 0;
    }

    public function registrarFallosRecuperados(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $ejecutivo = $this->resolveEjecutivoContext(
            $primaryRole,
            $user?->id,
            (int) $request->input('ejecutivo_id', $request->query('ejecutivo_id')),
        );

        if ($primaryRole === 'ejecutivo') {
            abort_if(!$ejecutivo, 403, 'Perfil de ejecutivo no configurado.');
        }

        $supervisor = $this->resolveSupervisorContext($request, [
            'promotores' => fn ($query) => $query->select('id', 'supervisor_id'),
        ]);

        $promotorId = (int) $request->input('promotor_id');

        if ($promotorId <= 0) {
            throw ValidationException::withMessages([
                'fallo_id' => 'Selecciona una promotora válida antes de continuar.',
            ]);
        }

        $promotor = $this->loadPromotorForDesembolso($promotorId);

        if (!$promotor) {
            throw ValidationException::withMessages([
                'fallo_id' => 'La promotora seleccionada no está disponible.',
            ]);
        }

        $this->ensurePromotorBelongsToContext($supervisor, $promotor, $primaryRole ?? '');

        $accion = $request->input('accion', 'registrar_pago');

        $rules = [
            'promotor_id' => ['required', 'integer'],
            'fallo_id' => ['required', 'integer'],
            'accion' => ['required', 'in:registrar_pago,confirmar_fallo'],
        ];

        if ($accion === 'registrar_pago') {
            $rules['monto'] = ['required', 'numeric', 'min:0.01'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'monto.min' => 'El monto recuperado debe ser mayor a cero.',
        ]);

        $validated = $validator->validate();

        $falloId = (int) $validated['fallo_id'];

        $pagoProyectado = PagoProyectado::query()
            ->where('id', $falloId)
            ->whereHas('credito.cliente', function ($query) use ($promotor) {
                $query->where('promotor_id', $promotor->id);
            })
            ->with([
                'pagosReales.pagoCompleto',
                'pagosReales.pagoDiferido',
                'pagosReales.pagoAnticipo',
            ])
            ->first();

        if (!$pagoProyectado) {
            throw ValidationException::withMessages([
                'fallo_id' => 'El fallo seleccionado no pertenece a la promotora.',
            ]);
        }

        if ($validated['accion'] === 'confirmar_fallo') {
            return redirect()
                ->route('mobile.ejecutivo.desembolso', $this->buildDesembolsoRedirectParams($request, $promotor))
                ->with('status', 'Fallo confirmado sin registrar pagos.');
        }

        $monto = round((float) $validated['monto'], 2);
        $pendiente = $this->calcularDeudaPendiente($pagoProyectado);

        if ($monto - $pendiente > 0.01) {
            throw ValidationException::withMessages([
                'monto' => 'El monto recuperado excede el fallo pendiente.',
            ]);
        }

        $fechaPago = Carbon::now()->toDateString();

        DB::transaction(function () use ($falloId, $promotor, $monto, $fechaPago) {
            $pagoProyectado = PagoProyectado::query()
                ->where('id', $falloId)
                ->whereHas('credito.cliente', function ($query) use ($promotor) {
                    $query->where('promotor_id', $promotor->id);
                })
                ->with([
                    'pagosReales.pagoCompleto',
                    'pagosReales.pagoDiferido',
                    'pagosReales.pagoAnticipo',
                ])
                ->lockForUpdate()
                ->first();

            if (!$pagoProyectado) {
                throw ValidationException::withMessages([
                    'fallo_id' => 'El fallo seleccionado ya no está disponible.',
                ]);
            }

            $pendiente = $this->calcularDeudaPendiente($pagoProyectado);

            if ($pendiente <= 0) {
                throw ValidationException::withMessages([
                    'monto' => 'El fallo ya no tiene monto pendiente por recuperar.',
                ]);
            }

            $montoARegistrar = min($monto, $pendiente);

            if ($montoARegistrar <= 0) {
                throw ValidationException::withMessages([
                    'monto' => 'El monto a registrar debe ser mayor a cero.',
                ]);
            }

            $pagoReal = PagoReal::create([
                'pago_proyectado_id' => $pagoProyectado->id,
                'tipo' => 'recuperado_en_desembolso',
                'fecha_pago' => $fechaPago,
                'comentario' => 'Pago registrado durante el desembolso.',
            ]);

            PagoCompleto::create([
                'pago_real_id' => $pagoReal->id,
                'monto_completo' => $montoARegistrar,
            ]);
        });

        $message = 'Pago de fallo registrado correctamente.';

        // Comentario: si la petición proviene del flujo asíncrono enviamos una respuesta JSON para el modal.
        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
            ], 201);
        }

        return redirect()
            ->route('mobile.ejecutivo.desembolso', $this->buildDesembolsoRedirectParams($request, $promotor))
            ->with('status', $message);
    }
    
    public function busqueda(Request $request, BusquedaClientesService $busquedaService)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $supervisor = null;
        $promotoresContext = null;
        $supervisores = collect();
        $supervisorContextQuery = [];

        if ($primaryRole === 'ejecutivo') {
            $ejecutivo = $this->resolveEjecutivoContext($primaryRole, $user?->id, (int) $request->query('ejecutivo_id'));
            abort_if(!$ejecutivo, 403, 'Perfil de ejecutivo no configurado.');

            $supervisoresAsignados = $ejecutivo->supervisors()
                ->select('id', 'nombre', 'apellido_p', 'apellido_m')
                ->with([
                    'promotores' => fn ($query) => $query->select('id', 'supervisor_id'),
                ])
                ->orderBy('nombre')
                ->orderBy('apellido_p')
                ->orderBy('apellido_m')
                ->get();

            $promotoresContext = $supervisoresAsignados
                ->flatMap(fn (Supervisor $assignedSupervisor) => collect($assignedSupervisor->promotores ?? []))
                ->filter()
                ->unique(fn ($promotor) => data_get($promotor, 'id'))
                ->values();

            $supervisores = $supervisoresAsignados->map(function (Supervisor $assignedSupervisor) {
                return [
                    'id' => $assignedSupervisor->id,
                    'nombre' => collect([
                        $assignedSupervisor->nombre,
                        $assignedSupervisor->apellido_p,
                        $assignedSupervisor->apellido_m,
                    ])->filter()->implode(' '),
                ];
            });

            $request->session()->forget('mobile.supervisor_context');

            $request->attributes->set('acting_supervisor_id', null);
            $request->attributes->set('acting_supervisor', null);
            $request->attributes->set('supervisor_context_query', $supervisorContextQuery);

            view()->share([
                'actingSupervisorId' => null,
                'actingSupervisor' => null,
                'supervisorContextQuery' => $supervisorContextQuery,
            ]);
        } else {
            $supervisor = $this->resolveSupervisorContext($request, [
                'promotores' => fn ($query) => $query->select('id', 'supervisor_id'),
            ]);

            $supervisores = $this->buildSupervisorOptionsForBusqueda($request, $primaryRole);
            $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);
        }

        $busqueda = $busquedaService->buscar($request, $supervisor, $promotoresContext);

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

    protected function loadPromotorForDesembolso(int $promotorId): ?Promotor
    {
        return Promotor::query()
            ->with(['supervisor.ejecutivo'])
            ->find($promotorId);
    }

    protected function buildDesembolsoRedirectParams(Request $request, Promotor $promotor): array
    {
        $params = [
            'promotor' => $promotor->id,
        ];

        $ejecutivoId = $request->input('ejecutivo_id', $request->query('ejecutivo_id'));

        if ($ejecutivoId) {
            $params['ejecutivo_id'] = $ejecutivoId;
        }

        $supervisorId = $request->input('supervisor', $request->query('supervisor'));

        if ($supervisorId) {
            $params['supervisor'] = $supervisorId;
        }

        return $params;
    }

    protected function promotorDisponibleEnContexto(
        ?Promotor $promotor,
        ?Supervisor $supervisor,
        ?string $primaryRole
    ): bool {
        if (!$promotor) {
            return false;
        }

        if ($supervisor) {
            return $promotor->supervisor_id === $supervisor->id;
        }

        return in_array($primaryRole, ['administrativo', 'superadmin'], true);
    }

    protected function resolveDesembolsoRange(): array
    {
        $today = CarbonImmutable::now()->endOfDay();
        $start = $today->previous(CarbonInterface::SATURDAY)->startOfDay();

        return [$start, $today];
    }

    protected function calcularDeudaPendiente(PagoProyectado $pagoProyectado): float
    {
        $total = (float) ($pagoProyectado->deuda_total ?? $pagoProyectado->monto_proyectado ?? 0);
        $pagado = 0.0;

        $pagoProyectado->loadMissing([
            'pagosReales.pagoCompleto',
            'pagosReales.pagoDiferido',
            'pagosReales.pagoAnticipo',
        ]);

        foreach ($pagoProyectado->pagosReales as $pagoReal) {
            if ($pagoReal->pagoCompleto) {
                $pagado += (float) ($pagoReal->pagoCompleto->monto_completo ?? 0);
            }

            if ($pagoReal->pagoDiferido) {
                $pagado += (float) ($pagoReal->pagoDiferido->monto_diferido ?? 0);
            }

            if ($pagoReal->pagoAnticipo) {
                $pagado += (float) ($pagoReal->pagoAnticipo->monto_anticipo ?? 0);
            }
        }

        return max(round($total - $pagado, 2), 0);
    }

    public function registrarPagoDesembolso(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $ejecutivo = $this->resolveEjecutivoContext(
            $primaryRole,
            $user?->id,
            (int) $request->input('ejecutivo_id', $request->query('ejecutivo_id')),
        );

        if ($primaryRole === 'ejecutivo') {
            abort_if(!$ejecutivo, 403, 'Perfil de ejecutivo no configurado.');
        }

        $supervisor = $this->resolveSupervisorContext($request, [
            'promotores' => fn ($query) => $query->select('id', 'supervisor_id'),
        ]);

        $promotorId = (int) $request->input('promotor_id');

        if ($promotorId <= 0) {
            throw ValidationException::withMessages([
                'promotor_id' => 'Selecciona una promotora válida antes de registrar pagos.',
            ]);
        }

        $promotor = $this->loadPromotorForDesembolso($promotorId);

        if (!$promotor) {
            throw ValidationException::withMessages([
                'promotor_id' => 'La promotora seleccionada no está disponible.',
            ]);
        }

        $this->ensurePromotorBelongsToContext($supervisor, $promotor, $primaryRole ?? '');

        $validator = Validator::make($request->all(), [
            'promotor_id' => ['required', 'integer'],
            'credito_id' => ['required', 'integer'],
            'tipo' => ['required', 'string', 'in:completo,diferido'],
            'monto' => ['nullable', 'numeric', 'min:0.01'],
        ], [
            'monto.min' => 'El monto diferido debe ser mayor a cero.',
        ]);

        $validator->after(function ($validator) {
            $data = $validator->getData();
            if (($data['tipo'] ?? '') === 'diferido') {
                $monto = $data['monto'] ?? null;
                if ($monto === null || $monto === '' || (float) $monto <= 0) {
                    $validator->errors()->add('monto', 'Debes capturar el monto diferido a registrar.');
                }
            }
        });

        $validated = $validator->validate();

        $creditoId = (int) $validated['credito_id'];

        $credito = Credito::query()
            ->where('id', $creditoId)
            ->whereHas('cliente', function ($query) use ($promotor) {
                $query->where('promotor_id', $promotor->id);
            })
            ->with([
                'pagosProyectados' => function ($query) {
                    $query->orderBy('fecha_limite')->orderBy('semana');
                },
                'pagosProyectados.pagosReales.pagoCompleto',
                'pagosProyectados.pagosReales.pagoDiferido',
                'pagosProyectados.pagosReales.pagoAnticipo',
            ])
            ->first();

        if (!$credito) {
            throw ValidationException::withMessages([
                'credito_id' => 'El crédito seleccionado no pertenece a la promotora.',
            ]);
        }

        $pagoPendiente = collect($credito->pagosProyectados)
            ->first(function (PagoProyectado $pago) {
                return $this->calcularDeudaPendiente($pago) > 0;
            });

        if (!$pagoPendiente) {
            throw ValidationException::withMessages([
                'credito_id' => 'El crédito seleccionado no tiene pagos pendientes.',
            ]);
        }

        $tipo = $validated['tipo'];
        $montoSolicitado = $tipo === 'diferido'
            ? round((float) ($validated['monto'] ?? 0), 2)
            : null;

        $fechaPago = Carbon::now()->toDateString();

        $resultado = DB::transaction(function () use ($pagoPendiente, $credito, $tipo, $montoSolicitado, $fechaPago) {
            $pagoProyectado = PagoProyectado::query()
                ->where('id', $pagoPendiente->id)
                ->where('credito_id', $credito->id)
                ->with([
                    'pagosReales.pagoCompleto',
                    'pagosReales.pagoDiferido',
                    'pagosReales.pagoAnticipo',
                ])
                ->lockForUpdate()
                ->first();

            if (!$pagoProyectado) {
                throw ValidationException::withMessages([
                    'credito_id' => 'El pago seleccionado ya no está disponible.',
                ]);
            }

            $pendiente = $this->calcularDeudaPendiente($pagoProyectado);

            if ($pendiente <= 0) {
                throw ValidationException::withMessages([
                    'credito_id' => 'El pago proyectado ya no tiene saldo pendiente.',
                ]);
            }

            $montoARegistrar = $tipo === 'completo'
                ? $pendiente
                : min($pendiente, max($montoSolicitado ?? 0, 0));

            if ($montoARegistrar <= 0) {
                throw ValidationException::withMessages([
                    'monto' => 'El monto a registrar debe ser mayor a cero.',
                ]);
            }

            $pagoReal = PagoReal::create([
                'pago_proyectado_id' => $pagoProyectado->id,
                'tipo' => $tipo,
                'fecha_pago' => $fechaPago,
                'comentario' => 'Pago registrado desde desembolsos móviles.',
            ]);

            if ($tipo === 'completo') {
                PagoCompleto::create([
                    'pago_real_id' => $pagoReal->id,
                    'monto_completo' => $montoARegistrar,
                ]);
            } else {
                PagoDiferido::create([
                    'pago_real_id' => $pagoReal->id,
                    'monto_diferido' => $montoARegistrar,
                ]);
            }

            return [
                'pago_real_id' => $pagoReal->id,
                'monto_registrado' => $montoARegistrar,
                'pendiente_restante' => max($pendiente - $montoARegistrar, 0),
            ];
        });

        $mensajeBase = $tipo === 'completo'
            ? 'Se registró un pago completo por '
            : 'Se registró un pago diferido por ';

        $montoTexto = '$' . number_format($resultado['monto_registrado'], 2, '.', ',');
        $message = $mensajeBase . $montoTexto . '.';

        $payloadRespuesta = [
            'message' => $message,
            'pago' => [
                'id' => $resultado['pago_real_id'],
                'tipo' => $tipo,
                'monto' => $resultado['monto_registrado'],
                'pendiente_restante' => $resultado['pendiente_restante'],
            ],
        ];

        if ($request->wantsJson()) {
            return response()->json($payloadRespuesta, 201);
        }

        return redirect()
            ->route('mobile.ejecutivo.desembolso', $this->buildDesembolsoRedirectParams($request, $promotor))
            ->with('status', $message);
    }

    /*
     * -----------------------------------------------------------------
     * Faltan metodos para Cartera Activa, Falla Actual, Cartera Vencida, Cartera Inactiva
     * -----------------------------------------------------------------
     */

    /**
     * @return array<int, string>
     */
    private function inactiveClientStates(): array
    {
        return [
            ClienteEstado::INACTIVO->value,
            ClienteEstado::CANCELADO->value,
        ];
    }
}
