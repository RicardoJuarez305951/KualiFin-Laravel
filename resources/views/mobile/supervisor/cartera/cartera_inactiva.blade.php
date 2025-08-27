{{-- resources/views/mobile/supervisor/cartera/cartera_inactiva.blade.php --}}

@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    $promotores = collect(range(1, 3))->map(function ($i) use ($faker) {
        $clientes = collect(range(1, rand(3, 6)))->map(function () use ($faker) {
            return [
                'nombre'       => $faker->firstName . ' ' . $faker->lastName,
                'curp'         => strtoupper($faker->bothify('????######??????##')),
                'fecha_nac'    => $faker->date('Y-m-d', '2000-01-01'),
                'direccion'    => $faker->streetAddress . ', ' . $faker->city,
                'ultimo_credito' => $faker->date('Y-m-d'),
                'monto_credito'  => $faker->numberBetween(1000, 20000),
                'telefono'     => $faker->numerify('55########'),
                'fallas'       => $faker->numberBetween(1, 10),
            ];
        });

        return [
            'nombre'   => $faker->name,
            'clientes' => $clientes,
        ];
    });
@endphp

<x-layouts.mobile.mobile-layout>
    <div class="p-4 space-y-5">
        <h1 class="text-xl font-bold text-gray-900">Cartera Inactiva</h1>

        @foreach($promotores as $promotor)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                {{-- Header Promotor --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 text-[12px] font-bold rounded-full bg-gray-200 text-gray-700">
                            {{ $loop->iteration }}
                        </span>
                        <div>
                            <span class="block text-[15px] font-semibold text-gray-900">{{ $promotor['nombre'] }}</span>
                            <span class="text-xs text-gray-500">Promotor</span>
                        </div>
                    </div>
                </div>

                {{-- Lista de clientes inactivos --}}
                <div class="px-3 py-2">
                    @foreach($promotor['clientes'] as $idx => $cliente)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            {{-- Datos cliente básicos --}}
                            <div>
                                <p class="text-[14px] font-medium text-gray-800">{{ $cliente['nombre'] }}</p>
                                <p class="text-[12px] text-gray-500">Fallas: {{ $cliente['fallas'] }}</p>
                            </div>

                            {{-- Botones --}}
                            <div class="flex gap-2">
                                {{-- Botón Detalles --}}

                                <button @click="$store.details.open(@js($cliente))"
                                    class="w-8 h-8 border-2 border-blue-500 text-blue-500 rounded-full flex items-center justify-center"
                                    title="Detalle">
                                    D
                                </button>

                                {{-- Botón Llamar --}}
                                <a href="tel:{{ $c['telefono'] ?? $c->telefono ?? '' }}"
                                  class="w-8 h-8 border-2 border-green-500 text-green-500 rounded-full flex items-center justify-center"
                                  title="Llamar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a.75.75 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143.75.75 0 0 1 .38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Botón Regresar --}}
        <a href="{{ url()->previous() }}"
          class="flex items-center justify-center rounded-xl border border-gray-300 text-white text-sm font-semibold px-3 py-2 bg-blue-600 hover:bg-blue-700 shadow-sm">
          Regresar
        </a>
    </div>
    @include('mobile.modals.detalle')
</x-layouts.mobile.mobile-layout>
