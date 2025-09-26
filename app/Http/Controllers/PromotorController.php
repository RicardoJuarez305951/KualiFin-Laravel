<?php
namespace App\Http\Controllers;


use App\Http\Controllers\FiltrosController;
use App\Models\Aval;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\PagoProyectado;
use App\Models\Supervisor;
use App\Services\ExcelReaderService;
use App\Support\RoleHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PromotorController extends Controller
{
    public function __construct(private FiltrosController $filtrosController)
    {
    }

    private const AVAL_CREDIT_STATUS_BLOCKLIST = [
        'prospectado',
        'prospectado_recredito',
        'solicitado',
        'aprobado',
        'supervisado',
        'desembolsado',
        'vencido',
        'cancelado',
    ];

    private const AVAL_CARTERA_STATUS_BLOCKLIST = [
        'activo',
        'moroso',
        'desembolsado',
    ];

    public function index(Request $request)
    {
        $this->resolvePromotorContext($request);

        return view('mobile.index');
    }

    public function objetivo(Request $request)
    {
        $semanasAConsiderar = 4;
        $ahora = now();
        $inicioRango = $ahora->copy()->startOfWeek()->subWeeks($semanasAConsiderar - 1);
        $finRango = $ahora->copy()->endOfWeek();

        $promotor = $this->resolvePromotorContext($request, [
            'clientes' => function ($query) use ($inicioRango, $finRango) {
                $query->with(['creditos' => function ($creditosQuery) use ($inicioRango, $finRango) {
                    $creditosQuery
                        ->whereNotNull('fecha_inicio')
                        ->whereBetween('fecha_inicio', [
                            $inicioRango->copy()->toDateString(),
                            $finRango->copy()->toDateString(),
                        ])
                        ->orderBy('fecha_inicio');
                }]);
            },
        ]);

        if (!$promotor) {
            abort(403, 'No tienes acceso al objetivo de promotores.');
        }

        $clientes = $promotor->clientes ?? collect();
        $creditos = $clientes->flatMap(fn ($cliente) => $cliente->creditos ?? collect());

        $ventasPorSemana = collect(range(0, $semanasAConsiderar - 1))
            ->map(function ($offset) use ($inicioRango, $creditos) {
                $inicioSemana = $inicioRango->copy()->addWeeks($offset);
                $finSemana = $inicioSemana->copy()->endOfWeek();

                $total = $creditos
                    ->filter(function ($credito) use ($inicioSemana, $finSemana) {
                        $fechaInicio = $credito->fecha_inicio;

                        if (!$fechaInicio) {
                            return false;
                        }

                        if (!$fechaInicio instanceof Carbon) {
                            $fechaInicio = Carbon::parse($fechaInicio);
                        }

                        return $fechaInicio->greaterThanOrEqualTo($inicioSemana)
                            && $fechaInicio->lessThanOrEqualTo($finSemana);
                    })
                    ->sum(fn ($credito) => (float) $credito->monto_total);

                return [
                    'label' => sprintf('Sem %s', $inicioSemana->format('W')),
                    'range' => sprintf('%s - %s', $inicioSemana->format('d/m'), $finSemana->format('d/m')),
                    'total' => round($total, 2),
                ];
            })
            ->values();

        $objetivoSemanal = (float) ($promotor->venta_maxima ?? 0);
        $ventaActual = (float) ($ventasPorSemana->last()['total'] ?? 0.0);
        $objetivoEjercicio = (float) ($promotor->venta_proyectada_objetivo ?? 0);

        $avanceReal = $objetivoSemanal > 0 ? ($ventaActual / $objetivoSemanal) * 100 : 0.0;
        $porcentajeActual = round($avanceReal, 1);
        $fraseMotivacional = $this->buildMotivationalMessage($avanceReal);

        return view('mobile.promotor.objetivo.objetivo', [
            'objetivoSemanal' => $objetivoSemanal,
            'ventaActual' => $ventaActual,
            'objetivoEjercicio' => $objetivoEjercicio,
            'ventasPorSemana' => $ventasPorSemana,
            'porcentajeActual' => $porcentajeActual,
            'fraseMotivacional' => $fraseMotivacional,
        ]);
    }

    public function venta(Request $request)
    {
        $promotor = $this->resolvePromotorContext($request, [
            'supervisor.user',
            'supervisor.ejecutivo.user',
            'clientes' => fn ($query) => $query->with('credito')->orderBy('nombre'),
        ]);

        if (!$promotor) {
            abort(403, 'No tienes acceso a la venta de promotores.');
        }

        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $supervisor = $promotor->supervisor?->user?->name;
        $ejecutivo = $promotor->supervisor?->ejecutivo?->user?->name;

        $clientes = $promotor->clientes ?? collect();
        $total = $clientes->sum(fn ($cliente) => $cliente->credito->monto_total ?? $cliente->monto_maximo);

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

    public function enviarVentas(Request $request)
    {
        try {
            $promotor = $this->resolvePromotorContext($request);

            if (!$promotor) {
                Log::warning('Usuario sin acceso a promotor intento enviar ventas.', ['user_id' => Auth::id()]);
                return response()->json(['success' => false, 'message' => 'Perfil de promotor no encontrado.'], 404);
            }

            DB::beginTransaction();

            $clientes = Cliente::where('promotor_id', $promotor->id)
                ->where('activo', true)
                ->with('credito')
                ->lockForUpdate()
                ->get();

            foreach ($clientes as $cliente) {
                $cliente->update([
                    'tiene_credito_activo' => false,
                    'cartera_estado' => 'inactivo',
                    'activo' => false,
                ]);

                $credito = $cliente->credito;
                if ($credito) {
                    $nuevoEstado = $credito->estado === 'prospectado_recredito'
                        ? 'prospectado_recredito'
                        : 'prospectado';

                    $credito->update([
                        'estado' => $nuevoEstado,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Ventas enviadas a supervision correctamente.']);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error al enviar ventas: ' . $exception->getMessage(), ['exception' => $exception]);
            return response()->json(['success' => false, 'message' => 'Hubo un error al procesar la solicitud.'], 500);
        }
    }


    public function ingresar_cliente()
    {
        return view('mobile.promotor.venta.ingresar_cliente');
    }

    public function storeCliente(Request $request, ExcelReaderService $excel)
    {
        $promotor = $this->resolvePromotorContext($request);

        if (!$promotor) {
            Log::warning('Intento de creacion de cliente por usuario sin contexto de promotor.', ['user_id' => Auth::id()]);
            $message = 'No tienes un perfil de promotor asignado.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 403)
                : back()->with('error', $message);
        }

        try {
            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'apellido_p' => 'required|string|max:100',
                'apellido_m' => 'nullable|string|max:100',
                'CURP' => 'required|string|size:18',
                'monto' => 'required|numeric|min:0|max:3000',
                'aval_nombre' => 'required|string|max:100',
                'aval_apellido_p' => 'required|string|max:100',
                'aval_apellido_m' => 'nullable|string|max:100',
                'aval_CURP' => 'required|string|size:18',
                'contacto.calle' => 'required|string|max:255',
                'contacto.numero_ext' => 'required|string|max:25',
                'contacto.numero_int' => 'nullable|string|max:25',
                'contacto.colonia' => 'required|string|max:255',
                'contacto.municipio' => 'required|string|max:255',
                'contacto.cp' => 'required|string|max:10',
            ]);

            $clienteExistente = Cliente::where('CURP', $data['CURP'])->first();
            if ($clienteExistente) {
                throw ValidationException::withMessages([
                    'CURP' => 'La CURP ingresada ya esta registrada para otro cliente.',
                ]);
            }

            $avalComoCliente = Cliente::with('credito')->where('CURP', $data['aval_CURP'])->first();
            if ($avalComoCliente && $this->avalNoDisponible($avalComoCliente)) {
                throw ValidationException::withMessages([
                    'aval_CURP' => 'El aval tiene un credito activo o en proceso y no puede respaldar esta solicitud.',
                ]);
            }

            $nombreCompleto = $this->formatFullName(
                $data['nombre'],
                $data['apellido_p'],
                $data['apellido_m'] ?? ''
            );
            if ($nombreCompleto !== '') {
                $registrosDeudores = $excel->searchDebtors($nombreCompleto);
                if (!empty($registrosDeudores)) {
                    throw ValidationException::withMessages([
                        'nombre' => 'El cliente aparece en la lista de deudores y no puede registrarse.',
                    ]);
                }
            }

            $clienteEvaluado = new Cliente([
                'id' => 0,
                'promotor_id' => $promotor->id,
                'CURP' => $data['CURP'],
                'nombre' => $data['nombre'],
                'apellido_p' => $data['apellido_p'],
                'apellido_m' => $data['apellido_m'] ?? '',
                'tiene_credito_activo' => false,
                'cartera_estado' => 'inactivo',
            ]);
            $clienteEvaluado->setRelation('promotor', $promotor);
            $clienteEvaluado->setRelation('creditos', collect());

            $formulario = [
                'cliente' => [
                    'curp' => $data['CURP'],
                ],
                'aval' => [
                    'curp' => $data['aval_CURP'],
                ],
                'contacto' => $data['contacto'],
                'credito' => [
                    'fecha_inicio' => Carbon::now()->toDateString(),
                ],
            ];

            $contexto = [
                'tipo_solicitud' => 'nuevo',
                'promotor_id' => $promotor->id,
                'supervisor_id' => $promotor->supervisor_id,
                'fecha_solicitud' => Carbon::now(),
                'ultimo_credito' => null,
            ];

            $resultadoFiltros = $this->filtrosController->evaluar($clienteEvaluado, $formulario, $contexto);

            if (!$resultadoFiltros['passed']) {
                $mensajeFiltro = $resultadoFiltros['message'] ?? 'La solicitud no cumple con los criterios requeridos.';

                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $mensajeFiltro], 422)
                    : back()->with('error', $mensajeFiltro)->withInput();
            }

            DB::transaction(function () use ($data, $promotor) {
                $cliente = Cliente::create([
                    'promotor_id' => $promotor->id,
                    'CURP' => $data['CURP'],
                    'nombre' => $data['nombre'],
                    'apellido_p' => $data['apellido_p'],
                    'apellido_m' => $data['apellido_m'] ?? '',
                    'fecha_nacimiento' => now()->subYears(18),
                    'tiene_credito_activo' => false,
                    'cartera_estado' => 'inactivo',
                    'monto_maximo' => $data['monto'],
                    'activo' => false,
                ]);

                $credito = Credito::create([
                    'cliente_id' => $cliente->id,
                    'monto_total' => $data['monto'],
                    'estado' => 'prospectado',
                    'interes' => 0,
                    'periodicidad' => '15Semanas',
                    'fecha_inicio' => now(),
                    'fecha_final' => now()->addMonths(12),
                ]);

                Aval::create([
                    'CURP' => $data['aval_CURP'],
                    'credito_id' => $credito->id,
                    'nombre' => $data['aval_nombre'],
                    'apellido_p' => $data['aval_apellido_p'],
                    'apellido_m' => $data['aval_apellido_m'] ?? '',
                    'fecha_nacimiento' => now()->subYears(25),
                    'direccion' => 'Por definir',
                    'telefono' => 'Por definir',
                    'parentesco' => 'Por definir',
                ]);
            });

            $message = 'Cliente creado con exito.';
            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->route('mobile.promotor.ingresar_cliente')->with('success', $message);
        } catch (ValidationException $exception) {
            Log::warning('Error de validacion al crear cliente.', ['errors' => $exception->errors(), 'user_id' => Auth::id()]);

            $customMessage = $exception->validator->errors()->first('CURP')
                ?: $exception->validator->errors()->first('aval_CURP')
                ?: 'Datos invalidos: ' . collect($exception->errors())->flatten()->implode(' ');

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $customMessage], 422)
                : back()->with('error', $customMessage)->withErrors($exception->errors())->withInput();
        } catch (\Throwable $exception) {
            Log::error('Error al crear cliente: ' . $exception->getMessage(), ['exception' => $exception]);
            $message = 'No se pudo crear el cliente. Intentalo de nuevo.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : back()->with('error', $message);
        }
    }

    public function storeRecredito(Request $request, ExcelReaderService $excel)
    {
        $promotor = $this->resolvePromotorContext($request);
        if (!$promotor) {
            $message = 'No tienes un perfil de promotor asignado.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 403)
                : back()->with('error', $message);
        }

        $isNewAval = $request->boolean('r_newAval');

        $rules = [
            'CURP' => 'required|string|size:18|exists:clientes,CURP',
            'monto' => 'required|numeric|min:0|max:20000',
            'r_newAval' => 'required|boolean',
            'contacto.calle' => 'required|string|max:255',
            'contacto.numero_ext' => 'required|string|max:25',
            'contacto.numero_int' => 'nullable|string|max:25',
            'contacto.colonia' => 'required|string|max:255',
            'contacto.municipio' => 'required|string|max:255',
            'contacto.cp' => 'required|string|max:10',
        ];

        if ($isNewAval) {
            $rules = array_merge($rules, [
                'aval_nombre' => 'required|string|max:100',
                'aval_apellido_p' => 'required|string|max:100',
                'aval_apellido_m' => 'nullable|string|max:100',
                'aval_CURP' => 'required|string|size:18',
            ]);
        }

        try {
            $data = $request->validate($rules);

            $clienteRegistro = Cliente::where('CURP', $data['CURP'])
                ->with(['creditos' => fn ($query) => $query->orderByDesc('fecha_inicio')])
                ->with('promotor')
                ->first();

            if ($clienteRegistro) {
                $nombreCompleto = $this->formatFullName(
                    $clienteRegistro->nombre,
                    $clienteRegistro->apellido_p,
                    $clienteRegistro->apellido_m
                );

                if ($nombreCompleto !== '') {
                    $registrosDeudores = $excel->searchDebtors($nombreCompleto);

                    if (!empty($registrosDeudores)) {
                        throw ValidationException::withMessages([
                            'CURP' => 'El cliente aparece en la lista de deudores y no puede solicitar un recredito.',
                        ]);
                    }
                }
            }

            $avalCurp = null;
            if ($isNewAval) {
                $avalCurp = $data['aval_CURP'];
            } else {
                $cliente = $clienteRegistro;
                if ($cliente) {
                    $prevAval = Aval::whereHas('credito', fn ($query) => $query->where('cliente_id', $cliente->id))
                        ->latest('creado_en')
                        ->first();
                    if ($prevAval) {
                        $avalCurp = $prevAval->CURP;
                    }
                }
            }

            if ($avalCurp) {
                $avalComoCliente = Cliente::with('credito')->where('CURP', $avalCurp)->first();
                if ($avalComoCliente && $this->avalNoDisponible($avalComoCliente)) {
                    throw ValidationException::withMessages([
                        'aval_CURP' => 'El aval tiene un credito activo o en proceso y no puede respaldar esta solicitud.',
                    ]);
                }
            }

            if ($clienteRegistro) {
                $clienteRegistro->setRelation('creditos', $clienteRegistro->creditos ?? collect());
                if ($clienteRegistro->relationLoaded('promotor')) {
                    $clienteRegistro->promotor?->loadMissing('supervisor');
                }

                $ultimoCredito = $clienteRegistro->creditos->first();

                $formulario = [
                    'cliente' => [
                        'curp' => $clienteRegistro->CURP,
                    ],
                    'aval' => [
                        'curp' => $avalCurp ?? '',
                    ],
                    'contacto' => $data['contacto'],
                    'credito' => [
                        'fecha_inicio' => Carbon::now()->toDateString(),
                    ],
                ];

                $contexto = [
                    'tipo_solicitud' => 'recredito',
                    'promotor_id' => $promotor->id,
                    'supervisor_id' => $promotor->supervisor_id,
                    'fecha_solicitud' => Carbon::now(),
                    'ultimo_credito' => $ultimoCredito,
                    'credito_actual_id' => $ultimoCredito?->id,
                ];

                $resultadoFiltros = $this->filtrosController->evaluar($clienteRegistro, $formulario, $contexto);

                if (!$resultadoFiltros['passed']) {
                    $mensajeFiltro = $resultadoFiltros['message'] ?? 'La solicitud no cumple con los criterios requeridos.';

                    return $request->expectsJson()
                        ? response()->json(['success' => false, 'message' => $mensajeFiltro], 422)
                        : back()->with('error', $mensajeFiltro)->withInput();
                }
            }
        } catch (ValidationException $exception) {
            $customMessage = $exception->validator->errors()->first('aval_CURP')
                ?: 'Datos invalidos: ' . collect($exception->errors())->flatten()->implode(' ');

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $customMessage], 422)
                : back()->withErrors($exception->errors())->withInput()->with('error', $customMessage);
        }

        try {
            DB::transaction(function () use ($data, $promotor, $isNewAval) {
                $cliente = Cliente::where('CURP', $data['CURP'])->lockForUpdate()->firstOrFail();

                if ($cliente->promotor_id !== $promotor->id) {
                    throw new \RuntimeException('No estas autorizado para otorgar un recredito a este cliente.');
                }

                $ultimoCredito = $cliente->credito;
                if ($cliente->tiene_credito_activo || ($ultimoCredito && in_array($ultimoCredito->estado, self::AVAL_CREDIT_STATUS_BLOCKLIST, true))) {
                    throw new \RuntimeException('El cliente ya tiene un credito activo o en proceso.');
                }

                $avalDataForCreation = [];
                if ($isNewAval) {
                    $avalDataForCreation = [
                        'CURP' => $data['aval_CURP'],
                        'nombre' => $data['aval_nombre'],
                        'apellido_p' => $data['aval_apellido_p'],
                        'apellido_m' => $data['aval_apellido_m'] ?? null,
                        'fecha_nacimiento' => now()->subYears(25),
                        'direccion' => 'Por definir',
                        'telefono' => 'Por definir',
                        'parentesco' => 'Por definir',
                    ];
                } else {
                    $prevAval = Aval::whereHas('credito', fn ($query) => $query->where('cliente_id', $cliente->id))
                        ->latest('creado_en')
                        ->first();

                    if (!$prevAval) {
                        throw new \RuntimeException('No se encontro un aval previo para este cliente. Debe registrar uno nuevo.');
                    }

                    $avalDataForCreation = $prevAval->only([
                        'CURP',
                        'nombre',
                        'apellido_p',
                        'apellido_m',
                        'fecha_nacimiento',
                        'direccion',
                        'telefono',
                        'parentesco',
                    ]);
                }

                $credito = Credito::create([
                    'cliente_id' => $cliente->id,
                    'monto_total' => $data['monto'],
                    'estado' => 'prospectado_recredito',
                    'interes' => 0,
                    'periodicidad' => 'Mes',
                    'fecha_inicio' => now(),
                    'fecha_final' => now()->addWeeks(16),
                ]);

                Aval::create(array_merge($avalDataForCreation, ['credito_id' => $credito->id]));

                $cliente->update([
                    'tiene_credito_activo' => false,
                    'cartera_estado' => 'inactivo',
                    'activo' => false,
                ]);
            });

            $message = 'Recredito solicitado con exito.';
            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->route('mobile.promotor.ingresar_cliente')->with('success', $message);
        } catch (\Throwable $exception) {
            Log::error('Error al procesar recredito: ' . $exception->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all(),
                'exception' => $exception,
            ]);

            $userMessage = 'No se pudo procesar el recredito. ' . $exception->getMessage();

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $userMessage], 500)
                : back()->with('error', $userMessage)->withInput();
        }
    }

        

        public function cartera(Request $request)
    {
        $promotor = $this->resolvePromotorContext($request, [
            'clientes' => function ($query) {
                $query->with([
                    'credito' => function ($creditQuery) {
                        $creditQuery->with([
                            'pagosProyectados' => fn ($subQuery) => $subQuery->orderBy('semana'),
                            'avales' => fn ($subQuery) => $subQuery->orderByDesc('id'),
                            'datoContacto',
                        ]);
                    },
                    'creditos' => function ($creditosQuery) {
                        $creditosQuery->with([
                            'avales' => fn ($subQuery) => $subQuery->orderByDesc('id'),
                            'datoContacto',
                        ])->orderByDesc('id')->limit(1);
                    },
                ])->orderBy('nombre');
            },
        ]);

        if (!$promotor) {
            return view('mobile.promotor.cartera.cartera', [
                'activos' => collect(),
                'vencidos' => collect(),
                'inactivos' => collect(),
            ]);
        }

        $clientes = $promotor->clientes ?? collect();

        $activos = collect();
        $vencidos = collect();
        $inactivos = collect();

        foreach ($clientes as $cliente) {
            $credito = $cliente->credito;
            $estadoCartera = $cliente->cartera_estado ?? $this->mapCreditoEstadoACartera($credito) ?? 'inactivo';

            $pagoPendiente = $credito?->pagosProyectados?->firstWhere('estado', 'pendiente');
            $pagoPendienteData = $this->buildPendingPaymentData($pagoPendiente, $cliente);

            $cliente->pago_proyectado_pendiente = $pagoPendienteData;
            if ($pagoPendienteData && (!isset($cliente->deuda_total) || $cliente->deuda_total === null)) {
                $cliente->deuda_total = $pagoPendienteData['deuda_vencida'];
            }

            $cliente->cartera_estado = $estadoCartera;
            $cliente->tiene_credito_activo = in_array($estadoCartera, ['activo', 'moroso', 'desembolsado'], true);
            unset($cliente->semana_credito, $cliente->monto_semanal);

            if (in_array($estadoCartera, ['activo', 'desembolsado'], true)) {
                if ($pagoPendiente) {
                    $cliente->semana_credito = $pagoPendiente->semana;
                    $cliente->monto_semanal = $pagoPendiente->monto_proyectado;
                }

                $activos->push($cliente);
                continue;
            }

            if ($estadoCartera === 'moroso') {
                $vencidos->push($cliente);
                continue;
            }

            $ultimoCredito = $cliente->creditos->first() ?? $credito;
            $contacto = $ultimoCredito?->datoContacto;

            $clienteDireccion = $this->buildDireccionFromContacto($contacto);
            $clienteTelefono = $this->pickTelefonoFromContacto($contacto);
            $fechaUltimoCredito = optional($ultimoCredito?->fecha_inicio)->format('Y-m-d')
                ?: optional($ultimoCredito?->fecha_final)->format('Y-m-d');

            $avalModel = $ultimoCredito?->avales?->first();
            $aval = [
                'apellido_p' => $avalModel?->apellido_p ?? '',
                'apellido_m' => $avalModel?->apellido_m ?? '',
                'nombre' => $avalModel?->nombre ?? '',
                'direccion' => $avalModel?->direccion ?? '',
                'telefono' => $avalModel?->telefono ?? '',
                'CURP' => $avalModel?->CURP ?? '',
                'curp' => $avalModel?->CURP ?? '',
            ];
            $avalNombreCompleto = $this->formatFullName(
                $aval['apellido_p'],
                $aval['apellido_m'],
                $aval['nombre']
            );

            $clienteResumen = [
                'apellido_p' => $cliente->apellido_p,
                'apellido_m' => $cliente->apellido_m,
                'nombre' => $cliente->nombre,
                'direccion' => $clienteDireccion,
                'telefono' => $clienteTelefono,
                'CURP' => $cliente->CURP,
                'curp' => $cliente->CURP,
            ];

            $inactivos->push([
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'apellido_p' => $cliente->apellido_p,
                'apellido_m' => $cliente->apellido_m,
                'direccion' => $clienteDireccion,
                'telefono' => $clienteTelefono,
                'fecha_ultimo_credito' => $fechaUltimoCredito,
                'aval' => array_merge($aval, [
                    'nombre_completo' => $avalNombreCompleto,
                ]),
                'ultimo_aval' => array_merge($aval, [
                    'nombre_completo' => $avalNombreCompleto,
                ]),
                'aval_nombre' => $avalNombreCompleto,
                'aval_apellido_p' => $aval['apellido_p'],
                'aval_apellido_m' => $aval['apellido_m'],
                'aval_direccion' => $aval['direccion'],
                'aval_telefono' => $aval['telefono'],
                'aval_CURP' => $aval['CURP'],
                'ultimo_credito' => $ultimoCredito ? [
                    'id' => $ultimoCredito->id,
                    'estado' => $ultimoCredito->estado,
                    'fecha_inicio' => optional($ultimoCredito->fecha_inicio)->format('Y-m-d'),
                    'fecha_final' => optional($ultimoCredito->fecha_final)->format('Y-m-d'),
                    'dato_contacto' => $contacto ? $contacto->toArray() : null,
                ] : null,
                'cliente_resumen' => $clienteResumen,
                'promotor' => $promotor->nombre ?? '',
                'promotor_id' => $promotor->id,
            ]);
        }

        return view('mobile.promotor.cartera.cartera', [
            'activos' => $activos->values(),
            'vencidos' => $vencidos->values(),
            'inactivos' => $inactivos->values(),
        ]);
    }

    private function buildPendingPaymentData(?PagoProyectado $pagoProyectado, $cliente): ?array
    {
        if (!$pagoProyectado) {
            return null;
        }

        $montoProyectado = (float) ($pagoProyectado->monto_proyectado ?? 0);

        $deudaVencida = collect([
            $pagoProyectado->deuda_total ?? null,
            $pagoProyectado->deuda_vencida ?? null,
            $cliente->deuda_total ?? null,
            $cliente->deuda ?? null,
        ])
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value)
            ->first();

        if ($deudaVencida === null) {
            $deudaVencida = $montoProyectado;
        }

        return [
            'id' => $pagoProyectado->id,
            'monto_proyectado' => $montoProyectado,
            'deuda_vencida' => $deudaVencida,
        ];
    }

    private function sharePromotorContext(Request $request, ?Promotor $promotor): void
    {
        if ($promotor) {
            $request->attributes->set('acting_promotor_id', $promotor->id);
            $request->attributes->set('acting_promotor', $promotor);
            view()->share([
                'actingPromotor' => $promotor,
                'actingPromotorId' => $promotor->id,
                'promotorContextQuery' => ['promotor' => $promotor->id],
            ]);
        } else {
            $request->attributes->set('acting_promotor_id', null);
            $request->attributes->set('acting_promotor', null);
            view()->share([
                'actingPromotor' => null,
                'actingPromotorId' => null,
                'promotorContextQuery' => [],
            ]);
        }
    }

    private function resolvePromotorContext(Request $request, array $with = []): ?Promotor
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);
        $sessionKey = 'mobile.promotor_context';
        $requestedId = (int) $request->query('promotor');

        if ($primaryRole === 'promotor') {
            $promotor = $user?->promotor;
            if ($promotor) {
                $promotor->loadMissing($with);
                $request->session()->put($sessionKey, $promotor->id);
            } else {
                $request->session()->forget($sessionKey);
            }

            $this->sharePromotorContext($request, $promotor);

            return $promotor;
        }

        $query = Promotor::query();

        if ($primaryRole === 'supervisor') {
            $supervisor = Supervisor::firstWhere('user_id', $user?->id);
            if (!$supervisor) {
                $request->session()->forget($sessionKey);
                $this->sharePromotorContext($request, null);

                return null;
            }
            $query->where('supervisor_id', $supervisor->id);
        } elseif ($primaryRole === 'ejecutivo') {
            $ejecutivo = Ejecutivo::firstWhere('user_id', $user?->id);
            if (!$ejecutivo) {
                $request->session()->forget($sessionKey);
                $this->sharePromotorContext($request, null);

                return null;
            }

            $supervisorIds = $ejecutivo->supervisors()->pluck('id');
            if ($supervisorIds->isEmpty()) {
                $request->session()->forget($sessionKey);
                $this->sharePromotorContext($request, null);

                return null;
            }

            $query->whereIn('supervisor_id', $supervisorIds);
        } elseif (!in_array($primaryRole, ['administrativo', 'superadmin'], true)) {
            $request->session()->forget($sessionKey);
            $this->sharePromotorContext($request, null);

            return null;
        }

        $loader = function (int $id) use ($query, $with) {
            if ($id <= 0) {
                return null;
            }

            return (clone $query)->with($with)->find($id);
        };

        if ($requestedId > 0) {
            $promotor = $loader($requestedId);
            if ($promotor) {
                $request->session()->put($sessionKey, $promotor->id);
                $this->sharePromotorContext($request, $promotor);

                return $promotor;
            }
        }

        $sessionId = (int) $request->session()->get($sessionKey);
        if ($sessionId > 0) {
            $promotor = $loader($sessionId);
            if ($promotor) {
                $this->sharePromotorContext($request, $promotor);

                return $promotor;
            }
        }

        $promotor = (clone $query)->with($with)
            ->orderBy('nombre')
            ->orderBy('apellido_p')
            ->orderBy('apellido_m')
            ->first();

        if ($promotor) {
            $request->session()->put($sessionKey, $promotor->id);
        } else {
            $request->session()->forget($sessionKey);
        }

        $this->sharePromotorContext($request, $promotor);

        return $promotor;
    }

    private function buildDireccionFromContacto($contacto): string
    {
        if (!$contacto) {
            return '';
        }

        return collect([
            $contacto->calle ?? null,
            $contacto->numero_ext ? '#' . $contacto->numero_ext : null,
            $contacto->numero_int ? 'Int ' . $contacto->numero_int : null,
            $contacto->colonia ?? null,
            $contacto->municipio ?? null,
            $contacto->estado ?? null,
            $contacto->cp ? 'CP ' . $contacto->cp : null,
        ])->filter()->implode(', ');
    }

    private function pickTelefonoFromContacto($contacto): string
    {
        if (!$contacto) {
            return '';
        }

        return collect([$contacto->tel_cel ?? null, $contacto->tel_fijo ?? null])
            ->map(fn ($telefono) => $telefono ? trim($telefono) : null)
            ->filter()
            ->first() ?? '';
    }

    private function formatFullName(...$parts): string
    {
        return collect($parts)
            ->map(function ($part) {
                if ($part === null) {
                    return null;
                }

                $part = is_string($part) ? trim($part) : (string) $part;

                return $part !== '' ? $part : null;
            })
            ->filter()
            ->implode(' ');
    }

    private function buildMotivationalMessage(float $percentage): string
    {
        if ($percentage >= 120.0) {
            return '¡Impresionante! Superaste tu objetivo semanal, sigue así.';
        }

        if ($percentage >= 100.0) {
            return '¡Objetivo semanal alcanzado! Excelente trabajo.';
        }

        if ($percentage >= 75.0) {
            return '¡Estás muy cerca, un último esfuerzo te llevará a la meta!';
        }

        if ($percentage >= 50.0) {
            return 'Vas por buen camino, mantén el ritmo.';
        }

        if ($percentage > 0.0) {
            return 'Buen inicio, cada visita suma para lograr tu objetivo.';
        }

        return 'Aún no registras ventas esta semana, ¡vamos con todo!';
    }

    public function cliente_historial(Request $request, Cliente $cliente)
    {
        $promotor = $this->resolvePromotorContext($request);

        if (!$promotor || $cliente->promotor_id !== $promotor->id) {
            abort(403, 'No autorizado');
        }

        $cliente->load([
            'promotor.user',
            'promotor.supervisor.user',
            'credito.pagosProyectados' => function ($query) {
                $query->orderBy('semana');
            },
        ]);

        return view('mobile.promotor.cartera.cliente_historial', compact('cliente'));
    }

    private function mapCreditoEstadoACartera(?Credito $credito): ?string
    {
        if (!$credito) {
            return null;
        }

        return match ($credito->estado) {
            'desembolsado' => 'desembolsado',
            'vencido' => 'moroso',
            'liquidado' => 'regularizado',
            'cancelado' => 'inactivo',
            'prospectado', 'prospectado_recredito', 'solicitado', 'aprobado', 'supervisado' => 'activo',
            default => null,
        };
    }
    private function avalNoDisponible(Cliente $cliente): bool
    {
        if ($cliente->tiene_credito_activo) {
            return true;
        }

        $estadoCredito = $cliente->credito?->estado;
        if ($estadoCredito && in_array($estadoCredito, self::AVAL_CREDIT_STATUS_BLOCKLIST, true)) {
            return true;
        }

        if ($cliente->cartera_estado && in_array($cliente->cartera_estado, self::AVAL_CARTERA_STATUS_BLOCKLIST, true)) {
            return true;
        }

        return false;
    }
}
