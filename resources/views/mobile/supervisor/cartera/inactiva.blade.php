{{-- resources/views/mobile/supervisor/cartera/inactiva.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    // Clientes inactivos, sin deuda, buen historial
    $clientes = collect(range(1, 8))->map(fn($i) => [
        'id'         => $i,
        'nombre'     => $faker->firstName(),
        'apellido_p' => $faker->lastName(),
        'apellido_m' => $faker->lastName(),
        'telefono'   => $faker->phoneNumber(),
        'email'      => $faker->safeEmail(),
        'colonia'    => $faker->city(),
        'direccion'  => $faker->streetAddress(),
        'curp'       => strtoupper($faker->bothify('????######??????##')),
        'fecha_ultimo_credito' => $faker->date('d/m/Y', '-1 years'),
        'monto_ultimo_credito' => number_format($faker->randomFloat(2, 2000, 15000), 2, '.', ','),
    ])->sortBy(fn($c) => mb_strtolower($c['apellido_p']))->values()->toArray();
@endphp

<x-layouts.mobile.mobile-layout title="Clientes Inactivos">
  <div class="w-full max-w-xl mx-auto p-4 space-y-6">
    <h2 class="text-xl font-bold text-gray-800 text-center">
      Cartera Inactiva - Clientes Potenciales para Reacreditación
    </h2>

    {{-- Lista de tarjetas desplegables --}}
    <div x-data="{ openId: null }" class="space-y-4">
      @foreach($clientes as $c)
        @php
          $nombreCompleto = trim($c['nombre'].' '.$c['apellido_p'].' '.$c['apellido_m']);
        @endphp

        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
          {{-- Encabezado tarjeta --}}
          <button 
            class="w-full flex justify-between items-center px-4 py-3 text-left focus:outline-none"
            @click="openId = (openId === {{ $c['id'] }} ? null : {{ $c['id'] }})"
          >
            <div>
              <h3 class="text-base font-semibold text-gray-900">{{ $nombreCompleto }}</h3>
              <p class="text-sm text-gray-500">Colonia: {{ $c['colonia'] }}</p>
            </div>
            <svg x-show="openId !== {{ $c['id'] }}" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <svg x-show="openId === {{ $c['id'] }}" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
          </button>

          {{-- Contenido desplegable --}}
          <div x-show="openId === {{ $c['id'] }}" x-collapse>
            <div class="px-4 pb-4 text-sm text-gray-700 space-y-2">
              <div><span class="font-semibold">Teléfono:</span> {{ $c['telefono'] }}</div>
              <div><span class="font-semibold">Email:</span> {{ $c['email'] }}</div>
              <div><span class="font-semibold">Dirección:</span> {{ $c['direccion'] }}</div>
              <div><span class="font-semibold">CURP:</span> {{ $c['curp'] }}</div>
              <div><span class="font-semibold">Fecha último crédito:</span> {{ $c['fecha_ultimo_credito'] }}</div>
              <div><span class="font-semibold">Monto último crédito:</span> ${{ $c['monto_ultimo_credito'] }}</div>
              <div class="pt-2">
                <a href="tel:{{ $c['telefono'] }}" 
                   class="inline-block px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold shadow">
                  📞 Contactar
                </a>
                <a href="{{ route("mobile.$role.reacreditacion", ['cliente' => $c['id']]) }}"
                   class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow ml-2">
                  💳 Reacreditar
                </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Botón regresar --}}
    <a href="{{ route("mobile.$role.cartera") }}"
       class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
      ← Regresar a Cartera
    </a>
  </div>
</x-layouts.mobile.mobile-layout>
