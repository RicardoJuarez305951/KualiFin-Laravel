@php
    $metrics = $data['metrics'] ?? [];
    $segments = $data['segments'] ?? [];
    $branches = $data['branches'] ?? [];
@endphp

<div class="space-y-10">
    <header class="space-y-3">
        <h1 class="text-3xl font-bold text-gray-900">Cartera global</h1>
        <p class="text-gray-600">Consolidado de cartera por plaza, producto y etapa. Revisa saldos, indicadores de riesgo y focos de seguimiento.</p>
    </header>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($metrics as $metric)
            @php
                $color = match ($metric['color']) {
                    'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                    'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
                    'blue' => 'border-blue-200 bg-blue-50 text-blue-700',
                    default => 'border-purple-200 bg-purple-50 text-purple-700',
                };
            @endphp
            <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ $metric['label'] }}</p>
                    <span class="inline-flex items-center rounded-full border {{ $color }} px-3 py-1 text-xs font-semibold">
                        {{ $metric['badge'] }}
                    </span>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $metric['value'] }}</p>
                <p class="text-xs font-medium text-blue-600">{{ $metric['trend'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <article class="xl:col-span-7 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Distribucion por producto</h2>
                    <p class="text-sm text-gray-600">Comparativo del capital vigente y numero de cuentas.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver detalle</button>
            </div>
            <div class="space-y-4">
                @foreach ($segments as $segment)
                    @php
                        $amountPercent = min(100, round($segment['amount'] / 10 * 100));
                        $accountPercent = min(100, round($segment['accounts'] / 700 * 100));
                    @endphp
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-900">{{ $segment['name'] }}</p>
                            <span class="text-xs font-semibold text-blue-600">${{ number_format($segment['amount'], 1) }} M</span>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs uppercase text-gray-500">Capital vigente</p>
                                <div class="mt-1 h-2 rounded-full bg-white">
                                    <div class="h-2 rounded-full bg-blue-500" style="width: {{ $amountPercent }}%"></div>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs uppercase text-gray-500">Cuentas</p>
                                <div class="mt-1 h-2 rounded-full bg-white">
                                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $accountPercent }}%"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">{{ $segment['accounts'] }} cuentas activas</p>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="xl:col-span-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Semaforo de riesgo</h2>
                    <p class="text-sm text-gray-600">Seguimiento rapido a buckets de mora y acciones sugeridas.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Configurar alertas</button>
            </div>
            <ul class="space-y-4 text-sm text-gray-700">
                <li class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-emerald-700">0 - 7 dias</p>
                        <span class="text-sm font-semibold text-emerald-700">72% recuperado</span>
                    </div>
                    <p class="mt-1 text-xs text-emerald-600">Campana de cobranza preventiva en curso.</p>
                </li>
                <li class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-amber-700">8 - 30 dias</p>
                        <span class="text-sm font-semibold text-amber-700">$640K</span>
                    </div>
                    <p class="mt-1 text-xs text-amber-600">Asignar visitas domiciliarias a 120 clientes criticos.</p>
                </li>
                <li class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-rose-700">31+ dias</p>
                        <span class="text-sm font-semibold text-rose-700">$410K</span>
                    </div>
                    <p class="mt-1 text-xs text-rose-600">Escalar con Juridico: 45 expedientes listos para demanda.</p>
                </li>
            </ul>
        </article>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Detalle por plaza</h2>
                <p class="text-sm text-gray-600">Ranking de cartera con metricas clave de mora y responsables.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                    Exportar CSV
                </button>
                <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                    Ajustar filtros
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Plaza</th>
                        <th class="px-6 py-3 text-left">Vigente</th>
                        <th class="px-6 py-3 text-left">Riesgo</th>
                        <th class="px-6 py-3 text-left">Vencida</th>
                        <th class="px-6 py-3 text-left">% Mora</th>
                        <th class="px-6 py-3 text-left">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($branches as $branch)
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $branch['plaza'] }}</td>
                            <td class="px-6 py-4">{{ $branch['vigente'] }}</td>
                            <td class="px-6 py-4 text-amber-600">{{ $branch['riesgo'] }}</td>
                            <td class="px-6 py-4 text-rose-600">{{ $branch['vencida'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                    {{ $branch['mora'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $branch['lead'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
