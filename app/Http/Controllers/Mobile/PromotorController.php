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

            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'apellido_p' => 'required|string|max:100',
                'apellido_m' => 'nullable|string|max:100',
                'CURP' => 'required|string|size:18|unique:clientes,CURP',
                'monto' => 'required|numeric|min:0|max:3000',
                'aval_nombre' => 'required|string|max:100',
                'aval_apellido_p' => 'required|string|max:100',
                'aval_apellido_m' => 'nullable|string|max:100',
                'aval_CURP' => 'required|string|size:18',
            ]);

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
            $message = 'Datos inválidos: ' . collect($e->errors())->flatten()->implode(' ');
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
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
        } catch (ValidationException $e) {
            $message = 'Datos inválidos: ' . collect($e->errors())->flatten()->implode(' ');
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->withErrors($e->errors())->withInput();
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
                    // Lógica para un Aval Nuevo
                    $avalCurp = $data['aval_CURP'];
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
                    
                    $avalCurp = $prevAval->CURP;
                    $avalDataForCreation = $prevAval->only([
                        'CURP', 'nombre', 'apellido_p', 'apellido_m', 'fecha_nacimiento', 
                        'direccion', 'telefono', 'parentesco'
                    ]);
                }
                
                $ultimoCreditoDelAval = Aval::ultimoCreditoActivo($avalCurp);
                if ($ultimoCreditoDelAval && $ultimoCreditoDelAval->credito && in_array($ultimoCreditoDelAval->credito->estado, ['activo', 'vigente', 'pendiente'])) {
                    throw new \Exception('El aval seleccionado ya está participando en otro crédito activo o pendiente.');
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

    // Si no hay promotor, regresa colecciones vacías
    if (!$promotor) {
        $activos = collect();
        $vencidos = collect();
        $inactivos = collect();

        return view('mobile.promotor.cartera.cartera', compact('activos', 'vencidos', 'inactivos'));
    }

    // Cargamos clientes con su crédito y pagos proyectados ordenados por semana
    $clientes = $promotor->clientes()
        ->with([
            'credito.pagosProyectados' => fn ($q) => $q->orderBy('semana'),
        ])
        ->orderBy('nombre')
        ->get();

    $activos = collect();
    $vencidos = collect();
    $inactivos = collect();

    foreach ($clientes as $cliente) {
        $credito = $cliente->credito;

        // Default/compatibilidad con vistas que usan flags
        $cliente->tiene_credito_activo = false;
        $cliente->estatus = 'inactivo';
        unset($cliente->semana_credito, $cliente->monto_semanal);

        if ($credito) {
            // Normalizamos estatus a partir del crédito
            // estados esperados: 'activo', 'mora', otros -> 'inactivo'
            $estado = $credito->estado;

            if ($estado === 'activo') {
                $pagoPendiente = $credito->pagosProyectados
                    ? $credito->pagosProyectados->firstWhere('estado', 'pendiente')
                    : null;

                if ($pagoPendiente) {
                    // Datos útiles para la vista
                    $cliente->semana_credito = $pagoPendiente->semana;
                    $cliente->monto_semanal  = $pagoPendiente->monto_proyectado;
                }

                $cliente->tiene_credito_activo = true;
                $cliente->estatus = 'activo';
                $activos->push($cliente);
                continue;
            }

            if ($estado === 'mora') {
                $cliente->tiene_credito_activo = true; // sigue teniendo crédito, solo que vencido
                $cliente->estatus = 'vencido';
                $vencidos->push($cliente);
                continue;
            }
        }

        // Sin crédito o estado no reconocido => inactivo
        $inactivos->push($cliente);
    }

    // Opcional: reindexar
    $activos   = $activos->values();
    $vencidos  = $vencidos->values();
    $inactivos = $inactivos->values();

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
