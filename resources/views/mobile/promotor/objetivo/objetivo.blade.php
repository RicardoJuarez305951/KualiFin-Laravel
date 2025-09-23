{{-- resources/views/mobile/promotor/objetivo/objetivo.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    $objetivoSemanal     = $faker->randomFloat(2, 10000, 50000);
    $ventaActual         = $faker->randomFloat(2, 0, $objetivoSemanal);
    $objetivoEjecutivo   = $faker->randomFloat(2, 50000, 200000);

    $ventasPorSemana = [];
    for ($i = 1; $i <= 4; $i++) {
        $ventasPorSemana[$i] = $faker->randomFloat(2, 1000, 50000);
    }

    $frase = $faker->sentence();

    function formatCurrency($v) {
        return '$' . number_format($v, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout title="Mi Objetivo">
    <div class="bg-white text-center rounded-2xl p-6 w-[22rem] sm:w-[26rem]">
        <section class="p-2">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Mi objetivo semanal</h2>
            <p class="text-lg font-bold">{{ formatCurrency($objetivoSemanal) }}</p>
        </section>

        <section class="p-2">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Venta Registrada</h2>
            <p class="text-lg font-bold">{{ formatCurrency($ventaActual) }}</p>
        </section>

        <section class="p-2">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Mi Objetivo P/Ejercicio</h2>
            <p class="text-lg font-bold">{{ formatCurrency($objetivoEjecutivo) }}</p>
        </section>

        <section class="p-2">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Ventas registradas</h2>
            <ul class="space-y-2">
                @foreach ($ventasPorSemana as $semana => $monto)
                    <li>Sem {{ $semana }} - {{ formatCurrency($monto) }}</li>
                @endforeach
            </ul>
        </section>

        <section class="p-2">
            <p class="text-center text-sm italic text-gray-600">{{ $frase }}</p>
        </section>

        <a href="{{ route("mobile.$role.index") }}"
           class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
            ← Regresar
        </a>
    </div>
</x-layouts.mobile.mobile-layout>

