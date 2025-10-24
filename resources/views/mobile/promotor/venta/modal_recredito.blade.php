{{-- ======================
         MODAL: RECREDITO
       ====================== --}}
@php($faker = \Faker\Factory::create('es_MX'))
<div
    x-show="showRecredito"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6"
    @keydown.escape.window="resetRecreditoForm()"
    @click.self="resetRecreditoForm()"
>
  <div
      @click.stop
      x-data="{
        isLoading: false,
        r_newAval: false,
        r_clientIneUploaded: false,
        r_clientCompUploaded: false,
        r_avalIneUploaded: false,
        r_avalCompUploaded: false,
        result: {
            show: false,
            success: false,
            message: ''
        },
        riskConfirm: {
            show: false,
            message: '',
            detalles: [],
            decisionHandler: null
        },
        resetRiskConfirm() {
            this.riskConfirm = {
                show: false,
                message: '',
                detalles: [],
                decisionHandler: null
            };
        },
        async submitRecredito(event) {
            this.isLoading = true;
            this.result.show = false;
            this.resetRiskConfirm();

            const form = event.target;

            const enviarSolicitud = async (decision = null) => {
                const formData = new FormData(form);
                formData.set('r_newAval', this.r_newAval ? '1' : '0');

                if (decision) {
                    formData.set('decision_riesgo', decision);
                } else {
                    formData.delete('decision_riesgo');
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('[name=_token]').value
                    }
                });

                let data = {};
                try {
                    data = await response.clone().json();
                } catch (_) {
                    data = {};
                }

                return { response, data };
            };

            const formatearDeudas = (lista = []) => {
                if (!Array.isArray(lista) || lista.length === 0) {
                    return [];
                }

                return lista.map(item => {
                    const nombre = item?.cliente ?? 'Sin nombre';
                    const deuda = item?.deuda ?? 'Monto no disponible';
                    return `${nombre} - Deuda: ${deuda}`;
                });
            };

            const procesarRespuesta = (response, data) => {
                const estadoCredito = data?.estado_credito ?? '';
                this.result.success = response.ok && data?.success && estadoCredito !== 'rechazado';
                this.result.message = data?.message || (this.result.success ? 'Operacion exitosa.' : 'Ocurrio un error inesperado.');

                if (this.result.success) {
                    form.reset();
                    this.r_newAval = false;
                    this.r_clientIneUploaded = false;
                    this.r_clientCompUploaded = false;
                    this.r_avalIneUploaded = false;
                    this.r_avalCompUploaded = false;
                    this.resetRiskConfirm();
                }

                this.result.show = true;
            };

            try {
                let { response, data } = await enviarSolicitud();

                if (data?.requires_confirmation) {
                    const mensajes = [];
                    const clienteMensajes = [];
                    const avalMensajes = [];

                    if (data.cliente_tiene_deuda && Array.isArray(data.deuda_cliente) && data.deuda_cliente.length) {
                        formatearDeudas(data.deuda_cliente).forEach(linea => clienteMensajes.push(linea));
                    }

                    if (data.cliente_moroso_bd) {
                        clienteMensajes.push('Registrado como moroso en la base de clientes.');
                    }

                    if (data.aval_tiene_deuda && Array.isArray(data.deuda_aval) && data.deuda_aval.length) {
                        formatearDeudas(data.deuda_aval).forEach(linea => avalMensajes.push(linea));
                    }

                    if (clienteMensajes.length) {
                        mensajes.push('Cliente:');
                        clienteMensajes.forEach(linea => mensajes.push(`  - ${linea}`));
                    }

                    if (avalMensajes.length) {
                        mensajes.push('Aval:');
                        avalMensajes.forEach(linea => mensajes.push(`  - ${linea}`));
                    }

                    if (mensajes.length === 0) {
                        mensajes.push('No se recibieron detalles de la deuda.');
                    }

                    this.riskConfirm.show = true;
                    this.riskConfirm.message = data.message ?? 'Se detectaron deudas asociadas a la solicitud.';
                    this.riskConfirm.detalles = mensajes;
                    this.riskConfirm.decisionHandler = async (decision) => {
                        this.riskConfirm.show = false;
                        this.isLoading = true;
                        try {
                            const resultadoDecision = await enviarSolicitud(decision);
                            procesarRespuesta(resultadoDecision.response, resultadoDecision.data);
                        } catch (errorDecision) {
                            console.error('Error en la solicitud:', errorDecision);
                            this.result.success = false;
                            this.result.message = 'No se pudo completar la solicitud. Intenta nuevamente.';
                            this.result.show = true;
                        } finally {
                            this.isLoading = false;
                            this.resetRiskConfirm();
                        }
                    };

                    this.isLoading = false;
                    return;
                }

                procesarRespuesta(response, data);
            } catch (error) {
                console.error('Error en la solicitud:', error);
                this.result.success = false;
                this.result.message = 'No se pudo conectar con el servidor. Revisa tu conexion a internet.';
                this.result.show = true;
            } finally {
                this.isLoading = false;
            }
        },
        handleRiskDecision(decision) {
            if (typeof this.riskConfirm.decisionHandler === 'function') {
                this.riskConfirm.decisionHandler(decision);
            }
        },
        resetLocalForm() {
            this.$refs.formRecredito.reset();
            this.r_newAval = false;
            this.r_clientIneUploaded = false;
            this.r_clientCompUploaded = false;
            this.r_avalIneUploaded = false;
            this.r_avalCompUploaded = false;
            this.result.show = false;
            this.resetRiskConfirm();
        }
    }"
      class="relative z-10 flex w-full max-w-lg flex-col overflow-hidden rounded-3xl bg-white text-gray-900 shadow-xl ring-1 ring-slate-900/10"
  >
    <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
      <h3 class="text-base font-semibold uppercase tracking-wide text-slate-900">Ingresar Datos (Recredito)</h3>
      <button
          type="button"
          class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
          aria-label="Cerrar"
          @click="resetRecreditoForm()"
      >
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </header>

    <div class="relative flex-1 overflow-hidden">
        <!-- Overlay de Carga -->
        <div x-show="isLoading" x-transition class="absolute inset-0 z-30 flex items-center justify-center bg-white/80 backdrop-blur-sm">
            <svg class="h-10 w-10 animate-spin text-blue-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Modal de Resultado -->
        <div x-show="result.show" x-transition.opacity class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/95 px-6 py-8 text-center backdrop-blur">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full" :class="result.success ? 'bg-green-100' : 'bg-red-100'">
                <svg class="h-10 w-10" :class="result.success ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path x-show="result.success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    <path x-show="!result.success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <p class="text-base font-medium text-gray-800" x-text="result.message"></p>
            <button @click="result.success ? resetLocalForm() : result.show = false" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-blue-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white">
                Cerrar
            </button>
        </div>

        <!-- Modal de Confirmacion de Riesgo -->
        <div x-show="riskConfirm.show" x-transition.opacity class="absolute inset-0 z-40 flex flex-col items-center justify-center bg-white/95 px-6 py-8 text-center backdrop-blur">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                </svg>
            </div>
            <h4 class="text-base font-semibold text-gray-900">Solicitud con riesgo</h4>
            <p class="mt-3 whitespace-pre-line text-sm text-gray-700" x-text="riskConfirm.message"></p>
            <ul class="mt-4 w-full max-h-40 space-y-1 overflow-y-auto text-left">
                <template x-for="(detalle, index) in riskConfirm.detalles" :key="index">
                    <li class="rounded-lg border border-slate-100 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm" x-text="detalle"></li>
                </template>
            </ul>
            <div class="mt-6 grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                <button @click="handleRiskDecision('rechazar')" class="inline-flex w-full items-center justify-center rounded-xl border border-red-500 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                    Rechazar
                </button>
                <button @click="handleRiskDecision('aceptar')" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white">
                    Aceptar riesgo
                </button>
            </div>
        </div>

        <form x-ref="formRecredito" method="POST" action="{{ route('mobile.promotor.store_recredito') }}" @submit.prevent="submitRecredito" class="flex h-full flex-col overflow-y-auto">
            @csrf
            <div class="space-y-6 px-6 py-6">
                {{-- div1: Cliente --}}
                <div class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">CURP del Cliente:</p>
                    <input name="CURP" type="text" placeholder="CURP (18 caracteres)"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm uppercase focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300"
                           required minlength="18" maxlength="18" pattern="[A-Z0-9]{18}"
                           title="El CURP debe contener 18 caracteres alfanumericos en mayusculas."
                           @input="event.target.value = event.target.value.toUpperCase()">

                    <p class="text-sm font-semibold text-slate-900">Monto del recredito:</p>
                    <input name="monto" type="number" step="100.00" min="0" max="20000" placeholder="Monto" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300" required>

                    <p class="text-sm font-semibold text-slate-900">Nombre del cliente (autocompletado):</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <span class="rounded-xl border border-slate-200 bg-gray-100 px-3 py-5"></span>
                        <span class="rounded-xl border border-slate-200 bg-gray-100 px-3 py-5"></span>
                        <span class="rounded-xl border border-slate-200 bg-gray-100 px-3 py-5"></span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">
                        {{-- INE Cliente --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-medium uppercase tracking-wide text-slate-600">INE (Opcional)</label>
                            <input type="file" name="cliente_ine" accept="image/*,application/pdf" class="hidden" x-ref="r_cliIne" @change="r_clientIneUploaded = true">
                            <button type="button" @click="$refs.r_cliIne.click()" :class="r_clientIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-xl px-3 py-2 text-sm font-medium transition">
                                <span x-text="r_clientIneUploaded ? ' INE cargado' : 'Subir INE'"></span>
                            </button>
                        </div>

                        {{-- Comprobante Domicilio Cliente --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-medium uppercase tracking-wide text-slate-600">Comprobante (Opcional)</label>
                            <input type="file" name="cliente_comprobante" accept="image/*,application/pdf" class="hidden" x-ref="r_cliComp" @change="r_clientCompUploaded = true">
                            <button type="button" @click="$refs.r_cliComp.click()" :class="r_clientCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-xl px-3 py-2 text-sm font-medium transition">
                                <span x-text="r_clientCompUploaded ? ' Comprobante cargado' : 'Subir Comprobante'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Selector de Aval --}}
                <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">Aval:</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="r_newAval = false" :class="!r_newAval ? 'bg-blue-800 text-white' : 'border border-blue-800 text-blue-800 bg-white'" class="inline-flex w-full items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition">
                            Mismo Aval
                        </button>
                        <button type="button" @click="r_newAval = true" :class="r_newAval ? 'bg-blue-800 text-white' : 'border border-blue-800 text-blue-800 bg-white'" class="inline-flex w-full items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition">
                            Nuevo Aval
                        </button>
                    </div>
                    <p class="text-xs text-gray-600" x-text="r_newAval ? 'Captura un nuevo aval para este recredito.' : 'Se usara el aval del credito anterior.'"></p>
                </div>

                {{-- div2: Aval (solo si es Nuevo Aval) --}}
                <div x-show="r_newAval" x-transition.opacity class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">Nombre del nuevo aval:</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <input name="aval_nombre" type="text" placeholder="Nombre"  class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300" :required="r_newAval">
                        <input name="aval_apellido_p" type="text" placeholder="Apellido Paterno"  class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300" :required="r_newAval">
                        <input name="aval_apellido_m" type="text" placeholder="Apellido Materno"  class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    </div>

                    <p class="text-sm font-semibold text-slate-900">CURP del nuevo aval:</p>
                    <input name="aval_CURP" type="text" placeholder="CURP (18 caracteres)" 
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm uppercase focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300"
                           :required="r_newAval" minlength="18" maxlength="18" pattern="[A-Z0-9]{18}"
                           title="El CURP debe contener 18 caracteres alfanumericos en mayusculas."
                           @input="event.target.value = event.target.value.toUpperCase()">

                    <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">
                        {{-- INE Aval --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-medium uppercase tracking-wide text-slate-600">INE (Opcional)</label>
                            <input name="aval_ine" type="file" accept="image/*,application/pdf" class="hidden" x-ref="r_avalIne" @change="r_avalIneUploaded = true">
                            <button type="button" @click="$refs.r_avalIne.click()" :class="r_avalIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-xl px-3 py-2 text-sm font-medium transition">
                                <span x-text="r_avalIneUploaded ? ' INE cargado' : 'Subir INE'"></span>
                            </button>
                        </div>

                        {{-- Comprobante Domicilio Aval --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-medium uppercase tracking-wide text-slate-600">Comprobante (Opcional)</label>
                            <input name="aval_comprobante" type="file" accept="image/*,application/pdf" class="hidden" x-ref="r_avalComp" @change="r_avalCompUploaded = true">
                            <button type="button" @click="$refs.r_avalComp.click()" :class="r_avalCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-xl px-3 py-2 text-sm font-medium transition">
                                <span x-text="r_avalCompUploaded ? ' Comprobante cargado' : 'Subir Comprobante'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="space-y-3">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-800 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white">
                        Agregar Recredito
                    </button>
                    <button type="button" @click="resetRecreditoForm()" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-blue-800 transition hover:border-blue-200 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white">
                        Regresar
                    </button>
                </div>
            </div>
        </form>
    </div>
  </div>
</div>

