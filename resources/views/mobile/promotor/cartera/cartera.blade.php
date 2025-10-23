{{-- resources/views/mobile/promotor/cartera/cartera.blade.php --}}
<x-layouts.mobile.mobile-layout title="Tu Cartera">
    <div
        x-data="{
            // Vencida detail
            vencidaDetail: {
                nombre_cliente: '',
                direccion_cliente: '',
                telefono_cliente: '',
                nombre_aval: '',
                direccion_aval: '',
                telefono_aval: '',
                promotor: '',
                supervisora: '',
                monto_deuda: '',
                fecha_prestamo: '',
            },
            showVencidaDetail: false,

            // Inactiva detail
            showInactivaDetail: null,

            normalizeString(value) {
                if (value === undefined || value === null) {
                    return '';
                }

                if (typeof value === 'string') {
                    return value.trim();
                }

                if (typeof value === 'number' || typeof value === 'boolean') {
                    return String(value);
                }

                return '';
            },

            formatName(...parts) {
                return parts
                    .map((part) => this.normalizeString(part))
                    .filter(Boolean)
                    .join(' ')
                    .replace(/\s+/g, ' ')
                    .trim();
            },

            formatCurrency(value) {
                const numeric = Number(value) || 0;
                return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(numeric);
            },

            pickValue(target, paths) {
                for (const path of paths) {
                    if (!path) continue;

                    const segments = path.split('.');
                    let current = target;

                    for (const segment of segments) {
                        if (current === undefined || current === null) {
                            current = undefined;
                            break;
                        }

                        current = current[segment];
                    }

                    if (current === undefined || current === null) {
                        continue;
                    }

                    if (typeof current === 'string') {
                        const trimmed = current.trim();
                        if (trimmed !== '') {
                            return trimmed;
                        }
                        continue;
                    }

                    if (Array.isArray(current)) {
                        if (current.length) {
                            return current;
                        }
                        continue;
                    }

                    if (typeof current === 'object') {
                        if (Object.keys(current).length) {
                            return current;
                        }
                        continue;
                    }

                    return current;
                }

                return '';
            },

            resolveAval(c) {
                let aval = this.pickValue(c, ['ultimo_aval', 'aval', 'aval_actual']);

                if (Array.isArray(aval) && aval.length) {
                    aval = aval[aval.length - 1];
                }

                if (!aval || typeof aval !== 'object' || Array.isArray(aval)) {
                    const avalesCredito = this.pickValue(c, ['credito.avales']);
                    if (Array.isArray(avalesCredito) && avalesCredito.length) {
                        aval = avalesCredito[avalesCredito.length - 1];
                    }
                }

                if (!aval || typeof aval !== 'object' || Array.isArray(aval)) {
                    const avales = this.pickValue(c, ['avales']);
                    if (Array.isArray(avales) && avales.length) {
                        aval = avales[avales.length - 1];
                    }
                }

                if (!aval || typeof aval !== 'object' || Array.isArray(aval)) {
                    aval = {
                        apellido_p: this.pickValue(c, ['aval_apellido_p']),
                        apellido_m: this.pickValue(c, ['aval_apellido_m']),
                        nombre: this.pickValue(c, ['aval_nombre']),
                        direccion: this.pickValue(c, ['aval_direccion']),
                        telefono: this.pickValue(c, ['aval_telefono']),
                        CURP: this.pickValue(c, ['aval_CURP', 'aval_curp']),
                    };
                }

                return aval && typeof aval === 'object' && !Array.isArray(aval) ? aval : {};
            },

            openInactivaDetail(c) {
                const clienteApellidoP = this.pickValue(c, ['apellido_p', 'cliente.apellido_p', 'apellido']);
                const clienteApellidoM = this.pickValue(c, ['apellido_m', 'cliente.apellido_m']);
                const clienteNombre = this.pickValue(c, ['nombre', 'cliente.nombre']);

                const clienteDireccion = this.normalizeString(this.pickValue(c, ['direccion', 'cliente.direccion', 'domicilio']));
                const clienteCurp = this.normalizeString(this.pickValue(c, ['CURP', 'curp', 'cliente.CURP', 'cliente.curp']));
                const clienteTelefono = this.normalizeString(this.pickValue(c, ['telefono', 'cliente.telefono']));

                const aval = this.resolveAval(c);
                const avalApellidoP = this.pickValue(aval, ['apellido_p', 'apellido']);
                const avalApellidoM = this.pickValue(aval, ['apellido_m']);
                const avalNombre = this.pickValue(aval, ['nombre']);
                const avalDireccion = this.normalizeString(this.pickValue(aval, ['direccion', 'domicilio']));
                const avalCurp = this.normalizeString(this.pickValue(aval, ['CURP', 'curp']));
                const avalTelefono = this.normalizeString(this.pickValue(aval, ['telefono', 'telefono_contacto']));

                this.showInactivaDetail = {
                    client: {
                        nombre: this.formatName(clienteApellidoP, clienteApellidoM, clienteNombre),
                        direccion: clienteDireccion,
                        curp: clienteCurp,
                        telefono: clienteTelefono,
                    },
                    aval: {
                        nombre: this.formatName(avalApellidoP, avalApellidoM, avalNombre),
                        direccion: avalDireccion,
                        curp: avalCurp,
                        telefono: avalTelefono,
                    },
                    fecha_ultimo_credito: this.normalizeString(this.pickValue(c, ['fecha_ultimo_credito', 'ultimo_credito.fecha', 'fecha_ultimo'])),
                };
            },

            openVencidaDetail(c) {
                this.vencidaDetail = {
                    nombre_cliente: `${c['apellido'] ?? c.apellido ?? ''} ${c['nombre'] ?? c.nombre ?? ''}`.trim(),
                    direccion_cliente: c['direccion'] ?? c.direccion ?? '',
                    telefono_cliente: c['telefono'] ?? c.telefono ?? '',
                    nombre_aval: c['aval_nombre'] ?? c.aval_nombre ?? '',
                    direccion_aval: c['aval_direccion'] ?? c.aval_direccion ?? '',
                    telefono_aval: c['aval_telefono'] ?? c.aval_telefono ?? '',
                    promotor: c['promotor'] ?? c.promotor ?? '',
                    supervisora: c['supervisora'] ?? c.supervisora ?? '',
                    monto_deuda: c['monto_deuda'] ?? c.monto_deuda ?? '',
                    fecha_prestamo: c['fecha_prestamo'] ?? c.fecha_prestamo ?? '',
                };
                this.showVencidaDetail = true;
            }
        }"
        class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8 text-slate-900"
    >
        @php
            $activosCount = collect($activos ?? [])->count();
            $vencidosCount = collect($vencidos ?? [])->count();
            $inactivosCount = collect($inactivos ?? [])->count();
        @endphp

        <section class="rounded-3xl border border-gray-300 bg-white p-6 shadow space-y-4">
            <header class="space-y-2 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-600">Resumen</p>
                <h1 class="text-2xl font-bold text-slate-900 leading-tight">Tu cartera</h1>
                <p class="text-sm text-slate-600">Controla tus clientes activos, vencidos e inactivos desde un solo lugar.</p>
            </header>
            <div class="grid grid-cols-3 gap-3 text-center text-sm">
                <div class="rounded-2xl border border-gray-200 bg-slate-50 p-3 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-slate-700">Activa</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">{{ $activosCount }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-amber-50 p-3 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-amber-600">Vencida</p>
                    <p class="mt-1 text-lg font-semibold text-amber-700">{{ $vencidosCount }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-slate-100 p-3 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-slate-700">Inactiva</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">{{ $inactivosCount }}</p>
                </div>
            </div>
        </section>

        <div class="space-y-6">
            <section class="space-y-4">
                <header class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Cartera activa</h3>
                    <span class="inline-flex items-center rounded-full bg-slate-900 text-white px-3 py-1 text-xs font-semibold shadow">
                        {{ $activosCount }} clientes
                    </span>
                </header>
                <div class="rounded-2xl border border-gray-300 bg-white p-4 shadow">
                    @include('mobile.promotor.cartera.activa', ['activos' => $activos])
                </div>
            </section>

            <section class="space-y-4">
                <header class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Cartera vencida</h3>
                    <span class="inline-flex items-center rounded-full bg-amber-500 text-white px-3 py-1 text-xs font-semibold shadow">
                        {{ $vencidosCount }} casos
                    </span>
                </header>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow">
                    @include('mobile.promotor.cartera.vencida', ['vencidos' => $vencidos])
                </div>
            </section>

            <section class="space-y-4">
                <header class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Cartera inactiva</h3>
                    <span class="inline-flex items-center rounded-full bg-slate-200 text-slate-800 px-3 py-1 text-xs font-semibold shadow">
                        {{ $inactivosCount }} registros
                    </span>
                </header>
                <div class="rounded-2xl border border-gray-300 bg-white p-4 shadow">
                    @include('mobile.promotor.cartera.inactiva', ['inactivos' => $inactivos])
                </div>
            </section>
        </div>

        <button
            class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-black"
            @click="$store.multiPay.toggleMode()"
        >
            Pagos Múltiples
        </button>

        <div
            x-show="$store.multiPay.active"
            class="mt-3 flex flex-col gap-3 sm:flex-row"
        >
            <button
                class="flex-1 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-emerald-700"
                @click="$store.multiPay.confirm()"
            >
                Registrar pagos
            </button>

            <button
                class="flex-1 inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow hover:bg-gray-50"
                @click="$store.multiPay.cancel()"
            >
                Cancelar
            </button>
        </div>

        <div
            x-show="$store.multiPay.active && $store.multiPay.clients.length"
            x-cloak
            class="mt-4 space-y-3"
        >
            <h4 class="text-lg font-semibold text-slate-900">Clientes seleccionados</h4>

            <ul class="space-y-3">
                <template x-for="cliente in $store.multiPay.clients" :key="cliente.pago_proyectado_id ?? cliente.id">
                    <li
                        class="flex items-start justify-between gap-3 rounded-2xl border border-gray-300 bg-white px-4 py-3 shadow"
                        :class="$store.multiPay.summaryItemClasses(cliente.tipo)"
                    >
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-slate-900" x-text="cliente.nombre"></p>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wide text-slate-700"
                                :class="$store.multiPay.summaryTypeTextClasses(cliente.tipo)"
                                x-text="$store.multiPay.typeLabel(cliente.tipo)"
                            ></span>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <button
                                type="button"
                                class="text-xs font-semibold text-rose-600 hover:text-rose-700"
                                @click.stop="$store.multiPay.remove(cliente.id)"
                                aria-label="Quitar"
                            >
                                X
                            </button>

                            <p
                                class="text-sm font-semibold text-slate-900"
                                :class="$store.multiPay.summaryAmountClasses(cliente.tipo)"
                                x-text="formatCurrency(cliente.monto)"
                            ></p>
                            <template x-if="cliente.anticipo && cliente.anticipo > 0">
                                <p class="text-xs font-semibold text-slate-700">
                                    Adelanto:
                                    <span x-text="formatCurrency(cliente.anticipo)"></span>
                                </p>
                            </template>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        <div
            x-show="$store.multiPay.active && !$store.multiPay.clients.length"
            x-cloak
            class="mt-4 text-sm text-slate-600"
        >
            Selecciona clientes para ver el resumen de pagos.
        </div>

        <div class="mt-8">
            <a href="{{ route('mobile.promotor.index') }}"
               class="inline-flex w-full items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow hover:bg-gray-50">
                Regresar
            </a>
        </div>
         @include('mobile.modals.calculadora')
         @include('mobile.modals.detalle')

        <div
            x-show="$store.multiPay.showSuccess"
            x-cloak
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/70 px-4 py-6"
        >
            <div class="w-full max-w-sm space-y-4 rounded-3xl border border-gray-300 bg-white p-6 text-center shadow-2xl">
                <div class="w-16 h-16 rounded-full bg-green-100 text-green-600 mx-auto flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-slate-900" x-text="$store.multiPay.successMessage"></h2>
                <ul
                    x-show="$store.multiPay.successDetails.length"
                    class="text-left text-sm text-slate-600 space-y-2"
                >
                    <template x-for="pago in $store.multiPay.successDetails" :key="pago.pago_proyectado_id ?? pago.id ?? pago.tipo">
                        <li class="border border-gray-200 rounded-xl px-3 py-2">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-slate-900" x-text="$store.multiPay.typeLabel(pago.tipo)"></span>
                                <span
                                    x-show="$store.multiPay.paymentAmount(pago)"
                                    class="text-sm text-slate-600"
                                    x-text="$store.multiPay.paymentAmount(pago)"
                                ></span>
                            </div>
                        </li>
                    </template>
                </ul>
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-black"
                    @click="$store.multiPay.acknowledgeSuccess()"
                >
                    Aceptar
                </button>
            </div>
        </div>

        {{-- Modal: Detalle Cartera Vencida (estructura 4 grids) --}}
        <div
            x-show="showVencidaDetail"
            x-cloak
            @keydown.escape.window="showVencidaDetail=false"
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/70"
        >
            <div class="w-[22rem] sm:w-[26rem] rounded-3xl border border-gray-300 bg-white p-6 shadow-2xl" @click.away="showVencidaDetail=false" x-transition>
                <h3 class="text-lg font-bold mb-4" x-text="vencidaDetail.nombre_cliente"></h3>

                {{-- Grid fila 1: Cliente (11) | Aval (12) --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- 11: Datos de cliente --}}
                    <div class="space-y-1">
                        <p class="text-xs text-slate-600">Nombre cliente</p>
                        <p class="font-medium" x-text="vencidaDetail.nombre_cliente"></p>

                        <p class="text-xs text-slate-600 mt-2">Dirección</p>
                        <p class="break-words" x-text="vencidaDetail.direccion_cliente"></p>

                        <p class="text-xs text-slate-600 mt-2">Teléfono</p>
                        <p x-text="vencidaDetail.telefono_cliente"></p>
                    </div>

                    {{-- 12: Datos de aval --}}
                    <div class="space-y-1">
                        <p class="text-xs text-slate-600">Nombre aval</p>
                        <p class="font-medium" x-text="vencidaDetail.nombre_aval"></p>

                        <p class="text-xs text-slate-600 mt-2">Dir. aval</p>
                        <p class="break-words" x-text="vencidaDetail.direccion_aval"></p>

                        <p class="text-xs text-slate-600 mt-2">Tel. aval</p>
                        <p x-text="vencidaDetail.telefono_aval"></p>
                    </div>
                </div>

                {{-- Divisor --}}
                <div class="my-4 border-t border-gray-200"></div>

                {{-- Grid fila 2: Promotor + Deuda (21) | Supervisora + Fecha (22) --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- 21: Promotor y deuda --}}
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-slate-600">Promotor</p>
                            <p class="font-medium" x-text="vencidaDetail.promotor"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600">Monto deuda</p>
                            <p class="font-semibold text-red-600"
                            x-text="new Intl.NumberFormat('es-MX',{style:'currency', currency:'MXN'})
                                        .format(Number(vencidaDetail.monto_deuda || 0))"></p>
                        </div>
                    </div>

                    {{-- 22: Supervisora y fecha --}}
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-slate-600">Supervisora</p>
                            <p class="font-medium" x-text="vencidaDetail.supervisora"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600">Fecha préstamo</p>
                            <p class="font-medium" x-text="vencidaDetail.fecha_prestamo"></p>
                        </div>
                    </div>
                </div>

                <button class="w-full mt-5 py-2 bg-blue-600 text-white rounded-md"
                        @click="showVencidaDetail=false">
                    Cerrar
                </button>
            </div>
        </div>


        {{-- Modal: Detalle Cartera Inactiva --}}
        <div
            x-show="showInactivaDetail"
            x-cloak
            @keydown.escape.window="showInactivaDetail=null"
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/70"
        >
            <div class="w-[20rem] rounded-3xl border border-gray-300 bg-white p-6 shadow-2xl" @click.away="showInactivaDetail = null" x-transition>
                <div class="mb-4">
                    <h3 class="text-lg font-bold">Cliente</h3>
                    <p class="font-semibold" x-text="showInactivaDetail.client.nombre"></p>
                    <p x-text="showInactivaDetail.client.direccion"></p>
                    <p><span class="font-semibold">CURP:</span> <span x-text="showInactivaDetail.client.curp"></span></p>
                    <p><span class="font-semibold">Telefono:</span> <span x-text="showInactivaDetail.client.telefono"></span></p>
                </div>
                <div class="mb-4">
                    <h3 class="text-lg font-bold">Ultimo Aval</h3>
                    <p class="font-semibold" x-text="showInactivaDetail.aval.nombre"></p>
                    <p x-text="showInactivaDetail.aval.direccion"></p>
                    <p><span class="font-semibold">CURP:</span> <span x-text="showInactivaDetail.aval.curp"></span></p>
                    <p><span class="font-semibold">Telefono:</span> <span x-text="showInactivaDetail.aval.telefono"></span></p>
                </div>
                <p class="mb-4"><span class="font-semibold">Fecha último crédito:</span> <span x-text="showInactivaDetail.fecha_ultimo_credito"></span></p>
                <button @click="showInactivaDetail = null" class="w-full py-2 bg-blue-600 text-white rounded">Cerrar</button>
            </div>
        </div>

    </div>
</x-layouts.mobile.mobile-layout>








