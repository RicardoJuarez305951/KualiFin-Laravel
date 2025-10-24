@php
    $metrics = $data['metrics'] ?? [];
    $filters = $data['filters'] ?? [];
    $executives = $filters['executives'] ?? [];
    $supervisors = $filters['supervisors'] ?? [];
    $disbursements = $data['disbursements'] ?? [];
    $documentTemplates = $data['documentTemplates'] ?? [];
    $cashMovements = $data['cashMovements'] ?? ['recepcion' => [], 'entrega' => []];
    $receiptHistory = $data['receiptHistory'] ?? [];
@endphp

<div class="space-y-10">
    <header class="space-y-3">
        <h1 class="text-3xl font-bold text-gray-900">Desembolsos para inversion</h1>
        <p class="text-gray-600">Control operativo de desembolsos, filtros por ejecutivo y supervisor, y generacion de documentos imprimibles.</p>
    </header>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($metrics as $metric)
            <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-3">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ $metric['label'] }}</p>
                <p class="text-3xl font-bold text-gray-900">{{ $metric['value'] }}</p>
                <p class="text-xs font-medium text-blue-600">{{ $metric['trend'] ?? '' }}</p>
            </article>
        @endforeach
    </section>

    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Listado de desembolsos</h2>
                <p class="text-sm text-gray-600">Filtra por responsable y genera documentos: Desembolso, Recibo y Pagare grupal.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    Exportar Excel
                </button>
                <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Programar desembolso
                </button>
            </div>
        </div>

        <div class="flex flex-col gap-4 px-6 py-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="text-sm text-gray-600">
                    Ejecutivo
                    <select class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach ($executives as $option)
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
            </div>
            <div class="flex items-center gap-3 text-sm font-semibold">
                <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-gray-600 hover:bg-gray-50">
                    Limpiar filtros
                </button>
                <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                    Aplicar filtros
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Folio</th>
                        <th class="px-6 py-3 text-left">Cliente</th>
                        <th class="px-6 py-3 text-left">Monto</th>
                        <th class="px-6 py-3 text-left">Fecha</th>
                        <th class="px-6 py-3 text-left">Ejecutivo</th>
                        <th class="px-6 py-3 text-left">Supervisor</th>
                        <th class="px-6 py-3 text-left">Estado</th>
                        <th class="px-6 py-3 text-left">Documentos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($disbursements as $item)
                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $item['folio'] }}</td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $item['cliente'] }}</p>
                                <p class="text-xs text-gray-500">{{ $item['ventanilla'] ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $item['monto'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item['fecha'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item['ejecutivo'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item['supervisor'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                    {{ $item['estado'] ?? 'Pendiente' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($item['documentos'] ?? [] as $doc)
                                        <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-blue-600 hover:text-blue-700">
                                            Imprimir {{ $doc }}
                                        </button>
                                    @endforeach
                                </div>
                            </td>
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
                    <h2 class="text-lg font-semibold text-gray-900">Recepcion de dinero</h2>
                    <p class="text-sm text-gray-600">Entradas a caja con recibo imprimible.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Registrar</button>
            </div>
            <ul class="space-y-4 text-sm text-gray-700">
                @foreach ($cashMovements['recepcion'] ?? [] as $movement)
                    <li class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-gray-900">{{ $movement['referencia'] }}</p>
                            <span class="text-xs text-gray-500">{{ $movement['hora'] ?? '--' }}</span>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                            <span>{{ $movement['responsable'] ?? 'Sin responsable' }}</span>
                            <span class="font-semibold text-blue-600">{{ $movement['monto'] ?? '$0' }}</span>
                            <button class="inline-flex items-center rounded-md border border-blue-200 bg-white px-3 py-1 text-xs font-semibold text-blue-600 hover:text-blue-700">
                                Recibo {{ $movement['recibo'] ?? '' }}
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Entrega de dinero</h2>
                    <p class="text-sm text-gray-600">Control de salidas y programacion de efectivo.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Programar entrega</button>
            </div>
            <ul class="space-y-4 text-sm text-gray-700">
                @foreach ($cashMovements['entrega'] ?? [] as $movement)
                    <li class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-gray-900">{{ $movement['referencia'] }}</p>
                            <span class="text-xs text-gray-500">{{ $movement['hora'] ?? '--' }}</span>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                            <span>{{ $movement['responsable'] ?? 'Sin responsable' }}</span>
                            <span class="font-semibold text-blue-600">{{ $movement['monto'] ?? '$0' }}</span>
                            <button class="inline-flex items-center rounded-md border border-blue-200 bg-white px-3 py-1 text-xs font-semibold text-blue-600 hover:text-blue-700">
                                Recibo {{ $movement['recibo'] ?? '' }}
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </article>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <article class="xl:col-span-7 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Plantillas y formatos</h2>
                    <p class="text-sm text-gray-600">Administracion de documentos de desembolso, recibo y pagaré grupal.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Actualizar formato</button>
            </div>
            <ul class="space-y-4 text-sm text-gray-700">
                @foreach ($documentTemplates as $template)
                    <li class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-gray-900">{{ $template['name'] }}</p>
                            <span class="inline-flex items-center rounded-full border border-blue-200 bg-white px-3 py-1 text-xs font-semibold text-blue-600">
                                {{ $template['status'] ?? 'Pendiente' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Responsable: {{ $template['owner'] ?? 'N/D' }}</span>
                            <span>Actualizado: {{ $template['updated_at'] ?? '--' }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </article>

        <article class="xl:col-span-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Historial de recibos</h2>
                    <p class="text-sm text-gray-600">Control de entrega y firmas.</p>
                </div>
                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Descargar PDF</button>
            </div>
            <ul class="space-y-4 text-sm text-gray-700">
                @foreach ($receiptHistory as $receipt)
                    <li class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-1">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-gray-900">{{ $receipt['folio'] }}</p>
                            <span class="text-xs text-gray-500">{{ $receipt['fecha'] ?? '--' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $receipt['entregado_a'] ?? 'Sin destinatario' }}</span>
                            <span class="font-semibold text-blue-600">{{ $receipt['monto'] ?? '$0' }}</span>
                        </div>
                        <p class="text-xs text-gray-500">Firmado: {{ $receipt['firmado'] ?? 'No' }}</p>
                    </li>
                @endforeach
            </ul>
        </article>
    </section>
</div>
