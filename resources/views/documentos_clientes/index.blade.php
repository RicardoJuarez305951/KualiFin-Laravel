<x-layouts.authenticated title="Documentos de clientes">
    @php
        $documentos = $documentos ?? [];
    @endphp

    <div class="mx-auto max-w-7xl py-8 space-y-8">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Documentos de clientes</h1>
                <p class="text-gray-600">Seguimiento basico de entregables para el rol administrativo.</p>
            </div>
            <a href="{{ route('administrativo.documentos.create') }}"
               class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Registrar documento
            </a>
        </div>

        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-600">
                        <tr>
                            <th class="px-6 py-3">ID</th>
                            <th class="px-6 py-3">Cliente</th>
                            <th class="px-6 py-3">Tipo</th>
                            <th class="px-6 py-3">Estatus</th>
                            <th class="px-6 py-3">Actualizado</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($documentos as $documento)
                            <tr>
                                <td class="px-6 py-4 font-mono text-xs text-gray-600">DOC-{{ str_pad($documento['id'], 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $documento['cliente'] }}</td>
                                <td class="px-6 py-4">{{ $documento['tipo'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ $documento['estatus'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ $documento['actualizado'] }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-3 text-sm">
                                        <a href="{{ route('administrativo.documentos.show', ['documento' => $documento['id']]) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                            Ver
                                        </a>
                                        <a href="{{ route('administrativo.documentos.edit', ['documento' => $documento['id']]) }}" class="text-gray-600 hover:text-gray-700 font-medium">
                                            Editar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                    No hay documentos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
