@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');
    // 50/50 de venta viable o no viable
    $viable = random_int(0,1) === 1;

    // Si no es viable, genera ejemplos
    $problemas = [
      'Cliente con deuda',
      'Cliente de otra plaza',
      'Límite de firmas como aval'
    ];
    shuffle($problemas);
    $lista = array_slice($problemas, 0, 3);
@endphp

<x-layouts.mobile.mobile-layout title="Solicitar Venta">
  <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md space-y-6">

    @if (! $viable)
      {{-- No viable --}}
      <div class="text-center space-y-3">
        <p class="text-lg font-semibold text-red-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="inline w-6 h-6 mr-1 align-middle" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
            <path d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Lo sentimos, los siguientes elementos no son viables
        </p>

        <ul class="space-y-3 text-left">
          @foreach ($lista as $razon)
            <li class="flex items-start gap-3 text-sm text-gray-800">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-red-500 mt-1" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
                <path d="M6 18L18 6M6 6l12 12"/>
              </svg>
              <div>
                <span class="font-semibold">{{ $faker->name() }}</span><br>
                <span class="text-gray-600">{{ $razon }}</span>
              </div>
            </li>
          @endforeach
        </ul>
      </div>

      <a href="{{ route('mobile.venta') }}"
          class="block w-full bg-red-700 hover:bg-red-800 text-white font-semibold
                py-3 rounded-xl text-center shadow-md transition ring-1 ring-red-900/30 focus:outline-none focus:ring-2 focus:ring-red-600">
        Corregir elementos
      </a>

    @else
      {{-- Viable --}}
      <div class="text-center space-y-3">
        <p class="text-3xl font-extrabold text-green-600 flex items-center justify-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
            <path d="M5 13l4 4L19 7"/>
          </svg>
          VENTA VIABLE
        </p>
        <p class="text-sm text-gray-700">Felicidades, toda tu venta es viable.</p>
      </div>

      <a href="{{ route('mobile.venta') }}"
          class="block w-full bg-green-600 hover:bg-green-700 text-white font-semibold
                py-3 rounded-xl text-center shadow-md transition ring-1 ring-green-900/30 focus:outline-none focus:ring-2 focus:ring-green-500">
        FINALIZAR Y ENVIAR
      </a>
    @endif

  </div>
</x-layouts.mobile.mobile-layout>
