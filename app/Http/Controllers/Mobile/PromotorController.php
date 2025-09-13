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
            'promotor.clientes' => function ($query) {
                $query->where('activo', 1)
                      ->where('tiene_credito_activo', 1)
                      ->with('credito');
            }
        ]);

        $promotor = $user->promotor;
        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $supervisor = $promotor?->supervisor?->user?->name;
        $ejecutivo = $promotor?->supervisor?->ejecutivo?->user?->name;

        $clientes = $promotor?->clientes->map(function ($cliente) {
            $monto = $cliente->credito->monto_total ?? $cliente->monto_maximo;
            return [
                'nombre' => trim($cliente->nombre . ' ' . $cliente->apellido_p),
                'monto' => (float) $monto,
            ];
        }) ?? collect();

        $total = $clientes->sum('monto');

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
                    'tiene_credito_activo' => 0,
                    'estatus' => 'A supervision',
                    'activo' => 0,
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
                'monto' => 'required|numeric|min:0|max:3000'
            ]);

            DB::transaction(function () use ($data, $promotor) {
                $cliente = Cliente::create([
                    'promotor_id' => $promotor->id,
                    'CURP' => $data['CURP'],
                    'nombre' => $data['nombre'],
                    'apellido_p' => $data['apellido_p'],
                    'apellido_m' => $data['apellido_m'] ?? '',
                    'fecha_nacimiento' => now()->subYears(18),
                    'tiene_credito_activo' => true,
                    'estatus' => 'pendiente',
                    'monto_maximo' => $data['monto'],
                    'activo' => false,
                ]);

                Credito::create([
                    'cliente_id' => $cliente->id,
                    'monto_total' => $data['monto'],
                    'estado' => 'pendiente',
                    'interes' => 0,
                    'periodicidad' => 'semanal',
                    'fecha_inicio' => now(),
                    'fecha_final' => now()->addMonths(12),
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
        } catch (\Illuminate\Validation\ValidationException $e) {
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

        $data = $request->validate([
            'CURP' => 'required|string|size:18|exists:clientes,CURP',
            'monto' => 'required|numeric|min:0|max:20000',
        ]);

        try {
            DB::transaction(function () use ($data, $promotor) {
                $cliente = Cliente::where('CURP', $data['CURP'])->firstOrFail();

                if ($cliente->promotor_id !== $promotor->id) {
                    throw new \Exception('No autorizado para dar crédito a este cliente.');
                }

                Credito::create([
                    'cliente_id' => $cliente->id,
                    'monto_total' => $data['monto'],
                    'estado' => 'pendiente',
                    'interes' => 0,
                    'periodicidad' => 'semanal',
                    'fecha_inicio' => now(),
                    'fecha_final' => now()->addMonths(12),
                ]);
            });

            $message = 'Re-crédito asignado con éxito.';
            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->route('mobile.promotor.ingresar_cliente')->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error al asignar re-crédito: ' . $e->getMessage(), ['exception' => $e]);
            $message = 'No se pudo asignar el re-crédito. ' . $e->getMessage();
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : back()->with('error', $message);
        }
    }

    public function cartera()
    {
        $promotor = Auth::user()->promotor;
        $clientes = $promotor ? $promotor->clientes()->orderBy('nombre')->get() : collect();

        return view('mobile.promotor.cartera.cartera', compact('clientes'));
    }

    public function cliente_historial(Cliente $cliente)
    {
        if ($cliente->promotor_id !== Auth::user()->promotor?->id) {
            abort(403, 'No autorizado');
        }

        $cliente->load('creditos');

        return view('mobile.promotor.cartera.cliente_historial', compact('cliente'));
    }
}
