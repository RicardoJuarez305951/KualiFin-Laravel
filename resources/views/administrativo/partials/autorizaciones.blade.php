@php
    $summary = $data['summary'] ?? [];
    $filters = $data['filters'] ?? [];
    $creditCaps = $data['creditCaps'] ?? [];
    $salesLimits = $data['salesLimits'] ?? [];
    $clientFlags = $data['clientFlags'] ?? [];
    $changeRequests = $data['changeRequests'] ?? [];
    $workerChanges = $data['workerChanges'] ?? [];
    $weeklyActions = $data['weeklyActions'] ?? [];
    $executiveRequests = $data['executiveRequests'] ?? [];
    $observations = $data['observations'] ?? [];
@endphp

<div class="space-y-10">
    <header class="space-y-3">
        <h1 class="text-3xl font-bold text-gray-900">Autorizaciones operativas</h1>
        <p class="text-gray-600">Gestiona filtros, topes de credito, excepciones y solicitudes de cambios para promotores, supervisores y ejecutivos.</p>
    </header>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($summary as $item)
            <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-2">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ $item['label'] }}</p>
                <p class="text-3xl font-bold text-gray-900">{{ $item['value'] }}</p>
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    {{ $item['badge'] ?? 'Actualizado' }}
                </span>
            </article>
        @endforeach
    </section>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Filtros configurados</h2>
                <p class="text-sm text-gray-600">CRUD de filtros operativos para segmentar clientes y ventas.</p>
            </div>
            <div class="flex items-center gap-3 text-sm font-semibold">
                <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-gray-600 hover:bg-gray-50">
                    Administrar catalogo
                </button>
                <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                    Crear filtro
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Nombre</th>
                        <th class="px-6 py-3 text-left">Campos</th>
                        <th class="px-6 py-3 text-left">Responsable</th>
                        <th class="px-6 py-3 text-left">Estado</th>
                        <th class="px-6 py-3 text-left">Actualizado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($filters as $filter)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $filter['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $filter['description'] ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                {{ implode(', ', $filter['fields'] ?? []) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $filter['owner'] ?? 'Sin asignar' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                    {{ $filter['status'] ?? 'Pendiente' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $filter['updated_at'] ?? '--' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Montos de credito por segmento</h2>
                    <p class="text-sm text-gray-600">Topes vigentes y cambios propuestos para clientes.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver historial</button>
            </div>
            <div class="space-y-4">
                @foreach ($creditCaps as $cap)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-900">{{ $cap['segment'] }}</p>
                            <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                {{ $cap['limit'] }}
                            </span>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center justify-between gap-3 text-xs text-gray-500">
                            <span>Estado: <span class="font-semibold text-blue-600">{{ $cap['status'] ?? 'Pendiente' }}</span></span>
                            <span>Responsable: {{ $cap['owner'] ?? 'N/D' }}</span>
                            <span>Vigencia: {{ $cap['effective'] ?? 'Por definir' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Limites y horarios de venta</h2>
                    <p class="text-sm text-gray-600">Ventanas operativas y excepciones por plaza/promotora.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Editar configuracion</button>
            </div>
            <div class="space-y-4">
                @foreach ($salesLimits as $limit)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-900">{{ $limit['zone'] }}</p>
                            <span class="text-xs font-semibold text-blue-600">{{ $limit['daily_limit'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500">Ventana: {{ $limit['time_window'] ?? 'No definido' }}</p>
                        <p class="text-xs text-gray-500">Excepcion: {{ $limit['exceptions'] ?? 'Ninguna' }}</p>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Alertas de clientes y excepciones</h2>
                <p class="text-sm text-gray-600">Casos con mas de dos familiares, esposos promotores y clientes extra.</p>
            </div>
            <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Visualizar expediente</button>
        </div>
        <ul class="space-y-4 text-sm text-gray-700">
            @foreach ($clientFlags as $flag)
                <li class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-gray-900">{{ $flag['client'] }}</p>
                        <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                            {{ $flag['flag'] }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500">Promotor: {{ $flag['promoter'] ?? 'Sin dato' }}</p>
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                        <span>Estado: <span class="font-semibold text-blue-600">{{ $flag['status'] ?? 'Pendiente' }}</span></span>
                        <span>{{ $flag['next_step'] ?? 'Sin siguiente accion' }}</span>
                        <button class="inline-flex items-center rounded-md border border-blue-200 bg-white px-3 py-1 text-xs font-semibold text-blue-600 hover:text-blue-700">
                            Confirmar cambio
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Solicitudes de cambio</h2>
                    <p class="text-sm text-gray-600">Visualizacion y confirmacion de modificaciones en curso.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver todas</button>
            </div>
            <div class="space-y-4">
                @foreach ($changeRequests as $request)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-900">{{ $request['folio'] }}</span>
                            <span class="inline-flex items-center rounded-full border border-blue-200 bg-white px-3 py-1 text-xs font-semibold text-blue-600">
                                {{ $request['status'] ?? 'Pendiente' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-700">{{ $request['type'] }}</p>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                            <span>Solicitado por: {{ $request['requested_by'] ?? 'N/D' }}</span>
                            <span>Limite: {{ $request['deadline'] ?? 'Sin definir' }}</span>
                            <button class="text-xs font-semibold text-blue-600 hover:text-blue-700">Aprobar</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Trabajadores y comisiones</h2>
                    <p class="text-sm text-gray-600">Cambios de plaza, comisiones y observaciones de personal.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Gestionar</button>
            </div>
            <div class="space-y-4">
                @foreach ($workerChanges as $change)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <p class="text-sm font-semibold text-gray-900">{{ $change['employee'] }}</p>
                        <p class="text-xs text-gray-500">{{ $change['change'] }}</p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Estado: <span class="font-semibold text-blue-600">{{ $change['status'] ?? 'Pendiente' }}</span></span>
                            <span>{{ $change['requested_at'] ?? '--' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <article class="xl:col-span-7 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Acciones de la semana</h2>
                    <p class="text-sm text-gray-600">Seguimiento por rol: promotor, supervisor y ejecutivo.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Exportar</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ($weeklyActions as $action)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <p class="text-sm font-semibold text-gray-900">{{ $action['role'] }}</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $action['actions'] }}</p>
                        <p class="text-xs text-gray-500">{{ $action['detail'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="xl:col-span-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Solicitudes desde ejecutivo</h2>
                    <p class="text-sm text-gray-600">Peticion directa para ampliar cupos o horarios.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Nueva solicitud</button>
            </div>
            <ul class="space-y-4 text-sm text-gray-700">
                @foreach ($executiveRequests as $request)
                    <li class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-gray-900">{{ $request['executive'] }}</p>
                            <span class="inline-flex items-center rounded-full border border-blue-200 bg-white px-3 py-1 text-xs font-semibold text-blue-600">
                                {{ $request['status'] ?? 'Pendiente' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-700">{{ $request['request'] }}</p>
                        <p class="text-xs text-gray-500">{{ $request['created_at'] ?? '--' }}</p>
                    </li>
                @endforeach
            </ul>
        </article>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Observaciones generales</h2>
                <p class="text-sm text-gray-600">Notas de control para seguimiento semanal.</p>
            </div>
            <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Agregar nota</button>
        </div>
        <ul class="space-y-4 text-sm text-gray-700">
            @foreach ($observations as $observation)
                <li class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-gray-900">{{ $observation['author'] }}</p>
                        <span class="text-xs text-gray-500">{{ $observation['timestamp'] ?? '--' }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-700">{{ $observation['note'] }}</p>
                </li>
            @endforeach
        </ul>
    </section>
</div>
