{{-- resources/views/mobile/supervisor/venta/venta.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    // Datos generales
    $clientesProspectados = $faker->numberBetween(10, 50);
    $clientesPorSupervisar = $faker->numberBetween(1, 10);
    $montoVenta = $faker->randomFloat(2, 50000, 200000);
    $inversionRequerida = $faker->randomFloat(2, 20000, 80000);
    
    // Objetivos
    $moneyWeeklyNow = $faker->randomFloat(2, 2000, 10000);
    $fechaLimite = $faker->dateTimeBetween('now', '+1 week')->format('d/m/Y');
    $moneyWeeklyTarget = 10000.00;
    $moneyProgress = min(100, ($moneyWeeklyNow / $moneyWeeklyTarget) * 100);

    // Prospectados generales
    $prospectosGenerales = collect(range(1, 6))->map(fn() => [
        'nombre' => $faker->name(),
        'plaza'  => $faker->city(),
        'alerta' => $faker->boolean(20) // 20% con alerta
    ]);

    // Prospectos por promotora
    $prospectosPorPromotora = collect(range(1, 3))->map(fn($i) => [
        'promotora' => $faker->name(),
        'prospectos' => collect(range(1, $faker->numberBetween(2, 5)))->map(fn() => [
            'nombre' => $faker->name(),
            'alerta' => $faker->boolean(20)
        ])
    ]);

    // Clientes para supervisión esta semana
    $paraSupervision = collect(range(1, 4))->map(fn() => [
        'nombre' => $faker->name(),
        'direccion' => $faker->streetAddress() . ', ' . $faker->city(),
        'fecha' => now()->addDays($faker->numberBetween(0, 6))->format('d/m/Y'),
        'alerta' => $faker->boolean(30)
    ]);

    function formatCurrency($v) {
        return '$' . number_format($v, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout title="Venta - Supervisor">
  <div class="max-w-md mx-auto space-y-6">
    
    {{-- Objetivos del supervisor --}}
    <div class="bg-white rounded-2xl shadow-md p-6 grid grid-cols-2 gap-4 text-center">
        <div>
            <p class="text-gray-500 text-sm">
                Dinero Actual
            </p>
            <p class="font-bold text-blue-600">
                {{ formatCurrency($moneyWeeklyNow) }}
            </p>
            <p class="text-gray-500 text-sm">
                Dinero Objetivo
            </p>
            <p class="font-bold text-red-600">
                {{ formatCurrency($moneyWeeklyTarget) }}
            </p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">
                Fecha Limite
            </p>
            <p class="font-semibold text-yellow-600">
                {{ $fechaLimite }}
            </p>
            <div class="w-full bg-gray-200 rounded-full h-3 mt-4">
                <div
                    class="bg-green-500 h-3 rounded-full"
                    style="width: {{ $moneyProgress }}%;">
                </div>
            </div>
            <p class="text-xs mt-1 font-semibold">
                {{ number_format($moneyProgress, 0) }}% completado
            </p>
        </div>
    </div>

    {{-- Datos generales --}}
    <div class="bg-white rounded-2xl shadow-md p-6 grid grid-cols-2 gap-4 text-center">
        <div>
            <p class="text-gray-500 text-sm">Prospectados</p>
            <p class="text-xl font-bold">{{ $clientesProspectados }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Por Supervisar</p>
            <p class="text-xl font-bold">{{ $clientesPorSupervisar }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Monto Venta</p>
            <p class="text-xl font-bold text-green-600">{{ formatCurrency($montoVenta) }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Inversión Req.</p>
            <p class="text-xl font-bold text-blue-600">{{ formatCurrency($inversionRequerida) }}</p>
        </div>
    </div>

    {{-- Prospectados generales --}}
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-lg font-semibold mb-3">Prospectados - General</h2>
        <ul class="divide-y divide-gray-200">
            @foreach($prospectosGenerales as $p)
                <li class="flex justify-between py-2">
                    <div>
                        <p class="font-medium">{{ $p['nombre'] }}</p>
                        <p class="text-sm text-gray-500">{{ $p['plaza'] }}</p>
                    </div>
                    @if($p['alerta'])
                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">⚠ Alerta</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Prospectados por promotora --}}
    <div class="bg-white rounded-2xl shadow-md p-6 space-y-4">
        <h2 class="text-lg font-semibold">Prospectados por Promotora</h2>
        @foreach($prospectosPorPromotora as $grupo)
            <div>
                <p class="font-semibold text-blue-800">{{ $grupo['promotora'] }}</p>
                <ul class="ml-4 mt-2 space-y-1">
                    @foreach($grupo['prospectos'] as $pros)
                        <li class="flex justify-between">
                            <span>{{ $pros['nombre'] }}</span>
                            @if($pros['alerta'])
                                <span class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded">⚠</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    {{-- Para supervisión --}}
    <div class="bg-white rounded-2xl shadow-md p-6 space-y-3">
        <h2 class="text-lg font-semibold">Para Supervisión (Semana Actual)</h2>
        @foreach($paraSupervision as $c)
            <div class="border rounded-lg p-3">
                <a href="{{ route("mobile.$role.index") }}"
                <p class="font-medium">{{ $c['nombre'] }}</p>
                <p class="text-sm text-gray-500">{{ $c['direccion'] }}</p>
                <p class="text-xs text-gray-400">Fecha: {{ $c['fecha'] }}</p>
                @if($c['alerta'])
                    <span class="inline-block mt-1 px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded">⚠ Supervisar urgente</span>
                @endif
                </a>
            </div>
        @endforeach
    </div>


    {{-- Botón regresar --}}
    <a href="{{ route("mobile.$role.index") }}"
       class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
      ← Regresar
    </a>
  </div>
</x-layouts.mobile.mobile-layout>
