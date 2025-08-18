{{-- ======================
     MODAL: VIABILIDAD
   ====================== --}}
<div x-show="showViabilidad" x-cloak class="fixed inset-0 z-40 flex items-center justify-center px-4">
  <div class="absolute inset-0 bg-black/50" @click="showViabilidad = false"></div>

  <div @click.stop class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative text-gray-900">
    <template x-if="viable">
      <p class="text-center text-xl font-semibold text-green-600">Felicidades, tu venta es viable</p>
    </template>

    <template x-if="!viable">
      <div class="text-center">
        <p class="text-xl font-semibold text-red-700 mb-4">Tu venta no es viable</p>
        <ul class="space-y-2 text-left">
          <template x-for="(error, index) in errores" :key="index">
            <li class="flex items-start gap-2 text-sm text-gray-800">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-red-500 mt-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
                <path d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span x-text="error"></span>
            </li>
          </template>
        </ul>
      </div>
    </template>
  </div>
</div>
