{{-- ======================
         MODAL: RECREDITO
       ====================== --}}
@php($faker = \Faker\Factory::create('es_MX'))
<div x-show="showRecredito" x-cloak class="fixed inset-0 z-40 flex items-center justify-center px-4">
  <div class="absolute inset-0 bg-black/50" @click="resetRecreditoForm()"></div>

  <div @click.stop class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative text-gray-900 overflow-hidden">
    
    <!-- Alpine.js Component for Form Handling -->
    <div x-data="{
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
    }">
        <h3 class="text-xl font-semibold uppercase text-center mb-4">Ingresar Datos (Recredito)</h3>

        <!-- Overlay de Carga -->
        <div x-show="isLoading" x-transition class="absolute inset-0 bg-white/70 flex items-center justify-center z-20">
            <svg class="animate-spin h-10 w-10 text-blue-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Modal de Resultado -->
        <div x-show="result.show" x-transition.opacity class="absolute inset-0 bg-white flex flex-col items-center justify-center text-center z-10 p-6">
            <div class="mx-auto mb-4 w-16 h-16 rounded-full flex items-center justify-center" :class="result.success ? 'bg-green-100' : 'bg-red-100'">
                <svg class="w-10 h-10" :class="result.success ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path x-show="result.success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    <path x-show="!result.success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <p class="text-lg font-medium text-gray-800" x-text="result.message"></p>
            <button @click="result.success ? resetLocalForm() : result.show = false" class="mt-6 w-full bg-blue-800 text-white font-semibold py-2 rounded-lg">
                Cerrar
            </button>
        </div>

        <!-- Modal de Confirmacion de Riesgo -->
        <div x-show="riskConfirm.show" x-transition.opacity class="absolute inset-0 bg-white flex flex-col items-center justify-center text-center z-20 p-6">
            <div class="mx-auto mb-4 w-16 h-16 rounded-full flex items-center justify-center bg-yellow-100 text-yellow-600">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                </svg>
            </div>
            <h4 class="text-lg font-semibold text-gray-900">Solicitud con riesgo</h4>
            <p class="mt-3 text-sm text-gray-700 whitespace-pre-line" x-text="riskConfirm.message"></p>
            <ul class="mt-4 w-full max-h-40 overflow-y-auto text-left space-y-1">
                <template x-for="(detalle, index) in riskConfirm.detalles" :key="index">
                    <li class="text-sm text-gray-700" x-text="detalle"></li>
                </template>
            </ul>
            <div class="mt-6 w-full grid grid-cols-2 gap-3">
                <button @click="handleRiskDecision('rechazar')" class="w-full border border-red-600 text-red-600 font-semibold py-2 rounded-lg hover:bg-red-50 transition">
                    Rechazar
                </button>
                <button @click="handleRiskDecision('aceptar')" class="w-full bg-blue-800 text-white font-semibold py-2 rounded-lg hover:bg-blue-900 transition">
                    Aceptar riesgo
                </button>
            </div>
        </div>

        <form x-ref="formRecredito" method="POST" action="{{ route('mobile.promotor.store_recredito') }}" @submit.prevent="submitRecredito" class="space-y-4">
            @csrf
            
            {{-- div1: Cliente --}}
            <div class="space-y-3 border rounded-xl p-4">
                <p class="font-semibold mt-2">CURP del Cliente:</p>
                <input name="CURP" type="text" placeholder="CURP (18 caracteres)"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 uppercase"
                       required minlength="18" maxlength="18" pattern="[A-Z0-9]{18}"
                       title="El CURP debe contener 18 caracteres alfanumericos en mayusculas."
                       @input="event.target.value = event.target.value.toUpperCase()">

                <p class="font-semibold">Monto del recredito:</p>
                <input name="monto" type="number" step="100.00" min="0" max="20000" placeholder="Monto" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" required>

                <p class="font-semibold">Nombre del cliente (autocompletado):</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <span class="border rounded-lg px-3 py-5 bg-gray-100"></span>
                    <span class="border rounded-lg px-3 py-5 bg-gray-100"></span>
                    <span class="border rounded-lg px-3 py-5 bg-gray-100"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    {{-- INE Cliente --}}
                    <div>
                        <label class="text-sm font-medium block mb-1">INE (Opcional)</label>
                        <input type="file" name="cliente_ine" accept="image/*,application/pdf" class="hidden" x-ref="r_cliIne" @change="r_clientIneUploaded = true">
                        <button type="button" @click="$refs.r_cliIne.click()" :class="r_clientIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-lg px-3 py-2 font-medium transition">
                            <span x-text="r_clientIneUploaded ? ' INE cargado' : 'Subir INE'"></span>
                        </button>
                    </div>

                    {{-- Comprobante Domicilio Cliente --}}
                    <div>
                        <label class="text-sm font-medium block mb-1">Comprobante (Opcional)</label>
                        <input type="file" name="cliente_comprobante" accept="image/*,application/pdf" class="hidden" x-ref="r_cliComp" @change="r_clientCompUploaded = true">
                        <button type="button" @click="$refs.r_cliComp.click()" :class="r_clientCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-lg px-3 py-2 font-medium transition">
                            <span x-text="r_clientCompUploaded ? ' Comprobante cargado' : 'Subir Comprobante'"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Selector de Aval --}}
            <div class="mb-4">
                <p class="font-semibold mb-2">Aval:</p>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" @click="r_newAval = false" :class="!r_newAval ? 'bg-blue-800 text-white' : 'border border-blue-800 text-blue-800 bg-white'" class="w-full rounded-xl px-3 py-2 font-semibold transition">
                        Mismo Aval
                    </button>
                    <button type="button" @click="r_newAval = true" :class="r_newAval ? 'bg-blue-800 text-white' : 'border border-blue-800 text-blue-800 bg-white'" class="w-full rounded-xl px-3 py-2 font-semibold transition">
                        Nuevo Aval
                    </button>
                </div>
                <p class="mt-2 text-xs text-gray-600" x-text="r_newAval ? 'Captura un nuevo aval para este recredito.' : 'Se usara el aval del credito anterior.'"></p>
            </div>

            {{-- div2: Aval (solo si es Nuevo Aval) --}}
            <div x-show="r_newAval" x-transition.opacity class="space-y-3 border rounded-xl p-4">
                <p class="font-semibold">Nombre del nuevo aval:</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input name="aval_nombre" type="text" placeholder="Nombre"  class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" :required="r_newAval">
                    <input name="aval_apellido_p" type="text" placeholder="Apellido Paterno"  class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" :required="r_newAval">
                    <input name="aval_apellido_m" type="text" placeholder="Apellido Materno"  class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                </div>

                <p class="font-semibold mt-2">CURP del nuevo aval:</p>
                <input name="aval_CURP" type="text" placeholder="CURP (18 caracteres)" 
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 uppercase"
                       :required="r_newAval" minlength="18" maxlength="18" pattern="[A-Z0-9]{18}"
                       title="El CURP debe contener 18 caracteres alfanumericos en mayusculas."
                       @input="event.target.value = event.target.value.toUpperCase()">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    {{-- INE Aval --}}
                    <div>
                        <label class="text-sm font-medium block mb-1">INE (Opcional)</label>
                        <input name="aval_ine" type="file" accept="image/*,application/pdf" class="hidden" x-ref="r_avalIne" @change="r_avalIneUploaded = true">
                        <button type="button" @click="$refs.r_avalIne.click()" :class="r_avalIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-lg px-3 py-2 font-medium transition">
                            <span x-text="r_avalIneUploaded ? ' INE cargado' : 'Subir INE'"></span>
                        </button>
                    </div>

                    {{-- Comprobante Domicilio Aval --}}
                    <div>
                        <label class="text-sm font-medium block mb-1">Comprobante (Opcional)</label>
                        <input name="aval_comprobante" type="file" accept="image/*,application/pdf" class="hidden" x-ref="r_avalComp" @change="r_avalCompUploaded = true">
                        <button type="button" @click="$refs.r_avalComp.click()" :class="r_avalCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-lg px-3 py-2 font-medium transition">
                            <span x-text="r_avalCompUploaded ? ' Comprobante cargado' : 'Subir Comprobante'"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="space-y-3 pt-2">
                <button type="submit" class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl shadow-md transition ring-1 ring-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-700">
                    Agregar Recredito
                </button>
                <button type="button" @click="resetRecreditoForm()" class="w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3 rounded-xl transition focus:outline-none focus:ring-2 focus:ring-blue-700">
                    Regresar
                </button>
            </div>
        </form>
    </div>
  </div>
</div>

