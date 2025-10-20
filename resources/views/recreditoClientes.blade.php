<x-layouts.authenticated title="Recredito de clientes">
    @php
        $applications = $applications ?? [];
    @endphp

    <div class="mx-auto max-w-6xl py-8 space-y-8">
        <header class="space-y-2 text-center">
            <h1 class="text-3xl font-bold text-gray-900">Recredito de clientes</h1>
            <p class="text-gray-600">Listado de solicitudes recientes con informacion resumida.</p>
        </header>

        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="flex flex-col gap-4 px-6 py-4 border-b md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Solicitudes activas</h2>
                    <p class="text-sm text-gray-500">Vista de seguimiento rapido para el area administrativa.</p>
                </div>
                <button class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Crear solicitud manual
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Cliente</th>
                            <th class="px-6 py-3">CURP</th>
                            <th class="px-6 py-3">Ciclo</th>
                            <th class="px-6 py-3">Monto solicitado</th>
                            <th class="px-6 py-3">Ultimo pago</th>
                            <th class="px-6 py-3">Estatus</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($applications as $application)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $application['cliente'] }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $application['curp'] }}</td>
                                <td class="px-6 py-4">{{ $application['ciclo'] }}</td>
                                <td class="px-6 py-4 text-right font-semibold">
                                    ${{ number_format($application['monto_solicitado'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ $application['ultimo_pago'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700">
                                        {{ $application['estatus'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                        Ver detalle
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-6 text-center text-gray-500">
                                    No hay solicitudes registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
