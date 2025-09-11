{{-- ======================
         MODAL: RECRÉDITO
       ====================== --}}
@php($faker = \Faker\Factory::create('es_MX'))
    <div x-show="showRecredito" x-cloak class="fixed inset-0 z-40 flex items-center justify-center px-4">
      <div class="absolute inset-0 bg-black/50" @click="resetRecreditoForm()"></div>

      <div @click.stop class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative text-gray-900">
        <h3 class="text-xl font-semibold uppercase text-center mb-4">Ingresar Datos (Recrédito)</h3>

        {{-- div1: Cliente --}}
        <div class="space-y-3 border rounded-xl p-4 mb-4">
          <p class="font-semibold mt-2">CURP:</p>
          <input type="text" placeholder="CURP" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
          
          
          <p class="font-semibold">Nombre del cliente:</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <span class="border rounded-lg px-3 py-5 focus:ring-blue-400"></span>
            <span class="border rounded-lg px-3 py-5 focus:ring-blue-400"></span>
            <span class="border rounded-lg px-3 py-5 focus:ring-blue-400"></span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            {{-- INE Cliente --}}
            <div>
              <label class="text-sm font-medium block mb-1">INE</label>
              <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="r_cliIne"
                     @change="r_clientIneUploaded = true">
              <button @click="$refs.r_cliIne.click()"
                      :class="r_clientIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'"
                      class="w-full rounded-lg px-3 py-2 font-medium transition">
                <span x-text="r_clientIneUploaded ? '✔ INE cargado' : 'Subir INE'"></span>
              </button>
            </div>

            {{-- Comprobante Domicilio Cliente --}}
            <div>
              <label class="text-sm font-medium block mb-1">Comprobante Domicilio</label>
              <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="r_cliComp"
                     @change="r_clientCompUploaded = true">
              <button @click="$refs.r_cliComp.click()"
                      :class="r_clientCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'"
                      class="w-full rounded-lg px-3 py-2 font-medium transition">
                <span x-text="r_clientCompUploaded ? '✔ Comprobante cargado' : 'Subir Comprobante'"></span>
              </button>
            </div>
          </div>
        </div>

        {{-- Selector de Aval --}}
        <div class="mb-4">
          <p class="font-semibold mb-2">Aval:</p>
          <div class="grid grid-cols-2 gap-3">
            <button type="button"
                    @click="r_newAval = false"
                    :class="r_newAval ? 'border border-blue-800 text-blue-800 bg-white' : 'bg-blue-800 text-white'"
                    class="w-full rounded-xl px-3 py-2 font-semibold transition">
              Mismo Aval
            </button>
            <button type="button"
                    @click="r_newAval = true"
                    :class="r_newAval ? 'bg-blue-800 text-white' : 'border border-blue-800 text-blue-800 bg-white'"
                    class="w-full rounded-xl px-3 py-2 font-semibold transition">
              Nuevo Aval
            </button>
          </div>
          <p class="mt-2 text-xs text-gray-600"
            x-text="r_newAval ? 'Captura un nuevo aval para este recrédito.' : 'Se usará el aval previo registrado.'"></p>
        </div>

        {{-- div2: Aval (solo si es Nuevo Aval) --}}
        <div x-show="r_newAval" x-transition.opacity
            class="space-y-3 border rounded-xl p-4 mb-6">
          <p class="font-semibold">Nombre del aval:</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="text" placeholder="Nombre"  class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
            <input type="text" placeholder="Apellido Paterno"  class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
            <input type="text" placeholder="Apellido Materno"  class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
          </div>

          <p class="font-semibold mt-2">CURP:</p>
          <input type="text" placeholder="CURP" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            {{-- INE Aval --}}
            <div>
              <label class="text-sm font-medium block mb-1">INE</label>
              <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="r_avalIne"
                    @change="r_avalIneUploaded = true">
              <button @click="$refs.r_avalIne.click()"
                      :class="r_avalIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'"
                      class="w-full rounded-lg px-3 py-2 font-medium transition">
                <span x-text="r_avalIneUploaded ? '✔ INE cargado' : 'Subir INE'"></span>
              </button>
            </div>

            {{-- Comprobante Domicilio Aval --}}
            <div>
              <label class="text-sm font-medium block mb-1">Comprobante Domicilio</label>
              <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="r_avalComp"
                    @change="r_avalCompUploaded = true">
              <button @click="$refs.r_avalComp.click()"
                      :class="r_avalCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'"
                      class="w-full rounded-lg px-3 py-2 font-medium transition">
                <span x-text="r_avalCompUploaded ? '✔ Comprobante cargado' : 'Subir Comprobante'"></span>
              </button>
            </div>
          </div>
        </div>

        {{-- Acciones --}}
        <div class="space-y-3">
          <button @click.prevent="checkViabilidad(); resetRecreditoForm()"
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