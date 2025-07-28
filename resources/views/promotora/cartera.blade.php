{{-- resources/views/mobile/cartera.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');
    $clients = collect(range(1, 8))->map(fn($i) => [
        'id'     => $i,
        'lastName'   => $faker->lastName(),
        'semana' => $faker->numberBetween(1, 12),
        'due'    => $faker->randomFloat(2, 100, 1000),
    ])->toArray();
@endphp

<x-layouts.promotora_mobile.mobile-layout title="Tu Cartera">
  <div x-data="{
        showCalc: false,
        calcAmount: '',
        clientName: ''
      }">    
    <div class="bg-white rounded-2xl shadow p-6 w-full max-w-md">
      <h2 class="text-center text-2xl font-bold text-gray-800 mb-6">Tu Cartera</h2>
      
      <ul class="space-y-4 text-sm">
        @foreach ($clients as $c)
          <li class="flex items-center justify-between">
            <div class="flex-1">
              <span class="text-gray-800 font-medium">{{ $c['lastName'] }}</span>
            </div>
            <div class="w-20 text-right">
              <span class="text-yellow-600 text-xs">Sem {{ $c['semana'] }}</span>
            </div>
            <div class="w-24 text-right pr-1">
              <span class="text-gray-900 font-semibold">${{ number_format($c['due'], 2) }}</span>
            </div>
            <div class="flex space-x-2 ml-2">
              {{-- Botón $ --}}
              <button
                @click="clientName = '{{ $c['lastName'] }}'; showCalc = true; calcAmount = '';"
                class="w-10 h-10 border-2 border-green-500 text-green-500 hover:bg-green-100 rounded-full flex items-center justify-center"
                title="Registrar pago">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v2m0 10v2" />
                </svg>
              </button>

              {{-- Botón H --}}
              <a href="{{ route('promotora.cliente_historial', ['client' => $c['id']]) }}"
                 class="w-10 h-10 border-2 border-yellow-500 text-yellow-500 hover:bg-yellow-100 rounded-full flex items-center justify-center"
                 title="Historial">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </a>
            </div>
          </li>
        @endforeach
      </ul>
      
      <div class="mt-8 space-y-4">
        <button
          class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg shadow-md transition">
          Enviar Reporte
        </button>
        <a href="{{ route('promotora.index') }}"
           class="block w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3">
          Regresar
        </a>
      </div>
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
</x-layouts.promotora_mobile.mobile-layout>
