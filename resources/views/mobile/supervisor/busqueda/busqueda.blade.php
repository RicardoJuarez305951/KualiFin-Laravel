<x-layouts.mobile.mobile-layout title="Búsqueda">
    @php
        $fake = [
            'clientes' => [
                [
                    'nombre'         => 'Juan Pérez',
                    'email'          => 'juan@example.com',
                    'domicilio'      => 'Av. Reforma 123, CDMX',
                    'promotor'       => 'Luis Hernández',
                    'tipo_credito'   => 'activo',
                    'monto_credito'  => 5000,
                    'fecha_creacion' => '2024-05-10',
                    'status'         => 'activo_con_deuda',
                    'deuda'          => 1200,
                    'fotos'          => [
                        'https://via.placeholder.com/150?text=Cliente+1',
                        'https://via.placeholder.com/150?text=Cliente+1',
                    ],
                    'garantia'       => 'https://via.placeholder.com/150?text=Garantia+1',
                    'aval'           => [
                        'nombre'   => 'Carlos López',
                        'telefono' => '555-1111',
                    ],
                ],
                [
                    'nombre'         => 'María Gómez',
                    'email'          => 'maria@example.com',
                    'domicilio'      => 'Calle Luna 456, CDMX',
                    'promotor'       => 'Ana Torres',
                    'tipo_credito'   => 'en falla',
                    'monto_credito'  => 7000,
                    'fecha_creacion' => '2024-06-15',
                    'status'         => 'liquidado',
                    'fotos'          => [
                        'https://via.placeholder.com/150?text=Cliente+2',
                        'https://via.placeholder.com/150?text=Cliente+2',
                    ],
                    'garantia'       => 'https://via.placeholder.com/150?text=Garantia+2',
                    'aval'           => [
                        'nombre'   => 'Laura Ruiz',
                        'telefono' => '555-2222',
                    ],
                ],
                [
                    'nombre'         => 'Pedro López',
                    'email'          => 'pedro@example.com',
                    'domicilio'      => 'Calle Sol 789, CDMX',
                    'promotor'       => 'Luis Hernández',
                    'tipo_credito'   => 'activo',
                    'monto_credito'  => 6000,
                    'fecha_creacion' => '2024-07-01',
                    'status'         => 'activo_sin_deuda',
                    'fotos'          => [
                        'https://via.placeholder.com/150?text=Cliente+3',
                        'https://via.placeholder.com/150?text=Cliente+3',
                    ],
                    'garantia'       => 'https://via.placeholder.com/150?text=Garantia+3',
                    'aval'           => [
                        'nombre'   => 'Miguel Pérez',
                        'telefono' => '555-3333',
                    ],
                ],
                [
                    'nombre'         => 'Lucía Díaz',
                    'email'          => 'lucia@example.com',
                    'domicilio'      => 'Av. Norte 321, CDMX',
                    'promotor'       => 'Ana Torres',
                    'tipo_credito'   => 'en falla',
                    'monto_credito'  => 8000,
                    'fecha_creacion' => '2024-08-20',
                    'status'         => 'deudor',
                    'deuda_interes'  => 2000,
                    'fotos'          => [
                        'https://via.placeholder.com/150?text=Cliente+4',
                        'https://via.placeholder.com/150?text=Cliente+4',
                    ],
                    'garantia'       => 'https://via.placeholder.com/150?text=Garantia+4',
                    'aval'           => [
                        'nombre'   => 'Rosa Martínez',
                        'telefono' => '555-4444',
                    ],
                ],
            ],
            'promotores' => [
                ['nombre' => 'Luis Hernández', 'email' => 'luis@example.com'],
                ['nombre' => 'Ana Torres', 'email' => 'ana@example.com'],
            ],
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
                    href="javascript:history.back()"
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
                                                Activo con cantidad de deuda: ${{ number_format($r['deuda'], 2) }}
                                            @elseif($r['status'] === 'activo_sin_deuda')
                                                Activo pero sin deuda mensual activa
                                            @elseif($r['status'] === 'liquidado')
                                                Liquidado
                                            @elseif($r['status'] === 'deudor')
                                                Deudor con la cantidad de la deuda con intereses: ${{ number_format($r['deuda_interes'], 2) }}
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
