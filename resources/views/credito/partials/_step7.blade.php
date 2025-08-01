{{-- resources/views/credito/partials/_step7.blade.php --}}
<div class="space-y-6">
  <h2 class="text-xl font-semibold">Paso 7: Resumen de Documentación</h2>

  {{-- Resumen de todo formData --}}
  <div class="bg-gray-50 p-4 rounded h-96 overflow-auto">
    <pre class="whitespace-pre-wrap text-sm" x-text="JSON.stringify(formData, null, 2)"></pre>
  </div>

  {{-- Botón de envío --}}
  <div class="flex justify-end">
    <button
      type="button"
      @click="submitForm"
      class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
    >
      Enviar Solicitud
    </button>
  </div>
</div>
