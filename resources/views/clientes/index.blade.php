<x-layouts.authenticated title="Clientes">
    @php
        $clientes = $clientes ?? [];
    @endphp

    <div class="mx-auto max-w-7xl py-8 space-y-8">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Directorio de clientes</h1>
                <p class="text-gray-600">Listado de clientes administrados con informacion resumida.</p>
            </div>
            <a href="{{ route('administrativo.clientes.create') }}"
               class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Nuevo cliente
            </a>
        </div>

        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Nombre</th>
                            <th class="px-6 py-3">CURP</th>
                            <th class="px-6 py-3">Promotor</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Monto maximo</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($clientes as $cliente)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $cliente['nombre'] }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $cliente['curp'] }}</td>
                                <td class="px-6 py-4">{{ $cliente['promotor'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                        {{ $cliente['estado'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">${{ number_format($cliente['monto_maximo'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-3 text-sm">
                                        <a href="{{ route('administrativo.clientes.show', ['cliente' => $loop->iteration]) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                            Ver
                                        </a>
                                        <a href="{{ route('administrativo.clientes.edit', ['cliente' => $loop->iteration]) }}" class="text-gray-600 hover:text-gray-700 font-medium">
                                            Editar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                    No hay clientes registrados todavia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
