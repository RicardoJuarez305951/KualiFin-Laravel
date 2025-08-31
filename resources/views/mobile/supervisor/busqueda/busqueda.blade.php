<x-layouts.mobile.mobile-layout title="Búsqueda">
    @php
        $faker = \Faker\Factory::create('es_MX');
        $fake = [
            'clientes' => collect(range(1, 5))->map(function ($i) use ($faker) {
                return [
                    'nombre'         => $faker->name(),
                    'email'          => $faker->safeEmail(),
                    'domicilio'      => $faker->streetAddress() . ', ' . $faker->city(),
                    'promotor'       => $faker->name(),
                    'tipo_credito'   => $faker->randomElement(['activo', 'en falla']),
                    'monto_credito'  => $faker->numberBetween(3000, 20000),
                    'fecha_creacion' => $faker->date('Y-m-d'),
                    'status'         => $faker->randomElement(['activo_con_deuda','activo_sin_deuda','liquidado','deudor']),
                    'deuda'          => $faker->randomFloat(2, 100, 5000),
                    'deuda_interes'  => $faker->randomFloat(2, 100, 5000),
                    'fotos'          => [
                        asset('imagenes_kualifin_propuestas/' . $faker->numberBetween(1,11) . '.jpg'),
                        asset('imagenes_kualifin_propuestas/' . $faker->numberBetween(1,11) . '.jpg'),
                    ],
                     'garantia'       => asset('imagenes_kualifin_propuestas/' . $faker->numberBetween(1,11) . '.jpg'),
                    'aval'     => [
                        'nombre'   => $faker->name(),
                        'telefono' => $faker->phoneNumber(),
                    ],
                ];
            })->toArray(),
        ];

        $query = request('q');
        $resultados = [];

        if ($query) {
            foreach ($fake as $tipo => $lista) {
                foreach ($lista as $item) {
                    if (stripos($item['nombre'], $query) !== false || stripos($item['email'], $query) !== false) {
                        $resultados[] = array_merge($item, ['tipo' => $tipo]);
                    }
                }
            }
        }
    @endphp

    <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md space-y-6">
        <h1 class="text-xl font-bold text-gray-900 text-center">Ingresa tu búsqueda</h1>

        <form method="GET" class="space-y-4">
            <input
                type="text"
                name="q"
                value="{{ $query }}"
                placeholder="Buscar..."
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
            <div class="flex justify-between gap-4">
                <button
                    type="submit"
                    class="flex-1 py-2 bg-blue-800 text-white font-semibold rounded-lg hover:bg-blue-900 shadow-sm"
                >Buscar</button>
                <a
                    href="{{ route('mobile.supervisor.index') }}"
                    class="flex-1 py-2 text-center bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400"
                >Regresar</a>
            </div>
        </form>

        @if($query)
            @if(count($resultados))
                <div class="space-y-2">
                    @foreach($resultados as $r)
                        @if($r['tipo'] === 'clientes')
                            <div x-data="{ open: false, detail: false }" class="border border-gray-200 rounded">
                                <div class="flex items-center justify-between p-2 cursor-pointer" @click="open = !open">
                                    <p class="font-semibold text-gray-800">{{ $r['nombre'] }}</p>
                                    <a href="#" @click.stop="detail = true" class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded">D</a>
                                </div>
                                <div x-show="open" x-cloak class="p-2 text-sm text-gray-700 space-y-1">
                                    <p><span class="font-semibold">Domicilio:</span> {{ $r['domicilio'] }}</p>
                                    <p><span class="font-semibold">Promotor:</span> {{ $r['promotor'] }}</p>
                                    <p><span class="font-semibold">Tipo de crédito:</span> {{ ucfirst($r['tipo_credito']) }}</p>
                                    <p><span class="font-semibold">Cantidad:</span> ${{ number_format($r['monto_credito'], 2) }}</p>
                                    <p><span class="font-semibold">Fecha:</span> {{ $r['fecha_creacion'] }}</p>
                                </div>

                                <div x-show="detail" x-cloak @click.self="detail = false" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
                                    <div class="bg-white rounded-lg p-4 w-full max-w-md relative">
                                        <button class="absolute top-2 right-2 text-gray-500" @click="detail = false">✕</button>
                                        <h2 class="text-lg font-bold mb-2">{{ $r['nombre'] }}</h2>
                                        <p class="mb-2">
                                            <span class="font-semibold">Status:</span>
                                            @if($r['status'] === 'activo_con_deuda')
                                                Activo con deuda: ${{ number_format($r['deuda'], 2) }}
                                            @elseif($r['status'] === 'activo_sin_deuda')
                                                Activo sin deuda
                                            @elseif($r['status'] === 'liquidado')
                                                Liquidado
                                            @elseif($r['status'] === 'deudor')
                                                Deudor: ${{ number_format($r['deuda_interes'], 2) }}
                                            @endif
                                        </p>
                                        <div class="grid grid-cols-2 gap-2 mb-2">
                                            @foreach($r['fotos'] as $foto)
                                                <img src="{{ $foto }}" alt="Foto del cliente" class="rounded" />
                                            @endforeach
                                            <img src="{{ $r['garantia'] }}" alt="Foto de la garantía" class="rounded col-span-2" />
                                        </div>
                                        <div class="text-sm">
                                            <p class="font-semibold">Aval</p>
                                            <p>{{ $r['aval']['nombre'] }}</p>
                                            <p>{{ $r['aval']['telefono'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-2 border border-gray-200 rounded">
                                <p class="font-semibold">{{ ucfirst($r['tipo']) }}: {{ $r['nombre'] }}</p>
                                <p class="text-sm text-gray-600">{{ $r['email'] }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500">No se encontraron resultados.</p>
            @endif
        @endif
    </div>
</x-layouts.mobile.mobile-layout>