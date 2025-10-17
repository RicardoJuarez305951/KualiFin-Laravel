{{-- =========================
         MODAL: CREDITO (Cliente nuevo)
       ========================= --}}
@php($faker = \Faker\Factory::create('es_MX'))
<div x-show="showCliente" x-cloak class="fixed inset-0 z-40 flex items-center justify-center px-4">
  <div class="absolute inset-0 bg-black/50" @click="resetClienteForm()"></div>

  <div @click.stop class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative z-50 text-gray-900">
    <h3 class="text-xl font-semibold uppercase text-center mb-4">Ingresar Datos (Credito)</h3>

    <form method="POST" action="{{ route('mobile.promotor.store_cliente') }}" @submit.prevent="submitNuevoCliente($event)" class="space-y-4">
      @csrf
      {{-- div1: Cliente --}}
      <div class="space-y-3 border rounded-xl p-4">
        <p class="font-semibold">Nombre del cliente:</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <input name="nombre" type="text" placeholder="Nombre" required class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
          <input name="apellido_p" type="text" placeholder="Apellido Paterno" required class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
          <input name="apellido_m" type="text" placeholder="Apellido Materno" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
        </div>

        <p class="font-semibold mt-2">CURP:</p>
        <input name="CURP" type="text" placeholder="CURP" maxlength="18" minlength="18" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 uppercase" @input="event.target.value = event.target.value.toUpperCase()">

        <p class="font-semibold mt-2">Monto del credito:</p>
        <input name="monto" type="number" step="100.00" min="0" max="3000" placeholder="Monto" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
          {{-- INE Cliente --}}
          <div>
            <label class="text-sm font-medium block mb-1">INE</label>
            <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="cliIne" @change="clientIneUploaded = true">
            <button type="button" @click="$refs.cliIne.click()" :class="clientIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-lg px-3 py-2 font-medium transition">
              <span x-text="clientIneUploaded ? 'INE cargado' : 'Subir INE'"></span>
            </button>
          </div>

            {{-- Comprobante Domicilio Cliente --}}
          <div>
            <label class="text-sm font-medium block mb-1">Comprobante Domicilio</label>
            <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="cliComp" @change="clientCompUploaded = true">
            <button type="button" @click="$refs.cliComp.click()" :class="clientCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-lg px-3 py-2 font-medium transition">
              <span x-text="clientCompUploaded ? 'Comprobante cargado' : 'Subir Comprobante'"></span>
            </button>
          </div>
        </div>
      </div>

      {{-- div2: Aval --}}
      <div class="space-y-3 border rounded-xl p-4">
        <p class="font-semibold">Nombre del aval:</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <input name="aval_nombre" type="text" placeholder="Nombre" required class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
          <input name="aval_apellido_p" type="text" placeholder="Apellido Paterno" required class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
          <input name="aval_apellido_m" type="text" placeholder="Apellido Materno" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
        </div>

        <p class="font-semibold mt-2">CURP:</p>
        <input name="aval_CURP" type="text" placeholder="CURP" maxlength="18" minlength="18" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 uppercase" @input="event.target.value = event.target.value.toUpperCase()">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
          {{-- INE Aval --}}
          <div>
            <label class="text-sm font-medium block mb-1">INE</label>
            <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="avalIne" @change="avalIneUploaded = true">
            <button type="button" @click="$refs.avalIne.click()" :class="avalIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-lg px-3 py-2 font-medium transition">
              <span x-text="avalIneUploaded ? 'INE cargado' : 'Subir INE'"></span>
            </button>
          </div>

          {{-- Comprobante Domicilio Aval --}}
          <div>
            <label class="text-sm font-medium block mb-1">Comprobante Domicilio</label>
            <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="avalComp" @change="avalCompUploaded = true">
            <button type="button" @click="$refs.avalComp.click()" :class="avalCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-lg px-3 py-2 font-medium transition">
              <span x-text="avalCompUploaded ? 'Comprobante cargado' : 'Subir Comprobante'"></span>
            </button>
          </div>
        </div>
      </div>

      {{-- Acciones --}}
      <div class="space-y-3">
        <button type="submit" class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl shadow-md transition ring-1 ring-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-700">
          Agregar
        </button>
        <button type="button" @click="resetClienteForm()" class="w-full text-center text-blue-800 hover:text-blue-900 font-medium py-3 rounded-xl transition focus:outline-none focus:ring-2 focus:ring-blue-700">
          Regresar
        </button>
      </div>
    </form>

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
  </div>
</div>
