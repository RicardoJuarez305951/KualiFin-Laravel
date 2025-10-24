<x-layouts.authenticated title="Panel de revision">
    @php
        $alerts = $alerts ?? [];
        $checklist = $checklist ?? [];
    @endphp

    <div class="mx-auto max-w-6xl py-8 space-y-8">
        <header class="space-y-2 text-center">
            <h1 class="text-3xl font-bold text-gray-900">Panel de revision documental</h1>
            <p class="text-gray-600">Monitorea solicitudes y seguimiento interno.</p>
        </header>

        @if ($alerts)
            <div class="space-y-3">
                @foreach ($alerts as $alert)
                    @php
                        $baseClasses = 'flex items-start gap-3 rounded-lg border px-4 py-3 text-sm';
                        $styles = match ($alert['type'] ?? 'info') {
                            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
                            'danger' => 'bg-red-50 border-red-200 text-red-700',
                            default => 'bg-blue-50 border-blue-200 text-blue-700',
                        };
                    @endphp
                    <div class="{{ $baseClasses }} {{ $styles }}">
                        <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-current"></span>
                        <p>{{ $alert['message'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Checklist de evaluaciones</h2>
                <button class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    Registrar avance
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Folio</th>
                            <th class="px-6 py-3">Cliente</th>
                            <th class="px-6 py-3">Monto</th>
                            <th class="px-6 py-3">Estatus</th>
                            <th class="px-6 py-3">Responsable</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($checklist as $item)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $item['folio'] }}</td>
                                <td class="px-6 py-4">{{ $item['cliente'] }}</td>
                                <td class="px-6 py-4 text-right font-semibold">
                                    ${{ number_format($item['monto'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        {{ $item['estado'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $item['responsable'] }}</td>
                                <td class="px-6 py-4">
                                    <button class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                        Ver detalles
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                    No hay registros en curso.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
