{{-- =========================
     MODAL: RESULTADO DE INSERCIÓN
   ========================= --}}
<div x-show="showResultado" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 px-4 py-6">

  {{-- Panel de Notificación --}}
  <div @click.stop class="bg-white rounded-2xl shadow-lg w-full max-w-sm p-6 relative text-center">

    {{-- Caso Éxito --}}
    <template x-if="resultadoExito">
      <div class="space-y-4">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
          <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <p class="text-xl font-semibold text-gray-800" x-text="resultadoMensaje"></p>
        <button @click="showResultado = false" class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-2.5 rounded-lg transition-colors">
          Aceptar
        </button>
      </div>
    </template>

    {{-- Caso Error --}}
    <template x-if="!resultadoExito">
      <div class="space-y-4">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <div>
            <p class="text-xl font-semibold text-gray-800"
               x-text="resultadoEstado === 'rechazado' ? 'Crédito registrado como rechazado' : 'Error en la operación'"></p>
            <p class="mt-1 text-gray-600" x-text="resultadoMensaje"></p>
        </div>
        <button @click="showResultado = false" class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-2.5 rounded-lg transition-colors">
          Aceptar
        </button>
      </div>
    </template>

  </div>
</div>