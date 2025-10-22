@php
    $investmentMetrics = $data['investmentMetrics'] ?? [];
    $pipelineStages = $data['pipelineStages'] ?? [];
    $upcomingFlows = $data['upcomingFlows'] ?? [];
    $portfolio = $data['portfolio'] ?? [];
@endphp

<div class="space-y-10">
    <header class="space-y-3">
        <h1 class="text-3xl font-bold text-gray-900">Gestion de inversiones</h1>
        <p class="text-gray-600">Control del pipeline de captacion, asignacion de capital y seguimiento de rendimientos.</p>
    </header>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($investmentMetrics as $metric)
            @php
                $color = match ($metric['color']) {
                    'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                    'purple' => 'border-purple-200 bg-purple-50 text-purple-700',
                    'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
                    default => 'border-blue-200 bg-blue-50 text-blue-700',
                };
            @endphp
            <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-3">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ $metric['label'] }}</p>
                    <span class="inline-flex items-center rounded-full border {{ $color }} px-3 py-1 text-xs font-semibold">
                        Monitoreo
                    </span>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $metric['value'] }}</p>
                <p class="text-xs font-medium text-blue-600">{{ $metric['trend'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Pipeline de captacion</h2>
                <p class="text-sm text-gray-600">Visualiza el avance por etapa antes de liberar capital.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Exportar pipeline
                </button>
                <button class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Crear oportunidad
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($pipelineStages as $stage => $items)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-900">{{ $stage }}</p>
                        <span class="text-xs font-semibold text-blue-600">{{ count($items) }} casos</span>
                    </div>
                    <div class="space-y-3">
                        @foreach ($items as $item)
                            <div class="rounded-lg border border-white bg-white p-3 shadow-sm space-y-2">
                                <div class="flex items-center justify-between text-sm font-semibold text-gray-900">
                                    <span>{{ $item['folio'] }}</span>
                                    <span class="text-blue-600">{{ $item['monto'] }}</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $item['inversionista'] }}</p>
                                <p class="text-xs text-gray-500">{{ $item['producto'] }}</p>
                                <p class="text-xs text-emerald-600 font-semibold">{{ $item['avance'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <article class="xl:col-span-7 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Distribucion de portafolio</h2>
                    <p class="text-sm text-gray-600">Resumen de productos vigentes y su rendimiento esperado.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver detalle</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Producto</th>
                            <th class="px-6 py-3 text-left">Capital</th>
                            <th class="px-6 py-3 text-left">Tasa</th>
                            <th class="px-6 py-3 text-left">Vencimientos</th>
                            <th class="px-6 py-3 text-left">Estatus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($portfolio as $product)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $product['nombre'] }}</td>
                                <td class="px-6 py-4">{{ $product['monto'] }}</td>
                                <td class="px-6 py-4 text-blue-600">{{ $product['tasa'] }}</td>
                                <td class="px-6 py-4">{{ $product['vencimientos'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        {{ $product['estatus'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <article class="xl:col-span-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Proximos hitos</h2>
                    <p class="text-sm text-gray-600">Fechas clave de firma, pagos e hitos comerciales.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Programar recordatorio</button>
            </div>
            <ol class="space-y-4 text-sm text-gray-700">
                @foreach ($upcomingFlows as $flow)
                    <li class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <p class="text-xs font-semibold text-blue-600">{{ $flow['fecha'] }}</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $flow['concepto'] }}</p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $flow['responsable'] }}</span>
                            <span class="font-semibold text-blue-600">{{ $flow['monto'] }}</span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </article>
    </section>
</div>
