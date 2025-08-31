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
                ],
                [
                    'nombre'         => 'María Gómez',
                    'email'          => 'maria@example.com',
                    'domicilio'      => 'Calle Luna 456, CDMX',
                    'promotor'       => 'Ana Torres',
                    'tipo_credito'   => 'en falla',
                    'monto_credito'  => 7000,
                    'fecha_creacion' => '2024-06-15',
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
                            <div x-data="{ open: false }" class="border border-gray-200 rounded">
                                <div class="flex items-center justify-between p-2 cursor-pointer" @click="open = !open">
                                    <p class="font-semibold text-gray-800">{{ $r['nombre'] }}</p>
                                    <a href="#" @click.stop class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded">D</a>
                                </div>
                                <div x-show="open" x-cloak class="p-2 text-sm text-gray-700 space-y-1">
                                    <p><span class="font-semibold">Domicilio:</span> {{ $r['domicilio'] }}</p>
                                    <p><span class="font-semibold">Promotor:</span> {{ $r['promotor'] }}</p>
                                    <p><span class="font-semibold">Tipo de crédito:</span> {{ ucfirst($r['tipo_credito']) }}</p>
                                    <p><span class="font-semibold">Cantidad:</span> ${{ number_format($r['monto_credito'], 2) }}</p>
                                    <p><span class="font-semibold">Fecha:</span> {{ $r['fecha_creacion'] }}</p>
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
