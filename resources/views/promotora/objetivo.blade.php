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
    <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md space-y-6">

      {{-- Título --}}
      <div class="text-center">
        <h2 class="text-xl font-bold text-gray-900">Resumen de tus Objetivos</h2>
        <p class="text-sm text-gray-600">¡Sigue avanzando, estás muy cerca!</p>
      </div>

      {{-- Objetivo semanal --}}
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center gap-2 text-blue-900">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
               viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 12c1.657 0 3-1.343 3-3S13.657 6 12 6s-3 1.343-3 3 1.343 3 3 3zm0 0v8m0-8H4m8 0h8" />
          </svg>
          <span class="font-semibold">Objetivo semanal</span>
        </div>
        <p class="text-2xl font-bold mt-1 text-blue-900">${{ number_format($weeklyTarget, 2) }}</p>
      </div>

      {{-- Objetivo del ejercicio --}}
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-center gap-2 text-yellow-900">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
               viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M11.25 3v1.5m0 15V21m-6.364-1.636l1.06-1.06m10.606 0l1.06 1.06M21 12h-1.5M3 12H1.5m1.636-6.364l1.06 1.06m12.728-1.06l1.06 1.06M6 12a6 6 0 1112 0 6 6 0 01-12 0z" />
          </svg>
          <span class="font-semibold">Resto del ejercicio</span>
        </div>
        <p class="text-2xl font-bold mt-1 text-yellow-900">${{ number_format($remainingExerciseTarget, 2) }}</p>
      </div>

      {{-- Proyección actual --}}
      <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center gap-2 text-red-900">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
               viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="font-semibold">Debe proyectado</span>
        </div>
        <p class="text-2xl font-bold mt-1 text-red-900">${{ number_format($projectedDue, 2) }}</p>
      </div>

      {{-- Historial --}}
      <div>
        <h3 class="text-sm font-semibold text-gray-800 mb-2">Ventas anteriores</h3>
        <ul class="space-y-1 text-sm text-gray-700">
          @foreach ($salesHistory as $idx => $amt)
            <li class="flex justify-between border-b border-gray-100 py-1">
              <span class="text-gray-500">Semana {{ $idx + 1 }}</span>
              <span class="font-medium">${{ number_format($amt, 2) }}</span>
            </li>
          @endforeach
        </ul>
      </div>

      {{-- Mensaje motivacional --}}
      <div class="text-center bg-green-100 border border-green-200 text-green-900 text-sm rounded-lg p-3">
        {{ $message }}
      </div>

      {{-- Botón --}}
      <a href="{{ route('promotora.index') }}"
         class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg text-center shadow-md transition">
        REGRESAR
      </a>
    </div>
  </div>
</x-layouts.promotora_mobile.mobile-layout>
