<x-layouts.mobile.mobile-layout title="Ingresar Cliente">
  <div x-data="{
        showCliente: false,
        showRecredito: false,
        showSuccess: false,
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

    {{-- Modal Éxito --}}
    <div x-show="showSuccess" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
      <div class="absolute inset-0 bg-black/50" @click="showSuccess = false"></div>

      <div @click.stop class="relative bg-white rounded-2xl shadow-lg w-full max-w-xs p-6 text-center z-50 space-y-4">
        <p class="text-lg font-semibold text-gray-900">Cliente procesado</p>
        <button @click="showSuccess = false; window.location='{{ route("mobile.$role.venta") }}'"
                class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-2 rounded-xl shadow-md transition ring-1 ring-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-700">
          OK
        </button>
      </div>
    </div>
</x-layouts.mobile.mobile-layout>
