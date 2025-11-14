<x-layouts.authenticated :title="$vistaMeta['title'] ?? 'Cartera actual'">
    @php
        $portfolioMetrics = $portfolioMetrics ?? [];
        $aging = $aging ?? [];
        $mix = $mix ?? [];
        $clients = $clients ?? [];
        $vistaMeta = $vistaMeta ?? [
            'numero' => '6',
            'title' => 'Cartera actual',
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
                        {{ $vistaMeta['numero'] ?? '—' }}. {{ ucfirst($vistaMeta['title'] ?? 'Cartera actual') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Conoce la mezcla vigente, el DEBE semanal y los clientes clave para priorizar cobranza y liberaciones.
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
            @foreach ($portfolioMetrics as $card)
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                    <p class="text-sm text-gray-600">{{ $card['helper'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Aging</h2>
                        <p class="text-sm text-gray-600">Reglas: semana extra al pasar a 8-30, bloqueo en >30.</p>
                    </div>
                    <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Exportar XLSX
                    </button>
                </header>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3 text-left">Bucket</th>
                                <th class="px-6 py-3 text-left">Monto</th>
                                <th class="px-6 py-3 text-left">Clientes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($aging as $item)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $item['bucket'] }}</td>
                                    <td class="px-6 py-4 text-blue-600">{{ $item['monto'] }}</td>
                                    <td class="px-6 py-4">{{ $item['clientes'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Mix por plazo</h2>
                    <p class="text-sm text-gray-600">Recuerda: factor 11 (13 semanas), 10 (14) y 8 (22) para convertir DEBE.</p>
                </header>
                <div class="divide-y divide-gray-100">
                    @foreach ($mix as $item)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-base font-semibold text-gray-900">{{ $item['plazo'] }}</p>
                                <p class="text-sm text-gray-600">Factor: {{ $item['debe_factor'] }}</p>
                            </div>
                            <span class="text-2xl font-bold text-gray-900">{{ $item['porcentaje'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Clientes clave</h2>
                <p class="text-sm text-gray-600">Ideal para definir planes de cobranza o liberaciones.</p>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Cliente</th>
                            <th class="px-6 py-3 text-left">Promotora</th>
                            <th class="px-6 py-3 text-left">Saldo</th>
                            <th class="px-6 py-3 text-left">Estatus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($clients as $client)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $client['cliente'] }}</td>
                                <td class="px-6 py-4">{{ $client['promotora'] }}</td>
                                <td class="px-6 py-4 text-blue-600">{{ $client['saldo'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ $client['estatus'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
