<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Ejecutivo;
use App\Models\Ejercicio;
use App\Models\Promotor;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Concerns\HandlesSupervisorContext;
use App\Services\BusquedaClientesService;
use App\Support\RoleHierarchy;
use App\Services\Recibos\ReciboDesembolsoDataService;

class SupervisorController extends Controller
{
    use HandlesSupervisorContext;
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

    public function objetivo(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);
        $supervisor = $this->resolveSupervisorContext($request);

        if ($primaryRole === 'supervisor') {
            abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');
        }

        $now = now();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();

        $ejercicioInicio = null;
        $ejercicioFin = null;

        if ($supervisor) {
            $ejercicioActual = Ejercicio::where('supervisor_id', $supervisor->id)
                ->orderByDesc('fecha_inicio')
                ->first();

            if ($ejercicioActual?->fecha_inicio) {
                $ejercicioInicio = $ejercicioActual->fecha_inicio instanceof Carbon
                    ? $ejercicioActual->fecha_inicio->copy()
                    : Carbon::parse($ejercicioActual->fecha_inicio);
            }

            if ($ejercicioActual?->fecha_final) {
                $ejercicioFin = $ejercicioActual->fecha_final instanceof Carbon
                    ? $ejercicioActual->fecha_final->copy()
                    : Carbon::parse($ejercicioActual->fecha_final);
            }
        }

        if (!$ejercicioInicio) {
            $ejercicioInicio = $now->copy()->startOfMonth();
        }

        if (!$ejercicioFin) {
            $ejercicioFin = $now->copy()->endOfMonth();
        }

        $exerciseRangeEnd = $ejercicioFin->copy()->endOfDay();
        $nowEnd = $now->copy()->endOfDay();

        if ($exerciseRangeEnd->greaterThan($nowEnd)) {
            $exerciseRangeEnd = $nowEnd;
        }

        if ($supervisor) {
            $supervisor->load([
                'promotores' => function ($query) use ($ejercicioInicio, $exerciseRangeEnd) {
                    $query->select('id', 'supervisor_id', 'nombre', 'apellido_p', 'apellido_m', 'venta_maxima', 'venta_proyectada_objetivo')
                        ->with([
                            'clientes' => function ($clienteQuery) use ($ejercicioInicio, $exerciseRangeEnd) {
                                $clienteQuery->select('id', 'promotor_id', 'nombre', 'apellido_p', 'apellido_m')
                                    ->with(['creditos' => function ($creditosQuery) use ($ejercicioInicio, $exerciseRangeEnd) {
                                        $creditosQuery->select('id', 'cliente_id', 'monto_total', 'fecha_inicio')
                                            ->whereNotNull('fecha_inicio')
                                            ->whereBetween('fecha_inicio', [
                                                $ejercicioInicio->copy()->startOfDay()->toDateTimeString(),
                                                $exerciseRangeEnd->copy()->toDateTimeString(),
                                            ])
                                            ->orderBy('fecha_inicio');
                                    }]);
                            },
                        ])
                        ->orderBy('nombre')
                        ->orderBy('apellido_p')
                        ->orderBy('apellido_m');
                },
            ]);
        }

        $promotores = $supervisor?->promotores ?? collect();
        $promotores = $promotores instanceof \Illuminate\Support\Collection ? $promotores : collect($promotores);

