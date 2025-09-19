<?php
namespace App\Http\Controllers;


use App\Models\Aval;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Promotor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PromotorController extends Controller
{
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
        $user = Auth::user();

        $user->load([
            'promotor.supervisor.ejecutivo.user',
            'promotor.clientes' => fn ($query) => $query->with('credito')->orderBy('nombre'),
        ]);

        $promotor = $user->promotor;
        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $supervisor = $promotor?->supervisor?->user?->name;
        $ejecutivo = $promotor?->supervisor?->ejecutivo?->user?->name;

        $clientes = $promotor?->clientes ?? collect();
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
            $promotor = Auth::user()->promotor;
            if (!$promotor) {
                Log::warning('Usuario sin perfil de promotor intento enviar ventas.', ['user_id' => Auth::id()]);
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

    public function storeCliente(Request $request)
    {
        try {
            $promotor = Promotor::where('user_id', Auth::id())->firstOrFail();

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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            Log::warning('Intento de creacion de cliente por usuario sin perfil de promotor.', ['user_id' => Auth::id()]);
            $message = 'No tienes un perfil de promotor asignado.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 403)
                : back()->with('error', $message);
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

    public function storeRecredito(Request $request)
    {
        $promotor = Auth::user()->promotor;
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

            $avalCurp = null;
            if ($isNewAval) {
                $avalCurp = $data['aval_CURP'];
            } else {
                $cliente = Cliente::where('CURP', $data['CURP'])->first();
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

        public function cartera()
    {
        $promotor = Auth::user()->promotor;
        if (!$promotor) {
            return view('mobile.promotor.cartera.cartera', [
                'activos' => collect(),
                'vencidos' => collect(),
                'inactivos' => collect(),
            ]);
        }

        $clientes = $promotor->clientes()
            ->with([
                'credito' => function ($query) {
                    $query->with([
                        'pagosProyectados' => fn ($subQuery) => $subQuery->orderBy('semana'),
                        'avales' => fn ($subQuery) => $subQuery->orderByDesc('id'),
                        'datoContacto',
                    ]);
                },
                'creditos' => function ($query) {
                    $query->with([
                        'avales' => fn ($subQuery) => $subQuery->orderByDesc('id'),
                        'datoContacto',
                    ])->orderByDesc('id')->limit(1);
                },
            ])
            ->orderBy('nombre')
            ->get();

        $activos = collect();
        $vencidos = collect();
        $inactivos = collect();

        foreach ($clientes as $cliente) {
            $credito = $cliente->credito;
            $estadoCartera = $cliente->cartera_estado ?? $this->mapCreditoEstadoACartera($credito) ?? 'inactivo';

            $cliente->cartera_estado = $estadoCartera;
            $cliente->tiene_credito_activo = in_array($estadoCartera, ['activo', 'moroso', 'desembolsado'], true);
            unset($cliente->semana_credito, $cliente->monto_semanal);

            if (in_array($estadoCartera, ['activo', 'desembolsado'], true)) {
                $pagoPendiente = $credito?->pagosProyectados?->firstWhere('estado', 'pendiente');
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
                'CURP' => $cliente->CURP,
                'curp' => $cliente->CURP,
                'direccion' => $clienteDireccion,
                'telefono' => $clienteTelefono,
                'fecha_ultimo_credito' => $fechaUltimoCredito ?: '',
                'cliente' => array_merge($clienteResumen, [
                    'nombre_completo' => $this->formatFullName(
                        $cliente->apellido_p,
                        $cliente->apellido_m,
                        $cliente->nombre
                    ),
                ]),
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
            ]);
        }

        return view('mobile.promotor.cartera.cartera', [
            'activos' => $activos->values(),
            'vencidos' => $vencidos->values(),
            'inactivos' => $inactivos->values(),
        ]);
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

    public function cliente_historial(Cliente $cliente)
    {
        if ($cliente->promotor_id !== Auth::user()->promotor?->id) {
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
