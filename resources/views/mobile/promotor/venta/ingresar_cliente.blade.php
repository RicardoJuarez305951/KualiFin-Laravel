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

        // Recrédito (usar flags separados si quieres aislarlos del modal cliente)
        r_clientCurpUploaded: false,
        r_clientIneUploaded: false,
        r_clientCompUploaded: false,
        r_avalCurpUploaded: false,
        r_avalDomUploaded: false,
        r_avalIneUploaded: false,
        r_avalCompUploaded: false,

        // Estado de inserción
        showResultado: false,
        resultadoMensaje: '',
        resultadoExito: false,
        resultadoEstado: '',

        resetClienteForm() {
          this.showCliente = false;
          this.clientCurpUploaded = false;
          this.clientIneUploaded = false;
          this.clientCompUploaded = false;
          this.avalCurpUploaded = false;
          this.avalDomUploaded = false;
          this.avalIneUploaded = false;
          this.avalCompUploaded = false;
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

          try {
            let { respuesta, datos } = await enviarSolicitud();

            if (datos?.requires_confirmation) {
              const mensajes = [];

              if (datos.cliente_tiene_deuda && Array.isArray(datos.deuda_cliente) && datos.deuda_cliente.length) {
                mensajes.push('Cliente:');
                formatearDeudas(datos.deuda_cliente).forEach(linea => mensajes.push(`  • ${linea}`));
              }
              if (datos.aval_tiene_deuda && Array.isArray(datos.deuda_aval) && datos.deuda_aval.length) {
                mensajes.push('Aval:');
                formatearDeudas(datos.deuda_aval).forEach(linea => mensajes.push(`  • ${linea}`));
              }

              if (mensajes.length === 0) {
                mensajes.push('No se recibieron detalles de la deuda.');
              }

              const confirmacion = window.confirm([
                datos.message ?? 'Se detectaron deudas asociadas a la solicitud.',
                '',
                mensajes.join('\n'),
                '',
                'Pulsa Aceptar para registrar el crédito con riesgo o Cancelar para guardarlo como rechazado.'
              ].join('\n'));

              const decision = confirmacion ? 'aceptar' : 'rechazar';
              ({ respuesta, datos } = await enviarSolicitud(decision));
            }

            const mensaje = datos?.message ?? 'Respuesta inesperada del servidor.';
            const operacionExitosa = respuesta.ok && datos?.success;
            this.resultadoEstado = datos?.estado_credito ?? '';
            this.resultadoExito = operacionExitosa && this.resultadoEstado !== 'rechazado';

            if (!operacionExitosa) {
              this.resultadoExito = false;
            }

            this.resultadoMensaje = mensaje;
            this.showResultado = true;

            if (datos?.success) {
              f.reset();
              this.resetClienteForm();
            }
          } catch (error) {
            console.error(error);
            this.resultadoExito = false;
            this.resultadoEstado = '';
            this.resultadoMensaje = 'Error de conexión. Inténtalo de nuevo.';
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
            this.resultadoMensaje = 'Error de conexión. Inténtalo de nuevo.';
            this.showResultado = true;
          });
        },
        checkViabilidad() {
          const posiblesErrores = [
            'Cliente o Aval con deuda',
            'Límite de firmas del Aval',
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
  >

    <div class="bg-white rounded-2xl shadow-md p-10 w-full max-w-md space-y-4">
      <h2 class="text-center text-lg font-semibold text-gray-900 uppercase">Ingresar Cliente</h2>

      <button @click="showCliente = true"
              class="flex items-center justify-center gap-2 w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl shadow-md transition ring-1 ring-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
          <path d="M12 4a4 4 0 100 8 4 4 0 000-8zm0 10c-4 0-8 2-8 6v2h16v-2c0-4-4-6-8-6z"/>
        </svg>
        Cliente nuevo (Crédito)
      </button>

      <button @click="showRecredito = true"
              class="flex items-center justify-center gap-2 w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl shadow-md transition ring-1 ring-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
          <path d="M3 7h18M6 7v6a6 6 0 0012 0V7"/>
          <path d="M9 10h6"/>
        </svg>
        Recrédito
      </button>

      <button @click="window.history.back()"
              class="w-full border border-blue-800 text-blue-800 font-medium py-3 rounded-xl hover:bg-blue-50 transition ring-1 ring-blue-900/20 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-700">
        Cancelar
      </button>
    </div>

    @include('mobile.promotor.venta.modal_nuevo_cliente')

    @include('mobile.promotor.venta.modal_recredito')

    @include('mobile.promotor.venta.modal_resultado')

  </div>
  
</x-layouts.mobile.mobile-layout>
