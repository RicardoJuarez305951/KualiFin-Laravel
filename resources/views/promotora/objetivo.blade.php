{{-- resources/views/mobile/objetivo.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    // Objetivos
    $weeklyTarget           = $faker->randomFloat(2, 10000, 50000);
    $exerciseTarget         = $faker->randomFloat(2, 100000, 500000);

    // Ventas históricas (3 semanas de ejemplo)
    $salesHistory = [
        $faker->randomFloat(2, 0, $weeklyTarget), 
        $faker->randomFloat(2, 0, $weeklyTarget), 
        $faker->randomFloat(2, 0, $weeklyTarget),
    ];

    // Cálculos
    $projectedDue            = max($weeklyTarget - $salesHistory[0], 0);
    $remainingExerciseTarget = max($exerciseTarget - array_sum($salesHistory), 0);

    // Mensaje motivacional
    $message = $projectedDue > 0
        ? '¡Vas muy bien, pero falta un poco para llegar!'
        : '¡Felicidades, objetivo semanal alcanzado!';
@endphp

<x-layouts.promotora_mobile.mobile-layout title="Tu Objetivo">
  <div class="bg-gray-100 min-h-screen p-4 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow p-6 w-full max-w-md">

      {{-- Objetivo semanal --}}
      <p class="text-center text-lg font-semibold text-black">
        Tu venta objetivo para esta semana
      </p>
      <p class="text-center text-2xl font-bold text-black mb-4">
        ${{ number_format($weeklyTarget, 2) }}
      </p>

      {{-- Objetivo resto del ejercicio --}}
      <p class="text-center text-lg font-semibold text-black">
        Tu venta objetivo para el resto del ejercicio
      </p>
      <p class="text-center text-2xl font-bold text-black mb-4">
        ${{ number_format($remainingExerciseTarget, 2) }}
      </p>

      {{-- Objetivo resto del ejercicio --}}
      <p class="text-center text-lg font-semibold text-black">
        Debe Proyectado
      </p>
      <p class="text-center text-2xl font-bold text-black mb-4">
        ${{ number_format($projectedDue, 2) }}
      </p>

      
      <hr class="border-gray-200 mb-6">

      {{-- Ventas históricas --}}
      <p class="text-sm font-semibold text-black mb-2">Tus ventas durante el ejercicio</p>
      <ul class="space-y-1 mb-6 text-sm text-black">
        @foreach ($salesHistory as $idx => $amt)
          <li>- SEM {{ $idx + 1 }}: ${{ number_format($amt, 2) }}</li>
        @endforeach
      </ul>

      {{-- Mensaje --}}
      <p class="text-center text-sm text-black mb-6">{{ $message }}</p>

      {{-- Botón regresar --}}
      <a href="{{ route('promotora.index') }}"
         class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg text-center">
        REGRESAR
      </a>

    </div>
  </div>
</x-layouts.promotora_mobile.mobile-layout>
