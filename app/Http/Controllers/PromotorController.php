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
            ->with(['credito.pagosProyectados' => fn ($query) => $query->orderBy('semana')])
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

            $inactivos->push($cliente);
        }

        return view('mobile.promotor.cartera.cartera', [
            'activos' => $activos->values(),
            'vencidos' => $vencidos->values(),
            'inactivos' => $inactivos->values(),
        ]);
    }

    public function cliente_historial(Cliente $cliente)
    {
        if ($cliente->promotor_id !== Auth::user()->promotor?->id) {
            abort(403, 'No autorizado');
        }

        $cliente->load('credito.pagosProyectados');

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







