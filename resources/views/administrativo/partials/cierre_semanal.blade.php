@php
    $summary = $data['summary'] ?? [];
    $filters = $data['filters'] ?? [];
    $periods = $filters['periods'] ?? [];
    $supervisors = $filters['supervisors'] ?? [];
    $executives = $filters['executives'] ?? [];
    $teamBreakdown = $data['teamBreakdown'] ?? [];
    $topPerformers = $data['topPerformers'] ?? [];
    $alerts = $data['alerts'] ?? [];
    $weeklyActions = $data['weeklyActions'] ?? [];
    $pendingFollowUps = $data['pendingFollowUps'] ?? [];
@endphp

<div class="space-y-10">
    <header class="space-y-3">
        <h1 class="text-3xl font-bold text-gray-900">Cierre semanal</h1>
        <p class="text-gray-600">Resumen consolidado de ventas y desembolsos por promotora con filtros de supervisor y ejecutivo.</p>
    </header>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($summary as $item)
            <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-2">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ $item['label'] }}</p>
                <p class="text-3xl font-bold text-gray-900">{{ $item['value'] }}</p>
                <p class="text-xs font-medium text-blue-600">{{ $item['trend'] ?? '' }}</p>
            </article>
        @endforeach
    </section>

    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Filtros del reporte</h2>
                <p class="text-sm text-gray-600">Selecciona periodo, supervisor o ejecutivo para recalcular el cierre.</p>
            </div>
            <div class="flex items-center gap-3 text-sm font-semibold">
                <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-gray-600 hover:bg-gray-50">
                    Limpiar
                </button>
                <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                    Aplicar filtros
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 px-6 py-4 md:grid-cols-3">
            <label class="text-sm text-gray-600">
                Periodo
                <select class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach ($periods as $option)
                        <option>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm text-gray-600">
                Supervisor
                <select class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach ($supervisors as $option)
                        <option>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm text-gray-600">
                Ejecutivo
                <select class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach ($executives as $option)
                        <option>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Ventas por promotora</h2>
                <p class="text-sm text-gray-600">Detalle de ventas, desembolsos, nuevos clientes y mora corta.</p>
            </div>
            <div class="flex items-center gap-3 text-sm font-semibold">
                <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-gray-600 hover:bg-gray-50">
                    Exportar CSV
                </button>
                <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                    Compartir reporte
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Promotora</th>
                        <th class="px-6 py-3 text-left">Ventas</th>
                        <th class="px-6 py-3 text-left">Desembolsos</th>
                        <th class="px-6 py-3 text-left">Nuevos</th>
                        <th class="px-6 py-3 text-left">Recreditos</th>
                        <th class="px-6 py-3 text-left">Mora 0-7</th>
                        <th class="px-6 py-3 text-left">Supervisor</th>
                        <th class="px-6 py-3 text-left">Ejecutivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($teamBreakdown as $team)
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $team['promotora'] }}</td>
                            <td class="px-6 py-4">{{ $team['ventas'] }}</td>
                            <td class="px-6 py-4">{{ $team['desembolsos'] }}</td>
                            <td class="px-6 py-4 text-center">{{ $team['nuevos'] }}</td>
                            <td class="px-6 py-4 text-center">{{ $team['recreditos'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    {{ $team['mora7'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $team['supervisor'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $team['ejecutivo'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <article class="xl:col-span-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Promotores destacados</h2>
                    <p class="text-sm text-gray-600">Ranking semanal por monto y colocaciones.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver detalle</button>
            </div>
            <ul class="space-y-4 text-sm text-gray-700">
                @foreach ($topPerformers as $performer)
                    <li class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-gray-900">{{ $performer['promoter'] }}</p>
                            <span class="text-xs font-semibold text-blue-600">{{ $performer['ventas'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500">{{ $performer['colocaciones'] ?? 0 }} colocaciones</p>
                    </li>
                @endforeach
            </ul>
        </article>

        <article class="xl:col-span-7 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Alertas del cierre</h2>
                    <p class="text-sm text-gray-600">Incidentes relevantes para tesoreria, juridico y supervision.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Asignar seguimiento</button>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                @foreach ($alerts as $alert)
                    @php
                        $palette = match ($alert['type'] ?? '') {
                            'critical' => 'border-rose-200 bg-rose-50 text-rose-700',
                            'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
                            default => 'border-blue-200 bg-blue-50 text-blue-700',
                        };
                    @endphp
                    <div class="rounded-xl border {{ $palette }} px-4 py-3 space-y-1 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-wide">{{ $alert['title'] }}</p>
                        <p class="text-xs">{{ $alert['detail'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Acciones clave de la semana</h2>
                    <p class="text-sm text-gray-600">Compromisos por area para la siguiente ventana.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Registrar accion</button>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach ($weeklyActions as $action)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <p class="text-sm font-semibold text-gray-900">{{ $action['area'] }}</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $action['value'] }}</p>
                        <p class="text-xs text-gray-500">{{ $action['item'] }} · {{ $action['note'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Pendientes de seguimiento</h2>
                    <p class="text-sm text-gray-600">Actividades que deben cerrarse antes del siguiente corte.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Agregar pendiente</button>
            </div>
            <ul class="space-y-4 text-sm text-gray-700">
                @foreach ($pendingFollowUps as $item)
                    <li class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-gray-900">{{ $item['concepto'] }}</p>
                            <span class="text-xs text-gray-500">{{ $item['fecha'] ?? '--' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $item['responsable'] ?? 'Sin responsable' }}</span>
                            <span class="font-semibold text-blue-600">{{ $item['estatus'] ?? 'Pendiente' }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </article>
    </section>
</div>
