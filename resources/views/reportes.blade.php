<x-layouts.authenticated title="Reportes administrativos">
    @php
        $summary = $summary ?? [];
        $recentReports = $recentReports ?? [];
        $scheduled = $scheduled ?? [];

        $formatMoney = fn ($value) => is_numeric($value) && $value >= 1000
            ? '$' . number_format($value, 0, ',', '.')
            : (is_numeric($value) ? '$' . $value : $value);
    @endphp

    <div class="mx-auto max-w-7xl py-8 space-y-8">
        <div class="space-y-2 text-center">
            <h1 class="text-3xl font-bold text-gray-900">Reportes operativos</h1>
            <p class="text-gray-600">Panel de consulta rapida para el rol administrativo</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
            @forelse ($summary as $card)
                <div class="bg-white border rounded-lg shadow-sm p-6 space-y-2">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ $card['title'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $formatMoney($card['value']) }}
                    </p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600">
                        {{ $card['trend'] ?? '' }}
                    </span>
                </div>
            @empty
                <div class="bg-white border rounded-lg shadow-sm p-6 sm:col-span-2 xl:col-span-4">
                    <p class="text-sm text-gray-500">No hay datos de resumen disponibles.</p>
                </div>
            @endforelse
        </div>

        <div class="bg-white border rounded-lg shadow-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Reportes recientes</h2>
                <button class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    Programar reporte
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Titulo</th>
                            <th class="px-6 py-3">Responsable</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Actualizado</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white text-sm text-gray-700">
                        @forelse ($recentReports as $report)
                            <tr>
                                <td class="px-6 py-4 font-medium">{{ $report['title'] }}</td>
                                <td class="px-6 py-4">{{ $report['owner'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                        {{ $report['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ $report['updated_at'] }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ $report['download_url'] }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                        Descargar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-gray-500">
                                    Aun no se han generado reportes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Reportes programados</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($scheduled as $item)
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-base font-semibold text-gray-900">{{ $item['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $item['frequency'] }}</p>
                        </div>
                        <div class="mt-3 sm:mt-0">
                            <span class="text-sm font-medium text-gray-700">Proxima ejecucion:</span>
                            <p class="text-sm text-gray-600">{{ $item['next_run'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-6 text-sm text-gray-500">
                        No hay procesos programados todavia.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.authenticated>