        $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);

        $promotoresResumen = $promotores->map(function (Promotor $promotor) use ($weekStart, $weekEnd, $supervisorContextQuery) {
            $objetivoSemanal = (float) ($promotor->venta_maxima ?? 0);
            $objetivoEjercicio = (float) ($promotor->venta_proyectada_objetivo ?? 0);

            $clientes = $promotor->clientes ?? collect();
            $clientes = $clientes instanceof \Illuminate\Support\Collection ? $clientes : collect($clientes);

            $creditos = $clientes->flatMap(function ($cliente) {
                $creditosCliente = $cliente->creditos ?? collect();

                return $creditosCliente instanceof \Illuminate\Support\Collection
                    ? $creditosCliente
                    : collect($creditosCliente);
            });

            $ventaSemanal = $creditos->filter(function ($credito) use ($weekStart, $weekEnd) {
                $fecha = $credito->fecha_inicio;

                if (!$fecha) {
                    return false;
                }

                if (!$fecha instanceof Carbon) {
                    $fecha = Carbon::parse($fecha);
                }

                return $fecha->greaterThanOrEqualTo($weekStart)
                    && $fecha->lessThanOrEqualTo($weekEnd);
            })->sum(function ($credito) {
                return (float) ($credito->monto_total ?? 0);
            });

            $ventaEjercicio = $creditos->sum(function ($credito) {
                return (float) ($credito->monto_total ?? 0);
            });

            $faltanteSemanal = max(0.0, $objetivoSemanal - $ventaSemanal);
            $faltanteEjercicio = max(0.0, $objetivoEjercicio - $ventaEjercicio);

            $porcentajeSemanal = $objetivoSemanal > 0
                ? min(100, round(($ventaSemanal / $objetivoSemanal) * 100, 2))
                : 0.0;

            $route = Route::has('mobile.promotor.objetivo')
                ? route('mobile.promotor.objetivo', array_merge($supervisorContextQuery, ['promotor' => $promotor->id]))
                : '#';

            return [
                'id' => $promotor->id,
                'nombre' => trim(collect([
                    $promotor->nombre,
                    $promotor->apellido_p,
                    $promotor->apellido_m,
                ])->filter()->implode(' ')),
                'objetivo' => $objetivoSemanal,
                'objetivo_ejercicio' => $objetivoEjercicio,
                'venta' => $ventaSemanal,
                'venta_ejercicio' => $ventaEjercicio,
                'faltante' => $faltanteSemanal,
                'faltante_ejercicio' => $faltanteEjercicio,
                'porcentaje' => $porcentajeSemanal,
                'route' => $route,
            ];
        })->values();

        $objetivoSemanalTotal = (float) $promotoresResumen->sum('objetivo');
        $ventaSemanalTotal = (float) $promotoresResumen->sum('venta');
        $faltanteSemanalTotal = max(0.0, $objetivoSemanalTotal - $ventaSemanalTotal);
        $porcentajeSemanalTotal = $objetivoSemanalTotal > 0
            ? min(100, round(($ventaSemanalTotal / $objetivoSemanalTotal) * 100, 2))
            : 0.0;

        $objetivoEjercicioTotal = (float) $promotoresResumen->sum('objetivo_ejercicio');
        $ventaEjercicioTotal = (float) $promotoresResumen->sum('venta_ejercicio');
        $faltanteEjercicioTotal = max(0.0, $objetivoEjercicioTotal - $ventaEjercicioTotal);
        $porcentajeEjercicioTotal = $objetivoEjercicioTotal > 0
            ? min(100, round(($ventaEjercicioTotal / $objetivoEjercicioTotal) * 100, 2))
            : 0.0;

        $supervisorNombre = $supervisor
            ? collect([
                $supervisor->nombre,
                $supervisor->apellido_p,
                $supervisor->apellido_m,
            ])->filter()->implode(' ')
            : ($user?->name ?? 'Supervisor');

        return view('mobile.supervisor.objetivo.objetivo', [
            'supervisorNombre' => $supervisorNombre,
            'objetivoSemanalTotal' => $objetivoSemanalTotal,
            'ventaSemanalTotal' => $ventaSemanalTotal,
            'faltanteSemanalTotal' => $faltanteSemanalTotal,
            'porcentajeSemanalTotal' => $porcentajeSemanalTotal,
            'objetivoEjercicioTotal' => $objetivoEjercicioTotal,
            'ventaEjercicioTotal' => $ventaEjercicioTotal,
            'faltanteEjercicioTotal' => $faltanteEjercicioTotal,
            'porcentajeEjercicioTotal' => $porcentajeEjercicioTotal,
            'promotoresResumen' => $promotoresResumen,
        ]);
    }


    public function venta()
    {
        $user = auth()->user();
        $this->resolveSupervisorContext(request());

        $context = $this->resolveSupervisorsForUser($user);
        $supervisores = $context['supervisors'];
        $primaryRole = $context['primaryRole'];

        if ($primaryRole === 'supervisor' && $supervisores->isEmpty()) {
            abort(403, 'Perfil de supervisor no configurado.');
        }

        $supervisores = $supervisores instanceof \Illuminate\Support\Collection ? $supervisores : collect($supervisores);

        $promotores = $supervisores
            ->flatMap(function (Supervisor $supervisor) {
                return $supervisor->promotores ?? collect();
            })
            ->filter()
            ->values();

        $promotorIds = $promotores->pluck('id')->filter()->values();

        $prospectStatuses = ['activo', 'desembolsado', 'regularizado', 'inactivo'];
        $supervisionStatuses = ['moroso', 'desembolsado', 'regularizado'];

        $clientesProspectados = $promotorIds->isEmpty()
            ? 0
            : Cliente::whereIn('promotor_id', $promotorIds)->count();

        $clientesPorSupervisar = $promotorIds->isEmpty()
            ? 0
            : Cliente::whereIn('promotor_id', $promotorIds)
                ->whereIn('cartera_estado', $supervisionStatuses)
                ->count();

        $supervisorIds = $supervisores->pluck('id')->filter()->values();

        $ejercicios = $supervisorIds->isEmpty()
            ? collect()
            : Ejercicio::whereIn('supervisor_id', $supervisorIds)
                ->orderByDesc('fecha_inicio')
                ->get()
                ->unique('supervisor_id');

        $moneyWeeklyNow = (float) $ejercicios->sum(fn ($ejercicio) => $ejercicio->dinero_autorizado ?? 0);
        $moneyWeeklyTarget = (float) $ejercicios->sum(fn ($ejercicio) => $ejercicio->venta_objetivo ?? 0);

        $ultimaFecha = $ejercicios
            ->filter(fn ($ejercicio) => !empty($ejercicio->fecha_final))
            ->map(fn ($ejercicio) => $ejercicio->fecha_final instanceof Carbon ? $ejercicio->fecha_final : Carbon::parse($ejercicio->fecha_final))
            ->max();

        $fechaLimite = $ultimaFecha ? $ultimaFecha->format('d/m/Y') : null;

        $moneyProgress = $moneyWeeklyTarget > 0
            ? min(100, ($moneyWeeklyNow / $moneyWeeklyTarget) * 100)
            : 0;

        $promotoresSupervisados = $promotores->map(function ($promotor) use ($prospectStatuses, $supervisionStatuses) {
            $debe = (float) ($promotor->venta_maxima ?? 0);
            $registrada = (float) ($promotor->venta_proyectada_objetivo ?? 0);
            $falla = max(0, $debe - $registrada);

            $formatNombre = function ($cliente) {
                return trim($cliente->nombre . ' ' . $cliente->apellido_p . ' ' . ($cliente->apellido_m ?? ''));
            };

            $clientes = $promotor->clientes ?? collect();

            $prospectos = $clientes
                ->whereIn('cartera_estado', $prospectStatuses)
                ->map($formatNombre)
                ->values();

            $porSupervisar = $clientes
                ->whereIn('cartera_estado', $supervisionStatuses)
                ->map($formatNombre)
                ->values();

            return [
                'id'              => $promotor->id,
                'nombre'          => trim(($promotor->nombre ?? '') . ' ' . ($promotor->apellido_p ?? '') . ' ' . ($promotor->apellido_m ?? '')),
                'debe'            => $debe,
                'falla'           => $falla,
                'porcentajeFalla' => $debe > 0 ? ($falla / $debe) * 100 : 0,
                'ventaRegistrada' => $registrada,
                'prospectados'    => $prospectos,
                'porSupervisar'   => $porSupervisar,
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

    public function reciboDesembolso(Request $request, Promotor $promotor)
    {
        $supervisor = $this->resolveSupervisorContext($request);
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());

        $this->ensurePromotorBelongsToContext($supervisor, $promotor, $primaryRole);

        $dataService = app(ReciboDesembolsoDataService::class);
        $payload = $dataService->build($promotor);

        return view('mobile.supervisor.venta.recibo_desembolso', $payload);
    }

    public function solicitar_venta()
    {
        return view('mobile.supervisor.venta.solicitar_venta');
    }


    public function horarios(Request $request)
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());

        $supervisor = $this->resolveSupervisorContext($request, [
            'promotores' => function ($query) {
                $query->select('id', 'supervisor_id', 'nombre', 'apellido_p', 'apellido_m', 'dia_de_pago', 'hora_de_pago', 'venta_maxima', 'venta_proyectada_objetivo')
                    ->orderBy('nombre')
                    ->orderBy('apellido_p')
                    ->orderBy('apellido_m');
            },
        ]);

        if ($primaryRole === 'supervisor') {
            abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');
        }

        $promotores = $supervisor?->promotores ?? collect();
        $promotores = $promotores instanceof \Illuminate\Support\Collection ? $promotores : collect($promotores);

        $promotores = $promotores
            ->map(function (Promotor $promotor) {
                $promotor->nombre_completo = trim(($promotor->nombre ?? '') . ' ' . ($promotor->apellido_p ?? '') . ' ' . ($promotor->apellido_m ?? ''));

                return $promotor;
            })
            ->values();

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

        return view('mobile.supervisor.venta.horarios', [
            'venta_fecha'   => $ventaFecha,
            'promotores'    => $promotores,
            'definirRoute'  => $definirRoute,
        ]);
    }

    public function horariosDefinir(Request $request, Promotor $promotor)
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());
        $supervisor = $this->resolveSupervisorContext($request);

        if ($primaryRole === 'supervisor') {
            abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');
        }

        $this->ensurePromotorBelongsToContext($supervisor, $promotor, $primaryRole);

        $promotor->loadMissing('supervisor');

        return view('mobile.supervisor.venta.horarios_definir', [
            'promotor' => $promotor,
            'diaPago' => trim((string) ($promotor->dia_de_pago ?? '')),
            'horaPago' => $promotor->hora_de_pago,
            'supervisorNombre' => $this->buildFullName($promotor->supervisor, 'Sin supervisor'),
        ]);
    }

    public function horariosActualizar(Request $request, Promotor $promotor)
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());
        $supervisor = $this->resolveSupervisorContext($request);

        if ($primaryRole === 'supervisor') {
            abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');
        }

        $this->ensurePromotorBelongsToContext($supervisor, $promotor, $primaryRole);

        $validated = $request->validate([
            'dia_de_pago' => ['required', 'string', 'max:50'],
            'hora_de_pago' => ['required', 'date_format:H:i'],
        ]);

        $dia = trim((string) ($validated['dia_de_pago'] ?? ''));
        $hora = $validated['hora_de_pago'] ?? null;

        $promotor->forceFill([
            'dia_de_pago' => $dia !== '' ? $dia : null,
            'hora_de_pago' => $hora,
        ])->save();

        $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);

        return redirect()
            ->route(
                'mobile.supervisor.horarios.definir',
                array_merge($supervisorContextQuery, ['promotor' => $promotor->id])
            )
            ->with('status', 'El horario de pago se actualizó correctamente.');
    }

    public function ingresar_cliente()
    {
        return view('mobile.supervisor.venta.ingresar_cliente');
    }

    public function clientes_prospectados(Request $request)
    {
        [, $promotores] = $this->resolveSupervisorPromotoresConClientes($request);

        $nuevoStatuses = ['activo', 'desembolsado', 'regularizado', 'inactivo'];
        $recreditoStatuses = ['moroso'];

        $promotoresData = $promotores->map(function ($promotor) use ($nuevoStatuses, $recreditoStatuses) {
            $clientes = $promotor->clientes ?? collect();

            $mapCliente = function (Cliente $cliente) {
                return $this->mapClienteDetalle($cliente);
            };

            return [
                'id' => $promotor->id,
                'nombre' => trim($promotor->nombre . ' ' . $promotor->apellido_p . ' ' . ($promotor->apellido_m ?? '')),
                'clientes' => $clientes->whereIn('cartera_estado', $nuevoStatuses)->map($mapCliente)->values(),
                'recreditos' => $clientes->whereIn('cartera_estado', $recreditoStatuses)->map($mapCliente)->values(),
            ];
        });

        return view('mobile.supervisor.venta.clientes_prospectados', [
            'promotores' => $promotoresData,
        ]);
    }

    public function clientes_supervisados(Request $request)
    {
        [, $promotores] = $this->resolveSupervisorPromotoresConClientes($request);

        $supervisionStatuses = ['moroso', 'desembolsado', 'regularizado'];
        $recreditoStatuses = ['moroso'];

        $promotoresData = $promotores->map(function ($promotor) use ($supervisionStatuses, $recreditoStatuses) {
            $clientes = $promotor->clientes ?? collect();

            $mapCliente = function (Cliente $cliente) use ($promotor) {
                $data = $this->mapClienteDetalle($cliente);
                $data['promotor'] = trim($promotor->nombre . ' ' . $promotor->apellido_p . ' ' . ($promotor->apellido_m ?? ''));
                return $data;
            };

            return [
                'id' => $promotor->id,
                'nombre' => trim($promotor->nombre . ' ' . $promotor->apellido_p . ' ' . ($promotor->apellido_m ?? '')),
                'clientes' => $clientes->whereIn('cartera_estado', $supervisionStatuses)->map($mapCliente)->values(),
                'recreditos' => $clientes->whereIn('cartera_estado', $recreditoStatuses)->map($mapCliente)->values(),
            ];
        });

        return view('mobile.supervisor.venta.clientes_supervisados', [
            'promotores' => $promotoresData,
        ]);
    }

    public function aprobarProspecto(Cliente $cliente): JsonResponse
    {
        $credito = $cliente->credito;

        if (!$credito) {
            return response()->json(['message' => 'El cliente no tiene un credito asociado.'], 404);
        }

        DB::transaction(function () use ($cliente, $credito) {
            $credito->estado = 'Supervisado';
            $credito->save();

            $cliente->cartera_estado = 'activo';
            $cliente->activo = true;
            $cliente->save();
        });

        $cliente->refresh();
        $credito->refresh();

        return response()->json([
            'message' => 'Cliente supervisado correctamente.',
            'cliente' => [
                'id' => $cliente->id,
                'cartera_estado' => $cliente->cartera_estado,
                'credito_estado' => $credito->estado,
            ],
        ]);
    }

    public function rechazarProspecto(Cliente $cliente): JsonResponse
    {
        $credito = $cliente->credito;

        if (!$credito) {
            return response()->json(['message' => 'El cliente no tiene un credito asociado.'], 404);
        }

        DB::transaction(function () use ($cliente, $credito) {
            $credito->estado = 'Rechazado';
            $credito->save();

            $cliente->cartera_estado = 'inactivo';
            $cliente->activo = false;
            $cliente->save();
        });

        $cliente->refresh();
        $credito->refresh();

        return response()->json([
            'message' => 'Cliente rechazado correctamente.',
            'cliente' => [
                'id' => $cliente->id,
                'cartera_estado' => $cliente->cartera_estado,
                'credito_estado' => $credito->estado,
            ],
        ]);
    }

    public function cartera(Request $request)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);

        $supervisor = $this->resolveSupervisorContext($request, [
            'promotores' => function ($query) {
                $query->select('id', 'supervisor_id', 'nombre', 'apellido_p', 'apellido_m', 'dia_de_pago', 'hora_de_pago')
                    ->orderBy('nombre')
                    ->orderBy('apellido_p')
                    ->orderBy('apellido_m');
            },
        ]);

        if ($primaryRole === 'supervisor') {
            abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');
        }

        $promotores = $supervisor?->promotores ?? collect();
        $promotores = $promotores instanceof \Illuminate\Support\Collection ? $promotores : collect($promotores);
        $promotores = $promotores->values();

        $promotorIds = $promotores->pluck('id')->filter()->values();

        $objetivosPromotores = $promotorIds->isNotEmpty()
            ? Promotor::query()
                ->whereIn('id', $promotorIds)
                ->get(['id', 'venta_maxima', 'venta_proyectada_objetivo'])
                ->keyBy('id')
            : collect();

        $now = now();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();

        $ventasSemanales = $promotorIds->isNotEmpty()
            ? Credito::query()
                ->selectRaw('clientes.promotor_id as promotor_id, SUM(creditos.monto_total) as total')
                ->join('clientes', 'clientes.id', '=', 'creditos.cliente_id')
                ->whereIn('clientes.promotor_id', $promotorIds)
                ->whereNotNull('creditos.fecha_inicio')
                ->whereBetween('creditos.fecha_inicio', [
                    $weekStart->copy()->startOfDay()->toDateTimeString(),
                    $weekEnd->copy()->endOfDay()->toDateTimeString(),
                ])
                ->whereIn('creditos.estado', ['desembolsado', 'activo'])
                ->groupBy('clientes.promotor_id')
                ->pluck('total', 'promotor_id')
            : collect();

        $promotores = $promotores->map(function (Promotor $promotor) use ($ventasSemanales, $objetivosPromotores) {
            $objetivoDatos = $objetivosPromotores->get($promotor->id);
            $objetivoSemanal = (float) ($objetivoDatos->venta_maxima ?? $promotor->venta_maxima ?? 0);
            $objetivoEjercicio = (float) ($objetivoDatos->venta_proyectada_objetivo ?? $promotor->venta_proyectada_objetivo ?? 0);
            $ventaSemanal = (float) ($ventasSemanales[$promotor->id] ?? 0);
            $faltanteSemanal = max(0.0, $objetivoSemanal - $ventaSemanal);
            $porcentajeSemanal = $objetivoSemanal > 0
                ? min(100, round(($ventaSemanal / $objetivoSemanal) * 100, 2))
                : 0.0;

            $promotor->setAttribute('venta_maxima', $objetivoSemanal);
            $promotor->setAttribute('venta_proyectada_objetivo', $objetivoEjercicio);
            $promotor->setAttribute('venta_real_semana', $ventaSemanal);
            $promotor->setAttribute('faltante_semana', $faltanteSemanal);
            $promotor->setAttribute('porcentaje_semana', $porcentajeSemanal);

            return $promotor;
        });

        $clienteIds = $promotorIds->isNotEmpty()
            ? Cliente::whereIn('promotor_id', $promotorIds)->pluck('id')
            : collect();

        $totalClientes = $clienteIds->count();
        $inactivos = $totalClientes > 0
            ? Cliente::whereIn('id', $clienteIds)
                ->where(function ($q) {
                    $q->where('activo', false)->orWhereNull('activo');
                })
                ->count()
            : 0;

        $cartera_inactivaP = $totalClientes > 0
            ? round(($inactivos / max(1, (float) $totalClientes)) * 100, 2)
            : 0.0;

        $cartera_activa = $clienteIds->isNotEmpty()
            ? (float) Credito::whereIn('cliente_id', $clienteIds)
                ->where('estado', 'activo')
                ->sum('monto_total')
            : 0.0;

        $cartera_vencida = $clienteIds->isNotEmpty()
            ? (float) Credito::whereIn('cliente_id', $clienteIds)
                ->where('estado', 'vencido')
                ->sum('monto_total')
            : 0.0;

        $cartera_falla = 0.0;
        if ($clienteIds->isNotEmpty()) {
            $creditos = Credito::whereIn('cliente_id', $clienteIds)
                ->with(['pagosProyectados' => function ($q) {
                    $q->where('fecha_limite', '<', now())
                        ->with(['pagosReales.pagoCompleto', 'pagosReales.pagoAnticipo', 'pagosReales.pagoDiferido']);
                }])
                ->get();

            foreach ($creditos as $cr) {
                foreach ($cr->pagosProyectados as $pp) {
                    $proyectado = (float) $pp->monto_proyectado;
                    $pagado = (float) $pp->pagosReales->sum(function ($pr) {
                        return (float) ($pr->monto ?? 0);
                    });
                    $deficit = max(0, $proyectado - $pagado);
                    $cartera_falla += $deficit;
                }
            }
        }

        $nombre_supervisor = $supervisor
            ? collect([
                $supervisor->nombre,
                $supervisor->apellido_p,
                $supervisor->apellido_m,
            ])->filter()->implode(' ')
            : ($user?->name ?? 'Supervisor');

        return view('mobile.supervisor.cartera.cartera', compact(
            'promotores',
            'cartera_activa',
            'cartera_vencida',
            'cartera_falla',
            'cartera_inactivaP',
            'nombre_supervisor'
        ));
    }

    public function carteraPromotor(Request $request, Promotor $promotor)
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());
        $supervisor = $this->resolveSupervisorContext($request);

        if (!$supervisor) {
            $message = $primaryRole === 'supervisor'
                ? 'Perfil de supervisor no configurado.'
                : 'Supervisor fuera de tu alcance.';

            abort(403, $message);
        }

        abort_unless($promotor->supervisor_id === $supervisor->id, 403, 'Promotor fuera de tu alcance.');

        $clientes = Cliente::where('promotor_id', $promotor->id)
            ->with('credito')
            ->get();

        return view('mobile.supervisor.cartera.promotor', compact('promotor', 'clientes'));
    }

    public function reporte()
    {
        return view('mobile.supervisor.cartera.reporte');
    }

    public function cliente_historial(Request $request, Cliente $cliente)
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());
        $supervisor = $this->resolveSupervisorContext($request);

        if (!$supervisor) {
            $message = $primaryRole === 'supervisor'
                ? 'Perfil de supervisor no configurado.'
                : 'Supervisor fuera de tu alcance.';

            abort(403, $message);
        }

        $cliente->load([
            'promotor.supervisor',
            'credito.pagosProyectados.pagosReales',
            'credito.garantias',
            'credito.avales.documentos',
            'credito.datoContacto',
            'documentos',
        ]);

        abort_unless(optional($cliente->promotor)->supervisor_id === $supervisor->id, 403, 'Cliente fuera de tu alcance.');

        $credito = $cliente->credito;

        abort_unless($credito, 404, 'El cliente no cuenta con crédito activo.');

        $totalWeeks = $credito->pagosProyectados->count();
        $fechaCredito = $credito->fecha_inicio ? Carbon::parse($credito->fecha_inicio) : null;

        $currentWeek = 0;
        if ($totalWeeks > 0 && $fechaCredito) {
            $currentWeek = min(now()->diffInWeeks($fechaCredito) + 1, $totalWeeks);
        }

        $semanas = $credito->pagosProyectados
            ->sortBy('semana')
            ->map(function ($pago) {
                $fechaLimite = Carbon::parse($pago->fecha_limite);
                $primerPago = $pago->pagosReales->sortBy('fecha_pago')->first();

                if ($primerPago) {
                    $fechaPago = Carbon::parse($primerPago->fecha_pago);

                    if ($fechaPago->lt($fechaLimite)) {
                        $estado = 'Adelantado';
                    } elseif ($fechaPago->gt($fechaLimite)) {
                        $estado = 'Atrasado';
                    } else {
                        $estado = 'Pagado';
                    }
                } else {
                    $estado = $fechaLimite->isPast() ? 'Atrasado' : 'Por pagar';
                }

                return [
                    'semana' => $pago->semana,
                    'monto' => (float) $pago->monto_proyectado,
                    'estado' => $estado,
                ];
            })
            ->values();

        $datoContacto = $credito->datoContacto;
        $clienteDireccion = $datoContacto
            ? collect([
                trim($datoContacto->calle . ' ' . $datoContacto->numero_ext),
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

        $garantiasCliente = $credito->garantias
            ->filter(fn ($garantia) => Str::lower((string) $garantia->propietario) === 'cliente')
            ->map(function ($garantia) {
                $descripcion = collect([
                    $garantia->tipo,
                    $garantia->marca,
                    $garantia->modelo,
                    $garantia->num_serie,
                ])->filter()->implode(' - ');

                return [
                    'descripcion' => $descripcion !== '' ? $descripcion : ($garantia->tipo ?? 'Garantía'),
                    'monto' => (float) $garantia->monto_garantizado,
                    'foto_url' => $garantia->foto_url,
                ];
            })
            ->values();

        $garantiasAval = $credito->garantias
            ->filter(fn ($garantia) => Str::lower((string) $garantia->propietario) === 'aval')
            ->map(function ($garantia) {
                $descripcion = collect([
                    $garantia->tipo,
                    $garantia->marca,
                    $garantia->modelo,
                    $garantia->num_serie,
                ])->filter()->implode(' - ');

                return [
                    'descripcion' => $descripcion !== '' ? $descripcion : ($garantia->tipo ?? 'Garantía'),
                    'monto' => (float) $garantia->monto_garantizado,
                    'foto_url' => $garantia->foto_url,
                ];
            })
            ->values();

        $documentosCliente = $cliente->documentos
            ->map(fn ($documento) => [
                'titulo' => (string) Str::of($documento->tipo_doc)->replace('_', ' ')->title(),
                'url' => $documento->url_s3,
            ])
            ->values();

        $documentosAval = $credito->avales
            ->flatMap(function ($aval) {
                $avalNombre = collect([
                    $aval->nombre,
                    $aval->apellido_p,
                    $aval->apellido_m,
                ])->filter()->implode(' ');

                return $aval->documentos->map(function ($documento) use ($avalNombre) {
                    $tituloDocumento = (string) Str::of($documento->tipo_doc)->replace('_', ' ')->title();

                    return [
                        'titulo' => $avalNombre
                            ? trim($avalNombre . ' - ' . $tituloDocumento)
                            : $tituloDocumento,
                        'url' => $documento->url_s3,
                    ];
                });
            })
            ->values();

        $aval = $credito->avales->first();

        $clienteNombre = collect([
            $cliente->nombre,
            $cliente->apellido_p,
            $cliente->apellido_m,
        ])->filter()->implode(' ');

        $promotor = $cliente->promotor;
        $promotorNombre = $promotor
            ? collect([
                $promotor->nombre,
                $promotor->apellido_p,
                $promotor->apellido_m,
            ])->filter()->implode(' ')
            : '';

        $supervisorNombre = collect([
            $supervisor->nombre,
            $supervisor->apellido_p,
            $supervisor->apellido_m,
        ])->filter()->implode(' ');

        $avalNombre = $aval
            ? collect([
                $aval->nombre,
                $aval->apellido_p,
                $aval->apellido_m,
            ])->filter()->implode(' ')
            : '';

        $avalDireccion = $aval?->direccion;
        $avalTelefonos = $aval
            ? collect([$aval->telefono])->filter()->unique()->values()
            : collect();

        $fechaCreditoTexto = $fechaCredito
            ? $fechaCredito->clone()->locale('es')->translatedFormat('j \de F \de Y')
            : null;

        $montoCredito = (float) $credito->monto_total;

        return view('mobile.supervisor.cartera.cliente_historial', compact(
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

    public function cartera_activa(Request $request)
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());
        $supervisor = $this->resolveSupervisorContext($request);

        if (!$supervisor) {
            $message = $primaryRole === 'supervisor'
                ? 'Perfil de supervisor no configurado.'
                : 'Supervisor fuera de tu alcance.';

            abort(403, $message);
        }

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m', 'dia_de_pago', 'hora_de_pago')
            ->orderBy('nombre')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function (Promotor $p) {
            $clientes = Cliente::where('promotor_id', $p->id)
                ->whereHas('credito', fn($q) => $q->where('estado', 'activo'))
                ->with(['credito.pagosProyectados.pagosReales.pagoCompleto', 'credito.pagosProyectados.pagosReales.pagoAnticipo', 'credito.pagosProyectados.pagosReales.pagoDiferido'])
                ->get();

            $dinero = 0.0;
            $items = $clientes->map(function ($c) use (&$dinero) {
                $credito = $c->credito;
                if (!$credito) return null;
                $dinero += (float) $credito->monto_total;

                $totalWeeks  = $credito->pagosProyectados->count();
                $fechaInicio = $credito->fecha_inicio ? Carbon::parse($credito->fecha_inicio) : null;
                $currentWeek = ($totalWeeks > 0 && $fechaInicio)
                    ? min(now()->diffInWeeks($fechaInicio) + 1, $totalWeeks)
                    : 0;

                $pago = $credito->pagosProyectados->firstWhere('semana', $currentWeek);
                $pagoSemanal = (float) ($pago->monto_proyectado ?? 0);
                $status = '!';

                if ($pago) {
                    $fechaLimite = $pago->fecha_limite ? Carbon::parse($pago->fecha_limite) : null;
                    $primerPago = $pago->pagosReales->sortBy('fecha_pago')->first();
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
                    'id'           => $c->id,
                    'nombre'       => trim($c->nombre . ' ' . $c->apellido_p . ' ' . ($c->apellido_m ?? '')),
                    'monto'        => (float) $credito->monto_total,
                    'semana'       => $currentWeek,
                    'pago_semanal' => $pagoSemanal,
                    'status'       => $status,
                ];
            })->filter()->values();

            return [
                'nombre'       => trim($p->nombre . ' ' . $p->apellido_p . ' ' . ($p->apellido_m ?? '')),
                'dia_de_pago'  => trim((string) ($p->dia_de_pago ?? '')),
                'hora_de_pago' => $p->hora_de_pago,
                'horario_pago' => $p->horario_pago_resumen,
                'dinero'       => $dinero,
                'clientes'     => $items,
            ];
        });

        return view('mobile.supervisor.cartera.cartera_activa', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
        ]);
    }

    public function cartera_vencida(Request $request)
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());
        $supervisor = $this->resolveSupervisorContext($request);

        if (!$supervisor) {
            $message = $primaryRole === 'supervisor'
                ? 'Perfil de supervisor no configurado.'
                : 'Supervisor fuera de tu alcance.';

            abort(403, $message);
        }

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m', 'dia_de_pago', 'hora_de_pago')
            ->orderBy('nombre')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function (Promotor $p) {
            $clientes = Cliente::where('promotor_id', $p->id)
                ->with(['credito.pagosProyectados.pagosReales.pagoCompleto', 'credito.pagosProyectados.pagosReales.pagoAnticipo', 'credito.pagosProyectados.pagosReales.pagoDiferido'])
                ->get();

            $items = collect();
            $dineroVencido = 0.0;
            $baseCreditos = 0.0;

            foreach ($clientes as $c) {
                $credito = $c->credito;
                if (!$credito) continue;
                $baseCreditos += (float) $credito->monto_total;

                $vencidos = $credito->pagosProyectados
                    ->filter(fn($pp) => $pp->fecha_limite && Carbon::parse($pp->fecha_limite)->isPast());

                if ($vencidos->isEmpty()) continue;

                $proyectado = (float) $vencidos->sum('monto_proyectado');
                $pagado = (float) $vencidos->flatMap->pagosReales->sum('monto');
                $deficit = max(0, $proyectado - $pagado);

                if ($deficit > 0) {
                    $dineroVencido += $deficit;
                    $estatus = $pagado <= 0 ? 'total' : 'parcial';
                    $items->push([
                        'id'     => $c->id,
                        'nombre' => trim($c->nombre . ' ' . $c->apellido_p . ' ' . ($c->apellido_m ?? '')),
                        'monto'  => $deficit,
                        'estatus'=> $estatus,
                    ]);
                }
            }

            $porcentajeVencido = $baseCreditos > 0 ? round(($dineroVencido / $baseCreditos) * 100) : 0;

            return [
                'nombre'       => trim($p->nombre . ' ' . $p->apellido_p . ' ' . ($p->apellido_m ?? '')),
                'dia_de_pago'  => trim((string) ($p->dia_de_pago ?? '')),
                'hora_de_pago' => $p->hora_de_pago,
                'horario_pago' => $p->horario_pago_resumen,
                'dinero'       => $dineroVencido,
                'vencido'      => $porcentajeVencido,
                'clientes'     => $items->values(),
            ];
        });

        return view('mobile.supervisor.cartera.cartera_vencida', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
        ]);
    }

    public function cartera_inactiva(Request $request)
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());
        $supervisor = $this->resolveSupervisorContext($request);

        if (!$supervisor) {
            $message = $primaryRole === 'supervisor'
                ? 'Perfil de supervisor no configurado.'
                : 'Supervisor fuera de tu alcance.';

            abort(403, $message);
        }

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m', 'dia_de_pago', 'hora_de_pago')
            ->orderBy('nombre')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function (Promotor $p) {
            $clientes = Cliente::where('promotor_id', $p->id)
                ->where(function ($q) { $q->where('activo', false)->orWhereNull('activo'); })
                ->with(['credito.pagosProyectados.pagosReales.pagoCompleto', 'credito.pagosProyectados.pagosReales.pagoAnticipo', 'credito.pagosProyectados.pagosReales.pagoDiferido', 'credito.datoContacto'])
                ->get();

            $items = $clientes->map(function ($c, $idx) {
                $credito = $c->credito; // puede ser null
                $dato = $credito?->datoContacto;
                $vencidos = $credito?->pagosProyectados?->filter(fn($pp) => $pp->fecha_limite && Carbon::parse($pp->fecha_limite)->isPast()) ?? collect();
                $proyectado = (float) $vencidos->sum('monto_proyectado');
                $pagado = (float) $vencidos->flatMap->pagosReales->sum('monto');
                $fallas = $vencidos->count();

                $direccion = $dato ? collect([
                    trim(($dato->calle ?? '') . ' ' . ($dato->numero_ext ?? '')),
                    $dato->numero_int ? 'Int. ' . $dato->numero_int : null,
                    $dato->colonia ?? null,
                    $dato->municipio ?? null,
                    $dato->estado ?? null,
                    $dato->cp ? 'CP ' . $dato->cp : null,
                ])->filter()->implode(', ') : null;

                return [
                    'nombre'         => trim($c->nombre . ' ' . $c->apellido_p . ' ' . ($c->apellido_m ?? '')),
                    'curp'           => $c->CURP,
                    'fecha_nac'      => $c->fecha_nacimiento?->format('Y-m-d'),
                    'direccion'      => $direccion,
                    'ultimo_credito' => $credito?->fecha_inicio ? Carbon::parse($credito->fecha_inicio)->format('Y-m-d') : null,
                    'monto_credito'  => $credito?->monto_total ? (float) $credito->monto_total : 0,
                    'telefono'       => $dato->tel_cel ?? $dato->tel_fijo ?? null,
                    'fallas'         => $fallas,
                ];
            })->values();

            return [
                'nombre'       => trim($p->nombre . ' ' . $p->apellido_p . ' ' . ($p->apellido_m ?? '')),
                'dia_de_pago'  => trim((string) ($p->dia_de_pago ?? '')),
                'hora_de_pago' => $p->hora_de_pago,
                'horario_pago' => $p->horario_pago_resumen,
                'clientes'     => $items,
            ];
        });

        return view('mobile.supervisor.cartera.cartera_inactiva', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
        ]);
    }

    public function cartera_falla(Request $request)
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());
        $supervisor = $this->resolveSupervisorContext($request);

        if (!$supervisor) {
            $message = $primaryRole === 'supervisor'
                ? 'Perfil de supervisor no configurado.'
                : 'Supervisor fuera de tu alcance.';

            abort(403, $message);
        }

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m', 'dia_de_pago', 'hora_de_pago')
            ->orderBy('nombre')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function (Promotor $p) {
            $clientes = Cliente::where('promotor_id', $p->id)
                ->with(['credito.pagosProyectados.pagosReales.pagoCompleto', 'credito.pagosProyectados.pagosReales.pagoAnticipo', 'credito.pagosProyectados.pagosReales.pagoDiferido'])
                ->get();

            $items = collect();
            $dineroFalla = 0.0;
            $baseCreditos = 0.0;

            foreach ($clientes as $c) {
                $credito = $c->credito;
                if (!$credito) continue;
                $baseCreditos += (float) $credito->monto_total;

                $vencidos = $credito->pagosProyectados
                    ->filter(fn($pp) => $pp->fecha_limite && Carbon::parse($pp->fecha_limite)->isPast());
                if ($vencidos->isEmpty()) continue;

                $proyectado = (float) $vencidos->sum('monto_proyectado');
                $pagado = (float) $vencidos->flatMap->pagosReales->sum('monto');
                $deficit = max(0, $proyectado - $pagado);

                if ($deficit > 0) {
                    $dineroFalla += $deficit;
                    $estatus = $pagado <= 0 ? 'total' : 'parcial';
                    $items->push([
                        'id'     => $c->id,
                        'nombre' => trim($c->nombre . ' ' . $c->apellido_p . ' ' . ($c->apellido_m ?? '')),
                        'monto'  => $deficit,
                        'estatus'=> $estatus,
                    ]);
                }
            }

            $porcentajeFalla = $baseCreditos > 0 ? round(($dineroFalla / $baseCreditos) * 100) : 0;

            return [
                'nombre'       => trim($p->nombre . ' ' . $p->apellido_p . ' ' . ($p->apellido_m ?? '')),
                'dia_de_pago'  => trim((string) ($p->dia_de_pago ?? '')),
                'hora_de_pago' => $p->hora_de_pago,
                'horario_pago' => $p->horario_pago_resumen,
                'dinero'       => $dineroFalla,
                'falla'        => $porcentajeFalla,
                'clientes'     => $items->values(),
            ];
        });

        return view('mobile.supervisor.cartera.cartera_falla', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
        ]);
    }

    public function cartera_historial_promotor()
    {
        return view('mobile.supervisor.cartera.historial_promotor');
    }

    public function cartera_reacreditacion()
    {
        return view('mobile.supervisor.cartera.reacreditacion');
    }

    public function busqueda(Request $request, BusquedaClientesService $busquedaService)
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());
        $supervisor = $this->resolveSupervisorContext($request, [
            'promotores' => fn ($query) => $query->select('id', 'supervisor_id'),
        ]);

        if ($primaryRole === 'supervisor') {
            abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');
        }

        $busqueda = $busquedaService->buscar($request, $supervisor);
        $supervisores = $this->buildSupervisorOptionsForBusqueda($request, $primaryRole);
        $supervisorContextQuery = $request->attributes->get('supervisor_context_query', []);

        return view('mobile.supervisor.busqueda.busqueda', array_merge($busqueda, [
            'role' => $primaryRole,
            'supervisores' => $supervisores,
            'supervisorContextQuery' => $supervisorContextQuery,
        ]));
    }

    public function apertura()
    {
        return view('mobile.supervisor.apertura.apertura');
    }

    private function resolveSupervisorPromotoresConClientes(Request $request): array
    {
        $with = [
            'promotores' => function ($query) {
                $query->select('id', 'supervisor_id', 'nombre', 'apellido_p', 'apellido_m', 'venta_maxima', 'venta_proyectada_objetivo', 'dia_de_pago', 'hora_de_pago')
                    ->with([
                        'clientes' => function ($clienteQuery) {
                            $clienteQuery->select('id', 'promotor_id', 'CURP', 'nombre', 'apellido_p', 'apellido_m', 'cartera_estado', 'fecha_nacimiento', 'tiene_credito_activo', 'monto_maximo', 'activo')
                                ->with([
                                    'documentos',
                                    'credito' => function ($creditoQuery) {
                                        $creditoQuery->select('creditos.id', 'creditos.cliente_id', 'creditos.monto_total', 'creditos.estado', 'creditos.periodicidad', 'creditos.fecha_inicio', 'creditos.fecha_final')
                                            ->with([
                                                'datoContacto',
                                                'ocupacion' => function ($ocupacionQuery) {
                                                    $ocupacionQuery->select('id', 'credito_id', 'actividad', 'nombre_empresa', 'calle', 'numero', 'colonia', 'municipio', 'telefono', 'antiguedad', 'monto_percibido', 'periodo_pago')
                                                        ->with(['ingresosAdicionales' => function ($ingresoQuery) {
                                                            $ingresoQuery->select('id', 'ocupacion_id', 'concepto', 'monto', 'frecuencia');
                                                        }]);
                                                },
                                                'informacionFamiliar' => function ($familiarQuery) {
                                                    $familiarQuery->select('id', 'credito_id', 'nombre_conyuge', 'celular_conyuge', 'actividad_conyuge', 'ingresos_semanales_conyuge', 'domicilio_trabajo_conyuge', 'personas_en_domicilio', 'dependientes_economicos', 'conyuge_vive_con_cliente');
                                                },
                                                'garantias' => function ($garantiaQuery) {
                                                    $garantiaQuery->select('id', 'credito_id', 'propietario', 'tipo', 'marca', 'modelo', 'num_serie', 'antiguedad', 'monto_garantizado', 'foto_url');
                                                },
                                                'avales' => function ($avalQuery) {
                                                    $avalQuery->select('id', 'credito_id', 'CURP', 'nombre', 'apellido_p', 'apellido_m', 'telefono', 'direccion');
                                                },
                                            ]);
                                    },
                                ])
                                ->orderBy('nombre');
                        },
                    ])
                    ->orderBy('nombre');
            },
        ];

        $supervisor = $this->resolveSupervisorContext($request, $with);
        $primaryRole = RoleHierarchy::resolvePrimaryRole($request->user());

        if ($primaryRole === 'supervisor') {
            abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');
        }

        if (!$supervisor) {
            return [null, collect()];
        }

        $promotores = $supervisor->promotores ?? collect();
        $promotores = $promotores instanceof \Illuminate\Support\Collection ? $promotores : collect($promotores);

        return [$supervisor, $promotores->values()];
    }

    private function resolveSupervisorsForUser($user): array
    {
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);
        $relations = $this->supervisorPromotoresRelationship();

        if ($primaryRole === 'supervisor') {
            $supervisor = Supervisor::with($relations)->firstWhere('user_id', $user?->id);

            return [
                'primaryRole' => $primaryRole,
                'supervisors' => $supervisor ? collect([$supervisor]) : collect(),
            ];
        }

        if (in_array($primaryRole, ['ejecutivo', 'administrativo', 'superadmin'], true)) {
            $query = Supervisor::query();

            if ($primaryRole === 'ejecutivo') {
                $ejecutivo = Ejecutivo::firstWhere('user_id', $user?->id);

                if (!$ejecutivo) {
                    return [
                        'primaryRole' => $primaryRole,
                        'supervisors' => collect(),
                    ];
                }

                $query->where('ejecutivo_id', $ejecutivo->id);
            }

            $supervisores = $query->with($relations)->get();

            return [
                'primaryRole' => $primaryRole,
                'supervisors' => $supervisores,
            ];
        }

        return [
            'primaryRole' => $primaryRole,
            'supervisors' => collect(),
        ];
    }

    private function supervisorPromotoresRelationship(): array
    {
        return [
            'promotores' => function ($query) {
                $query->select('id', 'supervisor_id', 'nombre', 'apellido_p', 'apellido_m', 'venta_maxima', 'venta_proyectada_objetivo', 'dia_de_pago', 'hora_de_pago')
                    ->with(['clientes' => function ($clienteQuery) {
                        $clienteQuery->select('id', 'promotor_id', 'nombre', 'apellido_p', 'apellido_m', 'cartera_estado', 'tiene_credito_activo')
                            ->orderBy('nombre');
                    }])
                    ->orderBy('nombre');
            },
        ];
    }

    private function mapClienteDetalle(Cliente $cliente): array
    {
        $credito = $cliente->credito;
        $documentosCollection = $cliente->documentos ?? collect();
        $formattedDocsCollection = $documentosCollection->map(function ($documento) {
            $titulo = Str::of($documento->tipo_doc ?? '')
                ->replace('_', ' ')
                ->title();

            return [
                'id' => $documento->id,
                'tipo' => $documento->tipo_doc,
                'titulo' => (string) $titulo,
                'url' => $documento->url_s3,
                'archivo' => $documento->nombre_arch,
            ];
        })->values();

        $findDoc = function (string $needle) use ($formattedDocsCollection) {
            return $formattedDocsCollection->first(function ($doc) use ($needle) {
                return Str::contains(Str::lower($doc['tipo'] ?? ''), $needle);
            });
        };

        $ineDoc = $findDoc('ine');
        $domDoc = $findDoc('domic');

        $dato = $credito?->datoContacto;
        $direccion = $dato ? collect([
            trim(($dato->calle ?? '') . ' ' . ($dato->numero_ext ?? '')),
            $dato->numero_int ? 'Int. ' . $dato->numero_int : null,
            $dato->colonia ?? null,
            $dato->municipio ?? null,
            $dato->estado ?? null,
            $dato->cp ? 'CP ' . $dato->cp : null,
        ])->filter()->implode(', ') : null;

        $aval = $credito?->avales?->last();
        $ocupacion = $credito?->ocupacion;
        $ingresosAdicionales = $ocupacion?->ingresosAdicionales?->map(function ($ingreso) {
            return [
                'concepto' => $ingreso->concepto,
                'monto' => (float) ($ingreso->monto ?? 0),
                'frecuencia' => $ingreso->frecuencia,
            ];
        })->values() ?? collect();
        $informacionFamiliar = $credito?->informacionFamiliar;
        $garantias = $credito?->garantias?->map(function ($garantia) {
            return [
                'propietario' => $garantia->propietario,
                'tipo' => $garantia->tipo,
                'marca' => $garantia->marca,
                'modelo' => $garantia->modelo,
                'num_serie' => $garantia->num_serie,
                'antiguedad' => $garantia->antiguedad,
                'monto_garantizado' => (float) ($garantia->monto_garantizado ?? 0),
                'foto_url' => $garantia->foto_url,
            ];
        })->values() ?? collect();
        $fechaInicioCredito = $credito?->fecha_inicio ? Carbon::parse($credito->fecha_inicio)->format('Y-m-d') : null;
        $fechaFinalCredito = $credito?->fecha_final ? Carbon::parse($credito->fecha_final)->format('Y-m-d') : null;

        return [
            'id' => $cliente->id,
            'nombre' => trim($cliente->nombre . ' ' . $cliente->apellido_p . ' ' . ($cliente->apellido_m ?? '')),
            'nombre_simple' => $cliente->nombre,
            'apellido_p' => $cliente->apellido_p,
            'apellido_m' => $cliente->apellido_m,
            'curp' => $cliente->CURP,
            'cartera_estado' => $cliente->cartera_estado,
            'fecha_nacimiento' => $cliente->fecha_nacimiento?->format('Y-m-d'),
            'tiene_credito_activo' => (bool) $cliente->tiene_credito_activo,
            'activo' => (bool) $cliente->activo,
            'monto_maximo' => (float) ($cliente->monto_maximo ?? 0),
            'monto_credito' => (float) ($credito?->monto_total ?? 0),
            'monto' => (float) ($cliente->monto_maximo ?? $credito?->monto_total ?? 0),
            'telefono' => $dato?->tel_cel ?? $dato?->tel_fijo,
            'direccion' => $direccion,
            'documentos' => [
                'ine' => $ineDoc['url'] ?? null,
                'comprobante' => $domDoc['url'] ?? null,
            ],
            'documentos_detalle' => $formattedDocsCollection->toArray(),
            'contacto' => $dato ? [
                'calle' => $dato->calle,
                'numero_ext' => $dato->numero_ext,
                'numero_int' => $dato->numero_int,
                'monto_mensual' => (float) ($dato->monto_mensual ?? 0),
                'colonia' => $dato->colonia,
                'municipio' => $dato->municipio,
                'estado' => $dato->estado,
                'cp' => $dato->cp,
                'tiempo_en_residencia' => $dato->tiempo_en_residencia,
                'tel_fijo' => $dato->tel_fijo,
                'tel_cel' => $dato->tel_cel,
                'tipo_de_vivienda' => $dato->tipo_de_vivienda,
            ] : null,
            'ocupacion' => $ocupacion ? [
                'actividad' => $ocupacion->actividad,
                'nombre_empresa' => $ocupacion->nombre_empresa,
                'calle' => $ocupacion->calle,
                'numero' => $ocupacion->numero,
                'colonia' => $ocupacion->colonia,
                'municipio' => $ocupacion->municipio,
                'telefono' => $ocupacion->telefono,
                'antiguedad' => $ocupacion->antiguedad,
                'monto_percibido' => (float) ($ocupacion->monto_percibido ?? 0),
                'periodo_pago' => $ocupacion->periodo_pago,
                'tiene_ingresos_adicionales' => $ingresosAdicionales->isNotEmpty(),
                'ingresos_adicionales' => $ingresosAdicionales->toArray(),
            ] : null,
            'familiares' => $informacionFamiliar ? [
                'tiene_conyuge' => ($informacionFamiliar->nombre_conyuge !== '' || $informacionFamiliar->celular_conyuge !== '' || $informacionFamiliar->actividad_conyuge !== ''),
                'nombre_conyuge' => $informacionFamiliar->nombre_conyuge,
                'celular_conyuge' => $informacionFamiliar->celular_conyuge,
                'actividad_conyuge' => $informacionFamiliar->actividad_conyuge,
                'ingresos_semanales_conyuge' => (float) ($informacionFamiliar->ingresos_semanales_conyuge ?? 0),
                'domicilio_trabajo_conyuge' => $informacionFamiliar->domicilio_trabajo_conyuge,
                'personas_en_domicilio' => (int) ($informacionFamiliar->personas_en_domicilio ?? 0),
                'dependientes_economicos' => (int) ($informacionFamiliar->dependientes_economicos ?? 0),
                'conyuge_vive_con_cliente' => (bool) $informacionFamiliar->conyuge_vive_con_cliente,
            ] : null,
            'garantias' => $garantias->toArray(),
            'aval' => $aval ? [
                'nombre' => trim($aval->nombre . ' ' . $aval->apellido_p . ' ' . ($aval->apellido_m ?? '')),
                'curp' => $aval->CURP,
                'telefono' => $aval->telefono ?? null,
                'direccion' => $aval->direccion ?? null,
            ] : null,
            'credito' => [
                'id' => $credito?->id,
                'monto_total' => (float) ($credito?->monto_total ?? 0),
                'estado' => $credito?->estado,
                'periodicidad' => $credito?->periodicidad,
                'fecha_inicio' => $fechaInicioCredito,
                'fecha_final' => $fechaFinalCredito,
            ],
        ];
    }

    private function buildFullName($model, string $default = '—'): string
    {
        if (!$model) {
            return $default;
        }

        $parts = collect([
            data_get($model, 'nombre'),
            data_get($model, 'apellido_p'),
            data_get($model, 'apellido_m'),
        ])->filter(function ($value) {
            return $value !== null && $value !== '';
        });

        return $parts->isNotEmpty() ? $parts->implode(' ') : $default;
    }

    private function extractDocumentPreviews($documents): array
    {
        $documents = $documents instanceof Collection ? $documents : collect($documents);

        $mapDoc = function ($document) {
            return [
                'titulo' => (string) Str::of(data_get($document, 'tipo_doc', 'Documento'))->replace('_', ' ')->title(),
                'url' => data_get($document, 'url_s3'),
                'archivo' => data_get($document, 'nombre_arch'),
            ];
        };

        $filterKeywords = function (array $keywords) use ($documents, $mapDoc) {
            return $documents
                ->filter(function ($document) use ($keywords) {
                    $type = Str::lower((string) data_get($document, 'tipo_doc', ''));
                    foreach ($keywords as $keyword) {
                        if (Str::contains($type, $keyword)) {
                            return true;
                        }
                    }

                    return false;
                })
                ->map($mapDoc)
                ->filter(fn ($doc) => !empty($doc['url']))
                ->values()
                ->all();
        };

        return [
            'ine' => $filterKeywords(['ine', 'identificacion', 'id']),
            'comprobante' => $filterKeywords(['domic', 'comprobante']),
        ];
    }

    private function mapBusquedaCliente(Cliente $cliente, ?Supervisor $contextSupervisor): array
    {
        $promotor = $cliente->promotor;
        $supervisor = $promotor?->supervisor;

        $belongsToContext = $contextSupervisor
            ? ($supervisor?->id === $contextSupervisor->id)
            : true;

        $credito = $cliente->credito;
        $estadoCredito = $credito?->estado ?? null;
        $estadoCreditoTexto = $estadoCredito
            ? (string) Str::of($estadoCredito)->replace('_', ' ')->title()
            : 'Sin crédito';

        $supervisorNombre = $this->buildFullName($supervisor, 'Sin supervisor');

        $response = [
            'id' => $cliente->id,
            'nombre' => $this->buildFullName($cliente, 'Sin nombre'),
            'estatus_credito' => $estadoCreditoTexto,
            'supervisor' => $supervisorNombre,
            'promotor' => $this->buildFullName($promotor, 'Sin promotor'),
            'puede_detallar' => $belongsToContext,
            'detalle' => null,
        ];

        if (!$belongsToContext) {
            return $response;
        }

        $datoContacto = $credito?->datoContacto;
        $telefonosCliente = $datoContacto
            ? collect([$datoContacto->tel_cel, $datoContacto->tel_fijo])->filter()->unique()->values()->all()
            : [];

        $domicilioCliente = $datoContacto
            ? collect([
                trim((string) ($datoContacto->calle ?? '') . ' ' . ($datoContacto->numero_ext ?? '')),
                $datoContacto->numero_int ? 'Int. ' . $datoContacto->numero_int : null,
                $datoContacto->colonia,
                $datoContacto->municipio,
                $datoContacto->estado,
                $datoContacto->cp ? 'CP ' . $datoContacto->cp : null,
            ])->filter()->implode(', ')
            : null;

        $clienteDocumentos = $cliente->documentos instanceof Collection
            ? $cliente->documentos
            : collect($cliente->documentos ?? []);

        $ultimoCreditoId = $credito?->id;
        $documentosCliente = $ultimoCreditoId
            ? $clienteDocumentos->where('credito_id', $ultimoCreditoId)
            : collect();

        $clienteDocs = $this->extractDocumentPreviews($documentosCliente);

        $avales = $credito?->avales instanceof Collection
            ? $credito->avales
            : collect($credito?->avales ?? []);

        $aval = $avales->sortByDesc('id')->first();

        $avalDocumentos = $aval && $aval->documentos instanceof Collection
            ? $aval->documentos
            : collect($aval?->documentos ?? []);

        $avalDocs = $this->extractDocumentPreviews($avalDocumentos);

        $avalTelefonos = $aval ? collect([$aval->telefono])->filter()->unique()->values()->all() : [];
        $avalDireccion = $aval?->direccion;
        $avalNombre = $aval ? $this->buildFullName($aval, 'Sin aval') : 'Sin aval';

        $response['aval'] = $avalNombre;
        $response['detalle'] = [
            'supervisor' => $supervisorNombre,
            'estatus_credito' => $estadoCreditoTexto,
            'cliente' => [
                'telefonos' => $telefonosCliente,
                'domicilio' => $domicilioCliente,
                'documentos' => $clienteDocs,
            ],
            'aval' => [
                'nombre' => $avalNombre,
                'telefonos' => $avalTelefonos,
                'domicilio' => $avalDireccion,
                'documentos' => $avalDocs,
            ],
        ];

        return $response;
    }

}
