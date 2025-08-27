{{-- resources/views/mobile/supervisor/cartera/cartera_falla.blade.php --}}

@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    // Estatus de falla para clientes
    $estatusFalla = ['total', 'parcial', 'tiempo'];

    $promotores = collect(range(1, 3))->map(function ($i) use ($faker, $estatusFalla) {
        $clientes = collect(range(1, rand(4, 7)))->map(function () use ($faker, $estatusFalla) {
            $estatus = collect($estatusFalla)->random();
            return [
                'nombre'  => $faker->firstName . ' ' . $faker->lastName,
                'monto'   => $faker->numberBetween(500, 8000),
                'estatus' => $estatus,
            ];
        });

        // total de fallas
        $totalFallado = $clientes->sum('monto');
        $porcentajeFalla = $faker->numberBetween(10, 60); // simulado %

        // ordenar clientes por prioridad de estatus
        $ordenPrioridad = ['total' => 1, 'parcial' => 2, 'tiempo' => 3];
        $clientesOrdenados = $clientes->sortBy(fn($c) => $ordenPrioridad[$c['estatus']])->values();

        return [
            'nombre'     => $faker->name,
            'dinero'     => $totalFallado,
            'falla'      => $porcentajeFalla,
            'clientes'   => $clientesOrdenados,
        ];
    });
@endphp

<x-layouts.mobile.mobile-layout>
    <div class="p-4 space-y-5">
        <h1 class="text-xl font-bold text-gray-900">Cartera Falla</h1>

        @foreach($promotores as $promotor)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                {{-- Header Promotor --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 text-[12px] font-bold rounded-full bg-red-100 text-red-700">
                            {{ $loop->iteration }}
                        </span>
                        <div>
                            <span class="block text-[15px] font-semibold text-gray-900">{{ $promotor['nombre'] }}</span>
                            <span class="text-xs text-gray-500">Promotor</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="block text-sm font-semibold text-rose-600">
                            ${{ number_format($promotor['dinero'], 2) }}
                        </span>
                        <span class="block text-[11px] text-gray-500">Falla total ({{ $promotor['falla'] }}%)</span>
                    </div>
                </div>

                {{-- Lista de clientes --}}
                <div class="px-3 py-2">
                    @foreach($promotor['clientes'] as $cliente)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            {{-- Datos cliente --}}
                            <div>
                                <p class="text-[14px] font-medium text-gray-800">{{ $cliente['nombre'] }}</p>
                                <p class="text-[12px] text-gray-500">
                                    Monto fallado: ${{ number_format($cliente['monto'], 2) }}
                                </p>
                            </div>

                            {{-- Botones --}}
                            <div class="flex gap-2">
                                {{-- Botón Cobrar --}}
                                <button @click="$store.calc.open(@js($cliente['nombre']))"
                                    class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-600 text-white font-bold hover:bg-emerald-700 shadow-sm">
                                    $
                                </button>

                                {{-- Botón Historial --}}
                                <a href="{{ route("mobile.promotor.cliente_historial") }}"
                                   class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500 text-white font-bold hover:bg-amber-600 shadow-sm">
                                    H
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @include('mobile.modals.calculadora')
        <a href="{{ url()->previous() }}"
          class="flex items-center justify-center rounded-xl border border-gray-300 text-white text-sm font-semibold px-3 py-2 bg-blue-600 hover:bg-blue-700 shadow-sm">
          Regresar
        </a>
    </div>
    
</x-layouts.mobile.mobile-layout>
