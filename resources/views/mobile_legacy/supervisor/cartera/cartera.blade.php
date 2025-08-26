{{-- resources/views/mobile/supervisor/dashboard.blade.php (o donde lo necesites) --}}
@php
    use Faker\Factory as Faker;

    /** @var string $role */
    $role = $role ?? 'promotor';
    $faker = Faker::create('es_MX');

    // Supervisor y métricas
    $nombre_supervisor = $faker->name();
    $cartera_activa    = $faker->randomFloat(2, 80000, 250000);
    $cartera_falla     = $faker->randomFloat(2,  5000,  60000);
    $cartera_vencida   = $faker->randomFloat(2, 10000, 120000);
    $cartera_inactivaP = $faker->numberBetween(1, 35); // porcentaje

    // Porcentaje de fallo (ajusta la fórmula si quieres otra)
    $porcentaje_fallo  = $cartera_activa > 0 ? round(($cartera_falla / $cartera_activa) * 100, 2) : 0;

    // Promotores
    $promotores = collect(range(1, 8))->map(fn($i) => [
        'name'     => $faker->name(),
        'progress' => $faker->numberBetween(5, 100), // %
    ]);
    function money_mx($v){ return '$' . number_format($v, 2, '.', ','); }
@endphp

<x-layouts.mobile.mobile-layout title="Panel Supervisor">
  <div class="mx-auto w-[22rem] sm:w-[26rem] p-4 sm:p-6 space-y-6">

    {{-- =======================
         DIV1: Resumen
       ======================= --}}
    <section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
      <div class="p-5 space-y-4">
        <h2 class="text-base font-bold text-gray-900">Resumen</h2>

        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Supervisor:</span>
            <span class="text-sm font-semibold text-gray-900">{{ $nombre_supervisor }}</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Cartera Activa:</span>
            <div class="flex items-center gap-2">
              <span class="text-sm font-semibold text-gray-900">{{ money_mx($cartera_activa) }}</span>
              <a href="{{ route("mobile.$role.cartera_activa") }}"
                 class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full bg-blue-600 text-white hover:bg-blue-700 transition"
                 title="Detalles">D</a>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Falla Actual:</span>
            <div class="flex items-center gap-2">
              <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 ring-1 ring-yellow-200">
                {{ $porcentaje_fallo }}%
              </span>
              <span class="text-sm font-semibold text-gray-900">{{ money_mx($cartera_falla) }}</span>
              <a href="{{ route("mobile.$role.cartera_falla") }}"
                 class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full bg-blue-600 text-white hover:bg-blue-700 transition"
                 title="Detalles">D</a>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Cartera Vencida:</span>
            <div class="flex items-center gap-2">
              <span class="text-sm font-semibold text-gray-900">{{ money_mx($cartera_vencida) }}</span>
              <a href="{{ route("mobile.$role.cartera_vencida") }}"
                 class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full bg-blue-600 text-white hover:bg-blue-700 transition"
                 title="Detalles">D</a>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Cartera Inactiva:</span>
            <div class="flex items-center gap-2">
              <span class="text-sm font-semibold text-gray-900">{{ $cartera_inactivaP }}%</span>
              <a href="{{ route("mobile.$role.cartera_inactiva") }}"
                 class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full bg-blue-600 text-white hover:bg-blue-700 transition"
                 title="Detalles">D</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- =======================
         DIV2: Promotores
       ======================= --}}
    <section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
      <div class="p-5">
        <h2 class="text-base font-bold text-gray-900 mb-3">Promotores</h2>

        <div class="space-y-3">
          @foreach($promotores as $i => $p)
            <a href="{{ route('mobile.promotor.cartera', ['id' => $loop->iteration]) }}"
              class="block rounded-xl border border-gray-100 p-3 shadow-md hover:shadow transition">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center justify-center w-6 h-6 text-[11px] font-bold rounded-full bg-indigo-100 text-indigo-700">
                    {{ $loop->iteration }}
                  </span>
                  <span class="text-sm font-semibold text-gray-900">{{ $p['name'] }}</span>
                </div>
              </div>

              {{-- Progress bar --}}
              <div class="mt-2">
                <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                  <div class="h-2 rounded-full bg-gradient-to-r from-indigo-600 to-blue-500"
                        style="width: {{ $p['progress'] }}%"></div>
                </div>
                <div class="mt-1 flex items-center justify-between text-[11px] text-gray-600">
                  <span>Progreso</span>
                  <span class="font-semibold">{{ $p['progress'] }}%</span>
                </div>
              </div>
            </a>
          @endforeach
        </div>
      </div>
    </section>

    {{-- =======================
         DIV3: Acciones
       ======================= --}}
    <section class="grid grid-cols-3 gap-3">
      <a href="{{ route("mobile.$role.index") }}"
         class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white text-gray-800 text-sm font-semibold px-3 py-2 hover:bg-gray-50 shadow-sm">
        Regresar
      </a>

      {{-- Actualizar: recarga la misma URL --}}
      <a href="{{ url()->current() }}"
         class="inline-flex items-center justify-center rounded-xl bg-blue-600 text-white text-sm font-semibold px-3 py-2 hover:bg-blue-700 shadow">
        Actualizar
      </a>

      {{-- Reporte: ajusta la ruta a la que uses para reportes --}}
      <a href="{{ route("mobile.$role.reporte") }}"
         class="inline-flex items-center justify-center rounded-xl bg-indigo-600 text-white text-sm font-semibold px-3 py-2 hover:bg-indigo-700 shadow">
        Reporte
      </a>
    </section>

  </div>
</x-layouts.mobile.mobile-layout>
