<x-layouts.authenticated :title="$vistaMeta['title'] ?? 'Zonas de riesgo'">
    @php
        $zones = $zones ?? [];
        $signals = $signals ?? [];
        $playbook = $playbook ?? [];
        $vistaMeta = $vistaMeta ?? [
            'numero' => '5',
            'title' => 'Zonas de riesgo',
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
                        {{ $vistaMeta['numero'] ?? '—' }}. {{ ucfirst($vistaMeta['title'] ?? 'Zonas de riesgo') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Cruza indicadores de falla real, falla de sistema y cartera vencida para priorizar visitas ejecutivas.
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

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Mapa operativo</h2>
                    <p class="text-sm text-gray-600">Clasificación por zona de atención.</p>
                </div>
                <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Descargar PDF
                </button>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Zona</th>
                            <th class="px-6 py-3 text-left">Nivel de riesgo</th>
                            <th class="px-6 py-3 text-left">Falla real</th>
                            <th class="px-6 py-3 text-left">Falla de sistema</th>
                            <th class="px-6 py-3 text-left">Acciones sugeridas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($zones as $zone)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $zone['zona'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                        @if ($zone['riesgo'] === 'Alto') bg-rose-100 text-rose-700
                                        @elseif ($zone['riesgo'] === 'Medio') bg-amber-100 text-amber-700
                                        @else bg-emerald-100 text-emerald-700 @endif">
                                        {{ $zone['riesgo'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-amber-600">{{ $zone['falla_real'] }}</td>
                                <td class="px-6 py-4 text-red-600">{{ $zone['falla_sistema'] }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $zone['acciones'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Señales de alerta</h2>
                    <p class="text-sm text-gray-600">Acciones alineadas con cierre viernes 12:00 y multas sabatinas.</p>
                </header>
                <div class="divide-y divide-gray-100">
                    @foreach ($signals as $signal)
                        <div class="px-6 py-4">
                            <h3 class="text-base font-semibold text-gray-900">{{ $signal['tipo'] }}</h3>
                            <p class="text-sm text-gray-600">{{ $signal['detalle'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Playbook</h2>
                    <p class="text-sm text-gray-600">Pasos sugeridos para contener riesgo.</p>
                </header>
                <div class="divide-y divide-gray-100">
                    @foreach ($playbook as $step)
                        <div class="px-6 py-4">
                            <h3 class="text-base font-semibold text-gray-900">{{ $step['etapa'] }}</h3>
                            <p class="text-sm text-gray-600">{{ $step['accion'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
