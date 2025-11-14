<x-layouts.authenticated :title="$vistaMeta['title'] ?? 'Reporte Venta y Falla'">
    @php
        $filters = $filters ?? ['periods' => [], 'plazas' => [], 'ejecutivos' => []];
        $metrics = $metrics ?? [];
        $timeline = $timeline ?? [];
        $fallas = $fallas ?? [];
        $alerts = $alerts ?? [];
        $vistaMeta = $vistaMeta ?? [
            'numero' => '1',
            'title' => 'Venta y falla',
            'category' => 'Reportes',
        ];

        $formatPercent = fn ($value) => is_numeric($value)
            ? number_format($value * 100, 1) . '%'
            : $value;
        $formatMoney = fn ($value) => is_numeric($value)
            ? '$ ' . number_format($value / 1000, 1) . ' K'
            : $value;
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-7xl space-y-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        {{ $vistaMeta['category'] ?? 'Reportes' }} · Vista {{ $vistaMeta['numero'] ?? '—' }}
                    </p>
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $vistaMeta['numero'] ?? '—' }}. {{ ucfirst($vistaMeta['title'] ?? 'Venta y falla') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Cruce semanal entre venta efectiva, DEBE operativo y los diferentes tipos de falla definidos en el documento maestro.
                    </p>
                </div>
                <a
                    href="{{ route('reportes') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    <span>&larr;</span>
                    Regresar a reportes
                </a>
            </div>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm p-6 space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <label class="text-sm text-gray-600">
                    Periodo
                    <select class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                        @foreach ($filters['periods'] as $period)
                            <option>{{ $period }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm text-gray-600">
                    Plaza
                    <select class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                        @foreach ($filters['plazas'] as $plaza)
                            <option>{{ $plaza }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm text-gray-600">
                    Ejecutivo
                    <select class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                        @foreach ($filters['ejecutivos'] as $exe)
                            <option>{{ $exe }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="flex flex-wrap gap-3 text-sm font-semibold">
                <button class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-50">
                    Limpiar filtros
                </button>
                <button class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Actualizar datos
                </button>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
            @foreach ($metrics as $metric)
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $metric['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $metric['value'] }}</p>
                    <p class="text-sm text-gray-500">{{ $metric['helper'] }}</p>
                    <span class="inline-flex text-xs font-semibold text-blue-600">{{ $metric['trend'] ?? '' }}</span>
                </article>
            @endforeach
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Serie semanal</h2>
                    <p class="text-sm text-gray-600">Convierte DEBE a efectivo con la fórmula (DEBE ÷ factor) × 100.</p>
                </div>
                <button class="rounded-lg bg-white border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Descargar CSV
                </button>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 uppercase text-xs text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Semana</th>
                            <th class="px-6 py-3 text-left">Venta</th>
                            <th class="px-6 py-3 text-left">DEBE operativo</th>
                            <th class="px-6 py-3 text-left">Falla real</th>
                            <th class="px-6 py-3 text-left">Falla de sistema</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($timeline as $row)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $row['period'] }}</td>
                                <td class="px-6 py-4">{{ $formatMoney($row['venta']) }}</td>
                                <td class="px-6 py-4">{{ $formatMoney($row['debe']) }}</td>
                                <td class="px-6 py-4 text-amber-600">{{ $formatPercent($row['falla_real']) }}</td>
                                <td class="px-6 py-4 text-red-600">{{ $formatPercent($row['falla_sistema']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Causas de falla</h2>
                    <p class="text-sm text-gray-600">Aplica medidas según glosario: semana extra, solicitud de garantías o bloqueo.</p>
                </header>
                <div class="divide-y divide-gray-100">
                    @foreach ($fallas as $cause)
                        <div class="px-6 py-4">
                            <h3 class="text-base font-semibold text-gray-900">{{ $cause['cause'] }}</h3>
                            <p class="text-sm text-gray-600">Impacto: {{ $cause['impact'] }}</p>
                            <p class="text-sm text-blue-600 font-medium">Mitigación: {{ $cause['mitigation'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Alertas y recomendaciones</h2>
                    <p class="text-sm text-gray-600">Basadas en los lineamientos de cierre del documento maestro.</p>
                </header>
                <div class="divide-y divide-gray-100">
                    @forelse ($alerts as $alert)
                        <div class="px-6 py-4">
                            <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                {{ strtoupper($alert['type']) }}
                            </span>
                            <p class="mt-2 text-sm text-gray-700">{{ $alert['message'] }}</p>
                        </div>
                    @empty
                        <p class="px-6 py-4 text-sm text-gray-500">Sin alertas para el periodo seleccionado.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
