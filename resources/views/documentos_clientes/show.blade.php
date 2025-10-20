<x-layouts.authenticated title="Detalle de documento">
    @php
        $documento = $documento ?? [];
        $historial = $historial ?? [];
    @endphp

    <div class="mx-auto max-w-4xl py-8 space-y-8">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Documento de cliente</h1>
                <p class="text-gray-600">Informacion ilustrativa para validar el layout.</p>
            </div>
            <a href="{{ route('administrativo.documentos.edit', ['documento' => $documento['id'] ?? 1]) }}"
               class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Editar documento
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white border rounded-lg shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Resumen</h2>
                <dl class="space-y-3 text-sm text-gray-600">
                    <div>
                        <dt class="font-medium text-gray-700">Cliente</dt>
                        <dd>{{ $documento['cliente'] ?? 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Credito</dt>
                        <dd>{{ $documento['credito'] ?? 'Sin folio' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Tipo de documento</dt>
                        <dd>{{ $documento['tipo'] ?? 'Sin tipo' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Ultima revision</dt>
                        <dd>{{ $documento['ultima_revision'] ?? 'Sin registro' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white border rounded-lg shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Estatus</h2>
                <div class="inline-flex rounded-full border border-indigo-200 bg-indigo-50 px-4 py-1 text-sm font-semibold text-indigo-700">
                    {{ $documento['estatus'] ?? 'Sin estatus' }}
                </div>
                <a href="{{ $documento['url'] ?? '#' }}" target="_blank" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                    Ver documento
                </a>
            </div>
        </div>

        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Historial de acciones</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($historial as $evento)
                    <div class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">{{ $evento['accion'] }}</p>
                        <p class="text-sm text-gray-600">{{ $evento['fecha'] }} - {{ $evento['usuario'] }}</p>
                    </div>
                @empty
                    <div class="px-6 py-6 text-sm text-gray-500">
                        No hay eventos registrados.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.authenticated>
