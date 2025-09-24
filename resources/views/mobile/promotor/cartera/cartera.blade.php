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
        class="bg-white rounded-2xl shadow p-4 w-full max-w-lg mx-auto"
    >
        <h2 class="text-center text-2xl font-bold text-gray-800 mb-6">Tu Cartera</h2>

        <div class="space-y-6">
            <section>
                <h3 class="text-2xl font-bold text-gray-700 mb-2">Cartera Activa</h3>
                @include('mobile.promotor.cartera.activa', ['activos' => $activos])
            </section>

            <section>
                <h3 class="text-2xl font-bold text-gray-700 mb-2">Cartera Vencida</h3>
                @include('mobile.promotor.cartera.vencida', ['vencidos' => $vencidos])
            </section>

            <section>
                <h3 class="text-2xl font-bold text-gray-700 mb-2">Cartera Inactiva</h3>
                @include('mobile.promotor.cartera.inactiva', ['inactivos' => $inactivos])
            </section>
        </div>

        <button
            class="w-full mt-6 py-2 bg-blue-600 text-white rounded"
            @click="$store.multiPay.toggleMode()"
        >
            Pagos Múltiples
        </button>

        <div
            x-show="$store.multiPay.active"
            class="mt-2 flex gap-2"
        >
            <button
                class="flex-1 py-2 bg-green-600 text-white rounded"
                @click="$store.multiPay.confirm()"
            >
                Registrar Pagos
            </button>

            <button
                class="flex-1 py-2 bg-red-600 text-white rounded"
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
            <h4 class="text-lg font-semibold text-gray-700">Clientes seleccionados</h4>

            <ul class="space-y-2">
                <template x-for="cliente in $store.multiPay.clients" :key="cliente.pago_proyectado_id ?? cliente.id">
                    <li
                        class="border rounded-xl px-3 py-2 shadow-sm flex items-start justify-between gap-3"
                        :class="$store.multiPay.summaryItemClasses(cliente.tipo)"
                    >
                        <div class="space-y-1">
                            <p class="text-base font-semibold text-gray-900" x-text="cliente.nombre"></p>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide"
                                :class="$store.multiPay.summaryTypeTextClasses(cliente.tipo)"
                                x-text="$store.multiPay.typeLabel(cliente.tipo)"
                            ></span>
                        </div>

                        <div class="text-right">
                            <p
                                class="text-sm font-semibold"
                                :class="$store.multiPay.summaryAmountClasses(cliente.tipo)"
                                x-text="formatCurrency(cliente.monto)"
                            ></p>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        <div
            x-show="$store.multiPay.active && !$store.multiPay.clients.length"
            x-cloak
            class="mt-4 text-sm text-gray-500"
        >
            Selecciona clientes para ver el resumen de pagos.
        </div>

        <div class="mt-8">
            <a href="{{ route('mobile.promotor.index') }}"
               class="block w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3">
                Regresar
            </a>
        </div>
         @include('mobile.modals.calculadora')
         @include('mobile.modals.detalle')

        {{-- Modal: Detalle Cartera Vencida (estructura 4 grids) --}}
        <div
            x-show="showVencidaDetail"
            x-cloak
            @keydown.escape.window="showVencidaDetail=false"
            class="fixed inset-0 z-10 flex items-center justify-center bg-black/50"
        >
            <div class="bg-white rounded-2xl p-6 w-[22rem] sm:w-[26rem]" @click.away="showVencidaDetail=false" x-transition>
                <h3 class="text-lg font-bold mb-4" x-text="vencidaDetail.nombre_cliente"></h3>

                {{-- Grid fila 1: Cliente (11) | Aval (12) --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- 11: Datos de cliente --}}
                    <div class="space-y-1">
                        <p class="text-xs text-gray-500">Nombre cliente</p>
                        <p class="font-medium" x-text="vencidaDetail.nombre_cliente"></p>

                        <p class="text-xs text-gray-500 mt-2">Dirección</p>
                        <p class="break-words" x-text="vencidaDetail.direccion_cliente"></p>

                        <p class="text-xs text-gray-500 mt-2">Teléfono</p>
                        <p x-text="vencidaDetail.telefono_cliente"></p>
                    </div>

                    {{-- 12: Datos de aval --}}
                    <div class="space-y-1">
                        <p class="text-xs text-gray-500">Nombre aval</p>
                        <p class="font-medium" x-text="vencidaDetail.nombre_aval"></p>

                        <p class="text-xs text-gray-500 mt-2">Dir. aval</p>
                        <p class="break-words" x-text="vencidaDetail.direccion_aval"></p>

                        <p class="text-xs text-gray-500 mt-2">Tel. aval</p>
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
                            <p class="text-xs text-gray-500">Promotor</p>
                            <p class="font-medium" x-text="vencidaDetail.promotor"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Monto deuda</p>
                            <p class="font-semibold text-red-600"
                            x-text="new Intl.NumberFormat('es-MX',{style:'currency', currency:'MXN'})
                                        .format(Number(vencidaDetail.monto_deuda || 0))"></p>
                        </div>
                    </div>

                    {{-- 22: Supervisora y fecha --}}
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-500">Supervisora</p>
                            <p class="font-medium" x-text="vencidaDetail.supervisora"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Fecha préstamo</p>
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
            class="fixed inset-0 z-10 flex items-center justify-center bg-black/50"
        >
            <div class="bg-white rounded-2xl p-6 w-80" @click.away="showInactivaDetail = null" x-transition>
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
