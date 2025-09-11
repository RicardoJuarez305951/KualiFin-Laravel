{{-- =========================
         MODAL: CRÉDITO (Cliente nuevo)
       ========================= --}}
@php($faker = \Faker\Factory::create('es_MX'))
    <div x-show="showCliente" x-cloak class="fixed inset-0 z-40 flex items-center justify-center px-4">
      <div class="absolute inset-0 bg-black/50" @click="resetClienteForm()"></div>

      <div @click.stop class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative text-gray-900">
        <h3 class="text-xl font-semibold uppercase text-center mb-4">Ingresar Datos (Crédito)</h3>

        {{-- div1: Cliente --}}
        <div class="space-y-3 border rounded-xl p-4 mb-4">
          <p class="font-semibold">Nombre del cliente:</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="text" placeholder="Nombre" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
            <input type="text" placeholder="Apellido Paterno" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
            <input type="text" placeholder="Apellido Materno" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
          </div>

          <p class="font-semibold mt-2">CURP:</p>
          <input type="text" placeholder="CURP" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            {{-- INE Cliente --}}
            <div>
              <label class="text-sm font-medium block mb-1">INE</label>
              <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="cliIne"
                     @change="clientIneUploaded = true">
              <button @click="$refs.cliIne.click()"
                      :class="clientIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'"
                      class="w-full rounded-lg px-3 py-2 font-medium transition">
                <span x-text="clientIneUploaded ? '✔ INE cargado' : 'Subir INE'"></span>
              </button>
            </div>

            {{-- Comprobante Domicilio Cliente --}}
            <div>
              <label class="text-sm font-medium block mb-1">Comprobante Domicilio</label>
              <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="cliComp"
                     @change="clientCompUploaded = true">
              <button @click="$refs.cliComp.click()"
                      :class="clientCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'"
                      class="w-full rounded-lg px-3 py-2 font-medium transition">
                <span x-text="clientCompUploaded ? '✔ Comprobante cargado' : 'Subir Comprobante'"></span>
              </button>
            </div>
          </div>
        </div>

        {{-- div2: Aval --}}
        <div class="space-y-3 border rounded-xl p-4 mb-6">
          <p class="font-semibold">Nombre del aval:</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="text" placeholder="Nombre" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
            <input type="text" placeholder="Apellido Paterno" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
            <input type="text" placeholder="Apellido Materno" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
          </div>

          <p class="font-semibold mt-2">CURP:</p>
          <input type="text" placeholder="CURP" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            {{-- INE Aval --}}
            <div>
              <label class="text-sm font-medium block mb-1">INE</label>
              <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="avalIne"
                     @change="avalIneUploaded = true">
              <button @click="$refs.avalIne.click()"
                      :class="avalIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'"
                      class="w-full rounded-lg px-3 py-2 font-medium transition">
                <span x-text="avalIneUploaded ? '✔ INE cargado' : 'Subir INE'"></span>
              </button>
            </div>

            {{-- Comprobante Domicilio Aval --}}
            <div>
              <label class="text-sm font-medium block mb-1">Comprobante Domicilio</label>
              <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="avalComp"
                     @change="avalCompUploaded = true">
              <button @click="$refs.avalComp.click()"
                      :class="avalCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'"
                      class="w-full rounded-lg px-3 py-2 font-medium transition">
                <span x-text="avalCompUploaded ? '✔ Comprobante cargado' : 'Subir Comprobante'"></span>
              </button>
            </div>
          </div>
        </div>

        {{-- Acciones --}}
        <div class="space-y-3">
          <button @click.prevent="checkViabilidad(); resetClienteForm()"
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