{{-- =========================
     MODAL: RESULTADO DE INSERCIÓN
   ========================= --}}
<div x-show="showResultado" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
  <div class="absolute inset-0 bg-black/50" @click="showResultado = false"></div>
  <div @click.stop class="bg-white rounded-2xl shadow-lg w-full max-w-sm p-6 text-center">
    <p class="text-xl font-semibold" :class="resultadoExito ? 'text-green-600' : 'text-red-600'" x-text="resultadoMensaje"></p>
    <div class="mt-6">
      <button @click="showResultado = false" class="w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-2 rounded-lg">
        Aceptar
      </button>
    </div>
  </div>
</div>
