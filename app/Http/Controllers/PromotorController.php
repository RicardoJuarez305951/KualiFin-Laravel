<?php
namespace App\Http\Controllers;


use App\Enums\ClienteEstado;
use App\Enums\CreditoEstado;
use App\Http\Controllers\FiltrosController;
use App\Models\Aval;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\DatoContacto;
use App\Models\Ejecutivo;
use App\Models\Promotor;
use App\Models\PagoProyectado;
use App\Models\Supervisor;
use App\Services\ExcelReaderService;
use App\Support\RoleHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PromotorController extends Controller
{
    public function __construct(private FiltrosController $filtrosController)
    {
    }

    private const AVAL_CREDIT_STATUS_BLOCKLIST = [
        CreditoEstado::PROSPECTADO->value,
        CreditoEstado::PROSPECTADO_REACREDITO->value,
        CreditoEstado::SOLICITADO->value,
        CreditoEstado::APROBADO->value,
        CreditoEstado::SUPERVISADO->value,
        CreditoEstado::DESEMBOLSADO->value,
        CreditoEstado::CLIENTE_RIESGO->value,
        CreditoEstado::AVAL_RIESGO->value,
        CreditoEstado::CLIENTE_AVAL_RIESGO->value,
        CreditoEstado::VENCIDO->value,
        CreditoEstado::CANCELADO->value,
        CreditoEstado::REQUIERE_AUTORIZACION->value,
    ];

    private const AVAL_CARTERA_STATUS_BLOCKLIST = [
        ClienteEstado::ACTIVO->value,
        ClienteEstado::MOROSO->value,
        ClienteEstado::DESEMBOLSADO->value,
    ];

    private ?string $clienteEstadoColumnCache = null;

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
                $cliente->update($this->withClienteEstado([
                    'tiene_credito_activo' => false,
                    'activo' => false,
                ], ClienteEstado::INACTIVO->value));

                if ($this->clienteEstadoColumn() === 'estatus') {
                    $cliente->setAttribute('cliente_estado', ClienteEstado::INACTIVO->value);
                }

                $credito = $cliente->credito;
                if ($credito) {
                    $estadoActual = CreditoEstado::tryFrom((string) $credito->estado);
                    $nuevoEstado = $estadoActual === CreditoEstado::PROSPECTADO_REACREDITO
                        ? CreditoEstado::PROSPECTADO_REACREDITO->value
                        : CreditoEstado::PROSPECTADO->value;

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
            $avalNombreCompleto = $this->formatFullName(
                $data['aval_nombre'],
                $data['aval_apellido_p'],
                $data['aval_apellido_m'] ?? ''
            );

            // El Excel usado por searchDebtors es una lista historica externa; nunca se altera via migraciones ni Eloquent.
            $registrosDeudaCliente = $nombreCompleto !== ''
                ? $excel->searchDebtors($nombreCompleto)
                : [];
            $estadoClienteMigracion = $this->obtenerEstadoClientePorCurp($data['CURP']);
            $clienteMarcadoMoroso = $estadoClienteMigracion !== null
                && $this->esEstadoMoroso($estadoClienteMigracion);
            // Recordatorio: fuente Excel y base MySQL son independientes, asi que esta consulta no refleja cambios hechos por seeders ni factories.
            $registrosDeudaAval = $avalNombreCompleto !== ''
                ? $excel->searchDebtors($avalNombreCompleto)
                : [];

            $clienteTieneDeuda = !empty($registrosDeudaCliente) || $clienteMarcadoMoroso;
            $avalTieneDeuda = !empty($registrosDeudaAval);

            $estadoCredito = CreditoEstado::PROSPECTADO->value;
            $mensajeResultado = 'Cliente creado con exito.';
            $tipoRiesgo = null;
            $decisionRiesgo = $request->input('decision_riesgo');

            $fuentesRiesgoCliente = [];
            if (!empty($registrosDeudaCliente)) {
                $fuentesRiesgoCliente[] = 'la lista de deudores';
            }
            if ($clienteMarcadoMoroso) {
                $fuentesRiesgoCliente[] = 'la base de clientes morosos';
            }
            $mensajeRiesgoCliente = $this->construirMensajeRiesgo('cliente', $fuentesRiesgoCliente);

            $fuentesRiesgoAval = !empty($registrosDeudaAval)
                ? ['la lista de deudores']
                : [];
            $mensajeRiesgoAval = $this->construirMensajeRiesgo('aval', $fuentesRiesgoAval);

            if (!$clienteTieneDeuda && !$avalTieneDeuda) {
                $decisionRiesgo = null;
            }

            if ($clienteTieneDeuda || $avalTieneDeuda) {
                $riesgosDetectados = [];
                if ($clienteTieneDeuda) {
                    $riesgosDetectados['cliente'] = [
                        'mensaje' => $mensajeRiesgoCliente,
                    ];
                }
                if ($avalTieneDeuda) {
                    $riesgosDetectados['aval'] = [
                        'mensaje' => $mensajeRiesgoAval,
                    ];
                }

                $tipoRiesgo = match (true) {
                    $clienteTieneDeuda && $avalTieneDeuda => CreditoEstado::CLIENTE_AVAL_RIESGO->value,
                    $clienteTieneDeuda => CreditoEstado::CLIENTE_RIESGO->value,
                    default => CreditoEstado::AVAL_RIESGO->value,
                };

                if ($decisionRiesgo === null) {
                    $mensajeConfirmacion = $this->construirMensajeConfirmacionRiesgo($riesgosDetectados, $tipoRiesgo);

                    return response()->json([
                        'success' => false,
                        'requires_confirmation' => true,
                        'message' => $mensajeConfirmacion,
                        'risk_type' => $tipoRiesgo,
                        'estado_credito' => $tipoRiesgo,
                        'deuda_cliente' => $registrosDeudaCliente,
                        'cliente_moroso_bd' => $clienteMarcadoMoroso,
                        'cliente_estado_bd' => $estadoClienteMigracion,
                        'deuda_aval' => $registrosDeudaAval,
                        'cliente_tiene_deuda' => $clienteTieneDeuda,
                        'aval_tiene_deuda' => $avalTieneDeuda,
                    ]);
                }

                if (!in_array($decisionRiesgo, ['aceptar', 'rechazar'], true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Decision de riesgo invalida proporcionada.',
                    ], 422);
                }

                if ($decisionRiesgo === 'aceptar') {
                    $estadoCredito = $tipoRiesgo;
                    $estadoLegible = Str::of($tipoRiesgo)->replace('_', ' ')->title();
                    $mensajeResultado = 'Solicitud registrada con estado ' . $estadoLegible . ' por deuda detectada.';
                } else {
                    $estadoCredito = CreditoEstado::RECHAZADO->value;
                    $mensajeResultado = 'Solicitud registrada como rechazada por deuda detectada.';
                }
            }

            $contactoSolicitud = $request->input('contacto');
            $contactoSolicitud = is_array($contactoSolicitud) ? $contactoSolicitud : [];

            $evaluacionDireccion = $this->evaluarRestriccionDireccion($contactoSolicitud);
            if ($evaluacionDireccion['status'] === 'blocked') {
                $mensajeBloqueo = $evaluacionDireccion['message'] ?? 'La direccion proporcionada ya se encuentra asociada a un credito reciente.';

                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $mensajeBloqueo], 422)
                    : back()->with('error', $mensajeBloqueo)->withInput();
            }

            if ($evaluacionDireccion['status'] === 'requires_authorization'
                && $estadoCredito !== CreditoEstado::RECHAZADO->value) {
                $estadoCredito = CreditoEstado::REQUIERE_AUTORIZACION->value;
                $tipoRiesgo = CreditoEstado::REQUIERE_AUTORIZACION->value;
                $mensajeAutorizacion = $evaluacionDireccion['message']
                    ?? 'Solicitud registrada con estado Requiere autorizacion por coincidencia de domicilio.';

                if ($mensajeResultado !== '' && $mensajeResultado !== 'Cliente creado con exito.') {
                    $mensajeResultado = $mensajeAutorizacion . ' Detalle previo: ' . $mensajeResultado;
                } else {
                    $mensajeResultado = $mensajeAutorizacion;
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
                'cliente_estado' => ClienteEstado::INACTIVO->value,
            ]);
            $clienteEvaluado->setAttribute('estatus', ClienteEstado::INACTIVO->value);
            $clienteEvaluado->setRelation('promotor', $promotor);
            $clienteEvaluado->setRelation('creditos', collect());

            $formulario = [
                'cliente' => [
                    'curp' => $data['CURP'],
                ],
                'aval' => [
                    'curp' => $data['aval_CURP'],
                ],
                'contacto' => $data['contacto'] ?? [],
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
                'permitir_credito_moroso' => $decisionRiesgo === 'aceptar' && $clienteTieneDeuda,
            ];

            $evaluarFiltros = $estadoCredito !== 'rechazado';
            if ($evaluarFiltros) {
                $resultadoFiltros = $this->filtrosController->evaluar($clienteEvaluado, $formulario, $contexto);

                if (!$resultadoFiltros['passed']) {
                    $mensajeFiltro = $resultadoFiltros['message'] ?? 'La solicitud no cumple con los criterios requeridos.';

                    return $request->expectsJson()
                        ? response()->json(['success' => false, 'message' => $mensajeFiltro], 422)
                        : back()->with('error', $mensajeFiltro)->withInput();
                }
            }

            DB::transaction(function () use ($data, $promotor, $estadoCredito) {
                $cliente = Cliente::create($this->withClienteEstado([
                    'promotor_id' => $promotor->id,
                    'CURP' => $data['CURP'],
                    'nombre' => $data['nombre'],
                    'apellido_p' => $data['apellido_p'],
                    'apellido_m' => $data['apellido_m'] ?? '',
                    'fecha_nacimiento' => now()->subYears(18),
                    'tiene_credito_activo' => false,
                    'monto_maximo' => $data['monto'],
                    'activo' => false,
                ], ClienteEstado::INACTIVO->value));

                if ($this->clienteEstadoColumn() === 'estatus') {
                    $cliente->setAttribute('cliente_estado', ClienteEstado::INACTIVO->value);
                }

                $credito = Credito::create([
                    'cliente_id' => $cliente->id,
                    'monto_total' => $data['monto'],
                    'estado' => $estadoCredito,
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

            $respuesta = [
                'success' => true,
                'message' => $mensajeResultado,
                'estado_credito' => $estadoCredito,
                'cliente_tiene_deuda' => $clienteTieneDeuda,
                'cliente_moroso_bd' => $clienteMarcadoMoroso,
                'cliente_estado_bd' => $estadoClienteMigracion,
                'aval_tiene_deuda' => $avalTieneDeuda,
                'deuda_cliente' => $registrosDeudaCliente,
                'deuda_aval' => $registrosDeudaAval,
            ];

            if ($request->expectsJson()) {
                return response()->json($respuesta);
            }

            return redirect()
                ->route('mobile.promotor.ingresar_cliente')
                ->with('success', $mensajeResultado);
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

        $estadoCreditoRecredito = CreditoEstado::PROSPECTADO_REACREDITO->value;
        $mensajeRecredito = 'Recredito solicitado con exito.';
        $decisionRiesgo = $request->input('decision_riesgo');
        $clienteRegistro = null;
        $clienteMarcadoMoroso = false;
        $registrosDeudaCliente = [];
        $mensajeRiesgoCliente = '';
        $clienteTieneDeuda = false;
        $registrosDeudaAval = [];
        $mensajeRiesgoAval = '';
        $avalTieneDeuda = false;
        $avalCurp = null;
        $avalNombreCompleto = '';
        $tipoRiesgo = null;
        $prevAvalParaRiesgo = null;

        $rules = [
            'CURP' => 'required|string|size:18|exists:clientes,CURP',
            'monto' => 'required|numeric|min:0|max:20000',
            'r_newAval' => 'required|boolean',
            'contacto' => 'nullable|array',
            'contacto.calle' => 'nullable|string|max:255',
            'contacto.numero_ext' => 'nullable|string|max:25',
            'contacto.numero_int' => 'nullable|string|max:25',
            'contacto.colonia' => 'nullable|string|max:255',
            'contacto.municipio' => 'nullable|string|max:255',
            'contacto.cp' => 'nullable|string|max:10',
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
                $clienteMarcadoMoroso = $this->esEstadoMoroso($this->obtenerEstadoCliente($clienteRegistro));

                $nombreCompleto = $this->formatFullName(
                    $clienteRegistro->nombre,
                    $clienteRegistro->apellido_p,
                    $clienteRegistro->apellido_m
                );

                if ($nombreCompleto !== '') {
                    // searchDebtors consulta un Excel historico independiente de la base MySQL.
                    $registrosDeudaCliente = $excel->searchDebtors($nombreCompleto);
                }

                $clienteTieneDeuda = $clienteMarcadoMoroso || !empty($registrosDeudaCliente);

                if ($clienteTieneDeuda) {
                    $fuentes = [];
                    if (!empty($registrosDeudaCliente)) {
                        $fuentes[] = 'la lista de deudores';
                    }
                    if ($clienteMarcadoMoroso) {
                        $fuentes[] = 'la base de clientes morosos';
                    }
                    $mensajeRiesgoCliente = $this->construirMensajeRiesgo('cliente', $fuentes);
                }
            }

            $avalCurp = null;
            if ($isNewAval) {
                $avalCurp = $data['aval_CURP'];
            } else {
                if ($clienteRegistro) {
                    $prevAvalParaRiesgo = Aval::whereHas('credito', fn ($query) => $query->where('cliente_id', $clienteRegistro->id))
                        ->latest('creado_en')
                        ->first();
                    if ($prevAvalParaRiesgo) {
                        $avalCurp = $prevAvalParaRiesgo->CURP;
                        $avalNombreCompleto = $this->formatFullName(
                            $prevAvalParaRiesgo->nombre,
                            $prevAvalParaRiesgo->apellido_p,
                            $prevAvalParaRiesgo->apellido_m
                        );
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

            if ($isNewAval) {
                $avalNombreCompleto = $this->formatFullName(
                    $data['aval_nombre'] ?? '',
                    $data['aval_apellido_p'] ?? '',
                    $data['aval_apellido_m'] ?? ''
                );
            }

            if ($avalNombreCompleto !== '') {
                // Igual que antes, la verificacion de deuda del aval viene unicamente del Excel historico.
                $registrosDeudaAval = $excel->searchDebtors($avalNombreCompleto);
            }

            $avalTieneDeuda = !empty($registrosDeudaAval);
            if ($avalTieneDeuda) {
                $mensajeRiesgoAval = $this->construirMensajeRiesgo('aval', ['la lista de deudores']);
            }

            if (!$clienteTieneDeuda && !$avalTieneDeuda) {
                $decisionRiesgo = null;
            }

            if ($clienteTieneDeuda || $avalTieneDeuda) {
                $riesgosDetectados = [];
                if ($clienteTieneDeuda) {
                    $riesgosDetectados['cliente'] = [
                        'mensaje' => $mensajeRiesgoCliente,
                    ];
                }
                if ($avalTieneDeuda) {
                    $riesgosDetectados['aval'] = [
                        'mensaje' => $mensajeRiesgoAval,
                    ];
                }

                $tipoRiesgo = match (true) {
                    $clienteTieneDeuda && $avalTieneDeuda => CreditoEstado::CLIENTE_AVAL_RIESGO->value,
                    $clienteTieneDeuda => CreditoEstado::CLIENTE_RIESGO->value,
                    default => CreditoEstado::AVAL_RIESGO->value,
                };

                if ($decisionRiesgo === null) {
                    $mensajeConfirmacion = $this->construirMensajeConfirmacionRiesgo($riesgosDetectados, $tipoRiesgo);

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'requires_confirmation' => true,
                            'message' => $mensajeConfirmacion,
                            'risk_type' => $tipoRiesgo,
                            'estado_credito' => $tipoRiesgo,
                            'cliente_tiene_deuda' => $clienteTieneDeuda,
                            'cliente_moroso_bd' => $clienteMarcadoMoroso,
                            'cliente_estado_bd' => $this->obtenerEstadoCliente($clienteRegistro),
                            'deuda_cliente' => $registrosDeudaCliente,
                            'aval_tiene_deuda' => $avalTieneDeuda,
                            'deuda_aval' => $registrosDeudaAval,
                        ]);
                    }

                    return back()
                        ->withInput()
                        ->with('error', $mensajeConfirmacion);
                }

                if (!in_array($decisionRiesgo, ['aceptar', 'rechazar'], true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Decision de riesgo invalida proporcionada.',
                    ], 422);
                }

                if ($decisionRiesgo === 'aceptar') {
                    $estadoCreditoRecredito = $tipoRiesgo;
                    $estadoLegible = Str::of($tipoRiesgo)->replace('_', ' ')->title();
                    $mensajeRecredito = 'Solicitud registrada con estado ' . $estadoLegible . ' por deuda detectada.';
                } else {
                    $estadoCreditoRecredito = CreditoEstado::RECHAZADO->value;
                    $mensajeRecredito = 'Solicitud registrada como rechazada por deuda detectada.';
                }
            }

            if ($clienteRegistro) {
                if ($estadoCreditoRecredito !== CreditoEstado::RECHAZADO->value) {
                    $clienteRegistro->setRelation('creditos', $clienteRegistro->creditos ?? collect());
                    if ($clienteRegistro->relationLoaded('promotor')) {
                        $clienteRegistro->promotor?->loadMissing('supervisor');
                    }

                    $ultimoCredito = $clienteRegistro->creditos->first();

                    $contactoSolicitud = $data['contacto'] ?? [];
                    $contactoSolicitud = is_array($contactoSolicitud) ? $contactoSolicitud : [];
                    $direccionNormalizada = $this->normalizarDireccion($contactoSolicitud);
                    $contactoVacio = empty(array_filter($contactoSolicitud, function ($valor) {
                        return $valor !== null && $valor !== '';
                    }));

                    if ($contactoVacio && !$direccionNormalizada['valida'] && $ultimoCredito) {
                        $ultimoCredito->loadMissing('datoContacto');
                        $contactoSolicitud = $this->extraerDireccionDesdeDatoContacto($ultimoCredito->datoContacto ?? null);
                    }

                    $evaluacionDireccion = $this->evaluarRestriccionDireccion($contactoSolicitud, $clienteRegistro?->id);
                    if ($evaluacionDireccion['status'] === 'blocked') {
                        $mensajeBloqueo = $evaluacionDireccion['message'] ?? 'La direccion proporcionada ya se encuentra asociada a un credito reciente.';

                        return $request->expectsJson()
                            ? response()->json(['success' => false, 'message' => $mensajeBloqueo], 422)
                            : back()->with('error', $mensajeBloqueo)->withInput();
                    }

                    if ($evaluacionDireccion['status'] === 'requires_authorization'
                        && $estadoCreditoRecredito !== CreditoEstado::RECHAZADO->value) {
                        $estadoCreditoRecredito = CreditoEstado::REQUIERE_AUTORIZACION->value;
                        $tipoRiesgo = CreditoEstado::REQUIERE_AUTORIZACION->value;
                        $mensajeAutorizacion = $evaluacionDireccion['message']
                            ?? 'Solicitud registrada con estado Requiere autorizacion por coincidencia de domicilio.';

                        if ($mensajeRecredito !== '' && $mensajeRecredito !== 'Recredito solicitado con exito.') {
                            $mensajeRecredito = $mensajeAutorizacion . ' Detalle previo: ' . $mensajeRecredito;
                        } else {
                            $mensajeRecredito = $mensajeAutorizacion;
                        }
                    }

                    $formulario = [
                        'cliente' => [
                            'curp' => $clienteRegistro->CURP,
                        ],
                        'aval' => [
                            'curp' => $avalCurp ?? '',
                        ],
                        'contacto' => $data['contacto'] ?? [],
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
                        'permitir_credito_moroso' => $decisionRiesgo === 'aceptar' && $clienteTieneDeuda,
                    ];

                    $resultadoFiltros = $this->filtrosController->evaluar($clienteRegistro, $formulario, $contexto);

                    if (!$resultadoFiltros['passed']) {
                        $mensajeFiltro = $resultadoFiltros['message'] ?? 'La solicitud no cumple con los criterios requeridos.';

                        return $request->expectsJson()
                            ? response()->json(['success' => false, 'message' => $mensajeFiltro], 422)
                            : back()->with('error', $mensajeFiltro)->withInput();
                    }
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
            DB::transaction(function () use ($data, $promotor, $isNewAval, $estadoCreditoRecredito) {
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
                    'estado' => $estadoCreditoRecredito,
                    'interes' => 0,
                    'periodicidad' => 'Mes',
                    'fecha_inicio' => now(),
                    'fecha_final' => now()->addWeeks(16),
                ]);

                Aval::create(array_merge($avalDataForCreation, ['credito_id' => $credito->id]));

                $cliente->update($this->withClienteEstado([
                    'tiene_credito_activo' => false,
                    'activo' => false,
                ], ClienteEstado::INACTIVO->value));

                if ($this->clienteEstadoColumn() === 'estatus') {
                    $cliente->setAttribute('cliente_estado', ClienteEstado::INACTIVO->value);
                }
            });

            $payload = [
                'success' => true,
                'message' => $mensajeRecredito,
                'estado_credito' => $estadoCreditoRecredito,
                'risk_type' => $tipoRiesgo,
                'cliente_tiene_deuda' => $clienteTieneDeuda,
                'cliente_moroso_bd' => $clienteMarcadoMoroso,
                'cliente_estado_bd' => $this->obtenerEstadoCliente($clienteRegistro),
                'deuda_cliente' => $registrosDeudaCliente,
                'aval_tiene_deuda' => $avalTieneDeuda,
                'deuda_aval' => $registrosDeudaAval,
            ];

            return $request->expectsJson()
                ? response()->json($payload)
                : redirect()->route('mobile.promotor.ingresar_cliente')->with('success', $mensajeRecredito);
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
                            'pagosProyectados' => function ($subQuery) {
                                $subQuery
                                    ->orderBy('semana')
                                    ->with([
                                        'pagosReales.pagoCompleto',
                                        'pagosReales.pagoAnticipo',
                                        'pagosReales.pagoDiferido',
                                    ]);
                            },
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
            $estadoCartera = $this->obtenerEstadoCliente($cliente)
                ?? $this->mapCreditoEstadoACartera($credito)
                ?? ClienteEstado::INACTIVO->value;

            $pagoPendiente = $credito?->pagosProyectados?->firstWhere('estado', 'pendiente');
            $pagoPendienteData = $this->buildPendingPaymentData($pagoPendiente, $cliente);

            $cliente->pago_proyectado_pendiente = $pagoPendienteData;
            if ($pagoPendienteData && (!isset($cliente->deuda_total) || $cliente->deuda_total === null)) {
                $cliente->deuda_total = $pagoPendienteData['deuda_vencida'];
            }

            $cliente->cliente_estado = $estadoCartera;
            $cliente->tiene_credito_activo = in_array($estadoCartera, [
                ClienteEstado::ACTIVO->value,
                ClienteEstado::MOROSO->value,
                ClienteEstado::DESEMBOLSADO->value,
            ], true);
            unset($cliente->semana_credito, $cliente->monto_semanal);

            if (in_array($estadoCartera, [
                ClienteEstado::ACTIVO->value,
                ClienteEstado::DESEMBOLSADO->value,
            ], true)) {
                if ($pagoPendiente) {
                    $cliente->semana_credito = $pagoPendiente->semana;
                    $cliente->monto_semanal = $pagoPendiente->monto_proyectado;
                }

                $activos->push($cliente);
                continue;
            }

            if ($estadoCartera === ClienteEstado::MOROSO->value) {
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

        $pagosReales = $pagoProyectado->relationLoaded('pagosReales')
            ? $pagoProyectado->pagosReales
            : $pagoProyectado->pagosReales()
                ->with(['pagoCompleto', 'pagoAnticipo', 'pagoDiferido'])
                ->get();

        $totalPagado = $pagosReales
            ->map(fn ($pagoReal) => (float) ($pagoReal->monto ?? 0))
            ->sum();

        $abonado = min($totalPagado, $montoProyectado);
        $adelantado = max(0.0, $totalPagado - $montoProyectado);

        $deudaVencida = collect([
            $pagoProyectado->deuda_total ?? null,
            $pagoProyectado->deuda_vencida ?? null,
            $cliente->deuda_total ?? null,
            $cliente->deuda ?? null,
        ])
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value)
            ->first();

        $deudaCalculada = max(0.0, $montoProyectado - $abonado);
        $tolerance = 0.01;

        if ($deudaVencida === null) {
            $deudaVencida = $deudaCalculada;
        } else {
            $deudaVencida = max(0.0, min((float) $deudaVencida, $montoProyectado));
            if ($deudaVencida + $tolerance < $deudaCalculada) {
                $deudaVencida = $deudaCalculada;
            }
        }

        if ($deudaVencida <= $tolerance) {
            $deudaVencida = 0.0;
        }

        return [
            'id' => $pagoProyectado->id,
            'monto_proyectado' => $montoProyectado,
            'abonado' => $abonado,
            'adelantado' => $adelantado,
            'pagado_total' => $totalPagado,
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

    /**
     * @param array<string, mixed>|null $contacto
     * @param int|null $clienteActualId Cliente al que pertenece la solicitud actual para excluirlo de la verificación
     * @return array{status: 'ok'|'blocked'|'requires_authorization', message: ?string, last_match_at: ?string}
     */
    private function evaluarRestriccionDireccion(?array $contacto, ?int $clienteActualId = null): array
    {
        $direccion = $this->normalizarDireccion($contacto);

        if (!$direccion['valida']) {
            return [
                'status' => 'ok',
                'message' => null,
                'last_match_at' => null,
            ];
        }

        $timestampColumn = Schema::hasColumn('datos_contacto', 'updated_at') ? 'updated_at' : 'creado_en';

        $contactoCoincidente = DatoContacto::query()
            ->select('datos_contacto.*')
            ->join('creditos', 'creditos.id', '=', 'datos_contacto.credito_id')
            ->whereNotNull('creditos.cliente_id')
            ->when($clienteActualId !== null, function ($query) use ($clienteActualId) {
                $query->where('creditos.cliente_id', '!=', $clienteActualId);
            })
            ->when($direccion['numero_int'] !== null, function ($query) use ($direccion) {
                $query->whereRaw('LOWER(TRIM(COALESCE(datos_contacto.numero_int, ""))) = ?', [$direccion['numero_int']]);
            })
            ->whereRaw('LOWER(TRIM(datos_contacto.calle)) = ?', [$direccion['calle']])
            ->whereRaw('LOWER(TRIM(datos_contacto.numero_ext)) = ?', [$direccion['numero_ext']])
            ->whereRaw('LOWER(TRIM(datos_contacto.colonia)) = ?', [$direccion['colonia']])
            ->whereRaw('LOWER(TRIM(datos_contacto.municipio)) = ?', [$direccion['municipio']])
            ->where('datos_contacto.cp', $direccion['cp'])
            ->orderByDesc('datos_contacto.' . $timestampColumn)
            ->first();

        if (!$contactoCoincidente) {
            return [
                'status' => 'ok',
                'message' => null,
                'last_match_at' => null,
            ];
        }

        $ultimaActualizacionRaw = $contactoCoincidente->{$timestampColumn};
        $ultimaActualizacion = $ultimaActualizacionRaw ? Carbon::parse($ultimaActualizacionRaw) : null;

        if (!$ultimaActualizacion) {
            return [
                'status' => 'blocked',
                'message' => 'No fue posible determinar la fecha de la última actualización del domicilio coincidente. Solicite autorización antes de continuar.',
                'last_match_at' => null,
            ];
        }

        $diasTranscurridos = $ultimaActualizacion->diffInDays(now());

        if ($diasTranscurridos < 42) {
            $diasEnteros = floor($diasTranscurridos);
            $mensajeDias = $diasEnteros === 1
                ? '1 día'
                : $diasEnteros . ' días';

            return [
                'status' => 'blocked',
                'message' => 'La dirección coincide con un crédito de otro cliente registrado hace ' . $mensajeDias . '. Deben transcurrir al menos 6 semanas desde la última actualización para registrar un nuevo crédito en este domicilio.',
                'last_match_at' => $ultimaActualizacion->toDateTimeString(),
            ];
        }

        return [
            'status' => 'requires_authorization',
            'message' => 'La dirección coincide con un crédito previo de otro cliente cuya última actualización fue hace más de 6 semanas (' . $ultimaActualizacion->toDateString() . '). Se registrará con estado Requiere autorización.',
            'last_match_at' => $ultimaActualizacion->toDateTimeString(),
        ];
    }

    /**
     * @param array<string, mixed>|null $contacto
     * @return array{valida: bool, calle: string, numero_ext: string, numero_int: ?string, colonia: string, municipio: string, cp: string}
     */
    private function normalizarDireccion(?array $contacto): array
    {
        $calle = $this->normalizarValorDireccion($contacto['calle'] ?? null);
        $numeroExt = $this->normalizarValorDireccion($contacto['numero_ext'] ?? null);
        $colonia = $this->normalizarValorDireccion($contacto['colonia'] ?? null);
        $municipio = $this->normalizarValorDireccion($contacto['municipio'] ?? null);
        $cp = isset($contacto['cp']) ? trim((string) $contacto['cp']) : '';
        $numeroInt = $this->normalizarValorDireccion($contacto['numero_int'] ?? null);

        $valida = $calle !== ''
            && $numeroExt !== ''
            && $colonia !== ''
            && $municipio !== ''
            && $cp !== '';

        return [
            'valida' => $valida,
            'calle' => $calle,
            'numero_ext' => $numeroExt,
            'numero_int' => $numeroInt !== '' ? $numeroInt : null,
            'colonia' => $colonia,
            'municipio' => $municipio,
            'cp' => $cp,
        ];
    }

    private function normalizarValorDireccion(mixed $valor): string
    {
        if (!is_string($valor)) {
            $valor = is_numeric($valor) ? (string) $valor : '';
        }

        return Str::of($valor)
            ->lower()
            ->squish()
            ->value();
    }

    private function extraerDireccionDesdeDatoContacto(?DatoContacto $datoContacto): array
    {
        if (!$datoContacto) {
            return [];
        }

        return [
            'calle' => $datoContacto->calle,
            'numero_ext' => $datoContacto->numero_ext,
            'numero_int' => $datoContacto->numero_int,
            'colonia' => $datoContacto->colonia,
            'municipio' => $datoContacto->municipio,
            'cp' => $datoContacto->cp,
        ];
    }

    private function clienteEstadoColumn(): ?string
    {
        if ($this->clienteEstadoColumnCache === null) {
            if (Schema::hasColumn('clientes', 'cliente_estado')) {
                $this->clienteEstadoColumnCache = 'cliente_estado';
            } elseif (Schema::hasColumn('clientes', 'estatus')) {
                $this->clienteEstadoColumnCache = 'estatus';
            } else {
                $this->clienteEstadoColumnCache = '';
            }
        }

        return $this->clienteEstadoColumnCache !== ''
            ? $this->clienteEstadoColumnCache
            : null;
    }

    private function withClienteEstado(array $attributes, string $estado): array
    {
        $column = $this->clienteEstadoColumn();

        if ($column) {
            $attributes[$column] = $estado;
        }

        return $attributes;
    }

    private function obtenerEstadoCliente(?Cliente $cliente): ?string
    {
        if (!$cliente) {
            return null;
        }

        $column = $this->clienteEstadoColumn();

        if (!$column) {
            return null;
        }

        $estado = $cliente->{$column} ?? null;

        if ($column === 'estatus' && $estado !== null) {
            $cliente->setAttribute('cliente_estado', $estado);
        }

        return $estado;
    }

    private function obtenerEstadoClientePorCurp(?string $curp): ?string
    {
        if (!$curp) {
            return null;
        }

        $column = $this->clienteEstadoColumn();

        if (!$column) {
            return null;
        }

        return DB::table('clientes')
            ->whereRaw('LOWER(CURP) = ?', [strtolower($curp)])
            ->value($column);
    }

    private function esEstadoMoroso(?string $estado): bool
    {
        if ($estado === null) {
            return false;
        }

        return strtolower($estado) === ClienteEstado::MOROSO->value;
    }

    /**
     * @param array<int, string> $fuentes
     */
    private function construirMensajeRiesgo(string $tipo, array $fuentes): string
    {
        $tipo = strtolower($tipo);

        if (empty($fuentes)) {
            return "Se detecto riesgo registrado para el {$tipo}.";
        }

        $fuentes = array_values(array_filter($fuentes));

        return 'Se detecto riesgo para el ' . $tipo . ' en ' . $this->formatearFuentesRiesgo($fuentes) . '.';
    }

    /**
     * @param array<string, array<string, string>> $riesgos
     */
    private function construirMensajeConfirmacionRiesgo(array $riesgos, string $tipoRiesgo): string
    {
        $mensajes = collect($riesgos)
            ->map(fn ($detalle) => $detalle['mensaje'] ?? null)
            ->filter()
            ->implode(' ');

        $mensajes = trim($mensajes);

        if ($mensajes === '') {
            $mensajes = 'Se detecto riesgo registrado para la solicitud.';
        }

        $estadoLegible = Str::of($tipoRiesgo)->replace('_', ' ')->title();

        return $mensajes . ' ¿Deseas continuar y registrar el credito como ' . $estadoLegible . '?';
    }

    /**
     * @param array<int, string> $fuentes
     */
    private function formatearFuentesRiesgo(array $fuentes): string
    {
        $total = count($fuentes);

        if ($total === 0) {
            return '';
        }

        if ($total === 1) {
            return $fuentes[0];
        }

        $ultimo = array_pop($fuentes);

        return implode(', ', $fuentes) . ' y ' . $ultimo;
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
                $query
                    ->orderBy('semana')
                    ->with([
                        'pagosReales' => function ($pagosQuery) {
                            $pagosQuery
                                ->orderBy('fecha_pago')
                                ->with(['pagoCompleto', 'pagoDiferido', 'pagoAnticipo']);
                        },
                    ]);
            },
        ]);

        $credito = $cliente->credito;
        $pagosProyectados = $credito?->pagosProyectados ?? collect();
        $now = now();

        $proximoPago = $pagosProyectados->first(function ($pago) use ($now) {
            $fechaLimite = $pago->fecha_limite instanceof Carbon
                ? $pago->fecha_limite
                : ($pago->fecha_limite ? Carbon::parse($pago->fecha_limite) : null);

            return $fechaLimite && $fechaLimite->endOfDay()->greaterThanOrEqualTo($now);
        });

        $semanaActual = $proximoPago?->semana ?? ($pagosProyectados->last()?->semana ?? null);

        $tablaDebeSemanal = $pagosProyectados
            ->map(function ($pago) {
                return [
                    'semana' => $pago->semana,
                    'monto' => (float) ($pago->monto_proyectado ?? 0),
                ];
            })
            ->values();

        $historialPagos = $pagosProyectados
            ->flatMap(function ($pagoProyectado) {
                return ($pagoProyectado->pagosReales ?? collect())->map(function ($pagoReal) use ($pagoProyectado) {
                    $tipo = strtolower((string) ($pagoReal->tipo ?? ''));
                    $fechaPago = $pagoReal->fecha_pago instanceof Carbon
                        ? $pagoReal->fecha_pago
                        : ($pagoReal->fecha_pago ? Carbon::parse($pagoReal->fecha_pago) : null);

                    $monto = (float) ($pagoReal->monto ?? 0);
                    if ($monto <= 0.0) {
                        return null;
                    }

                    $etiqueta = match ($tipo) {
                        'completo' => 'Pago',
                        'diferido' => 'Adelanto',
                        'anticipo' => 'Anticipo',
                        default => ucfirst($tipo ?: 'Pago'),
                    };

                    $color = match ($tipo) {
                        'anticipo' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                        'completo', 'diferido' => 'bg-green-100 text-green-800 border border-green-200',
                        default => 'bg-gray-100 text-gray-700 border border-gray-200',
                    };

                    return [
                        'id' => $pagoReal->id,
                        'semana' => $pagoProyectado->semana,
                        'tipo' => $tipo,
                        'etiqueta' => $etiqueta,
                        'monto' => $monto,
                        'fecha' => $fechaPago,
                        'fecha_texto' => $fechaPago?->format('Y-m-d'),
                        'clase' => $color,
                    ];
                })->filter();
            })
            ->filter()
            ->sortBy(fn ($entry) => $entry['fecha'] ?? Carbon::minValue())
            ->values();

        $dineroRecuperado = $historialPagos->sum('monto');

        $pagosHastaSemanaActual = $pagosProyectados->filter(function ($pago) use ($now, $semanaActual) {
            if ($semanaActual !== null && $pago->semana !== null) {
                return (int) $pago->semana <= (int) $semanaActual;
            }

            $fechaLimite = $pago->fecha_limite instanceof Carbon
                ? $pago->fecha_limite
                : ($pago->fecha_limite ? Carbon::parse($pago->fecha_limite) : null);

            return $fechaLimite
                ? $fechaLimite->endOfDay()->lessThanOrEqualTo($now)
                : false;
        });

        $proyectadoHastaSemanaActual = $pagosHastaSemanaActual->sum(function ($pago) {
            return (float) ($pago->monto_proyectado ?? 0);
        });

        $debeProyectado = $proyectadoHastaSemanaActual;
        $saldoContraProyeccion = max(0.0, $proyectadoHastaSemanaActual - $dineroRecuperado);

        $resumenFinanciero = [
            'prestamo' => (float) ($credito?->monto_total ?? 0),
            'recuperado' => $dineroRecuperado,
            'proyectado_hasta_hoy' => $proyectadoHastaSemanaActual,
            'debe_proyectado' => $debeProyectado,
            'saldo_proyectado' => $saldoContraProyeccion,
        ];

        return view('mobile.promotor.cartera.cliente_historial', [
            'cliente' => $cliente,
            'historialPagos' => $historialPagos,
            'resumenFinanciero' => $resumenFinanciero,
            'semanaActual' => $semanaActual,
            'tablaDebeSemanal' => $tablaDebeSemanal,
        ]);
    }

    private function mapCreditoEstadoACartera(?Credito $credito): ?string
    {
        if (!$credito) {
            return null;
        }

        $estado = is_string($credito->estado)
            ? CreditoEstado::tryFrom(strtolower($credito->estado))
            : null;

        return match ($estado) {
            CreditoEstado::DESEMBOLSADO => ClienteEstado::DESEMBOLSADO->value,
            CreditoEstado::VENCIDO => ClienteEstado::MOROSO->value,
            CreditoEstado::LIQUIDADO => ClienteEstado::REGULARIZADO->value,
            CreditoEstado::CANCELADO => ClienteEstado::INACTIVO->value,
            CreditoEstado::ACTIVO,
            CreditoEstado::PROSPECTADO,
            CreditoEstado::PROSPECTADO_REACREDITO,
            CreditoEstado::SOLICITADO,
            CreditoEstado::APROBADO,
            CreditoEstado::SUPERVISADO,
            CreditoEstado::CLIENTE_RIESGO,
            CreditoEstado::AVAL_RIESGO,
            CreditoEstado::CLIENTE_AVAL_RIESGO => ClienteEstado::ACTIVO->value,
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

        $estadoCartera = $this->obtenerEstadoCliente($cliente);
        if ($estadoCartera && in_array($estadoCartera, self::AVAL_CARTERA_STATUS_BLOCKLIST, true)) {
            return true;
        }

        return false;
    }
}
