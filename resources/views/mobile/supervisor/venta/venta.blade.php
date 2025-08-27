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

    // Promotores bajo supervisión
    $promotoresSupervisados = collect(range(1, 3))->map(function () use ($faker) {
        $debe = $faker->randomFloat(2, 10000, 50000);
        $falla = $faker->randomFloat(2, 0, $debe);
        return [
            'nombre' => $faker->name(),
            'debe' => $debe,
            'falla' => $falla,
            'porcentajeFalla' => $debe > 0 ? ($falla / $debe) * 100 : 0,
            'ventaRegistrada' => $faker->randomFloat(2, 5000, 30000),
            'prospectados' => collect(range(1, $faker->numberBetween(2, 5)))->map(fn() => $faker->name()),
            'porSupervisar' => collect(range(1, $faker->numberBetween(1, 4)))->map(fn() => $faker->name()),
        ];
    });
    
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
    <div class="bg-white rounded-2xl shadow-md p-6">
        <ul class="divide-y divide-gray-200">
            <li class="flex items-center justify-between py-2">
                <div>
                    <p class="text-gray-500 text-sm">Prospectados</p>
                    <p class="text-xl font-bold">{{ $clientesProspectados }}</p>
                </div>
                <a href="#" class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded">Ver</a>
            </li>
            <li class="flex items-center justify-between py-2">
                <div>
                    <p class="text-gray-500 text-sm">Por Supervisar</p>
                    <p class="text-xl font-bold">{{ $clientesPorSupervisar }}</p>
                </div>
                <a href="#" class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded">Ver</a>
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
                    <span>Debe: <span class="font-bold text-red-600">{{ formatCurrency($p['debe']) }}</span></span>
                    <span>Falla: <span class="font-bold text-yellow-600">{{ formatCurrency($p['falla']) }}</span></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ min(100, $p['porcentajeFalla']) }}%"></div>
                </div>
                <p class="text-xs font-semibold">{{ number_format($p['porcentajeFalla'], 0) }}% Falla</p>
                <p class="text-sm">Venta Registrada: <span class="font-bold">{{ formatCurrency($p['ventaRegistrada']) }}</span></p>
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

    {{-- Promotores Bajo Supervision --}}
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-lg font-semibold mb-3">Promotores Supervisados</h2>
        <ul class="divide-y divide-gray-200">
            <li class="flex items-center justify-between py-2">
                <div>
                    <p class="text-gray-500 text-sm">Lista de Promotores</p>
                </div>
            </li>
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

    {{-- Botón regresar --}}
    <a href="{{ route("mobile.$role.index") }}"
       class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
      ← Regresar
    </a>
  </div>
</x-layouts.mobile.mobile-layout>
