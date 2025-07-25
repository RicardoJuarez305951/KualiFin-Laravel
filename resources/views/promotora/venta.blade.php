{{-- resources/views/promotora/venta.blade.php --}}
@php
    $faker = \Faker\Factory::create('es_MX');
    $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $supervisora   = $faker->name();
    $ejecutivo     = $faker->name();
    $montoSemanal  = $faker->randomFloat(2, 10000, 50000);
    $ventas        = collect(range(1, 6))->map(fn() => [
        'name'   => $faker->name(),
        'amount' => $faker->randomFloat(2, 1000, 10000),
    ])->toArray();
    $total = array_sum(array_column($ventas, 'amount'));
@endphp

<x-layouts.promotora_mobile.mobile-layout title="Mi Venta">
  <div class="bg-gray-100 min-h-screen p-4 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow p-6 w-full max-w-md">

      {{-- Encabezado --}}
      <h2 class="text-center text-lg font-semibold text-black">
        Tu venta para el día<br>
        <span class="text-2xl font-bold">{{ $fecha }}</span>
      </h2>

      {{-- Roles --}}
      <div class="mt-4 space-y-1 text-sm text-black">
        <p><span class="font-semibold">Promotora:</span> {{ auth()->user()->name }}</p>
        <p><span class="font-semibold">Supervisora:</span> {{ $supervisora }}</p>
        <p><span class="font-semibold">Ejecutivo:</span> {{ $ejecutivo }}</p>
      </div>

      {{-- Monto semanal --}}
      <div class="flex justify-between items-center mt-4">
        <span class="text-sm text-black">Debe semanal:</span>
        <span class="text-sm font-bold text-black">
          ${{ number_format($montoSemanal, 2) }}
        </span>
      </div>
      <hr class="my-3 border-gray-200">

      {{-- Lista de ventas --}}
      <ul class="divide-y divide-gray-200 text-sm text-black">
        @foreach ($ventas as $venta)
          <li class="flex justify-between py-2">
            <span>{{ $venta['name'] }}</span>
            <span>${{ number_format($venta['amount'], 2) }}</span>
          </li>
        @endforeach
      </ul>
      <hr class="my-3 border-gray-200">

      {{-- Total préstamo --}}
      <div class="flex justify-between items-center">
        <span class="text-sm font-semibold text-black">
          Cantidad total del préstamo
        </span>
        <span class="text-sm font-bold text-black">
          ${{ number_format($total, 2) }}
        </span>
      </div>

      {{-- Botones --}}
      <div class="mt-6 space-y-3">
        <a href="{{ route('promotora.ingresar_cliente') }}"
           class="block w-full bg-blue-600 hover:bg-blue-700 text-white 
                  font-semibold py-3 rounded-lg text-center transition">
          Ingresar Cliente
        </a>
        <a href="{{ route('promotora.solicitar_venta') }}"
           class="block w-full bg-blue-800 hover:bg-blue-900 text-white 
                  font-semibold py-3 rounded-lg text-center transition">
          Ingresar venta
        </a>

        <a href="{{ route('promotora.index') }}"
                class="block w-full border border-blue-800 text-blue-800 font-medium py-3 rounded-lg text-center hover:bg-blue-50 transition">
                Regresar
        </a>

        
      </div>

    </div>
  </div>
</x-layouts.promotora_mobile.mobile-layout>
