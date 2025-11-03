<x-layouts.authenticated title="Administracion General">
    <div class="mx-auto max-w-7xl py-10 space-y-8">
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($summaryCards as $card)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-600">{{ $card['title'] }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-blue-600">{{ $card['subtext'] }}</p>
                </div>
            @endforeach
        </section>

        <p class="text-sm text-gray-500">
            Este tablero concentra la operacion diaria de administracion: revisa los desembolsos destinados a inversion, el estado general de los creditos en el sistema Kualifin y los movimientos de dinero por semana.
        </p>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">1. Desembolsos para inversion</p>
                    <h2 class="text-lg font-semibold text-gray-900">Operaciones recientes orientadas a inversion</h2>
                    <p class="text-sm text-gray-500">Datos de clientes e inversiones con folio INV.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Inversion</span>
            </header>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Folio</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Cliente</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Destino</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Monto</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Fecha</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Estatus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($investmentDisbursements as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $item['folio'] }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $item['cliente'] }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $item['destino'] }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $item['monto'] }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $item['fecha'] }}</td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                        'bg-emerald-100 text-emerald-700' => $item['estado'] === 'Desembolsado',
                                        'bg-amber-100 text-amber-700' => $item['estado'] === 'Programado',
                                        'bg-sky-100 text-sky-700' => $item['estado'] === 'En revision',
                                    ])>
                                        {{ $item['estado'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">2. Sistema Kualifin</p>
                    <h2 class="text-lg font-semibold text-gray-900">Resumen de creditos por estatus</h2>
                    <p class="text-sm text-gray-500">Toma como referencia la tabla creditos y su campo estado.</p>
                </div>
            </header>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Estatus</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Creditos</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Monto total</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Comentario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($creditOverview as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $row['estado'] }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $row['total'] }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $row['monto'] }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $row['comentario'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">3. Entradas y salidas</p>
                        <h2 class="text-lg font-semibold text-gray-900">Movimientos de efectivo</h2>
                        <p class="text-sm text-gray-500">Registros basados en la operacion con promotores e inversiones.</p>
                    </div>
                </header>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Tipo</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Origen</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Monto</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($cashFlow as $flow)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <span @class([
                                            'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                            'bg-emerald-100 text-emerald-700' => $flow['tipo'] === 'Entrada',
                                            'bg-rose-100 text-rose-700' => $flow['tipo'] === 'Salida',
                                        ])>
                                            {{ $flow['tipo'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">{{ $flow['origen'] }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $flow['monto'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $flow['detalle'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">4. Gastos</p>
                        <h2 class="text-lg font-semibold text-gray-900">Conceptos autorizados</h2>
                        <p class="text-sm text-gray-500">Resumen de gastos recientes con responsables.</p>
                    </div>
                </header>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Concepto</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Monto</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Responsable</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Fecha</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Notas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($expenses as $expense)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $expense['concepto'] }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $expense['monto'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $expense['responsable'] }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $expense['fecha'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $expense['comentario'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">5. Proyeccion semanal</p>
                    <h2 class="text-lg font-semibold text-gray-900">Prestamos y cobranza por semana</h2>
                </div>
            </header>
            <div class="grid gap-4 px-6 py-6 sm:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Semana actual</p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-700">
                        <li><span class="font-semibold text-gray-900">Prestamos:</span> {{ $weeklyProjection['semana_actual']['prestamos'] }}</li>
                        <li><span class="font-semibold text-gray-900">Cobrado:</span> {{ $weeklyProjection['semana_actual']['cobrado'] }}</li>
                        <li><span class="font-semibold text-gray-900">Saldo activo:</span> {{ $weeklyProjection['semana_actual']['saldo_activo'] }}</li>
                    </ul>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Semana siguiente</p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-700">
                        <li><span class="font-semibold text-gray-900">Meta prestamos:</span> {{ $weeklyProjection['semana_siguiente']['meta_prestamos'] }}</li>
                        <li><span class="font-semibold text-gray-900">Estimado de cobranza:</span> {{ $weeklyProjection['semana_siguiente']['estimado_cobranza'] }}</li>
                        <li><span class="font-semibold text-gray-900">Saldo programado:</span> {{ $weeklyProjection['semana_siguiente']['saldo_programado'] }}</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-200 px-6 py-4 text-sm text-gray-600">
                {{ $weeklyProjection['notas'] }}
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">6. Historial de fallo</p>
                        <h2 class="text-lg font-semibold text-gray-900">Clientes con semana extra</h2>
                        <p class="text-sm text-gray-500">Seguimiento a clientes con atraso fuera de calendario.</p>
                    </div>
                </header>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Cliente</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Folio</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Promotor</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Semanas extra</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Saldo pendiente</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Ultimo pago</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($failureHistory as $failure)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-gray-700">{{ $failure['cliente'] }}</td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $failure['folio'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $failure['promotor'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $failure['semanas_extra'] }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $failure['monto_pendiente'] }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $failure['ultimo_pago'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">7. Reportes</p>
                        <h2 class="text-lg font-semibold text-gray-900">Mensuales y anuales</h2>
                        <p class="text-sm text-gray-500">Listado de documentos clave por periodo.</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Indicadores</span>
                </header>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Nombre</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Periodo</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Responsable</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Estatus</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Descarga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($reports as $report)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $report['nombre'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $report['periodo'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $report['responsable'] }}</td>
                                    <td class="px-6 py-4">
                                        <span @class([
                                            'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                            'bg-emerald-100 text-emerald-700' => $report['estatus'] === 'Entregado',
                                            'bg-amber-100 text-amber-700' => $report['estatus'] === 'En validacion',
                                            'bg-sky-100 text-sky-700' => $report['estatus'] === 'Programado',
                                        ])>
                                            {{ $report['estatus'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ $report['descarga'] }}" class="text-sm font-semibold text-blue-600 hover:underline">
                                            Descargar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-layouts.authenticated>
