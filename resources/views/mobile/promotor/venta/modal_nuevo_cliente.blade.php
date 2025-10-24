{{-- =========================
         MODAL: CREDITO (Cliente nuevo)
       ========================= --}}
@php($faker = \Faker\Factory::create('es_MX'))
<div
    x-show="showCliente"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6"
    @keydown.escape.window="resetClienteForm()"
    @click.self="resetClienteForm()"
>
  <div
      @click.stop
      class="relative z-10 flex w-full max-w-lg flex-col overflow-hidden rounded-3xl bg-white text-gray-900 shadow-xl ring-1 ring-slate-900/10"
  >
    <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
      <h3 class="text-base font-semibold uppercase tracking-wide text-slate-900">Ingresar Datos (Credito)</h3>
      <button
          type="button"
          class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
          aria-label="Cerrar"
          @click="resetClienteForm()"
      >
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </header>

    <form
        method="POST"
        action="{{ route('mobile.promotor.store_cliente') }}"
        @submit.prevent="submitNuevoCliente($event)"
        class="flex-1 space-y-6 overflow-y-auto px-6 py-6"
    >
      @csrf
      {{-- div1: Cliente --}}
      <div class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-sm">
        <p class="text-sm font-semibold text-slate-900">Nombre del cliente:</p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
          <input name="nombre" type="text" placeholder="Nombre" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300">
          <input name="apellido_p" type="text" placeholder="Apellido Paterno" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300">
          <input name="apellido_m" type="text" placeholder="Apellido Materno" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300">
        </div>

        <p class="text-sm font-semibold text-slate-900">CURP:</p>
        <input name="CURP" type="text" placeholder="CURP" maxlength="18" minlength="18" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm uppercase focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300" @input="event.target.value = event.target.value.toUpperCase()">

        <p class="text-sm font-semibold text-slate-900">Monto del credito:</p>
        <input name="monto" type="number" step="100.00" min="0" max="3000" placeholder="Monto" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300">

        <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">
          {{-- INE Cliente --}}
          <div class="space-y-2">
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-600">INE</label>
            <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="cliIne" @change="clientIneUploaded = true">
            <button type="button" @click="$refs.cliIne.click()" :class="clientIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-xl px-3 py-2 text-sm font-medium transition">
              <span x-text="clientIneUploaded ? 'INE cargado' : 'Subir INE'"></span>
            </button>
          </div>

            {{-- Comprobante Domicilio Cliente --}}
          <div class="space-y-2">
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-600">Comprobante Domicilio</label>
            <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="cliComp" @change="clientCompUploaded = true">
            <button type="button" @click="$refs.cliComp.click()" :class="clientCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-xl px-3 py-2 text-sm font-medium transition">
              <span x-text="clientCompUploaded ? 'Comprobante cargado' : 'Subir Comprobante'"></span>
            </button>
          </div>
        </div>
      </div>

      {{-- div2: Aval --}}
      <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-sm font-semibold text-slate-900">Nombre del aval:</p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
          <input name="aval_nombre" type="text" placeholder="Nombre" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300">
          <input name="aval_apellido_p" type="text" placeholder="Apellido Paterno" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300">
          <input name="aval_apellido_m" type="text" placeholder="Apellido Materno" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300">
        </div>

        <p class="text-sm font-semibold text-slate-900">CURP:</p>
        <input name="aval_CURP" type="text" placeholder="CURP" maxlength="18" minlength="18" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm uppercase focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300" @input="event.target.value = event.target.value.toUpperCase()">

        <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">
          {{-- INE Aval --}}
          <div class="space-y-2">
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-600">INE</label>
            <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="avalIne" @change="avalIneUploaded = true">
            <button type="button" @click="$refs.avalIne.click()" :class="avalIneUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-xl px-3 py-2 text-sm font-medium transition">
              <span x-text="avalIneUploaded ? 'INE cargado' : 'Subir INE'"></span>
            </button>
          </div>

          {{-- Comprobante Domicilio Aval --}}
          <div class="space-y-2">
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-600">Comprobante Domicilio</label>
            <input type="file" accept="image/*,application/pdf" class="hidden" x-ref="avalComp" @change="avalCompUploaded = true">
            <button type="button" @click="$refs.avalComp.click()" :class="avalCompUploaded ? 'bg-green-600 text-white' : 'bg-yellow-400 text-black hover:bg-yellow-500'" class="w-full rounded-xl px-3 py-2 text-sm font-medium transition">
              <span x-text="avalCompUploaded ? 'Comprobante cargado' : 'Subir Comprobante'"></span>
            </button>
          </div>
        </div>
      </div>

      {{-- Acciones --}}
      <div class="space-y-3">
        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-800 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white">
          Agregar
        </button>
        <button type="button" @click="resetClienteForm()" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-blue-800 transition hover:border-blue-200 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white">
          Regresar
        </button>
      </div>
    </form>

    <!-- Modal de Confirmacion de Riesgo -->
    <div
        x-show="riskConfirm.show"
        x-transition.opacity
        class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/95 px-6 py-8 text-center backdrop-blur"
    >
      <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
        </svg>
      </div>
      <h4 class="text-base font-semibold text-gray-900">Solicitud con riesgo</h4>
      <p class="mt-3 whitespace-pre-line text-sm text-gray-700" x-text="riskConfirm.message"></p>
      <ul class="mt-4 w-full max-h-40 space-y-1 overflow-y-auto text-left">
        <template x-for="(detalle, index) in riskConfirm.detalles" :key="index">
          <li class="rounded-lg border border-slate-100 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm" x-text="detalle"></li>
        </template>
      </ul>
      <div class="mt-6 grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
        <button @click="handleRiskDecision('rechazar')" class="inline-flex w-full items-center justify-center rounded-xl border border-red-500 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
          Rechazar
        </button>
        <button @click="handleRiskDecision('aceptar')" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white">
          Aceptar riesgo
        </button>
      </div>
    </div>
  </div>
</div>
