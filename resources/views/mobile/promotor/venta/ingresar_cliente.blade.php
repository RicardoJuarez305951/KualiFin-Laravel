<x-layouts.mobile.mobile-layout title="Ingresar Cliente">
    <div
        x-data="{
            showCliente: false,
            showRecredito: false,
            showViabilidad: false,
            viable: false,
            errores: [],
            showError: false,
            clientCurpUploaded: false,
            clientIneUploaded: false,
            clientCompUploaded: false,
            avalCurpUploaded: false,
            avalDomUploaded: false,
            avalIneUploaded: false,
            avalCompUploaded: false,
            r_newAval: false,

            r_clientCurpUploaded: false,
            r_clientIneUploaded: false,
            r_clientCompUploaded: false,
            r_avalCurpUploaded: false,
            r_avalDomUploaded: false,
            r_avalIneUploaded: false,
            r_avalCompUploaded: false,

            showResultado: false,
            resultadoMensaje: '',
            resultadoExito: false,
            resultadoEstado: '',
            riskConfirm: {
                show: false,
                message: '',
                detalles: [],
                decisionHandler: null,
            },

            resetClienteForm() {
                this.showCliente = false;
                this.clientCurpUploaded = false;
                this.clientIneUploaded = false;
                this.clientCompUploaded = false;
                this.avalCurpUploaded = false;
                this.avalDomUploaded = false;
                this.avalIneUploaded = false;
                this.avalCompUploaded = false;
                this.resetRiskConfirm();
            },
            resetRecreditoForm() {
                this.showRecredito = false;
                this.r_clientCurpUploaded = false;
                this.r_clientIneUploaded = false;
                this.r_clientCompUploaded = false;
                this.r_avalCurpUploaded = false;
                this.r_avalDomUploaded = false;
                this.r_avalIneUploaded = false;
                this.r_avalCompUploaded = false;
            },
            resetRiskConfirm() {
                this.riskConfirm.show = false;
                this.riskConfirm.message = '';
                this.riskConfirm.detalles = [];
                this.riskConfirm.decisionHandler = null;
            },
            handleRiskDecision(decision) {
                if (typeof this.riskConfirm.decisionHandler === 'function') {
                    this.riskConfirm.decisionHandler(decision);
                }
            },
            validateMonto(valor, max = 3000) {
                const monto = parseFloat(valor);
                return !(isNaN(monto) || monto < 0 || monto > max);
            },
            async submitNuevoCliente(e) {
                const f = e.target;
                const valido =
                    f.nombre.value.trim() &&
                    f.apellido_p.value.trim() &&
                    f['CURP'].value.trim().length === 18 &&
                    this.validateMonto(f.monto.value);
                if (!valido) {
                    this.resultadoExito = false;
                    this.resultadoEstado = '';
                    this.resultadoMensaje = 'Datos incorrectos o incompletos. Por favor, verifica el formulario.';
                    this.showResultado = true;
                    return;
                }

                this.resetRiskConfirm();

                const enviarSolicitud = async (decision = null) => {
                    const formData = new FormData(f);
                    if (decision) {
                        formData.set('decision_riesgo', decision);
                    } else {
                        formData.delete('decision_riesgo');
                    }

                    const respuesta = await fetch('{{ route('mobile.promotor.store_cliente') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData
                    });

                    let datos = {};
                    try {
                        datos = await respuesta.clone().json();
                    } catch (_) {
                        datos = {};
                    }

                    return { respuesta, datos };
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

                const procesarRespuesta = (respuesta, datos) => {
                    const { status } = respuesta;
                    const success = Boolean(datos.success);
                    const message = datos.message ?? 'Solicitud procesada.';

                    if (success) {
                        this.resultadoExito = true;
                        this.resultadoEstado = datos.estado ?? '';
                        this.resultadoMensaje = message;
                        this.showResultado = true;
                        f.reset();
                        this.resetClienteForm();
                        return;
                    }

                    if (status === 422) {
                        const errores = datos.errors ?? {};
                        const mensajes = Object.values(errores).flat();
                        this.resultadoExito = false;
                        this.resultadoEstado = '';
                        this.resultadoMensaje = mensajes.length
                            ? mensajes.join(' ')
                            : 'Datos inválidos. Revisa el formulario.';
                        this.showResultado = true;
                        return;
                    }

                    if (status === 409) {
                        const detalles = formatearDeudas(datos.detalles ?? []);
                        this.resultadoExito = false;
                        this.resultadoEstado = datos.estado ?? '';
                        this.resultadoMensaje = message + (detalles.length ? `\n- ${detalles.join('\n- ')}` : '');
                        this.showResultado = true;
                        return;
                    }

                    if (status === 428) {
                        const { riesgo } = datos;
                        const detalles = formatearDeudas(riesgo?.detalles ?? []);
                        this.riskConfirm = {
                            show: true,
                            message: riesgo?.mensaje ?? 'Se requiere confirmaci&oacute;n de riesgo.',
                            detalles,
                            decisionHandler: async (decision) => {
                                this.resetRiskConfirm();
                                try {
                                    const { respuesta: resp2, datos: datos2 } = await enviarSolicitud(decision);
                                    procesarRespuesta(resp2, datos2);
                                } catch (error2) {
                                    console.error(error2);
                                    this.resultadoExito = false;
                                    this.resultadoEstado = '';
                                    this.resultadoMensaje = 'No se pudo completar la solicitud. Int&eacute;ntalo nuevamente.';
                                    this.showResultado = true;
                                }
                            },
                        };

                        return;
                    }

                    this.resultadoExito = false;
                    this.resultadoEstado = '';
                    this.resultadoMensaje = message || 'No se pudo completar la solicitud. Int&eacute;ntalo nuevamente.';
                    this.showResultado = true;
                };

                try {
                    const { respuesta, datos } = await enviarSolicitud();

                    if (respuesta.status === 428) {
                        const { riesgo } = datos;
                        const detalles = formatearDeudas(riesgo?.detalles ?? []);

                        this.riskConfirm = {
                            show: true,
                            message: riesgo?.mensaje ?? 'Se requiere confirmaci&oacute;n de riesgo.',
                            detalles,
                            decisionHandler: async (decision) => {
                                this.resetRiskConfirm();
                                try {
                                    const { respuesta: resp2, datos: datos2 } = await enviarSolicitud(decision);
                                    procesarRespuesta(resp2, datos2);
                                } catch (error2) {
                                    console.error(error2);
                                    this.resultadoExito = false;
                                    this.resultadoEstado = '';
                                    this.resultadoMensaje = 'No se pudo completar la solicitud. Int&eacute;ntalo nuevamente.';
                                    this.showResultado = true;
                                }
                            },
                        };

                        return;
                    }

                    procesarRespuesta(respuesta, datos);
                } catch (error) {
                    console.error(error);
                    this.resetRiskConfirm();
                    this.resultadoExito = false;
                    this.resultadoEstado = '';
                    this.resultadoMensaje = 'Error de conexi&oacute;n. Int&eacute;ntalo de nuevo.';
                    this.showResultado = true;
                }
            },
            submitRecredito(e) {
                const f = e.target;
                const valido =
                    f['CURP'].value.trim().length === 18 &&
                    this.validateMonto(f.monto.value);
                if (!valido) {
                    this.showError = true;
                    return;
                }

                const formData = new FormData(f);
                fetch('{{ route('mobile.promotor.store_recredito') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.resultadoExito = data.success;
                    this.resultadoMensaje = data.message;
                    this.showResultado = true;
                    if (data.success) {
                        f.reset();
                        this.resetRecreditoForm();
                    }
                })
                .catch(() => {
                    this.resultadoExito = false;
                    this.resultadoMensaje = 'Error de conexi&oacute;n. Int&eacute;ntalo de nuevo.';
                    this.showResultado = true;
                });
            },
            checkViabilidad() {
                const posiblesErrores = [
                    'Cliente o Aval con deuda',
                    'L&iacute;mite de firmas del Aval',
                    'Cliente en otra plaza'
                ];
                this.viable = Math.random() > 0.5;
                this.errores = this.viable
                    ? []
                    : posiblesErrores.filter(() => Math.random() > 0.5);
                if (!this.viable && this.errores.length === 0) {
                    this.errores = [posiblesErrores[0]];
                }
                this.showViabilidad = true;
            }
        }"
        x-init="
            @if(session('success'))
                showResultado = true;
                resultadoMensaje = @js(session('success'));
                resultadoExito = true;
            @elseif(session('error'))
                showResultado = true;
                resultadoMensaje = @js(session('error'));
                resultadoExito = false;
            @endif
        "
        class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8 text-slate-900"
    >
        <section class="rounded-3xl border border-gray-300 bg-white p-6 shadow space-y-4">
            <header class="space-y-2 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-600">Ventas</p>
                <h1 class="text-2xl font-bold text-slate-900 leading-tight">Ingresar cliente</h1>
                <p class="text-sm text-slate-600">
                    Selecciona si vas a capturar un cliente nuevo o un recr&eacute;dito y sigue los pasos guiados.
                </p>
            </header>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-slate-700">Nuevo</p>
                    <p class="mt-1 text-xs text-slate-600">Cliente sin historial en esta plaza.</p>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-emerald-700">Recr&eacute;dito</p>
                    <p class="mt-1 text-xs text-emerald-600">Renueva cr&eacute;dito a clientes validados.</p>
                </div>
            </div>
        </section>

        <div class="space-y-3">
            <button
                @click="showCliente = true"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-black"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M12 4a4 4 0 100 8 4 4 0 000-8zm0 10c-4 0-8 2-8 6v2h16v-2c0-4-4-6-8-6z" />
                </svg>
                Cliente nuevo (cr&eacute;dito)
            </button>

            <button
                @click="showRecredito = true"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow hover:bg-gray-50"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M3 7h18M6 7v6a6 6 0 0012 0V7" />
                    <path d="M9 10h6" />
                </svg>
                Recr&eacute;dito
            </button>

            <button
                @click="window.history.back()"
                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow hover:bg-gray-50"
            >
                Cancelar
            </button>
        </div>

        @include('mobile.promotor.venta.modal_nuevo_cliente')
        @include('mobile.promotor.venta.modal_recredito')
        @include('mobile.promotor.venta.modal_resultado')
    </div>
</x-layouts.mobile.mobile-layout>
