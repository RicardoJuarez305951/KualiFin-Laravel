<x-layouts.authenticated :title="$vistaMeta['title'] ?? 'Reporte de Falla Acumulada'">
    @php
        $summary = $summary ?? [];
        $cohorts = $cohorts ?? [];
        $clients = $clients ?? [];
        $thresholds = $thresholds ?? [];
        $vistaMeta = $vistaMeta ?? [
            'numero' => '3',
            'title' => 'Falla acumulada',
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
                        {{ $vistaMeta['numero'] ?? '—' }}. {{ ucfirst($vistaMeta['title'] ?? 'Falla acumulada') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Monitorea fallas reales y de sistema por cohorte para decidir bloqueos, semanas extra y garantías conforme al glosario.
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
            @foreach ($summary as $card)
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                    <span class="inline-flex text-xs font-semibold text-purple-600">{{ $card['trend'] }}</span>
                </article>
            @endforeach
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Cohortes</h2>
                <p class="text-sm text-gray-600">DEBE y fallas acumuladas durante las últimas semanas.</p>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Semana</th>
                            <th class="px-6 py-3 text-left">Clientes</th>
                            <th class="px-6 py-3 text-left">DEBE</th>
                            <th class="px-6 py-3 text-left">Falla acumulada</th>
                            <th class="px-6 py-3 text-left">RQ recuperado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($cohorts as $cohort)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $cohort['cohort'] }}</td>
                                <td class="px-6 py-4">{{ $cohort['clientes'] }}</td>
                                <td class="px-6 py-4">{{ $cohort['debe'] }}</td>
                                <td class="px-6 py-4 text-amber-600">{{ $cohort['fallas'] }}</td>
                                <td class="px-6 py-4 text-blue-600">{{ $cohort['rq'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Clientes críticos</h2>
                    <p class="text-sm text-gray-600">Aplica acciones según reglas del glosario (garantías, bloqueo, semana extra).</p>
                </header>
                <div class="divide-y divide-gray-100">
                    @foreach ($clients as $client)
                        <div class="px-6 py-4">
                            <h3 class="text-base font-semibold text-gray-900">{{ $client['cliente'] }}</h3>
                            <p class="text-sm text-gray-600">Promotora: {{ $client['promotora'] }}</p>
                            <p class="text-sm text-gray-600">Fallas reales: {{ $client['fallas_real'] }} · Falla de sistema: {{ $client['fallas_sistema'] }}</p>
                            <span class="mt-2 inline-flex rounded-full bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700">
                                {{ $client['status'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Umbrales operativos</h2>
                    <p class="text-sm text-gray-600">Basados en el Documento Maestro Kualifin Ultra.</p>
                </header>
                <div class="divide-y divide-gray-100">
                    @foreach ($thresholds as $rule)
                        <div class="px-6 py-4">
                            <h3 class="text-base font-semibold text-gray-900">{{ $rule['rule'] }}</h3>
                            <p class="text-sm text-gray-600">{{ $rule['action'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
