{{-- resources/views/mobile/supervisor/cartera/historial_promotor.blade.php --}}

@php
    use Faker\Factory;

    $faker = Factory::create('es_MX');

    // Datos de la promotora
    $promotora = (object) [
        'nombre'     => $faker->firstName(),
        'apellido_p' => $faker->lastName(),
        'apellido_m' => $faker->lastName(),
        'colonia'    => $faker->city(),
    ];

    // Clientes activos
    $clientes = collect(range(1, 5))->map(function () use ($faker) {
        $semanasTotales = $faker->numberBetween(9, 21);
        $semanaActual   = $faker->numberBetween(1, $semanasTotales);

        return (object) [
            'id'              => $faker->unique()->randomNumber(),
            'apellido_p'      => $faker->lastName(),
            'apellido_m'      => $faker->lastName(),
            'curp'            => strtoupper($faker->bothify('????######??????##')),
            'avales'          => [
                (object) ['nombre' => $faker->firstName().' '.$faker->lastName()],
                (object) ['nombre' => $faker->firstName().' '.$faker->lastName()],
            ],
            'falla'           => $faker->boolean(30), // 30% de probabilidad de falla
            'credito'         => number_format($faker->randomFloat(2, 1000, 10000), 2, '.', ','),
            'semana_actual'   => $semanaActual,
            'semanas_totales' => $semanasTotales,
            'fecha_inicio'    => $faker->date('Y-m-d', '-2 months'),
            'fecha_final'     => $faker->date('Y-m-d', '+2 months'),
        ];
    });
@endphp

<x-layouts.mobile.mobile-layout title="Historial de Promotora">
<div
    x-data="{
        filtro: 'todos'
    }"
    class="w-full max-w-2xl space-y-6"
>
    {{-- Encabezado --}}
    <section class="bg-white rounded-2xl shadow p-6">
      <h1 class="text-xl font-bold text-gray-900">
        {{ trim(($promotora->nombre ?? '').' '.($promotora->apellido_p ?? '').' '.($promotora->apellido_m ?? '')) }}
      </h1>
      <p class="text-gray-600 text-sm">
        Colonia / Plaza: <span class="font-medium">{{ $promotora->colonia ?? '—' }}</span>
      </p>
    </section>

    {{-- Filtro --}}
    <div class="bg-white rounded-2xl shadow p-4 flex gap-3 items-center">
        <label for="filtro" class="text-sm font-medium text-gray-700">Filtrar por:</label>
        <select id="filtro" x-model="filtro" class="border rounded-lg px-8 py-1 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="todos">Todos</option>
            <option value="falla">Falla</option>
            <option value="sin_falla">Sin Falla</option>
        </select>
    </div>

    {{-- Lista de clientes activos --}}
    <section class="bg-white rounded-2xl shadow overflow-hidden">
      <div class="px-6 py-4 border-b">
        <h2 class="text-base font-semibold text-gray-800">Clientes activos</h2>
      </div>

      <div class="divide-y">
        @forelse ($clientes as $cliente)
          @php
            $apellidos = trim(($cliente->apellido_p ?? '').' '.($cliente->apellido_m ?? ''));
            $aval1 = $cliente->avales[0]->nombre ?? null;
            $aval2 = $cliente->avales[1]->nombre ?? null;
            $estatusFalla = (bool) $cliente->falla;
            $progreso = ($cliente->semana_actual / $cliente->semanas_totales) * 100;
          @endphp

          <div 
              class="p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
              x-show="filtro === 'todos' || (filtro === 'falla' && {{ $estatusFalla ? 'true' : 'false' }}) || (filtro === 'sin_falla' && {{ $estatusFalla ? 'false' : 'true' }})"
          >
            <div class="space-y-1">
              <div class="text-sm text-gray-900">
                <span class="font-semibold">Apellidos:</span> {{ $apellidos ?: '—' }}
              </div>
              <div class="text-sm text-gray-700">
                <span class="font-semibold">CURP:</span> {{ $cliente->curp }}
              </div>
              <div class="text-sm text-gray-700">
                <span class="font-semibold">Crédito:</span> <span class="font-bold">${{ $cliente->credito }}</span>
              </div>
              <div class="text-sm text-gray-700">
                <span class="font-semibold">Avales:</span>
                {{ $aval1 ?? '—' }}{{ $aval1 && $aval2 ? ' y ' : '' }}{{ $aval2 ?? '' }}
              </div>
              <div class="text-sm">
                <span class="font-semibold">Estatus:</span>
                @if($estatusFalla)
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    ● Falla
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    ● Sin falla
                  </span>
                @endif
              </div>

              {{-- Barra de progreso --}}
              <div class="mt-2">
                <div class="flex justify-between text-xs text-gray-600 mb-1">
                  <span>Semana {{ $cliente->semana_actual }} de {{ $cliente->semanas_totales }}</span>
                  <span>{{ number_format($progreso, 0) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                  <div class="h-2 bg-blue-600" style="width: {{ $progreso }}%"></div>
                </div>
              </div>

              {{-- Fechas --}}
              <div class="text-xs text-gray-500 mt-1">
                Inicio: {{ \Carbon\Carbon::parse($cliente->fecha_inicio)->format('d/m/Y') }} | 
                Fin: {{ \Carbon\Carbon::parse($cliente->fecha_final)->format('d/m/Y') }}
              </div>
            </div>

            {{-- Botones --}}
            <div class="flex gap-2">
              <button
                  type="button"
                  @click="$store.calc.open(@js($apellidos))"
                  class="px-3 py-2 rounded-lg bg-blue-800 text-white text-sm font-semibold hover:bg-blue-900 shadow-sm">
                  Reportar pago
              </button>

              <a href='{{ route("mobile.$role.cliente_historial") }}'
                 class="px-3 py-2 rounded-lg bg-gray-100 text-gray-800 text-sm font-semibold hover:bg-gray-200 ring-1 ring-gray-200">
                Historial
              </a>
            </div>
          </div>
        @empty
          <div class="p-6 text-center text-gray-500 text-sm">
            No hay clientes activos para esta promotora.
          </div>
        @endforelse
      </div>

      @include('mobile.modals.calculadora')
    </section>

    {{-- Regresar --}}
    <div class="text-center">
      <a href="{{ route("mobile.$role.cartera") }}"
         class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-white shadow hover:shadow-md ring-1 ring-gray-200">
        ← Regresar a Cartera
      </a>
    </div>
</div>
</x-layouts.mobile.mobile-layout>
