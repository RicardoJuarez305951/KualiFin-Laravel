<x-layouts.authenticated title="Administracion General">
    <style>
        [x-cloak] { display: none !important; }
    </style>
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
                    <h2 class="text-lg font-semibold text-gray-900">Seguimiento por promotor y resumen de creditos</h2>
                    <p class="text-sm text-gray-500">Simulacion con datos Faker para explorar jerarquias y cartera.</p>
                </div>
            </header>

            <div
                x-data='kualifinSelector(@json($kualifinHierarchy))'
                class="border-b border-gray-200"
            >
                <div class="grid gap-4 px-6 py-6 md:grid-cols-3">
                    <label class="flex flex-col text-sm text-gray-700">
                        <span class="font-semibold text-gray-900">Ejecutivo</span>
                        <select
                            x-model="selectedExecutiveId"
                            @change="onExecutiveChange()"
                            class="mt-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="">Selecciona un ejecutivo</option>
                            <template x-for="executive in executives" :key="executive.id">
                                <option :value="executive.id" x-text="labelFor(executive)"></option>
                            </template>
                        </select>
                    </label>
                    <label class="flex flex-col text-sm text-gray-700">
                        <span class="font-semibold text-gray-900">Supervisor</span>
                        <select
                            x-model="selectedSupervisorId"
                            @change="onSupervisorChange()"
                            :disabled="!selectedExecutiveId"
                            class="mt-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:cursor-not-allowed disabled:bg-gray-100"
                        >
                            <option value="">Selecciona un supervisor</option>
                            <template x-for="supervisor in supervisors" :key="supervisor.id">
                                <option :value="supervisor.id" x-text="labelFor(supervisor)"></option>
                            </template>
                        </select>
                    </label>
                    <label class="flex flex-col text-sm text-gray-700">
                        <span class="font-semibold text-gray-900">Promotor</span>
                        <select
                            x-model="selectedPromoterId"
                            :disabled="!selectedSupervisorId"
                            class="mt-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:cursor-not-allowed disabled:bg-gray-100"
                        >
                            <option value="">Selecciona un promotor</option>
                            <template x-for="promoter in promoters" :key="promoter.id">
                                <option :value="promoter.id" x-text="labelFor(promoter)"></option>
                            </template>
                        </select>
                    </label>
                </div>

                <div class="px-6 pb-4">
                    <div class="grid gap-4 md:grid-cols-3 text-sm text-gray-700">
                        <div
                            x-show="selectedExecutive"
                            x-cloak
                            class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm"
                        >
                            <p class="font-semibold text-gray-900">Ejecutivo seleccionado</p>
                            <p class="mt-1"><span class="font-medium text-gray-800">Nombre:</span> <span x-text="selectedExecutive ? selectedExecutive.nombre : ''"></span></p>
                            <p class="mt-1"><span class="font-medium text-gray-800">Codigo:</span> <span x-text="selectedExecutive ? selectedExecutive.codigo : ''"></span></p>
                            <p class="mt-1"><span class="font-medium text-gray-800">Usuario:</span> <span x-text="selectedExecutive ? selectedExecutive.usuario : ''"></span></p>
                        </div>
                        <div
                            x-show="selectedSupervisor"
                            x-cloak
                            class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm"
                        >
                            <p class="font-semibold text-gray-900">Supervisor seleccionado</p>
                            <p class="mt-1"><span class="font-medium text-gray-800">Nombre:</span> <span x-text="selectedSupervisor ? selectedSupervisor.nombre : ''"></span></p>
                            <p class="mt-1"><span class="font-medium text-gray-800">Codigo:</span> <span x-text="selectedSupervisor ? selectedSupervisor.codigo : ''"></span></p>
                            <p class="mt-1"><span class="font-medium text-gray-800">Usuario:</span> <span x-text="selectedSupervisor ? selectedSupervisor.usuario : ''"></span></p>
                        </div>
                        <div
                            x-show="selectedPromoter"
                            x-cloak
                            class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm"
                        >
                            <p class="font-semibold text-gray-900">Promotor seleccionado</p>
                            <p class="mt-1"><span class="font-medium text-gray-800">Nombre:</span> <span x-text="selectedPromoter ? selectedPromoter.nombre : ''"></span></p>
                            <p class="mt-1"><span class="font-medium text-gray-800">Codigo:</span> <span x-text="selectedPromoter ? selectedPromoter.codigo : ''"></span></p>
                            <p class="mt-1"><span class="font-medium text-gray-800">Usuario:</span> <span x-text="selectedPromoter ? selectedPromoter.usuario : ''"></span></p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 px-6 pb-6">
                    <div x-show="!selectedPromoterId" x-cloak class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-4 text-sm text-gray-600">
                        Selecciona un ejecutivo, supervisor y promotor para visualizar la cartera simulada de clientes.
                    </div>

                    <template x-if="selectedPromoterId">
                        <div class="space-y-6" x-cloak>
                            <div class="grid gap-4 text-sm text-gray-800 md:grid-cols-4">
                                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Zona operativa</p>
                                    <p class="mt-2 text-base font-semibold text-gray-900" x-text="selectedPromoter ? selectedPromoter.zona : ''"></p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Rendimiento simulado</p>
                                    <p class="mt-2 text-base font-semibold text-gray-900" x-text="selectedPromoter ? selectedPromoter.porcentaje_rendimiento : ''"></p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total cartera</p>
                                    <p class="mt-2 text-base font-semibold text-gray-900" x-text="totalValue('prestamo_total')"></p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Abono semanal total</p>
                                    <p class="mt-2 text-base font-semibold text-gray-900" x-text="totalValue('abono_total')"></p>
                                </div>
                            </div>

                            <div class="grid gap-4 text-sm text-gray-800 md:grid-cols-4">
                                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Saldo pendiente total</p>
                                    <p class="mt-2 text-base font-semibold text-gray-900" x-text="totalValue('saldo_total')"></p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Clientes activos</p>
                                    <p class="mt-2 text-base font-semibold text-gray-900" x-text="totalValue('clientes')"></p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Ejecutivo responsable</p>
                                    <p class="mt-2 text-base font-semibold text-gray-900" x-text="selectedPromoter ? selectedPromoter.ejecutivo : ''"></p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Supervisor responsable</p>
                                    <p class="mt-2 text-base font-semibold text-gray-900" x-text="selectedPromoter ? selectedPromoter.supervisor : ''"></p>
                                </div>
                            </div>

                            <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-4 text-sm text-blue-900">
                                <div class="flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Promotor</p>
                                        <p class="text-lg font-semibold text-blue-900" x-text="selectedPromoter ? selectedPromoter.nombre : ''"></p>
                                        <p class="text-sm text-blue-700">
                                            <span x-text="selectedPromoter ? selectedPromoter.codigo : ''"></span>
                                            - Usuario:
                                            <span class="font-semibold" x-text="selectedPromoter ? selectedPromoter.usuario : ''"></span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Total calendario simulado</p>
                                        <p class="text-lg font-semibold text-blue-900" x-text="calendarTotalGlobal()"></p>
                                        <p class="text-xs text-blue-700" x-text="clients.length + ' clientes simulados'"></p>
                                    </div>
                                </div>
                                <p class="mt-3 text-xs text-blue-700">Datos generados con Faker para emular la planeacion semanal con cortes en lunes.</p>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-white px-4 py-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Resumen financiero simulado</p>
                                <div class="mt-4 space-y-4">
                                    <div class="grid gap-4 md:grid-cols-4">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Proyeccion</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('proyeccion')"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Ventas maximas</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('ventas_maximas')"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Flujo anterior</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('flujo_anterior')"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Flujo de efectivo</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('flujo_efectivo')"></p>
                                        </div>
                                    </div>
                                    <div class="grid gap-4 md:grid-cols-4">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Prestamo</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('prestamo')"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total a recuperar</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('total_recuperar')"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Prestamo real</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('prestamo_real')"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">T4 recuperado</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('t4_recuperado')"></p>
                                        </div>
                                    </div>
                                    <div class="grid gap-4 md:grid-cols-4">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Comision promotor</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('comision_promotor')"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Comision supervisor</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('comision_supervisor')"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Fondo de ahorro</p>
                                            <p class="mt-1 text-base font-semibold text-gray-900" x-text="financialValue('fondo_ahorro')"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Fallo</p>
                                            <p class="mt-1 text-base font-semibold text-rose-600" x-text="financialValue('fallo')"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-xs text-gray-700">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Fecha credito</th>
                                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Nombre cliente</th>
                                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Prestamo</th>
                                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Abono semanal</th>
                                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Debe</th>
                                            <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Estatus</th>
                                            <template x-for="column in calendarColumns" :key="column.key">
                                                <th class="px-3 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500 text-center" x-text="column.label"></th>
                                            </template>
                                        </tr>
                                        <tr x-show="calendarColumns.length">
                                            <th colspan="6" class="px-4 py-2 text-right text-[11px] font-semibold uppercase text-blue-900 bg-blue-50">Total por semana</th>
                                            <template x-for="column in calendarColumns" :key="`total-${column.key}`">
                                                <th class="px-3 py-2 text-xs font-semibold text-center" :class="calendarTotalClass(column)" x-text="column.total_formatted"></th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <template x-for="client in clients" :key="client.nombre + client.fecha_credito">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-xs text-gray-600" x-text="client.fecha_credito"></td>
                                                <td class="px-4 py-3 text-sm font-semibold text-gray-900" x-text="client.nombre"></td>
                                                <td class="px-4 py-3 text-sm font-semibold text-gray-900" x-text="client.prestamo"></td>
                                                <td class="px-4 py-3 text-sm text-gray-700" x-text="client.abono_semanal"></td>
                                                <td class="px-4 py-3 text-sm text-gray-700" x-text="client.saldo_pendiente"></td>
                                                <td class="px-4 py-3 text-xs text-gray-600" x-text="client.estatus"></td>
                                                <template x-for="column in calendarColumns" :key="client.nombre + '-' + column.key">
                                                    <td class="px-2 py-2" :class="matrixCellClass(client, column.key)" x-text="matrixDisplay(client, column.key)"></td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-xs text-gray-500" x-show="calendarColumns.length" x-cloak>
                                Los importes resaltados representan abonos semanales simulados en lunes consecutivos. Se generan de forma aleatoria para pruebas de interfaz.
                            </p>
                        </div>
                    </template>
            </div>

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

            @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('kualifinSelector', (hierarchy) => ({
                    hierarchy,
                    selectedExecutiveId: '',
                    selectedSupervisorId: '',
                    selectedPromoterId: '',
                    get executives() {
                        return this.hierarchy;
                    },
                    get supervisors() {
                        const executive = this.executives.find((item) => item.id === this.selectedExecutiveId);
                        return executive ? executive.supervisores : [];
                    },
                    get promoters() {
                        const supervisor = this.supervisors.find((item) => item.id === this.selectedSupervisorId);
                        return supervisor ? supervisor.promotores : [];
                    },
                    get selectedExecutive() {
                        return this.executives.find((item) => item.id === this.selectedExecutiveId) || null;
                    },
                    get selectedSupervisor() {
                        return this.supervisors.find((item) => item.id === this.selectedSupervisorId) || null;
                    },
                    get selectedPromoter() {
                        const promoter = this.promoters.find((item) => item.id === this.selectedPromoterId);
                        return promoter ? promoter : null;
                    },
                    get clients() {
                        return this.selectedPromoter ? this.selectedPromoter.clientes : [];
                    },
                    get calendarColumns() {
                        const promoter = this.selectedPromoter;
                        if (!promoter || !promoter.calendar_columns) {
                            return [];
                        }
                        return promoter.calendar_columns;
                    },
                    onExecutiveChange() {
                        if (!this.supervisors.some((item) => item.id === this.selectedSupervisorId)) {
                            this.selectedSupervisorId = '';
                        }
                        this.onSupervisorChange();
                    },
                    onSupervisorChange() {
                        if (!this.promoters.some((item) => item.id === this.selectedPromoterId)) {
                            this.selectedPromoterId = '';
                        }
                    },
                    labelFor(person) {
                        const username = person.usuario ? ` - ${person.usuario}` : '';
                        return `${person.nombre} (${person.codigo})${username}`;
                    },
                    totalValue(key) {
                        const promoter = this.selectedPromoter;
                        if (!promoter || !promoter.totals) {
                            return '';
                        }
                        return promoter.totals[key] ?? '';
                    },
                    financialValue(key) {
                        const promoter = this.selectedPromoter;
                        if (!promoter || !promoter.financial_summary) {
                            return '';
                        }
                        return promoter.financial_summary[key] ?? '';
                    },
                    calendarTotalGlobal() {
                        const promoter = this.selectedPromoter;
                        if (!promoter || !promoter.calendar_total_global) {
                            return '';
                        }
                        return promoter.calendar_total_global;
                    },
                    matrixDisplay(client, key) {
                        if (!client || !client.pagos_por_fecha_filtrados) {
                            return '';
                        }
                        const cell = client.pagos_por_fecha_filtrados[key];
                        if (!cell) {
                            return '';
                        }
                        if (typeof cell === 'object' && cell.display) {
                            return cell.display;
                        }
                        if (typeof cell === 'number') {
                            return cell.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
                        }
                        return '';
                    },
                    matrixCellClass(client, key) {
                        if (!client || !client.pagos_por_fecha_filtrados) {
                            return 'text-center text-[11px] text-gray-300';
                        }
                        const cell = client.pagos_por_fecha_filtrados[key];
                        if (!cell) {
                            return 'text-center text-[11px] text-gray-300';
                        }
                        const base = 'text-center text-[11px] font-semibold';
                        if (typeof cell === 'object') {
                            return `${base} ${cell.is_future ? 'bg-amber-100 text-amber-700' : 'bg-amber-200 text-amber-900'}`;
                        }
                        return `${base} bg-amber-100 text-amber-700`;
                    },
                    calendarTotalClass(column) {
                        return column && column.is_future ? 'bg-blue-50 text-blue-700' : 'bg-blue-100 text-blue-800';
                    },
                }));
            });
        </script>
    @endpush
</x-layouts.authenticated>








