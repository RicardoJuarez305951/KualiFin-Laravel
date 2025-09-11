<x-layouts.mobile.mobile-layout title="Ingresar Cliente">
  <div x-data="{
        showCliente: false,
        showRecredito: false,
        showViabilidad: false,
        viable: false,
        errores: [],
        showError: false,
<<<<<<< ours
=======
        errorMessage: '',
>>>>>>> theirs
        clientCurpUploaded: false,
        clientDomUploaded: false,
        clientIneUploaded: false,
        clientCompUploaded: false,
        avalCurpUploaded: false,
        avalDomUploaded: false,
        avalIneUploaded: false,
        avalCompUploaded: false,
        r_newAval: false,


        // Recrédito (usar flags separados si quieres aislarlos del modal cliente)
        r_clientCurpUploaded: false,
        r_clientDomUploaded: false,
        r_clientIneUploaded: false,
        r_clientCompUploaded: false,
        r_avalCurpUploaded: false,
        r_avalDomUploaded: false,
        r_avalIneUploaded: false,
        r_avalCompUploaded: false,

        resetClienteForm() {
          this.showCliente = false;
          this.clientCurpUploaded = false;
          this.clientDomUploaded = false;
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
          this.r_clientDomUploaded = false;
          this.r_clientIneUploaded = false;
          this.r_clientCompUploaded = false;
          this.r_avalCurpUploaded = false;
          this.r_avalDomUploaded = false;
          this.r_avalIneUploaded = false;
          this.r_avalCompUploaded = false;
          this.errorMessage = '';
          this.showError = false;
        },
        validateMonto(valor) {
          const monto = parseFloat(valor);
          return !(isNaN(monto) || monto < 0 || monto > 20000);
        },
        validateNuevoCliente(e) {
          const f = e.target;
          const valido =
            f.nombre.value.trim() &&
            f.apellido_p.value.trim() &&
            f.CURP.value.trim().length === 18 &&
            this.validateMonto(f.monto.value);
          const docs = this.clientIneUploaded && this.clientCompUploaded && this.avalIneUploaded && this.avalCompUploaded;
          if (!valido) {
            this.errorMessage = 'Datos incorrectos';
            this.showError = true;
            return;
          }
          if (!docs) {
            this.errorMessage = 'Información incompleta';
            this.showError = true;
            return;
          }
          f.submit();
        },
        validateRecredito(e) {
          const f = e.target;
          const valido =
            f.CURP.value.trim().length === 18 &&
            this.validateMonto(f.monto.value);
          const docsCliente = this.r_clientIneUploaded && this.r_clientCompUploaded;
          const docsAval = this.r_newAval ? (this.r_avalIneUploaded && this.r_avalCompUploaded) : true;
          if (!valido) {
            this.errorMessage = 'Datos incorrectos';
            this.showError = true;
            return;
          }
          if (!(docsCliente && docsAval)) {
            this.errorMessage = 'Información incompleta';
            this.showError = true;
            return;
          }
          f.submit();
        },
        validateMonto(valor) {
          const monto = parseFloat(valor);
          return !(isNaN(monto) || monto < 0 || monto > 3000);
        },
        validateNuevoCliente(e) {
          const f = e.target;
          const valido =
            f.nombre.value.trim() &&
            f.apellido_p.value.trim() &&
            f.CURP.value.trim().length === 18 &&
            this.validateMonto(f.monto.value);
          if (!valido) {
            this.showError = true;
            return;
          }
          f.submit();
        },
        validateRecredito(e) {
          const f = e.target;
          const valido =
            f.CURP.value.trim().length === 18 &&
            this.validateMonto(f.monto.value);
          if (!valido) {
            this.showError = true;
            return;
          }
          f.submit();
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
      }">

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

    @include('mobile.promotor.venta.modal_viabilidad')

    <div x-show="showError" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
      <div class="absolute inset-0 bg-black/50" @click="showError = false"></div>
      <div @click.stop class="bg-white rounded-2xl shadow-lg w-full max-w-sm p-6 text-center">
<<<<<<< ours
        <p class="text-lg font-semibold mb-4">Datos incorrectos</p>
=======
        <p class="text-lg font-semibold mb-4" x-text="errorMessage"></p>
>>>>>>> theirs
        <button @click="showError = false" class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-2 rounded-lg">Aceptar</button>
      </div>
    </div>
  </div>
<<<<<<< ours
  
=======
>>>>>>> theirs
</x-layouts.mobile.mobile-layout>
