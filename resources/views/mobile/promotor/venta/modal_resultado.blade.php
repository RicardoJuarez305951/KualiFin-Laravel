{{-- =========================
     MODAL: RESULTADO DE INSERCIÓN
   ========================= --}}
<div
    x-show="showResultado"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6"
    @keydown.escape.window="showResultado = false"
    @click.self="showResultado = false"
>

  {{-- Panel de Notificación --}}
  <div
      @click.stop
      class="relative w-full max-w-sm overflow-hidden rounded-3xl bg-white text-center shadow-2xl ring-1 ring-slate-900/10"
  >
    <button
        type="button"
        class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
        aria-label="Cerrar"
        @click="showResultado = false"
    >
      <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    {{-- Caso Éxito --}}
    <template x-if="resultadoExito">
      <div class="space-y-5 px-6 pb-6 pt-10">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
          <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <p class="text-lg font-semibold text-gray-800" x-text="resultadoMensaje"></p>
        <button @click="showResultado = false" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-800 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white">
          Aceptar
        </button>
      </div>
    </template>

    {{-- Caso Error --}}
    <template x-if="!resultadoExito">
      <div class="space-y-5 px-6 pb-6 pt-10">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <div class="space-y-2 text-gray-700">
            <p class="text-lg font-semibold text-gray-800"
               x-text="resultadoEstado === 'rechazado' ? 'Crédito registrado como rechazado' : 'Error en la operación'"></p>
            <p class="text-sm" x-text="resultadoMensaje"></p>
        </div>
        <button @click="showResultado = false" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-800 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 focus:ring-offset-white">
          Aceptar
        </button>
      </div>
    </template>

  </div>
</div>
