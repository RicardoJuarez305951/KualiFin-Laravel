{{-- resources/views/mobile/cartera.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');
    // Generar lista de clientes de ejemplo
    $clients = collect(range(1, 8))->map(fn($i) => [
        'id'     => $i,
        'name'   => $faker->name(),
        'semana'    => $faker->numberBetween(1, 12),
        'due'    => $faker->randomFloat(2, 100, 1000),
    ])->toArray();
@endphp

<x-layouts.promotora_mobile.mobile-layout title="Tu Cartera">
  <div x-data="{
        showCalc: false,
        calcAmount: '',
        clientName: ''
      }"
      class="bg-gray-100 min-h-screen p-4 flex items-center justify-center">
    
    {{-- Contenedor principal --}}
    <div class="bg-white rounded-2xl shadow p-6 w-full max-w-md">
      <h2 class="text-center text-2xl font-bold text-black mb-6">TU CARTERA</h2>
      
      {{-- Lista de clientes --}}
      <ul class="space-y-4 text-sm">
        @foreach ($clients as $c)
          <li class="flex items-center justify-between">
            <div class="flex-1">
              <span class=" text-black font-medium">{{ $c['name'] }}</span>
            </div>
            <div class="w-20 text-right">
              <span class="text-yellow-600">Semana {{ $c['semana'] }}</span>
            </div>
            <div class="w-20 text-right px-2">
              <span class="text-black font-semibold">${{ number_format($c['due'], 2) }}</span>
            </div>
            <div class="flex space-x-2 ml-4">
              {{-- Botón $ verde --}}
              <button
                @click="clientName = '{{ $c['name'] }}'; showCalc = true; calcAmount = '';"
                class="w-10 h-10 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center"
              >
                $
              </button>
              {{-- Botón H amarillo --}}
              <a href="{{ route('promotora.cliente_historial', ['client' => $c['id']]) }}"
                 class="w-10 h-10 bg-yellow-400 hover:bg-yellow-500 text-black rounded-full flex items-center justify-center">
                H
              </a>
            </div>
          </li>
        @endforeach
      </ul>
      
      {{-- Acciones al pie --}}
      <div class="mt-8 space-y-4">
        <button
          class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg">
          ENVIAR REPORTE
        </button>
        <a href="{{ route('promotora.index') }}"
           class="block w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3">
          REGRESAR
        </a>
      </div>
    </div>
    
    {{-- Modal Calculadora --}}
    <div x-show="showCalc" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center">
      {{-- Overlay --}}
      <div class="absolute inset-0 bg-black bg-opacity-50"
           @click="showCalc = false"></div>
      {{-- Contenido --}}
      <div @click.stop
           class="relative bg-white rounded-lg shadow-lg w-11/12 max-w-sm p-6">
        <h3 class="text-center text-lg font-semibold mb-4">
          <span class="font-bold" x-text="clientName"></span> pagará:
        </h3>
        <input type="number"
               x-model="calcAmount"
               placeholder="Ingresa monto"
               class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring"/>
        <div class="flex space-x-3">
          <button @click="showCalc = false"
                  class="flex-1 py-2 border border-gray-300 rounded hover:bg-gray-100">
            Cancelar
          </button>
          <button @click.prevent="/* Aquí envías calcAmount */ showCalc = false"
                  class="flex-1 py-2 bg-green-500 hover:bg-green-600 text-white rounded">
            Aceptar
          </button>
        </div>
      </div>
    </div>
    
  </div>
</x-layouts.promotora_mobile.mobile-layout>
