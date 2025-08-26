{{-- resources/views/mobile/cartera.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');
    $activos = collect(range(1, 5))->map(fn($i) => [
        'id'       => $i,
        'lastName' => $faker->lastName(),
        'semana'   => $faker->numberBetween(1, 12),
        'due'      => $faker->randomFloat(2, 100, 1000),
    ])->toArray();

    $vencidos = collect(range(1, 3))->map(fn($i) => [
        'id'       => $i,
        'lastName' => $faker->lastName(),
        'semana'   => $faker->numberBetween(1, 12),
        'due'      => $faker->randomFloat(2, 100, 1000),
    ])->toArray();

    $inactivos = collect(range(1, 2))->map(fn($i) => [
        'id'       => $i,
        'lastName' => $faker->lastName(),
        'semana'   => $faker->numberBetween(1, 12),
        'due'      => $faker->randomFloat(2, 100, 1000),
    ])->toArray();
@endphp

<x-layouts.mobile.mobile-layout title="Tu Cartera">
  <div x-data="{
        showCalc: false,
        calcAmount: '',
        clientName: ''
      }">    
    <div class="bg-white rounded-2xl shadow p-4 w-full max-w-lg mx-auto">
      <h2 class="text-center text-2xl font-bold text-gray-800 mb-6">Tu Cartera</h2>
      
      <h3 class="text-lg font-semibold text-gray-700 mb-2">Cartera Activa</h3>
      @include('mobile.promotor_legacy.cartera.activa', ['activos' => $activos])

      <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-2">Cartera Vencida</h3>
      @include('mobile.promotor_legacy.cartera.vencida', ['vencidos' => $vencidos])

      <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-2">Cartera Inactiva</h3>
      @include('mobile.promotor_legacy.cartera.inactiva', ['inactivos' => $inactivos])

      <div class="mt-8 space-y-4">
        <button
          class="w-full bg-green-600 hover:bg-green-800 text-white font-semibold py-3 rounded-lg shadow-md transition">
          Enviar Pagos Seleccionados
        </button>
        <button
          class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg shadow-md transition">
          Enviar Reporte
        </button>
      </div>

      <a href="{{ route("mobile.$role.index") }}"
         class="block w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3 mt-4">
        Regresar
      </a>
    </div>
    
    {{-- Modal --}}
    <div x-show="showCalc" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black bg-opacity-50" @click="showCalc = false"></div>
      
      <div @click.stop
           class="relative bg-white rounded-xl shadow-lg w-11/12 max-w-sm p-6 animate-fade-in">
        <h3 class="text-center text-lg font-semibold mb-4 text-gray-800">
          <span class="font-bold" x-text="clientName"></span> pagará:
        </h3>
        <input type="number"
               x-model="calcAmount"
               placeholder="Ingresa monto"
               class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-400"/>
        <div class="flex space-x-3">
          <button @click="showCalc = false"
                  class="flex-1 py-2 border border-gray-300 rounded hover:bg-gray-100">
            Cancelar
          </button>
          <button @click.prevent="/* Enviar */ showCalc = false"
                  class="flex-1 py-2 bg-green-500 hover:bg-green-600 text-white rounded">
            Aceptar
          </button>
        </div>
      </div>
    </div>
  </div>
</x-layouts.mobile.mobile-layout>
