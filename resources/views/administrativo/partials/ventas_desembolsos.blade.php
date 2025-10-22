@php
    $weeklyMetrics = $data['weeklyMetrics'] ?? [];
    $topPromoters = $data['topPromoters'] ?? [];
    $dailyTimeline = $data['dailyTimeline'] ?? [];
    $pipeline = $data['pipeline'] ?? [];
@endphp

<div class="space-y-10">
    <header class="space-y-3">
        <h1 class="text-3xl font-bold text-gray-900">Ventas y desembolsos</h1>
        <p class="text-gray-600">Dashboard operativo para monitorear colocaciones, estatus de desembolso y desempeno de promotores.</p>
    </header>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($weeklyMetrics as $metric)
            <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-3">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ $metric['label'] }}</p>
                <p class="text-3xl font-bold text-gray-900">{{ $metric['value'] }}</p>
                <p class="text-xs font-medium text-blue-600">{{ $metric['trend'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <article class="xl:col-span-7 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Pipeline de creditos</h2>
                    <p class="text-sm text-gray-600">Avances por etapa del flujo operativo.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver tablero</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($pipeline as $stage => $items)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-900">{{ ucfirst($stage) }}</p>
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white text-xs font-semibold text-blue-600 shadow-sm">
                                {{ count($items) }}
                            </span>
                        </div>
                        <div class="space-y-3">
                            @foreach ($items as $item)
                                <div class="rounded-lg border border-white bg-white px-4 py-3 shadow-sm space-y-2">
                                    <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                        <span>{{ $item['folio'] }}</span>
                                        <span>{{ $item['eta'] }}</span>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $item['cliente'] }}</p>
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ $item['responsable'] }}</span>
                                        <span class="font-semibold text-blue-600">{{ $item['monto'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="xl:col-span-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Promotores destacados</h2>
                    <p class="text-sm text-gray-600">Ranking semanal por monto desembolsado.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver todo</button>
            </div>
            <ul class="space-y-3">
                @foreach ($topPromoters as $promoter)
                    <li class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $promoter['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $promoter['zone'] }} - {{ $promoter['deals'] }} creditos</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-blue-600">{{ $promoter['amount'] }}</p>
                            <span class="inline-flex items-center rounded-full border border-blue-200 bg-white px-3 py-1 text-xs font-semibold text-blue-600">
                                {{ $promoter['badge'] }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </article>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <article class="xl:col-span-7 rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Historico semanal</h2>
                    <p class="text-sm text-gray-600">Comparativo de las ultimas 4 semanas por monto y unidades.</p>
                </div>
                <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                    Exportar detalle
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Semana</th>
                            <th class="px-6 py-3 text-left">Monto vendido</th>
                            <th class="px-6 py-3 text-left">Desembolsado</th>
                            <th class="px-6 py-3 text-left">Creditos</th>
                            <th class="px-6 py-3 text-left">% Conversion</th>
                            <th class="px-6 py-3 text-left">Observacion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-900">Semana 03 - Ene 13-19</td>
                            <td class="px-6 py-4">$3,200,000</td>
                            <td class="px-6 py-4">$2,850,000</td>
                            <td class="px-6 py-4">186</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    89%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">Campana nomina Q1 en marcha</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-900">Semana 02 - Ene 06-12</td>
                            <td class="px-6 py-4">$2,860,000</td>
                            <td class="px-6 py-4">$2,470,000</td>
                            <td class="px-6 py-4">162</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                    86%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">Reanudacion post vacaciones</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-900">Semana 01 - Dic 30 - Ene 05</td>
                            <td class="px-6 py-4">$2,140,000</td>
                            <td class="px-6 py-4">$1,930,000</td>
                            <td class="px-6 py-4">128</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                    90%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">Semana corta con guardias</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-900">Semana 52 - Dic 23-29</td>
                            <td class="px-6 py-4">$1,980,000</td>
                            <td class="px-6 py-4">$1,640,000</td>
                            <td class="px-6 py-4">114</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                                    83%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">Alta mora por temporada</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="xl:col-span-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Timeline de hoy</h2>
                    <p class="text-sm text-gray-600">Eventos clave de ventas y desembolsos.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver mas</button>
            </div>
            <ol class="space-y-4 text-sm text-gray-700">
                @foreach ($dailyTimeline as $event)
                    @php
                        $statusColors = [
                            'Liberado' => 'bg-emerald-500',
                            'En curso' => 'bg-amber-500',
                            'Cancelado' => 'bg-rose-500',
                        ];
                    @endphp
                    <li class="flex gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="flex flex-col items-center">
                            <span class="text-xs font-semibold text-gray-500">{{ $event['time'] }}</span>
                            <span class="mt-2 h-2 w-2 rounded-full {{ $statusColors[$event['status']] ?? 'bg-blue-500' }}"></span>
                        </div>
                        <div class="flex-1 space-y-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $event['title'] }}</p>
                            <p class="text-xs text-gray-500">{{ $event['description'] }}</p>
                            <span class="inline-flex rounded-full border border-blue-200 bg-white px-3 py-1 text-xs font-semibold text-blue-600">
                                {{ $event['status'] }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </article>
    </section>
</div>
