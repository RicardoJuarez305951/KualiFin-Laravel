{{-- resources/views/mobile/supervisor/venta/venta.blade.php --}}
@php
    use Carbon\Carbon;

    /** Semilla para datos demo (cámbiala si quieres otra corrida) */
    if (!defined('__VENTA_SUP_FAKE_SEED__')) {
        define('__VENTA_SUP_FAKE_SEED__', true);
        mt_srand(20250918);
    }

    /** @var string $role */
    $role = isset($role) && $role ? $role : 'supervisor';

    function formatCurrency($v) {
        return '$' . number_format((float)$v, 2, '.', ',');
    }

    /** ===================== FAKE GLOBALS (solo si no vienen del back) ===================== */
    // Dinero actual / objetivo de la semana
    if (!isset($moneyWeeklyTarget)) {
        $moneyWeeklyTarget = mt_rand(120_000, 250_000);
    }
    if (!isset($moneyWeeklyNow)) {
        // que sea coherente (menor o igual al objetivo)
        $moneyWeeklyNow = mt_rand((int)($moneyWeeklyTarget * 0.25), (int)($moneyWeeklyTarget * 0.9));
    }

    // Fecha límite (fin de semana)
    if (!isset($fechaLimite)) {
        $fechaLimite = Carbon::now()->endOfWeek(Carbon::FRIDAY)->format('d/m/y');
    }

    // Progreso (0–100)
    if (!isset($moneyProgress)) {
        $moneyProgress = $moneyWeeklyTarget > 0
            ? min(100, round(($moneyWeeklyNow / $moneyWeeklyTarget) * 100, 2))
            : 0;
    }

    // Contadores generales
    if (!isset($clientesProspectados))   $clientesProspectados   = mt_rand(15, 80);
    if (!isset($clientesPorSupervisar))  $clientesPorSupervisar  = mt_rand(5, 25);

    /** ===================== FAKE LISTA DE PROMOTORES SUPERVISADOS ===================== */
    if (!isset($promotoresSupervisados) || empty($promotoresSupervisados)) {
        $n = mt_rand(3, 6);
        $nombres = [
            'Ana Torres', 'Luis García', 'María López', 'Carlos Díaz',
            'Sofía Martínez', 'Jorge Ramírez', 'Elena Ruiz', 'Diego Herrera'
        ];

        $promotoresSupervisados = [];
        for ($i = 1; $i <= $n; $i++) {
            $nombre = $nombres[array_rand($nombres)];
            $debe   = mt_rand(15_000, 80_000);
            $falla  = mt_rand(0, (int)($debe * 0.35));
            $venta  = mt_rand(8_000, (int)max(8_000, $debe - $falla + mt_rand(0, 5_000)));
            $porcF  = $debe > 0 ? min(100, round(($falla / $debe) * 100, 2)) : 0;

            $prospectados  = [];
            $porSupervisar = [];
            $cats = ['Tienda', 'Mercado', 'Vecindad', 'Oficina', ' Taller', 'Online'];

            $nPros = mt_rand(3, 6);
            for ($j = 0; $j < $nPros; $j++) {
                $prospectados[] = "Cliente " . mt_rand(100, 999) . " (" . $cats[array_rand($cats)] . ")";
            }

            $nPS = mt_rand(2, 5);
            for ($k = 0; $k < $nPS; $k++) {
                $porSupervisar[] = "Cliente " . mt_rand(100, 999) . " (" . $cats[array_rand($cats)] . ")";
            }

            $promotoresSupervisados[] = [
                'id'                => $i,
                'nombre'            => $nombre,
                'debe'              => $debe,
                'falla'             => $falla,
                'porcentajeFalla'   => $porcF,
                'ventaRegistrada'   => $venta,
                'prospectados'      => $prospectados,
                'porSupervisar'     => $porSupervisar,
            ];
        }
    }
@endphp

