<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Ejercicio;
use App\Models\Promotor;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $user = auth()->user();
        $supervisor = Supervisor::with([
            'promotores' => function ($query) {
                $query->select('id', 'supervisor_id', 'nombre', 'apellido_p', 'apellido_m', 'venta_maxima', 'venta_proyectada_objetivo')
                    ->with(['clientes' => function ($clienteQuery) {
                        $clienteQuery->select('id', 'promotor_id', 'nombre', 'apellido_p', 'apellido_m', 'estatus', 'tiene_credito_activo')
                            ->orderBy('nombre');
                    }])
                    ->orderBy('nombre');
            },
        ])->firstWhere('user_id', $user?->id);

        abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');

        $promotores = $supervisor->promotores ?? collect();
        $promotorIds = $promotores->pluck('id');

        $prospectStatuses = ['pendiente', 'a_supervision', 'pendiente_recredito', 'inactivo'];
        $supervisionStatuses = ['a_supervision', 'pendiente_recredito', 'inactivo'];

        $clientesProspectados = $promotorIds->isEmpty()
            ? 0
            : Cliente::whereIn('promotor_id', $promotorIds)->count();

        $clientesPorSupervisar = $promotorIds->isEmpty()
            ? 0
            : Cliente::whereIn('promotor_id', $promotorIds)
                ->whereIn('estatus', $supervisionStatuses)
                ->count();

        $ejercicio = Ejercicio::where('supervisor_id', $supervisor->id)
            ->latest('fecha_inicio')
            ->first();

        $moneyWeeklyNow = (float) ($ejercicio?->dinero_autorizado ?? 0);
        $moneyWeeklyTarget = (float) ($ejercicio?->venta_objetivo ?? 0);
        $fechaLimite = $ejercicio?->fecha_final?->format('d/m/Y');

        $moneyProgress = $moneyWeeklyTarget > 0
            ? min(100, ($moneyWeeklyNow / $moneyWeeklyTarget) * 100)
            : 0;

        $promotoresSupervisados = $promotores->map(function ($promotor) use ($prospectStatuses, $supervisionStatuses) {
            $debe = (float) $promotor->venta_maxima;
            $registrada = (float) $promotor->venta_proyectada_objetivo;
            $falla = max(0, $debe - $registrada);

            $formatNombre = function ($cliente) {
                return trim($cliente->nombre . ' ' . $cliente->apellido_p . ' ' . ($cliente->apellido_m ?? ''));
            };

            $prospectos = $promotor->clientes
                ->whereIn('estatus', $prospectStatuses)
                ->map($formatNombre)
                ->values();

            $porSupervisar = $promotor->clientes
                ->whereIn('estatus', $supervisionStatuses)
                ->map($formatNombre)
                ->values();

            return [
                'nombre'          => trim($promotor->nombre . ' ' . $promotor->apellido_p . ' ' . ($promotor->apellido_m ?? '')),
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
        [, $promotores] = $this->resolveSupervisorPromotoresConClientes();

        $nuevoStatuses = ['pendiente', 'a_supervision', 'inactivo'];
        $recreditoStatuses = ['pendiente_recredito'];

        $promotoresData = $promotores->map(function ($promotor) use ($nuevoStatuses, $recreditoStatuses) {
            $clientes = $promotor->clientes ?? collect();

            $mapCliente = function (Cliente $cliente) {
                return $this->mapClienteDetalle($cliente);
            };

            return [
                'id' => $promotor->id,
                'nombre' => trim($promotor->nombre . ' ' . $promotor->apellido_p . ' ' . ($promotor->apellido_m ?? '')),
                'clientes' => $clientes->whereIn('estatus', $nuevoStatuses)->map($mapCliente)->values(),
                'recreditos' => $clientes->whereIn('estatus', $recreditoStatuses)->map($mapCliente)->values(),
            ];
        });

        return view('mobile.supervisor.venta.clientes_prospectados', [
            'promotores' => $promotoresData,
        ]);
    }

    public function clientes_supervisados()
    {
        [, $promotores] = $this->resolveSupervisorPromotoresConClientes();

        $supervisionStatuses = ['a_supervision', 'pendiente', 'inactivo'];
        $recreditoStatuses = ['pendiente_recredito'];

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
                'clientes' => $clientes->whereIn('estatus', $supervisionStatuses)->map($mapCliente)->values(),
                'recreditos' => $clientes->whereIn('estatus', $recreditoStatuses)->map($mapCliente)->values(),
            ];
        });

        return view('mobile.supervisor.venta.clientes_supervisados', [
            'promotores' => $promotoresData,
        ]);
    }

    public function cartera()
    {
        $user = auth()->user();

        // Busca el perfil de supervisor por user_id
        $supervisor = Supervisor::firstWhere('user_id', $user->id);

        // Si no hay perfil de supervisor, devuelve colección vacía
        $promotores = $supervisor
            ? Promotor::where('supervisor_id', $supervisor->id)
                ->select('id', 'nombre', 'apellido_p', 'apellido_m')
                ->orderBy('nombre')
                ->get()
            : collect();

        // Métricas de cartera (valores por defecto)
        $cartera_activa     = 0.0;
        $cartera_vencida    = 0.0;
        $cartera_falla      = 0.0;
        $cartera_inactivaP  = 0.0;
        $nombre_supervisor  = $user?->name ?? 'Supervisor';

        if ($supervisor) {
            // Nombre amigable del supervisor
            $nombre_supervisor = collect([
                $supervisor->nombre,
                $supervisor->apellido_p,
                $supervisor->apellido_m,
            ])->filter()->implode(' ');

            // Alcance de clientes bajo este supervisor
            $promotorIds = $promotores->pluck('id');
            $clienteIds  = Cliente::whereIn('promotor_id', $promotorIds)->pluck('id');

            // % de inactivos
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

            // Cartera activa (créditos activos)
            $cartera_activa = (float) Credito::whereIn('cliente_id', $clienteIds)
                ->where('estado', 'activo')
                ->sum('monto_total');

            // Cartera vencida (créditos marcados como vencidos)
            $cartera_vencida = (float) Credito::whereIn('cliente_id', $clienteIds)
                ->where('estado', 'vencido')
                ->sum('monto_total');

            // Falla actual (semanas vencidas con saldo pendiente)
            if ($clienteIds->isNotEmpty()) {
                $creditos = Credito::whereIn('cliente_id', $clienteIds)
                    ->with(['pagosProyectados' => function ($q) {
                        $q->where('fecha_limite', '<', now())
                          ->with(['pagosReales.pagoCompleto', 'pagosReales.pagoAnticipo', 'pagosReales.pagoDiferido']);
                    }])
                    ->get();

                $cartera_falla = 0.0;
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
        }

        return view('mobile.supervisor.cartera.cartera', compact(
            'promotores',
            'cartera_activa',
            'cartera_vencida',
            'cartera_falla',
            'cartera_inactivaP',
            'nombre_supervisor'
        ));
    }

    public function carteraPromotor(Promotor $promotor)
    {
        $user = auth()->user();
        $supervisor = Supervisor::firstWhere('user_id', $user->id);

        abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');
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
        $user = auth()->user();
        $supervisor = Supervisor::firstWhere('user_id', $user?->id);

        abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');

        $cliente->load([
            'promotor.supervisor',
            'credito.pagosProyectados.pagosReales',
            'credito.garantias',
            'credito.avales.documentos',
            'credito.datoContacto',
            'documentos',
        ]);

        abort_unless(optional($cliente->promotor)->supervisor_id === $supervisor->id, 403);

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

    public function cartera_activa()
    {
        $user = auth()->user();
        $supervisor = Supervisor::firstWhere('user_id', $user?->id);

        abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m')
            ->orderBy('nombre')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function ($p) {
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
                'nombre'   => trim($p->nombre . ' ' . $p->apellido_p . ' ' . ($p->apellido_m ?? '')),
                'dinero'   => $dinero,
                'clientes' => $items,
            ];
        });

        return view('mobile.supervisor.cartera.cartera_activa', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
        ]);
    }

    public function cartera_vencida()
    {
        $user = auth()->user();
        $supervisor = Supervisor::firstWhere('user_id', $user?->id);
        abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m')
            ->orderBy('nombre')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function ($p) {
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
                'nombre'   => trim($p->nombre . ' ' . $p->apellido_p . ' ' . ($p->apellido_m ?? '')),
                'dinero'   => $dineroVencido,
                'vencido'  => $porcentajeVencido,
                'clientes' => $items->values(),
            ];
        });

        return view('mobile.supervisor.cartera.cartera_vencida', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
        ]);
    }

    public function cartera_inactiva()
    {
        $user = auth()->user();
        $supervisor = Supervisor::firstWhere('user_id', $user?->id);
        abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m')
            ->orderBy('nombre')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function ($p) {
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
                'nombre'   => trim($p->nombre . ' ' . $p->apellido_p . ' ' . ($p->apellido_m ?? '')),
                'clientes' => $items,
            ];
        });

        return view('mobile.supervisor.cartera.cartera_inactiva', [
            'blocks' => $blocks,
            'promotoresPaginator' => $promotoresPaginator,
        ]);
    }

    public function cartera_falla()
    {
        $user = auth()->user();
        $supervisor = Supervisor::firstWhere('user_id', $user?->id);
        abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');

        $promotoresPaginator = Promotor::where('supervisor_id', $supervisor->id)
            ->select('id', 'nombre', 'apellido_p', 'apellido_m')
            ->orderBy('nombre')
            ->paginate(5);

        $blocks = collect($promotoresPaginator->items())->map(function ($p) {
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
                'nombre'   => trim($p->nombre . ' ' . $p->apellido_p . ' ' . ($p->apellido_m ?? '')),
                'dinero'   => $dineroFalla,
                'falla'    => $porcentajeFalla,
                'clientes' => $items->values(),
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

    public function busqueda()
    {
        return view('mobile.supervisor.busqueda.busqueda');
    }

    public function apertura()
    {
        return view('mobile.supervisor.apertura.apertura');
    }

    private function resolveSupervisorPromotoresConClientes(): array
    {
        $user = auth()->user();
        $supervisor = Supervisor::firstWhere('user_id', $user?->id);
        abort_if(!$supervisor, 403, 'Perfil de supervisor no configurado.');

        $promotores = $supervisor->promotores()
            ->with([
                'clientes' => function ($clienteQuery) {
                    $clienteQuery->select('id', 'promotor_id', 'CURP', 'nombre', 'apellido_p', 'apellido_m', 'estatus', 'fecha_nacimiento', 'tiene_credito_activo', 'monto_maximo', 'activo')
                        ->with([
                            'documentos',
                            'credito' => function ($creditoQuery) {
                                $creditoQuery->select('creditos.id', 'creditos.cliente_id', 'creditos.monto_total', 'creditos.estado', 'creditos.fecha_inicio')
                                    ->with([
                                        'datoContacto',
                                        'avales' => function ($avalQuery) {
                                            $avalQuery->select('id', 'credito_id', 'CURP', 'nombre', 'apellido_p', 'apellido_m', 'telefono', 'direccion');
                                        },
                                    ]);
                            },
                        ])
                        ->orderBy('nombre');
                },
            ])
            ->orderBy('nombre')
            ->get();

        return [$supervisor, $promotores];
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
        $fechaInicioCredito = $credito?->fecha_inicio ? Carbon::parse($credito->fecha_inicio)->format('Y-m-d') : null;

        return [
            'id' => $cliente->id,
            'nombre' => trim($cliente->nombre . ' ' . $cliente->apellido_p . ' ' . ($cliente->apellido_m ?? '')),
            'nombre_simple' => $cliente->nombre,
            'apellido_p' => $cliente->apellido_p,
            'apellido_m' => $cliente->apellido_m,
            'curp' => $cliente->CURP,
            'estatus' => $cliente->estatus,
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
                'fecha_inicio' => $fechaInicioCredito,
            ],
        ];
    }

}