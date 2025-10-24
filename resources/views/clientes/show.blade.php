<x-layouts.authenticated title="Detalle de cliente">
    @php
        $cliente = $cliente ?? null;
        $historial = $historial ?? [];
    @endphp

    <div class="mx-auto max-w-4xl py-8 space-y-8">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $cliente['nombre'] ?? 'Cliente sin nombre' }}
                </h1>
                <p class="text-gray-600">Resumen operativo del cliente seleccionado.</p>
            </div>
            <a href="{{ route('administrativo.clientes.edit', ['cliente' => $cliente['id'] ?? 1]) }}"
               class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Editar cliente
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white border rounded-lg shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Informacion general</h2>
                <dl class="space-y-3 text-sm text-gray-600">
                    <div>
                        <dt class="font-medium text-gray-700">CURP</dt>
                        <dd class="font-mono uppercase">{{ $cliente['curp'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Promotor</dt>
                        <dd>{{ $cliente['promotor'] ?? 'Sin asignar' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Telefono</dt>
                        <dd>{{ $cliente['telefono'] ?? 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Monto maximo autorizado</dt>
                        <dd>${{ number_format($cliente['monto_maximo'] ?? 0, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Saldo vigente</dt>
                        <dd>${{ number_format($cliente['saldo_vigente'] ?? 0, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Ultima actualizacion</dt>
                        <dd>{{ $cliente['ultima_actualizacion'] ?? 'Sin registro' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white border rounded-lg shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Estatus</h2>
                <div class="inline-flex rounded-full border border-green-200 bg-green-50 px-4 py-1 text-sm font-semibold text-green-700">
                    {{ $cliente['estado'] ?? 'Sin estado' }}
                </div>
                <p class="text-sm text-gray-600">
                    Esta informacion es solo ilustrativa para la vista administrativa.
                </p>
            </div>
        </div>

        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Historial rapido</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($historial as $evento)
                    <div class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">{{ $evento['evento'] }}</p>
                        <p class="text-sm text-gray-600">{{ $evento['fecha'] }} - {{ $evento['detalles'] }}</p>
                    </div>
                @empty
                    <div class="px-6 py-6 text-sm text-gray-500">
                        No hay movimientos registrados.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.authenticated>
