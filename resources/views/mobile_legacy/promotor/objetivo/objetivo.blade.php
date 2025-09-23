{{-- resources/views/mobile_legacy/promotor/objetivo/objetivo.blade.php --}}
@php
    /** @var \Illuminate\Support\Collection<int, array{label: string, range: string, total: float}> $ventasPorSemana */
    $ventasPorSemana = collect($ventasPorSemana ?? []);
    $formatCurrency = static fn ($value) => '$' . number_format((float) $value, 2, '.', ',');
@endphp

<x-layouts.mobile.mobile-layout title="Mi Objetivo">
    <div class="bg-white text-center rounded-2xl p-6 w-[22rem] sm:w-[26rem]">
        <section class="p-2">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Mi objetivo semanal</h2>
            <p class="text-lg font-bold">{{ $formatCurrency($objetivoSemanal ?? 0) }}</p>
        </section>

        <section class="p-2">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Venta Registrada</h2>
            <p class="text-lg font-bold">{{ $formatCurrency($ventaActual ?? 0) }}</p>
            <p class="text-xs text-gray-500">Avance: {{ number_format((float) ($porcentajeActual ?? 0), 1, '.', ',') }}%</p>
        </section>

        <section class="p-2">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Mi Objetivo P/Ejercicio</h2>
            <p class="text-lg font-bold">{{ $formatCurrency($objetivoEjercicio ?? 0) }}</p>
        </section>

        <section class="p-2">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Ventas registradas</h2>
            <ul class="space-y-2 text-left">
                @foreach ($ventasPorSemana as $semana)
                    <li class="flex flex-col rounded-xl bg-gray-50 px-3 py-2">
                        <div class="flex items-baseline justify-between text-sm font-semibold text-gray-700">
                            <span>{{ $semana['label'] }}</span>
                            <span class="text-gray-900">{{ $formatCurrency($semana['total'] ?? 0) }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ $semana['range'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="p-2">
            <p class="text-center text-sm italic text-gray-600">{{ $fraseMotivacional ?? '' }}</p>
        </section>

        <a href="{{ route("mobile.$role.index") }}"
           class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
            ← Regresar
        </a>
    </div>
</x-layouts.mobile.mobile-layout>

