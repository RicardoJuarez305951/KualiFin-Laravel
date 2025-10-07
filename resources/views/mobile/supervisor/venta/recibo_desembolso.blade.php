{{-- resources/views/mobile/supervisor/venta/recibo_desembolso.blade.php --}}
@php
    /** @var \App\Models\Promotor $promotor */
    $promotorNombre = trim(collect([
        $promotor->nombre ?? null,
        $promotor->apellido_p ?? null,
        $promotor->apellido_m ?? null,
    ])->filter()->implode(' '));
@endphp

<x-layouts.mobile.mobile-layout title="Formato Recibo Desembolso">
    <div
        class="max-w-5xl mx-auto space-y-6"
        x-data="reciboDesembolso({
            clientes: @json($clientes),
            promotorNombre: @json($promotorNombre),
            fechaHoy: @json($fechaHoy),
        })"
    >
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Formato Recibo Desembolso</h1>
                <p class="text-sm text-gray-500">Promotor: <span class="font-medium text-gray-700" x-text="form.promotor"></span></p>
            </div>
            <div class="text-sm text-gray-500 flex items-center gap-2">
                <span>Fecha:</span>
                <input
                    type="text"
                    x-model="form.fecha"
                    class="w-32 rounded-lg border border-gray-300 px-3 py-1 text-sm font-semibold text-gray-700 focus:border-blue-500 focus:ring focus:ring-blue-200"
                >
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-gray-700">Detalle de desembolsos</h2>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        @click="addRow()"
                    >
                        Agregar cliente
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-gray-600">
                            <th class="px-3 py-2 text-left font-semibold">Cliente</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Préstamo crédito anterior</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Préstamo solicitado</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">-5% (comisión)</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Total préstamo</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Recrédito nuevo</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Total recrédito</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Total préstamo - recrédito</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(row, index) in rows" :key="row.uid">
                            <tr class="align-top">
                                <td class="px-3 py-2 min-w-[160px]">
                                    <input
                                        type="text"
                                        x-model="row.cliente"
                                        list="clientes-sugeridos"
                                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        placeholder="Nombre del cliente"
                                    >
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        step="0.01"
                                        x-model.number="row.prestamoAnterior"
                                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-right text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        placeholder="0.00"
                                    >
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        step="0.01"
                                        x-model.number="row.prestamoSolicitado"
                                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-right text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        placeholder="0.00"
                                    >
                                </td>
                                <td class="px-3 py-2 text-right font-medium text-gray-600" x-text="money(rowCommission(row))"></td>
                                <td class="px-3 py-2 text-right font-medium text-gray-700" x-text="money(rowTotalPrestamo(row))"></td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        step="0.01"
                                        x-model.number="row.recreditoNuevo"
                                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-right text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        placeholder="0.00"
                                    >
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        step="0.01"
                                        x-model.number="row.totalRecredito"
                                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-right text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        placeholder="0.00"
                                    >
                                </td>
                                <td class="px-3 py-2 text-right font-semibold" x-text="money(rowTotalPrestamoMenosRecredito(row))"></td>
                                <td class="px-3 py-2 text-center">
                                    <button
                                        type="button"
                                        class="text-xs text-red-500 hover:text-red-600"
                                        @click="removeRow(row.uid)"
                                        x-show="rows.length > 1"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr class="font-semibold text-gray-700">
                            <td class="px-3 py-2 text-right">Totales:</td>
                            <td class="px-3 py-2 text-right" x-text="money(totalAnterior())"></td>
                            <td class="px-3 py-2 text-right" x-text="money(totalSolicitado())"></td>
                            <td class="px-3 py-2 text-right" x-text="money(totalComision())"></td>
                            <td class="px-3 py-2 text-right" x-text="money(totalPrestamo())"></td>
                            <td class="px-3 py-2 text-right" x-text="money(totalRecreditoNuevo())"></td>
                            <td class="px-3 py-2 text-right" x-text="money(totalRecredito())"></td>
                            <td class="px-3 py-2 text-right" x-text="money(totalPrestamoMenosRecredito())"></td>
                            <td class="px-3 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <datalist id="clientes-sugeridos">
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente['nombre'] }}"></option>
                @endforeach
            </datalist>
        </div>

        <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-700">Validaciones y firmas</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-600">Nombre de promotora de reconocimiento de clientes</label>
                    <input
                        type="text"
                        x-model="form.promotoraReconocimiento"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                        placeholder="Nombre completo"
                    >
                    <div class="h-20 rounded-xl border border-dashed border-gray-300 flex items-end justify-center pb-2 text-xs text-gray-400">
                        Firma
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-600">Nombre de ejecutivo - Validar</label>
                    <input
                        type="text"
                        x-model="form.ejecutivo"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                        placeholder="Nombre completo"
                    >
                    <div class="h-20 rounded-xl border border-dashed border-gray-300 flex items-end justify-center pb-2 text-xs text-gray-400">
                        Firma
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-700">Resumen financiero</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">Comisión de promotor</th>
                            <td class="px-3 py-2 text-right">
                                <input
                                    type="number"
                                    step="0.01"
                                    x-model.number="form.comisionPromotor"
                                    class="w-full max-w-[160px] rounded-lg border border-gray-300 px-2 py-1 text-right focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    placeholder="0.00"
                                >
                            </td>
                        </tr>
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">Comisión de supervisor</th>
                            <td class="px-3 py-2 text-right">
                                <input
                                    type="number"
                                    step="0.01"
                                    x-model.number="form.comisionSupervisor"
                                    class="w-full max-w-[160px] rounded-lg border border-gray-300 px-2 py-1 text-right focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    placeholder="0.00"
                                >
                            </td>
                        </tr>
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">Cartera actual del promotor</th>
                            <td class="px-3 py-2 text-right">
                                <input
                                    type="number"
                                    step="0.01"
                                    x-model.number="form.carteraActual"
                                    class="w-full max-w-[160px] rounded-lg border border-gray-300 px-2 py-1 text-right focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    placeholder="0.00"
                                >
                            </td>
                        </tr>
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Inversión</th>
                            <td class="px-3 py-2 text-right text-lg font-semibold" :class="inversion() >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="money(inversion())"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500">La inversión se calcula como comisiones + total préstamo menos recrédito - cartera actual.</p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <template x-for="tipo in ['Promotor', 'Supervisor']" :key="tipo">
                <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 text-center uppercase">Recibo de dinero</h3>
                        <p class="text-sm text-gray-500 text-center">RECIBÍ DE: MARCO ANTONIO GÜEMES ABUD</p>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between">
                            <span class="font-medium">Fecha:</span>
                            <span x-text="form.fecha"></span>
                        </div>
                        <div class="space-y-1">
                            <label class="block font-medium">Nombre completo de <span x-text="tipo.toLowerCase()"></span></label>
                            <input
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                :placeholder="'Nombre del ' + tipo.toLowerCase()"
                                x-model="tipo === 'Promotor' ? form.nombrePromotor : form.nombreSupervisor"
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Monto recibido:</span>
                            <span class="font-semibold" x-text="money(totalSolicitado())"></span>
                        </div>
                    </div>
                    <div class="h-20 rounded-xl border border-dashed border-gray-300 flex items-end justify-center pb-2 text-xs text-gray-400">
                        Firma
                    </div>
                    <p class="text-center text-xs sm:text-sm font-bold uppercase underline tracking-wide">POR CONCEPTO DE: OPERACIÓN FINANCIERA PARA PRESTAMOS INDIVIDUAL DE LAS PERONSAS MENCIONADAS EN ESTE DESEMBOLSO.</p>
                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('reciboDesembolso', (config) => ({
                rows: [],
                form: {
                    promotor: config.promotorNombre || '',
                    promotoraReconocimiento: '',
                    ejecutivo: '',
                    comisionPromotor: 0,
                    comisionSupervisor: 0,
                    carteraActual: 0,
                    nombrePromotor: config.promotorNombre || '',
                    nombreSupervisor: '',
                    fecha: config.fechaHoy || '',
                },
                clientes: config.clientes || [],
                currencyFormatter: new Intl.NumberFormat('es-MX', {
                    style: 'currency',
                    currency: 'MXN',
                    minimumFractionDigits: 2,
                }),
                init() {
                    const iniciales = (this.clientes || [])
                        .filter(c => c && (c.prestamo_anterior || c.prestamo_solicitado))
                        .map(c => this.nuevaFila({
                            cliente: c.nombre || '',
                            prestamoAnterior: c.prestamo_anterior || 0,
                            prestamoSolicitado: c.prestamo_solicitado || 0,
                        }));

                    this.rows = iniciales.length ? iniciales : [this.nuevaFila()];
                },
                uid() {
                    return Math.random().toString(36).slice(2) + Date.now().toString(36);
                },
                nuevaFila(data = {}) {
                    return {
                        uid: this.uid(),
                        cliente: data.cliente || '',
                        prestamoAnterior: Number(data.prestamoAnterior ?? data.prestamo_anterior ?? 0),
                        prestamoSolicitado: Number(data.prestamoSolicitado ?? data.prestamo_solicitado ?? 0),
                        recreditoNuevo: Number(data.recreditoNuevo ?? data.recredito_nuevo ?? 0),
                        totalRecredito: Number(data.totalRecredito ?? data.total_recredito ?? 0),
                    };
                },
                addRow() {
                    this.rows.push(this.nuevaFila());
                },
                removeRow(uid) {
                    this.rows = this.rows.filter(row => row.uid !== uid);
                },
                number(value) {
                    const n = Number(value);
                    return Number.isFinite(n) ? n : 0;
                },
                rowCommission(row) {
                    return this.number(row.prestamoSolicitado) * 0.05;
                },
                rowTotalPrestamo(row) {
                    return this.number(row.prestamoSolicitado) - this.rowCommission(row);
                },
                rowTotalPrestamoMenosRecredito(row) {
                    return this.rowTotalPrestamo(row) - this.number(row.totalRecredito);
                },
                sumRows(mapper) {
                    return this.rows.reduce((total, row) => total + mapper(row), 0);
                },
                totalAnterior() {
                    return this.sumRows(row => this.number(row.prestamoAnterior));
                },
                totalSolicitado() {
                    return this.sumRows(row => this.number(row.prestamoSolicitado));
                },
                totalComision() {
                    return this.sumRows(row => this.rowCommission(row));
                },
                totalPrestamo() {
                    return this.sumRows(row => this.rowTotalPrestamo(row));
                },
                totalRecreditoNuevo() {
                    return this.sumRows(row => this.number(row.recreditoNuevo));
                },
                totalRecredito() {
                    return this.sumRows(row => this.number(row.totalRecredito));
                },
                totalPrestamoMenosRecredito() {
                    return this.sumRows(row => this.rowTotalPrestamoMenosRecredito(row));
                },
                inversion() {
                    return this.number(this.form.comisionPromotor)
                        + this.number(this.form.comisionSupervisor)
                        + this.totalPrestamoMenosRecredito()
                        - this.number(this.form.carteraActual);
                },
                money(value) {
                    return this.currencyFormatter.format(this.number(value));
                },
            }));
        });
    </script>
</x-layouts.mobile.mobile-layout>