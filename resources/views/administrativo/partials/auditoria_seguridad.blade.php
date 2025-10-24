@php
    $securityAlerts = $data['securityAlerts'] ?? [];
    $activityLog = $data['activityLog'] ?? [];
    $sessionOverview = $data['sessionOverview'] ?? [];
@endphp

<div class="space-y-10">
    <header class="space-y-3">
        <h1 class="text-3xl font-bold text-gray-900">Auditoria y seguridad financiera</h1>
        <p class="text-gray-600">Supervision de riesgos financieros, cartera vencida y practicas atipicas en campo.</p>
    </header>

    <section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <article class="xl:col-span-8 space-y-4">
            @foreach ($securityAlerts as $alert)
                @php
                    $palette = match ($alert['type']) {
                        'critical' => 'border-rose-200 bg-rose-50 text-rose-700',
                        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
                        default => 'border-blue-200 bg-blue-50 text-blue-700',
                    };
                @endphp
                <div class="rounded-xl border {{ $palette }} px-6 py-5 shadow-sm">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-semibold uppercase tracking-wide">{{ $alert['title'] }}</p>
                            <p class="text-sm">{{ $alert['detail'] }}</p>
                            <p class="text-xs font-semibold">{{ $alert['timestamp'] }}</p>
                        </div>
                        <button class="inline-flex items-center rounded-md border border-white bg-white px-4 py-2 text-xs font-semibold text-blue-600 shadow-sm hover:text-blue-700">
                            {{ $alert['action'] }}
                        </button>
                    </div>
                </div>
            @endforeach
        </article>

        <aside class="xl:col-span-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Controles rapidos</h2>
                    <p class="text-sm text-gray-600">Acciones inmediatas ante hallazgos financieros.</p>
                </div>
                <button class="text-xs font-semibold text-blue-600 hover:text-blue-700">Configurar</button>
            </div>
            <ul class="space-y-3 text-sm text-gray-700">
                <li class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2">
                    <span>Bloquear usuario</span>
                    <button class="text-xs font-semibold text-blue-600 hover:text-blue-700">Ejecutar</button>
                </li>
                <li class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2">
                    <span>Escalar a juridico</span>
                    <button class="text-xs font-semibold text-blue-600 hover:text-blue-700">Enviar</button>
                </li>
                <li class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2">
                    <span>Programar auditoria</span>
                    <button class="text-xs font-semibold text-blue-600 hover:text-blue-700">Crear</button>
                </li>
            </ul>
        </aside>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Bitacora de actividad</h2>
                <p class="text-sm text-gray-600">Ultimos movimientos registrados en los modulos de riesgo.</p>
            </div>
            <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                Exportar bitacora
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Hora</th>
                        <th class="px-6 py-3 text-left">Usuario</th>
                        <th class="px-6 py-3 text-left">Modulo</th>
                        <th class="px-6 py-3 text-left">Evento</th>
                        <th class="px-6 py-3 text-left">Origen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($activityLog as $row)
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $row['time'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                    {{ $row['user'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $row['module'] }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $row['event'] }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500">{{ $row['origin'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Seguimiento operativo</h2>
                <p class="text-sm text-gray-600">Monitoreo de promotores, supervisores y analistas bajo observacion.</p>
            </div>
            <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver tablero</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($sessionOverview as $session)
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-900">{{ $session['user'] }}</p>
                        <span class="text-xs font-semibold text-blue-600">{{ $session['status'] }}</span>
                    </div>
                    <p class="text-xs text-gray-500">{{ $session['role'] }} - {{ $session['region'] }}</p>
                    <p class="text-xs text-gray-500">{{ $session['note'] }}</p>
                    <p class="text-xs text-gray-500">Ultima revision: {{ $session['last_seen'] }}</p>
                    <div class="flex items-center gap-3 text-xs font-semibold text-blue-600">
                        <button class="hover:text-blue-700">Abrir detalle</button>
                        <span class="text-gray-300">|</span>
                        <button class="hover:text-blue-700">Asignar seguimiento</button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