<x-layouts.mobile.mobile-layout title="Venta - Supervisor">
  <div class="max-w-sm mx-auto space-y-6">
    
    {{-- Objetivos del supervisor --}}
    <div class="bg-white rounded-2xl shadow-md p-6 grid grid-cols-2 gap-4 text-center">
        <div>
            <p class="text-gray-500 text-sm">Dinero Actual</p>
            <p class="font-bold text-blue-600">
                {{ formatCurrency($moneyWeeklyNow) }}
            </p>
            <p class="text-gray-500 text-sm mt-2">Dinero Objetivo</p>
            <p class="font-bold text-red-600">
                {{ formatCurrency($moneyWeeklyTarget) }}
            </p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Fecha Límite</p>
            <p class="font-semibold text-yellow-600">
                {{ $fechaLimite }}
            </p>
            <div class="w-full bg-gray-200 rounded-full h-3 mt-4">
                <div class="bg-green-500 h-3 rounded-full" style="width: {{ $moneyProgress }}%;"></div>
            </div>
            <p class="text-xs mt-1 font-semibold">
                {{ number_format($moneyProgress, 0) }}% completado
            </p>
        </div>
    </div>

    {{-- Datos generales --}}
    <div class="bg-white rounded-2xl shadow-md p-6">
        <ul class="divide-y divide-gray-200">
            <li class="flex items-center justify-between py-2">
                <div>
                    <p class="text-gray-500 text-sm">Prospectados</p>
                    <p class="text-xl font-bold">{{ $clientesProspectados }}</p>
                </div>
                {{-- <a href="{{ route("mobile.supervisor.clientes_prospectados") }}" class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded">D</a> --}}
                <a href="{{ route("mobile.supervisor.clientes_prospectados") }}" class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded">D</a>
            </li>
            <li class="flex items-center justify-between py-2">
                <div>
                    <p class="text-gray-500 text-sm">Por Supervisar</p>
                    <p class="text-xl font-bold">{{ $clientesPorSupervisar }}</p>
                </div>
                <a href="{{ route("mobile.supervisor.clientes_supervisados") }}" class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded">D</a>
            </li>
        </ul>
    </div>

    {{-- Promotores bajo supervisión --}}
    <div class="bg-white rounded-2xl shadow-md p-6 space-y-4">
        <h2 class="text-lg font-semibold mb-2">Promotores Supervisados</h2>
        @foreach($promotoresSupervisados as $p)
            <div class="border rounded-lg p-4 space-y-3">
                <p class="font-semibold">{{ $p['nombre'] }}</p>
                <div class="text-sm flex justify-between">
                    <span>Debe:
                        <span class="font-bold text-red-600">{{ formatCurrency($p['debe']) }}</span>
                    </span>
                    <span>Falla:
                        <span class="font-bold text-yellow-600">{{ formatCurrency($p['falla']) }}</span>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ min(100, $p['porcentajeFalla']) }}%"></div>
                </div>
                <div class="flex">
                    <div class="w-80">
                        <p class="text-xs font-semibold">{{ number_format($p['porcentajeFalla'], 0) }}% Falla</p>
                        <p class="text-sm">
                            Venta Registrada:
                            <span class="font-bold">{{ formatCurrency($p['ventaRegistrada']) }}</span>
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('mobile.supervisor.cartera_promotor', ['promotor' => $p['id']]) }}"
                           class="px-3 py-1 text-right text-sm font-semibold text-white bg-blue-600 rounded">
                          D
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <p class="font-semibold mb-1">Prospectados</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($p['prospectados'] as $c)
                                <li>{{ $c }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold mb-1">Por Supervisar</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($p['porSupervisar'] as $c)
                                <li>{{ $c }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Botones finales --}}
    <a href="{{ route("mobile.$role.horarios") }}"
       class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
      Horarios
    </a>
    <a href="{{ route("mobile.$role.index") }}"
       class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
      Regresar
    </a>
  </div>
</x-layouts.mobile.mobile-layout>
