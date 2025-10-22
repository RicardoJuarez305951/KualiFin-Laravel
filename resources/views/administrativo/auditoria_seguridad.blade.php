<x-layouts.authenticated title="Auditoria y seguridad financiera">
    @php
        $securityAlerts = [
            [
                'type' => 'critical',
                'title' => 'Cartera con mora critica',
                'detail' => 'Cliente: CU-45812 - 47 dias vencido - Saldo $58,200 MXN.',
                'timestamp' => '21 ene 2025 - 09:10',
                'action' => 'Abrir expediente',
            ],
            [
                'type' => 'warning',
                'title' => 'Promotor con tasa de rechazo inusual',
                'detail' => 'Promotor: PR-221 - 5 creditos rechazados y 3 cancelados en 48 h.',
                'timestamp' => '20 ene 2025 - 19:25',
                'action' => 'Analizar desempeno',
            ],
            [
                'type' => 'info',
                'title' => 'Supervision de ajustes manuales reiterados',
                'detail' => 'Supervisor: sgutierrez - Ajusto score de 4 clientes en 12 h.',
                'timestamp' => '20 ene 2025 - 12:40',
                'action' => 'Notificar auditoria',
            ],
        ];

        $activityLog = [
            ['time' => '21 ene - 10:32', 'user' => 'promotor.lgomez', 'module' => 'Cartera', 'event' => 'Registro pago parcial manual por $12,500 MXN', 'origin' => 'Sucursal Puebla'],
            ['time' => '21 ene - 09:58', 'user' => 'supervisor.aramirez', 'module' => 'Supervision', 'event' => 'Autorizo reestructura express para cliente con score 520', 'origin' => 'Regional Bajio'],
            ['time' => '20 ene - 18:45', 'user' => 'finanzas@kuali', 'module' => 'Riesgos', 'event' => 'Actualizo lista de clientes con mora >30 dias', 'origin' => 'Comite de riesgo'],
            ['time' => '20 ene - 16:10', 'user' => 'auditoria@kuali', 'module' => 'Auditoria interna', 'event' => 'Agrego evidencias al expediente PR-441', 'origin' => 'Mesa de control'],
        ];

        $sessionOverview = [
            ['user' => 'promotor.lgomez', 'role' => 'Promotor', 'region' => 'Zona Centro', 'status' => 'Bajo observacion', 'last_seen' => 'Hace 6 min', 'note' => '3 clientes con vencimiento >20 dias'],
            ['user' => 'supervisor.aramirez', 'role' => 'Supervisor', 'region' => 'Bajio', 'status' => 'Seguimiento activo', 'last_seen' => 'Hace 14 min', 'note' => 'Revisando excepciones de bonificacion'],
            ['user' => 'ejecutivo.mrivera', 'role' => 'Ejecutivo', 'region' => 'Metropolitana', 'status' => 'Sin hallazgos', 'last_seen' => 'Hace 45 min', 'note' => 'Sin ajustes manuales recientes'],
            ['user' => 'analista.seguridad', 'role' => 'Analista de riesgo', 'region' => 'Matriz', 'status' => 'Monitoreo', 'last_seen' => 'Hace 3 min', 'note' => 'Generando reporte diario'],
        ];
    @endphp

    <div class="mx-auto max-w-7xl py-10 space-y-10">
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
                    <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver lineamientos</button>
                </div>
                <div class="space-y-3 text-sm text-gray-700">
                    <button class="w-full rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-left font-semibold text-rose-700 hover:bg-rose-100">
                        Congelar desembolsos del cliente
                    </button>
                    <button class="w-full rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-left font-semibold text-amber-700 hover:bg-amber-100">
                        Escalar a comite de riesgo
                    </button>
                    <button class="w-full rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-left font-semibold text-blue-700 hover:bg-blue-100">
                        Enviar recordatorio de pago
                    </button>
                    <button class="w-full rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-left font-semibold text-emerald-700 hover:bg-emerald-100">
                        Programar visita de verificacion
                    </button>
                </div>
            </aside>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Bitacora de auditoria financiera</h2>
                    <p class="text-sm text-gray-600">Registro auditable de ajustes, condonaciones y alertas.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Filtros
                    </button>
                    <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                        Descargar reporte
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Fecha / Hora</th>
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
</x-layouts.authenticated>
