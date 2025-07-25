{{-- resources/views/mobile/ingresar_cliente.blade.php --}}
<x-layouts.promotora_mobile.mobile-layout title="Ingresar Cliente">
  <div x-data="{
        showCliente: false,
        showRecredito: false,
        showSuccess: false,
        clientCurpUploaded: false,
        clientDomUploaded: false,
        avalCurpUploaded: false,
        avalDomUploaded: false,
        resetClienteForm() {
          this.showCliente = false;
          this.clientCurpUploaded = false;
          this.clientDomUploaded = false;
        },
        resetRecreditoForm() {
          this.showRecredito = false;
          this.clientCurpUploaded = false;
          this.clientDomUploaded = false;
          this.avalCurpUploaded = false;
          this.avalDomUploaded = false;
        }
      }"
      class="bg-gray-100 min-h-screen p-4 flex items-center justify-center">


    <div class="bg-white rounded-2xl shadow p-6 w-full max-w-md">
      <h2 class="text-center text-lg font-semibold text-black mb-6">Ingresar Cliente</h2>
      <div class="space-y-4">
        <button @click="showCliente = true"
                class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg">
          Cliente nuevo
        </button>
        <button @click="showRecredito = true"
                class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg">
          Recrédito
        </button>
        <button @click="window.history.back()"
                class="w-full border border-blue-800 text-blue-800 font-medium py-3 rounded-lg hover:bg-blue-50">
          Cancelar
        </button>
      </div>
    </div>

    {{-- Modal Cliente nuevo --}}
    <div x-show="showCliente" x-cloak class="fixed inset-0 z-40 flex items-center justify-center">
      <div class="absolute inset-0 bg-black bg-opacity-50" @click="resetClienteForm()"></div>
      <div @click.stop class="bg-white rounded-lg shadow-lg w-11/12 max-w-md p-6 relative">
        <h3 class="text-xl font-semibold mb-4">Ingresar Datos</h3>

        <p class="font-semibold mb-2">Datos del cliente:</p>
        <input type="text" placeholder="CURP"
               class="w-full border border-yellow-400 rounded px-3 py-2 mb-3 focus:outline-none focus:ring"/>

        <div class="flex space-x-2 mb-4">
          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="clientCurpUploaded = true" x-ref="clientCurp">
          <button @click="$refs.clientCurp.click()"
                  :class="clientCurpUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition">
            <span x-text="clientCurpUploaded ? '✔ CURP' : 'CURP'"></span>
          </button>

          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="clientDomUploaded = true" x-ref="clientDom">
          <button @click="$refs.clientDom.click()"
                  :class="clientDomUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition">
            <span x-text="clientDomUploaded ? '✔ Comp. dom.' : 'Comp. dom.'"></span>
          </button>
        </div>

        <p class="font-semibold mb-2">Datos del aval:</p>
        <input type="text" placeholder="CURP"
               class="w-full border border-yellow-400 rounded px-3 py-2 mb-3 focus:outline-none focus:ring"/>

        <div class="flex space-x-2 mb-6">
          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="avalCurpUploaded = true" x-ref="avalCurp">
          <button @click="$refs.avalCurp.click()"
                  :class="avalCurpUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition">
            <span x-text="avalCurpUploaded ? '✔ CURP' : 'CURP'"></span>
          </button>

          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="avalDomUploaded = true" x-ref="avalDom">
          <button @click="$refs.avalDom.click()"
                  :class="avalDomUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition">
            <span x-text="avalDomUploaded ? '✔ Comp. dom.' : 'Comp. dom.'"></span>
          </button>
        </div>

        {{-- Acciones --}}
        <div class="space-y-3">
          <button @click.prevent="showSuccess = true; resetClienteForm()"
                  class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg">
            Agregar
          </button>
          <button @click="resetClienteForm()"
                  class="w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3">
            Regresar
          </button>
        </div>
      </div>
    </div>

    {{-- Modal Recrédito --}}
    <div x-show="showRecredito" x-cloak class="fixed inset-0 z-40 flex items-center justify-center">
      <div class="absolute inset-0 bg-black bg-opacity-50" @click="resetRecreditoForm()"></div>
      <div @click.stop class="bg-white rounded-lg shadow-lg w-11/12 max-w-md p-6 relative">
        <h3 class="text-xl font-semibold mb-4">Ingresar Datos</h3>

        <p class="font-semibold mb-2">Datos del cliente:</p>
        <input type="text" placeholder="CURP"
               class="w-full border border-yellow-400 rounded px-3 py-2 mb-3 focus:outline-none focus:ring"/>

        <div class="flex space-x-2 mb-4">
          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="clientCurpUploaded = true" x-ref="clientCurp2">
          <button @click="$refs.clientCurp2.click()"
                  :class="clientCurpUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition">
            <span x-text="clientCurpUploaded ? '✔ CURP' : 'CURP'"></span>
          </button>

          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="clientDomUploaded = true" x-ref="clientDom2">
          <button @click="$refs.clientDom2.click()"
                  :class="clientDomUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition">
            <span x-text="clientDomUploaded ? '✔ Comp. dom.' : 'Comp. dom.'"></span>
          </button>
        </div>

        <p class="font-semibold mb-2">Datos del aval:</p>
        <input type="text" placeholder="CURP"
               class="w-full border border-yellow-400 rounded px-3 py-2 mb-3 focus:outline-none focus:ring"/>

        <div class="flex space-x-2 mb-6">
          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="avalCurpUploaded = true" x-ref="avalCurp2">
          <button @click="$refs.avalCurp2.click()"
                  :class="avalCurpUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition">
            <span x-text="avalCurpUploaded ? '✔ CURP' : 'CURP'"></span>
          </button>

          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="avalDomUploaded = true" x-ref="avalDom2">
          <button @click="$refs.avalDom2.click()"
                  :class="avalDomUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition">
            <span x-text="avalDomUploaded ? '✔ Comp. dom.' : 'Comp. dom.'"></span>
          </button>
        </div>

        {{-- Acciones --}}
        <div class="space-y-3">
          <button @click.prevent="showSuccess = true; resetRecreditoForm()"
                  class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg">
            Agregar
          </button>
          <button @click="resetRecreditoForm()"
                  class="w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3">
            Regresar
          </button>
        </div>
      </div>
    </div>

        {{-- Modal Éxito --}}
    <div 
      x-show="showSuccess" 
      x-cloak 
      class="fixed inset-0 z-50 flex items-center justify-center">
      {{-- Overlay: clic aquí cierra --}}
      <div 
        class="absolute inset-0 bg-black bg-opacity-50" 
        @click="showSuccess = false"></div>

      {{-- Contenido: detiene clics y está por encima del overlay --}}
      <div 
        @click.stop 
        class="relative bg-white rounded-lg shadow-lg w-11/12 max-w-xs p-6 text-center z-50">
        <p class="text-lg font-semibold mb-4">Cliente procesado</p>
        <button 
          @click="showSuccess = false; window.location='{{ route('promotora.venta') }}'"
          class="mt-2 w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-2 rounded-lg">
          OK
        </button>
      </div>
    </div>
</x-layouts.promotora_mobile.mobile-layout>
