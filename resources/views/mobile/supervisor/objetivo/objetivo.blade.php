{{-- resources/views/supervisor/objetivo.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    // ===== DEMO DATA (reemplaza con tu query real) =====
    $supervisor = auth()->check() ? auth()->user()->name : $faker->name();

    // Objetivo semanal
    $objetivoSem = $faker->numberBetween(20_000, 120_000);
    $ventaSem    = $faker->numberBetween(0, $objetivoSem);
    $faltanteSem = max(0, $objetivoSem - $ventaSem);

    // Objetivo del ejercicio (mes/trimestre/año, según manejan)
    $objetivoEjercicio = $faker->numberBetween(200_000, 1_200_000);
    $ventaEjercicio    = $faker->numberBetween(0, $objetivoEjercicio);
    $faltanteEjercicio = max(0, $objetivoEjercicio - $ventaEjercicio);

    // Promotoras
    $promotoras = collect(range(1, 6))->map(function($i) use ($faker) {
        $nombre   = $faker->firstName().' '.$faker->lastName();
        $obj      = $faker->numberBetween(10_000, 80_000);
        $venta    = $faker->numberBetween(0, $obj);
        $faltante = max(0, $obj - $venta);
        $pct      = $obj > 0 ? round(($venta / $obj) * 100) : 0;
        return compact('nombre','obj','venta','faltante','pct');
    });

    function money($v){ return '$'.number_format($v, 2, '.', ','); }
@endphp

<x-layouts.mobile.mobile-layout title="Objetivo – {{ $supervisor }}">
  <div class="p-4 w-full max-w-md mx-auto space-y-6">

    {{-- DIV1: Semana --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
      <h2 class="text-base font-bold text-gray-900 mb-3">Semana (ventas)</h2>
      <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
        <div>
          <p class="text-gray-500">Supervisor</p>
          <p class="font-semibold">{{ $supervisor }}</p>
        </div>
        <div>
          <p class="text-gray-500">Objetivo Sem</p>
          <p class="font-bold text-indigo-700">{{ money($objetivoSem) }}</p>
        </div>
        <div>
          <p class="text-gray-500">Venta Sem</p>
          <p class="font-semibold text-green-700">{{ money($ventaSem) }}</p>
        </div>
        <div>
          <p class="text-gray-500">Para llegar</p>
          <p class="font-semibold text-amber-700">{{ money($faltanteSem) }}</p>
        </div>
      </div>

      @php
        $pctSem = $objetivoSem > 0 ? min(100, round(($ventaSem/$objetivoSem)*100)) : 0;
      @endphp
      <div class="mt-4">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
          <span>Progreso semanal</span>
          <span>{{ $pctSem }}%</span>
        </div>
        <div class="h-2.5 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600" style="width: {{ $pctSem }}%"></div>
        </div>
      </div>
    </div>

    {{-- DIV2: Ejercicio --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
      <h2 class="text-base font-bold text-gray-900 mb-3">Ejercicio (acumulado)</h2>
      <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
        <div>
          <p class="text-gray-500">Objetivo Ejercicio</p>
          <p class="font-bold text-indigo-700">{{ money($objetivoEjercicio) }}</p>
        </div>
        <div>
          <p class="text-gray-500">Venta en Ejercicio</p>
          <p class="font-semibold text-green-700">{{ money($ventaEjercicio) }}</p>
        </div>
        <div>
          <p class="text-gray-500">Para llegar</p>
          <p class="font-semibold text-amber-700">{{ money($faltanteEjercicio) }}</p>
        </div>
      </div>

      @php
        $pctEje = $objetivoEjercicio > 0 ? min(100, round(($ventaEjercicio/$objetivoEjercicio)*100)) : 0;
      @endphp
      <div class="mt-4">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
          <span>Progreso ejercicio</span>
          <span>{{ $pctEje }}%</span>
        </div>
        <div class="h-2.5 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full bg-gradient-to-r from-emerald-500 to-green-600" style="width: {{ $pctEje }}%"></div>
        </div>
      </div>
    </div>

    {{-- Barra divisora --}}
    <div class="relative">
      <div class="h-0.5 bg-gray-200"></div>
    </div>

    {{-- DIV3: Promotoras --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
      <h2 class="text-base font-bold text-gray-900 mb-3">Promotoras</h2>

      <div class="space-y-3">
        @foreach($promotoras as $idx => $p)
          <div class="rounded-xl border border-gray-100 p-3 shadow-sm">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-7 h-7 text-[11px] font-bold rounded-full bg-gray-200 text-gray-700 flex items-center justify-center">{{ $loop->iteration }}</span>
                <div>
                  <p class="text-[14px] font-semibold text-gray-900">{{ $p['nombre'] }}</p>
                  <p class="text-[12px] text-gray-500">
                    Obj: <span class="font-medium">{{ money($p['obj']) }}</span> ·
                    Venta: <span class="font-medium text-green-700">{{ money($p['venta']) }}</span> ·
                    Para llegar: <span class="font-medium text-amber-700">{{ money($p['faltante']) }}</span>
                  </p>
                </div>
              </div>
              <span class="text-[12px] font-semibold text-gray-600">{{ $p['pct'] }}%</span>
            </div>

            {{-- Barra porcentaje: ventas / objetivo --}}
            <div class="mt-2 h-2.5 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-sky-500 to-blue-600" style="width: {{ min(100, $p['pct']) }}%"></div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- DIV4: Botones --}}
    <div class="grid grid-cols-3 gap-3">
      <a href="{{ route("mobile.index") }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-semibold shadow-sm transition">Regresar</a>
      <a href="{{ url()->current() }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm transition">Actualizar</a>
      <a href="{{ route("mobile.$role.objetivo") }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow-sm transition">Reporte</a>
    </div>

  </div>
</x-layouts.mobile.mobile-layout>
