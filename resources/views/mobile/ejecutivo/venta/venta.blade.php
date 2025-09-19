{{-- resources/views/mobile/supervisor/venta/venta.blade.php --}}
@php
    /** @var string $role */
    $role = isset($role) && $role ? $role : 'supervisor';

    function formatCurrency($v) {
        return '$' . number_format($v, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout title="Venta - Supervisor">
  <div class="max-w-sm mx-auto space-y-6">
    
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
                <a href="{{ route("mobile.$role.clientes_prospectados") }}" class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded">D</a>
            </li>
            <li class="flex items-center justify-between py-2">
                <div>
                    <p class="text-gray-500 text-sm">Por Supervisar</p>
                    <p class="text-xl font-bold">{{ $clientesPorSupervisar }}</p>
                </div>
                <a href="{{ route("mobile.$role.clientes_supervisados") }}" class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded">D</a>
            </li>
        </ul>
    </div>

    {{-- Promotores bajo supervisiÃ³n --}}
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
                 <div class="flex">
                    <div class="w-80">
                        <p class="text-xs font-semibold">{{ number_format($p['porcentajeFalla'], 0) }}% Falla</p>
                        <p class="text-sm">Venta Registrada: <span class="font-bold">{{ formatCurrency($p['ventaRegistrada']) }}</span></p>
                    </div>
                    <div>
                        {{-- <a href="{{ route('mobile.supervisor.cartera_promotor', $p->id) }}" class="px-3 py-1 text-right text-sm font-semibold text-white bg-blue-600 rounded">D</a> --}}
                        <a href="{{ route('mobile.supervisor.cartera_promotor', ['promotor' => $p['id']]) }}" class="px-3 py-1 text-right text-sm font-semibold text-white bg-blue-600 rounded">D</a>
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

    {{-- BotÃ³n regresar --}}
    <a href="{{ route("mobile.$role.index") }}"
       class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
      Regresar
    </a>

    <a href="{{ route("mobile.$role.horarios") }}"
       class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
      Horarios
    </a>
    
  </div>
</x-layouts.mobile.mobile-layout>

