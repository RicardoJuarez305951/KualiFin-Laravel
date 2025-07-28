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
      }">

    <div class="bg-white rounded-2xl shadow-md p-10 w-full max-w-md space-y-4">
      <h2 class="text-center text-lg font-semibold text-gray-900 uppercase">Ingresar Cliente</h2>
      <button @click="showCliente = true"
              class="flex items-center justify-center gap-2 w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl shadow-md transition ring-1 ring-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-700">
        <!-- Icono Usuario -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
          <path d="M12 4a4 4 0 100 8 4 4 0 000-8zm0 10c-4 0-8 2-8 6v2h16v-2c0-4-4-6-8-6z"/>
        </svg>
        Cliente nuevo
      </button>

      <button @click="showRecredito = true"
              class="flex items-center justify-center gap-2 w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl shadow-md transition ring-1 ring-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-700">
        <!-- Icono Cartera -->
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

    {{-- Modal Cliente nuevo --}}
    <div x-show="showCliente" x-cloak
         class="fixed inset-0 z-40 flex items-center justify-center px-4">
      <div class="absolute inset-0 bg-black bg-opacity-50" @click="resetClienteForm()"></div>
      <div @click.stop
           class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative space-y-4 text-gray-900">
        <h3 class="text-xl font-semibold uppercase text-center">Ingresar Datos</h3>

        <p class="font-semibold">Datos del cliente:</p>
        <input type="text" placeholder="CURP"
               class="w-full border border-yellow-400 rounded-lg px-3 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-yellow-400"/>

        <div class="flex space-x-3 mb-4">
          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="clientCurpUploaded = true" x-ref="clientCurp"/>
          <button @click="$refs.clientCurp.click()"
                  :class="clientCurpUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
            <span x-text="clientCurpUploaded ? '✔ CURP' : 'CURP'"></span>
          </button>

          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="clientDomUploaded = true" x-ref="clientDom"/>
          <button @click="$refs.clientDom.click()"
                  :class="clientDomUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
            <span x-text="clientDomUploaded ? '✔ Comp. dom.' : 'Comp. dom.'"></span>
          </button>
        </div>

        <p class="font-semibold">Datos del aval:</p>
        <input type="text" placeholder="CURP"
               class="w-full border border-yellow-400 rounded-lg px-3 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-yellow-400"/>

        <div class="flex space-x-3 mb-6">
          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="avalCurpUploaded = true" x-ref="avalCurp"/>
          <button @click="$refs.avalCurp.click()"
                  :class="avalCurpUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
            <span x-text="avalCurpUploaded ? '✔ CURP' : 'CURP'"></span>
          </button>

          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="avalDomUploaded = true" x-ref="avalDom"/>
          <button @click="$refs.avalDom.click()"
                  :class="avalDomUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
            <span x-text="avalDomUploaded ? '✔ Comp. dom.' : 'Comp. dom.'"></span>
          </button>
        </div>

        {{-- Acciones --}}
        <div class="space-y-3">
          <button @click.prevent="showSuccess = true; resetClienteForm()"
                  class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl shadow-md transition ring-1 ring-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-700">
            Agregar
          </button>
          <button @click="resetClienteForm()"
                  class="w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3 rounded-xl transition focus:outline-none focus:ring-2 focus:ring-blue-700">
            Regresar
          </button>
        </div>
      </div>
    </div>

    {{-- Modal Recrédito --}}
    <div x-show="showRecredito" x-cloak
         class="fixed inset-0 z-40 flex items-center justify-center px-4">
      <div class="absolute inset-0 bg-black bg-opacity-50" @click="resetRecreditoForm()"></div>
      <div @click.stop
           class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative space-y-4 text-gray-900">
        <h3 class="text-xl font-semibold uppercase text-center">Ingresar Datos</h3>

        <p class="font-semibold">Datos del cliente:</p>
        <input type="text" placeholder="CURP"
               class="w-full border border-yellow-400 rounded-lg px-3 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-yellow-400"/>

        <div class="flex space-x-3 mb-4">
          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="clientCurpUploaded = true" x-ref="clientCurp2"/>
          <button @click="$refs.clientCurp2.click()"
                  :class="clientCurpUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
            <span x-text="clientCurpUploaded ? '✔ CURP' : 'CURP'"></span>
            <template x-if="clientCurpUploaded">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </template>
          </button>

          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="clientDomUploaded = true" x-ref="clientDom2"/>
          <button @click="$refs.clientDom2.click()"
                  :class="clientDomUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
            <span x-text="clientDomUploaded ? '✔ Comp. dom.' : 'Comp. dom.'"></span>
            <template x-if="clientDomUploaded">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </template>
          </button>
        </div>

        <p class="font-semibold">Datos del aval:</p>
        <input type="text" placeholder="CURP"
               class="w-full border border-yellow-400 rounded-lg px-3 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-yellow-400"/>

        <div class="flex space-x-3 mb-6">
          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="avalCurpUploaded = true" x-ref="avalCurp2"/>
          <button @click="$refs.avalCurp2.click()"
                  :class="avalCurpUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
            <span x-text="avalCurpUploaded ? '✔ CURP' : 'CURP'"></span>
            <template x-if="avalCurpUploaded">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </template>
          </button>

          <input type="file" accept="application/pdf,image/*" class="hidden"
                 @change="avalDomUploaded = true" x-ref="avalDom2"/>
          <button @click="$refs.avalDom2.click()"
                  :class="avalDomUploaded
                    ? 'bg-green-500 hover:bg-green-600 text-white'
                    : 'bg-yellow-400 hover:bg-yellow-500 text-black'"
                  class="flex-1 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
            <span x-text="avalDomUploaded ? '✔ Comp. dom.' : 'Comp. dom.'"></span>
            <template x-if="avalDomUploaded">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </template>
          </button>
        </div>

        {{-- Acciones --}}
        <div class="space-y-3">
          <button @click.prevent="showSuccess = true; resetRecreditoForm()"
                  class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl shadow-md transition ring-1 ring-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-700">
            Agregar
          </button>
          <button @click="resetRecreditoForm()"
                  class="w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3 rounded-xl transition focus:outline-none focus:ring-2 focus:ring-blue-700">
            Regresar
          </button>
        </div>
      </div>
    </div>

    {{-- Modal Éxito --}}
    <div x-show="showSuccess" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center px-4">
      <div class="absolute inset-0 bg-black bg-opacity-50" @click="showSuccess = false"></div>

      <div @click.stop
           class="relative bg-white rounded-2xl shadow-lg w-full max-w-xs p-6 text-center z-50 space-y-4">
        <p class="text-lg font-semibold text-gray-900">Cliente procesado</p>
        <button @click="showSuccess = false; window.location='{{ route('promotora.venta') }}'"
                class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-2 rounded-xl shadow-md transition ring-1 ring-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-700">
          OK
        </button>
      </div>
    </div>
</x-layouts.promotora_mobile.mobile-layout>
