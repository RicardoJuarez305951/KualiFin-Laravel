{{-- resources/views/mobile/solicitar_venta.blade.php --}}
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

<x-layouts.promotora_mobile.mobile-layout title="Solicitar Venta">
  <div class="bg-gray-100 min-h-screen p-4 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow p-6 w-full max-w-md">

      @if (! $viable)
        {{-- No viable --}}
        <p class="text-center text-lg font-semibold text-black mb-4">
          Lo sentimos, los siguientes elementos de tu venta no son viables
        </p>

        <ul class="space-y-2 mb-6">
          @foreach ($lista as $nombre => $razon)
            <li class="text-sm text-black">
              <span class="font-medium">{{ $faker->name() }}</span><br>
              <span class="text-gray-600">{{ $razon }}</span>
            </li>
          @endforeach
        </ul>

        <a href="{{ route('promotora.venta') }}"
           class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold
                  py-3 rounded-lg text-center transition">
          Corregir elementos
        </a>

      @else
        {{-- Viable --}}
        <p class="text-center text-2xl font-bold text-black mb-2">
          VENTA VIABLE
        </p>
        <p class="text-center text-sm text-black mb-6">
          Felicidades, toda tu venta es viable.
        </p>

        <a href="{{ route('promotora.venta') }}"
           class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold
                  py-3 rounded-lg text-center transition">
          FINALIZAR Y ENVIAR
        </a>
      @endif

    </div>
  </div>
</x-layouts.promotora_mobile.mobile-layout>
