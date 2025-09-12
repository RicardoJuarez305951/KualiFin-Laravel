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

        // ---------- INICIA LA CORRECCIÓN ---------- //

        // La sintaxis correcta es pasar un solo array al método load().
        $user->load([
            'promotor.supervisor.ejecutivo.user', 
            'promotor.clientes' => function ($query) {
                $query->where('activo', 1)->where('tiene_credito_activo', 1);
            }
        ]);

        // ---------- TERMINA LA CORRECCIÓN ---------- //

        $promotor = $user->promotor;

        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        // Usamos el helper optional() para evitar errores si alguna relación es nula.
        $supervisor = optional(optional($promotor)->supervisor)->user;
        $ejecutivo = optional(optional(optional($promotor)->supervisor)->ejecutivo)->user;


        $clientes = $promotor?->clientes->map(function ($cliente) {
            $cliente->load('credito'); 
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
            $user = Auth::user();
            
            $promotor = $user->promotor;
            if (!$promotor) {
                Log::warning("Usuario sin perfil de promotor intentó enviar ventas.", ['user_id' => $user->id]);
                return response()->json(['success' => false, 'message' => 'Perfil de promotor no encontrado.'], 404);
            }

            DB::beginTransaction();
            
            Cliente::where('promotora_id', $promotor->id)
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
            Log::error('Error al enviar ventas: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Hubo un error al procesar la solicitud.'], 500);
        }
    }

    public function ingresar_cliente()
    {
        return view('mobile.promotor.venta.ingresar_cliente');
    }

    public function storeCliente(Request $request)
    {
        $promotor = Promotor::first();
        $data = $request->validate([
            'nombre' => 'required|string',
            'apellido_p' => 'required|string',
            'apellido_m' => 'nullable|string',
            'CURP' => 'required|string|size:18',
            'monto' => 'required|numeric|min:0|max:3000'
        ]);

        $cliente = Cliente::create([
            'promotor_id' => $promotor?->id,
            'CURP' => $data['CURP'],
            'nombre' => $data['nombre'],
            'apellido_p' => $data['apellido_p'],
            'apellido_m' => $data['apellido_m'] ?? '',
            'fecha_nacimiento' => now()->subYears(18),
            'tiene_credito_activo' => true,
            'estatus' => 'activo',
            'monto_maximo' => $data['monto'],
            'activo' => true,
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

        return redirect()->route('mobile.promotor.ingresar_cliente');
    }

    public function storeRecredito(Request $request)
    {
        $data = $request->validate([
            'CURP' => 'required|string|size:18|exists:clientes,CURP',
            'monto' => 'required|numeric|min:0|max:20000',
        ]);

        $cliente = Cliente::where('CURP', $data['CURP'])->first();

        Credito::create([
            'cliente_id' => $cliente->id,
            'monto_total' => $data['monto'],
            'estado' => 'pendiente',
            'interes' => 0,
            'periodicidad' => 'semanal',
            'fecha_inicio' => now(),
            'fecha_final' => now()->addMonths(12),
        ]);

        return redirect()->route('mobile.promotor.ingresar_cliente');
    }

    public function cartera()
    {
        return view('mobile.promotor.cartera.cartera');
    }

    public function cliente_historial()
    {
        return view('mobile.promotor.cartera.cliente_historial');
    }
}
