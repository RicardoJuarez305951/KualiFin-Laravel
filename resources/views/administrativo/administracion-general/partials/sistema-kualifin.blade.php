<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="{{ $sectionId ?? 'vista-2' }}">
    <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">2. Sistema Kualifin</p>
            <h2 class="text-lg font-semibold text-gray-900">Seguimiento por promotor y resumen de creditos</h2>
            <p class="text-sm text-gray-500">Simulacion con datos Faker para explorar jerarquias y cartera.</p>
        </div>
    </header>

    <div x-data='kualifinSelector(@json($kualifinHierarchy))' class="border-b border-gray-200">
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
                <div x-show="selectedExecutive" x-cloak class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <p class="font-semibold text-gray-900">Ejecutivo seleccionado</p>
                    <p class="mt-1 text-xs text-gray-500" x-text="selectedExecutive?.nombre"></p>
                    <p class="text-xs text-gray-500" x-text="selectedExecutive?.usuario"></p>
                </div>
                <div x-show="selectedSupervisor" x-cloak class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <p class="font-semibold text-gray-900">Supervisor seleccionado</p>
                    <p class="mt-1 text-xs text-gray-500" x-text="selectedSupervisor?.nombre"></p>
                    <p class="text-xs text-gray-500" x-text="selectedSupervisor?.usuario"></p>
                </div>
                <div x-show="selectedPromoter" x-cloak class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <p class="font-semibold text-gray-900">Promotor seleccionado</p>
                    <p class="mt-1 text-xs text-gray-500" x-text="selectedPromoter?.nombre"></p>
                    <p class="text-xs text-gray-500" x-text="selectedPromoter?.zona"></p>
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-4 text-xs text-gray-600">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="font-semibold text-gray-900 text-sm">Prestamo total</p>
                    <p class="mt-2 text-xl font-bold text-gray-900" x-text="totalValue('prestamo_total')"></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="font-semibold text-gray-900 text-sm">Abono total</p>
                    <p class="mt-2 text-xl font-bold text-gray-900" x-text="totalValue('abono_total')"></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="font-semibold text-gray-900 text-sm">Saldo total</p>
                    <p class="mt-2 text-xl font-bold text-gray-900" x-text="totalValue('saldo_total')"></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="font-semibold text-gray-900 text-sm">Clientes</p>
                    <p class="mt-2 text-xl font-bold text-gray-900" x-text="totalValue('clientes')"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 py-5">
        <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Proyeccion inversion</p>
            <p class="mt-2 text-base font-semibold text-gray-900" x-text="financialValue('proyeccion')"></p>
        </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Ventas maximas</p>
                <p class="mt-2 text-base font-semibold text-gray-900" x-text="financialValue('ventas_maximas')"></p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Flujo previo</p>
                <p class="mt-2 text-base font-semibold text-gray-900" x-text="financialValue('flujo_anterior')"></p>
            </div>
        </div>

        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm text-sm text-gray-700">
            <div class="grid gap-4 md:grid-cols-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Inversion proyectada</p>
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
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">% Fallo</p>
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
                        <th class="px-2 py-2 text-[10px] font-semibold uppercase tracking-wide text-gray-500" x-text="column.label"></th>
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

    <p class="px-6 py-4 text-xs text-gray-500" x-show="calendarColumns.length" x-cloak>
        Los importes resaltados representan abonos semanales simulados en lunes consecutivos. Se generan de forma aleatoria para pruebas de interfaz.
    </p>

    <div class="overflow-x-auto px-6 pb-6">
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
