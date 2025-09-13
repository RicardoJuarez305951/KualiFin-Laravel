<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Promotor;
use App\Models\Cliente;
use App\Models\Credito;
use Illuminate\Http\Request;    
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Models\Aval;
use Illuminate\Validation\ValidationException;

class PromotorController extends Controller
{
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
            'promotor.clientes' => fn ($q) => $q->with('credito'),
        ]);

        $promotor = $user->promotor;
        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $supervisor = $promotor?->supervisor?->user?->name;
        $ejecutivo = $promotor?->supervisor?->ejecutivo?->user?->name;

        $clientes = $promotor?->clientes ?? collect();

        $total = $clientes->sum(fn ($c) => $c->credito->monto_total ?? $c->monto_maximo);

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
                Log::warning("Usuario sin perfil de promotor intentó enviar ventas.", ['user_id' => Auth::id()]);
                return response()->json(['success' => false, 'message' => 'Perfil de promotor no encontrado.'], 404);
            }

            DB::beginTransaction();
            
            Cliente::where('promotor_id', $promotor->id)
                ->where('activo', 1)
                ->update([
                    'tiene_credito_activo' => false,
                    'estatus' => 'a_supervision',
                    'activo' => false,
                ]);
            
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Ventas enviadas a supervisión correctamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al enviar ventas: ' . $e->getMessage(), ['exception' => $e]);
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

            // --- INICIO DE VALIDACIONES ---
            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'apellido_p' => 'required|string|max:100',
                'apellido_m' => 'nullable|string|max:100',
                // Se quita la regla 'unique' para personalizar el mensaje de error.
                'CURP' => 'required|string|size:18',
                'monto' => 'required|numeric|min:0|max:3000',
                'aval_nombre' => 'required|string|max:100',
                'aval_apellido_p' => 'required|string|max:100',
                'aval_apellido_m' => 'nullable|string|max:100',
                'aval_CURP' => 'required|string|size:18',
            ]);

            // 1. Validación manual para CURP de cliente duplicado.
            $clienteExistente = Cliente::where('CURP', $data['CURP'])->first();
            if ($clienteExistente) {
                throw ValidationException::withMessages([
                    'CURP' => 'La CURP ingresada ya está registrada para otro cliente.',
                ]);
            }

            // 2. Validación manual para el CURP del aval.
            $avalComoCliente = Cliente::where('CURP', $data['aval_CURP'])->first();
            if ($avalComoCliente) {
                // Un cliente no puede ser aval si tiene un crédito activo O una solicitud en proceso.
                $estatusNoPermitidos = ['pendiente', 'a_supervision', 'pendiente_recredito', 'activo', 'vencido'];
                if ($avalComoCliente->tiene_credito_activo || in_array($avalComoCliente->estatus, $estatusNoPermitidos)) {
                    throw ValidationException::withMessages([
                        'aval_CURP' => 'El aval es un cliente con un crédito activo o en proceso y no puede ser garante.',
                    ]);
                }
            }
            // --- FIN DE VALIDACIONES ---

            DB::transaction(function () use ($data, $promotor) {
                $cliente = Cliente::create([
                    'promotor_id' => $promotor->id,
                    'CURP' => $data['CURP'],
                    'nombre' => $data['nombre'],
                    'apellido_p' => $data['apellido_p'],
                    'apellido_m' => $data['apellido_m'] ?? '',
                    'fecha_nacimiento' => now()->subYears(18),
                    'tiene_credito_activo' => false,
                    'estatus' => 'pendiente',
                    'monto_maximo' => $data['monto'],
                    'activo' => false,
                ]);

                $credito = Credito::create([
                    'cliente_id' => $cliente->id,
                    'monto_total' => $data['monto'],
                    'estado' => 'pendiente',
                    'interes' => 0,
                    'periodicidad' => 'semanal',
                    'fecha_inicio' => now(),
                    'fecha_final' => now()->addMonths(12),
                ]);

                Aval::create([
                    'CURP' => $data['aval_CURP'],
                    'credito_id' => $credito->id,
                    'nombre' => $data['aval_nombre'],
                    'apellido_p' => $data['aval_apellido_p'],
                    'apellido_m' => $data['aval_apellido_m'] ?? '',
                    'fecha_nacimiento' => now()->subYears(25), // Placeholder
                    'direccion' => 'Desconocida', // Placeholder
                    'telefono' => 'N/A', // Placeholder
                    'parentesco' => 'Desconocido', // Placeholder
                ]);
            });

            $message = 'Cliente creado con éxito.';
            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->route('mobile.promotor.ingresar_cliente')->with('success', $message);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Intento de creación de cliente por usuario sin perfil de promotor.', ['user_id' => Auth::id()]);
            $message = 'No tienes un perfil de promotor asignado.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 403)
                : back()->with('error', $message);
        } catch (ValidationException $e) {
            Log::warning('Error de validación al crear cliente.', ['errors' => $e->errors(), 'user_id' => Auth::id()]);
            
            // Se prioriza mostrar los mensajes de error personalizados para CURP y aval_CURP.
            $customMessage = $e->validator->errors()->first('CURP') 
                ?: $e->validator->errors()->first('aval_CURP') 
                ?: 'Datos inválidos: ' . collect($e->errors())->flatten()->implode(' ');

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $customMessage], 422)
                : back()->with('error', $customMessage)->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error al crear cliente: ' . $e->getMessage(), ['exception' => $e]);
            $message = 'No se pudo crear el cliente. Inténtalo de nuevo.';
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
    
        // --- INICIO DE VALIDACIONES ---
        $rules = [
            // 1. Se mantiene la validación para asegurar que el cliente exista.
            'CURP' => 'required|string|size:18|exists:clientes,CURP',
            'monto' => 'required|numeric|min:0|max:20000',
            'r_newAval' => 'required|boolean',
        ];
    
        if ($isNewAval) {
            $rules = array_merge($rules, [
                'aval_nombre' => 'required|string|max:100',
                'aval_apellido_p' => 'required|string|max:100',
                'aval_apellido_m' => 'nullable|string|max:100',
                // 1. El CURP del nuevo aval no debe existir como cliente.
                'aval_CURP' => 'required|string|size:18',
            ]);
        }
        
        try {
            $data = $request->validate($rules);

            // 2. Validación manual para el aval (nuevo o existente).
            $avalCurp = null;
            if ($isNewAval) {
                $avalCurp = $data['aval_CURP'];
            } else {
                $cliente = Cliente::where('CURP', $data['CURP'])->first();
                if ($cliente) {
                    $prevAval = Aval::whereHas('credito', fn($q) => $q->where('cliente_id', $cliente->id))
                                    ->latest('creado_en')->first();
                    if ($prevAval) {
                        $avalCurp = $prevAval->CURP;
                    }
                }
            }

            if ($avalCurp) {
                $avalComoCliente = Cliente::where('CURP', $avalCurp)->first();
                if ($avalComoCliente) {
                    // Un cliente no puede ser aval si tiene un crédito activo O una solicitud en proceso.
                    $estatusNoPermitidos = ['pendiente', 'a_supervision', 'pendiente_recredito', 'activo', 'vencido'];
                    if ($avalComoCliente->tiene_credito_activo || in_array($avalComoCliente->estatus, $estatusNoPermitidos)) {
                        throw ValidationException::withMessages([
                            'aval_CURP' => 'El aval es un cliente con un crédito activo o en proceso y no puede ser garante.',
                        ]);
                    }
                }
            }
            // --- FIN DE VALIDACIONES ---

        } catch (ValidationException $e) {
            $customMessage = $e->validator->errors()->first('aval_CURP') ?: 'Datos inválidos: ' . collect($e->errors())->flatten()->implode(' ');
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $customMessage], 422)
                : back()->withErrors($e->errors())->withInput()->with('error', $customMessage);
        }
    
        try {
            DB::transaction(function () use ($data, $promotor, $isNewAval) {
                $cliente = Cliente::where('CURP', $data['CURP'])->firstOrFail();
    
                if ($cliente->promotor_id !== $promotor->id) {
                    throw new \Exception('No estás autorizado para otorgar un recrédito a este cliente.');
                }
    
                if ($cliente->tiene_credito_activo) {
                     throw new \Exception('El cliente ya tiene un crédito activo o en proceso.');
                }
    
                $avalDataForCreation = [];
    
                if ($isNewAval) {
                    $avalDataForCreation = [
                        'CURP' => $data['aval_CURP'],
                        'nombre' => $data['aval_nombre'],
                        'apellido_p' => $data['aval_apellido_p'],
                        'apellido_m' => $data['aval_apellido_m'] ?? null,
                        'fecha_nacimiento' => now()->subYears(25), // Placeholder
                        'direccion' => 'Desconocida', // Placeholder
                        'telefono' => 'N/A', // Placeholder
                        'parentesco' => 'Desconocido', // Placeholder
                    ];
                } else {
                    $prevAval = Aval::whereHas('credito', fn($q) => $q->where('cliente_id', $cliente->id))
                                    ->latest('creado_en')->first();
    
                    if (!$prevAval) {
                        throw new \Exception('No se encontró un aval previo para este cliente. Debe registrar uno nuevo.');
                    }
                    
                    $avalDataForCreation = $prevAval->only([
                        'CURP', 'nombre', 'apellido_p', 'apellido_m', 'fecha_nacimiento', 
                        'direccion', 'telefono', 'parentesco'
                    ]);
                }
                
                $credito = Credito::create([
                    'cliente_id' => $cliente->id,
                    'monto_total' => $data['monto'],
                    'estado' => 'pendiente',
                    'interes' => 0,
                    'periodicidad' => 'mensual',
                    'fecha_inicio' => now(),
                    'fecha_final' => now()->addWeeks(16),
                ]);
    
                Aval::create(array_merge($avalDataForCreation, ['credito_id' => $credito->id]));
                
                $cliente->update([
                    'tiene_credito_activo' => false,
                    'estatus' => 'pendiente_recredito',
                    'activo' => false,
                ]);
            });
    
            $message = 'Recrédito solicitado con éxito.';
            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->route('mobile.promotor.ingresar_cliente')->with('success', $message);
    
        } catch (\Exception $e) {
            Log::error('Error al procesar recrédito: ' . $e->getMessage(), [
                'user_id' => Auth::id(), 'request' => $request->all(), 'exception' => $e
            ]);
            
            $userMessage = 'No se pudo procesar el recrédito. ' . $e->getMessage();
            
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $userMessage], 500)
                : back()->with('error', $userMessage)->withInput();
        }
    }

    public function cartera()
    {
        $promotor = Auth::user()->promotor;
        $clientes = $promotor
            ? $promotor->clientes()
                ->with(['credito.pagosProyectados' => fn ($q) => $q->orderBy('semana')])
                ->orderBy('nombre')
                ->get()
            : collect();

        $activos = collect();
        $vencidos = collect();
        $inactivos = collect();

        foreach ($clientes as $cliente) {
            $credito = $cliente->credito;

            if ($credito && $credito->estado === 'activo') {
                $pagoPendiente = $credito->pagosProyectados->firstWhere('estado', 'pendiente');

                if ($pagoPendiente) {
                    $cliente->semana_credito = $pagoPendiente->semana;
                    $cliente->monto_semanal = $pagoPendiente->monto_proyectado;
                    $activos->push($cliente);
                    continue;
                }
            }

            if ($credito && $credito->estado === 'mora') {
                $vencidos->push($cliente);
            } else {
                $inactivos->push($cliente);
            }
        }

      return view('mobile.promotor.cartera.cartera', compact('activos', 'vencidos', 'inactivos'));
    }

    public function cliente_historial(Cliente $cliente)
    {
        if ($cliente->promotor_id !== Auth::user()->promotor?->id) {
            abort(403, 'No autorizado');
        }

        $cliente->load('credito.pagosProyectados');

        return view('mobile.promotor.cartera.cliente_historial', compact('cliente'));
    }
}

