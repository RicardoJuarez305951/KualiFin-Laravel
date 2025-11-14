<x-layouts.authenticated :title="$vistaMeta['title'] ?? 'Cierres por ejecutivo'">
    @php
        $filters = $filters ?? ['periods' => [], 'ejecutivos' => [], 'supervisores' => []];
        $executives = $executives ?? [];
        $activity = $activity ?? [];
        $vistaMeta = $vistaMeta ?? [
            'numero' => '2',
            'title' => 'Cierres por ejecutivo',
            'category' => 'Administración',
        ];
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-7xl space-y-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        {{ $vistaMeta['category'] ?? 'Administración' }} · Vista {{ $vistaMeta['numero'] ?? '—' }}
                    </p>
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $vistaMeta['numero'] ?? '—' }}. {{ $vistaMeta['title'] ?? 'Cierres por ejecutivo' }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Seguimiento operativo a los cierres semanales por ejecutivo para definir semana extra, bonos cero falla o bloqueos.
                    </p>
                </div>
                <a
                    href="{{ route('administrativo.administracion') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    <span>&larr;</span>
                    Regresar a administración
                </a>
            </div>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Filtros activos</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <label class="text-sm text-gray-600">
                    Periodo
                    <select class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach ($filters['periods'] as $period)
                            <option>{{ $period }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm text-gray-600">
                    Ejecutivo
                    <select class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach ($filters['ejecutivos'] as $executive)
                            <option>{{ $executive }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm text-gray-600">
                    Supervisor
                    <select class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach ($filters['supervisores'] as $supervisor)
                            <option>{{ $supervisor }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="flex flex-wrap gap-3 text-sm font-semibold">
                <button class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-50">
                    Limpiar filtros
                </button>
                <button class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Aplicar filtros
                </button>
                <button class="inline-flex items-center rounded-lg bg-white border border-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-50">
                    Exportar Excel
                </button>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach ($executives as $exec)
                <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase">{{ $exec['plaza'] }}</p>
                            <h3 class="text-xl font-bold text-gray-900">{{ $exec['nombre'] }}</h3>
                        </div>
                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ $exec['bono'] }}
                        </span>
                    </div>
                    <dl class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                        <div>
                            <dt class="font-semibold text-gray-500 uppercase tracking-wide">Venta</dt>
                            <dd class="text-lg font-bold text-gray-900">{{ $exec['venta'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-gray-500 uppercase tracking-wide">DEBE operativo</dt>
                            <dd class="text-lg font-bold text-gray-900">{{ $exec['debe_operativo'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-gray-500 uppercase tracking-wide">Falla real</dt>
                            <dd class="text-lg font-bold text-amber-600">{{ $exec['falla_real'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-gray-500 uppercase tracking-wide">Falla de sistema</dt>
                            <dd class="text-lg font-bold text-red-600">{{ $exec['falla_sistema'] }}</dd>
                        </div>
                    </dl>
                    <p class="text-sm text-gray-600">{{ $exec['observaciones'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                @foreach ($executives as $exec)
                    <article class="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <header class="border-b border-gray-100 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $exec['nombre'] }} · detalle de promotoras</h3>
                            <p class="text-sm text-gray-600">Controla semana de paro y aplica bloqueo por falla real > 3%.</p>
                        </header>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-600">
                                <thead class="bg-gray-50 uppercase text-xs text-gray-500">
                                    <tr>
                                        <th class="px-6 py-3 text-left">Promotora</th>
                                        <th class="px-6 py-3 text-left">Ventas</th>
                                        <th class="px-6 py-3 text-left">DEBE</th>
                                        <th class="px-6 py-3 text-left">Mora 7d</th>
                                        <th class="px-6 py-3 text-left">Semana de paro</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($exec['promotoras'] as $team)
                                        <tr>
                                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $team['nombre'] }}</td>
                                            <td class="px-6 py-4">{{ $team['ventas'] }}</td>
                                            <td class="px-6 py-4">{{ $team['debe'] }}</td>
                                            <td class="px-6 py-4">{{ $team['mora7'] }}</td>
                                            <td class="px-6 py-4">{{ $team['semana_paro'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </article>
                @endforeach
            </div>
            <aside class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <header class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900">Bitácora de cierre</h3>
                        <p class="text-sm text-gray-500">Fechas clave: viernes 12:00 cierre, sábado 18:00 multa promotora.</p>
                    </header>
                    <div class="divide-y divide-gray-100">
                        @forelse ($activity as $log)
                            <div class="px-5 py-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $log['time'] }}</p>
                                <p class="text-sm text-gray-700">{{ $log['message'] }}</p>
                            </div>
                        @empty
                            <p class="px-5 py-4 text-sm text-gray-500">Sin actividad registrada.</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-2">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Glosario express</h4>
                    <p class="text-sm text-gray-600"><span class="font-semibold">DEBE:</span> Pagos esperados semanalmente; convertir a efectivo con factor 11/10/8 según plazo.</p>
                    <p class="text-sm text-gray-600"><span class="font-semibold">Falla real:</span> Pago fuera de tiempo (antes del cierre del viernes) que genera alertas inmediatas.</p>
                    <p class="text-sm text-gray-600"><span class="font-semibold">Falla de sistema:</span> Pagos no recuperados al viernes 12:00 que suman semana extra.</p>
                </div>
            </aside>
        </section>
        </div>
    </div>
</x-layouts.authenticated>
