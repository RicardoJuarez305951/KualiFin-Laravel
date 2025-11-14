<x-layouts.authenticated :title="$vistaMeta['title'] ?? 'Crecimiento y decrecimiento'">
    @php
        $growthSummary = $growthSummary ?? [];
        $history = $history ?? [];
        $drivers = $drivers ?? [];
        $actions = $actions ?? [];
        $vistaMeta = $vistaMeta ?? [
            'numero' => '4',
            'title' => 'Crecimiento y decrecimiento',
            'category' => 'Reportes',
        ];
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-7xl space-y-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        {{ $vistaMeta['category'] ?? 'Reportes' }} · Vista {{ $vistaMeta['numero'] ?? '—' }}
                    </p>
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $vistaMeta['numero'] ?? '—' }}. {{ ucfirst($vistaMeta['title'] ?? 'Crecimiento y decrecimiento') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Evalúa la evolución de venta, DEBE y clientes por plaza para tomar decisiones de expansión o contención.
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

        <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
            @foreach ($growthSummary as $row)
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $row['plaza'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $row['crecimiento'] }}</p>
                    <p class="text-sm text-gray-600">DEBE: {{ $row['debe'] }}</p>
                    <p class="text-sm text-gray-600">Nuevos clientes: {{ $row['nuevos'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Histórico reciente</h2>
                    <p class="text-sm text-gray-600">Totales globales por mes (venta convertida a efectivo y clientes activos).</p>
                </div>
                <button class="rounded-lg bg-white border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Exportar
                </button>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Mes</th>
                            <th class="px-6 py-3 text-left">Venta</th>
                            <th class="px-6 py-3 text-left">Clientes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($history as $row)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $row['periodo'] }}</td>
                                <td class="px-6 py-4">$ {{ number_format($row['venta'] / 1000, 1) }} K</td>
                                <td class="px-6 py-4">{{ $row['clientes'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Factores clave</h2>
                    <p class="text-sm text-gray-600">Relacionados con metas S.M.A.R.T. y políticas de crecimiento.</p>
                </header>
                <div class="divide-y divide-gray-100">
                    @foreach ($drivers as $driver)
                        <div class="px-6 py-4">
                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                {{ $driver['factor'] }}
                            </span>
                            <p class="mt-2 text-sm text-gray-700">{{ $driver['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Plan de acción</h2>
                    <p class="text-sm text-gray-600">Acciones alineadas a gerencia y ejecutivos.</p>
                </header>
                <div class="divide-y divide-gray-100">
                    @foreach ($actions as $task)
                        <div class="px-6 py-4">
                            <h3 class="text-base font-semibold text-gray-900">{{ $task['name'] }}</h3>
                            <p class="text-sm text-gray-600">Responsable: {{ $task['owner'] }}</p>
                            <p class="text-sm text-gray-500">Fecha: {{ $task['deadline'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
