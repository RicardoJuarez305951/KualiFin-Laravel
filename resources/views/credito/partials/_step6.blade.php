{{-- resources/views/credito/partials/_step6.blade.php --}}
<div>
  {{-- Grid de garantías --}}
  <div class="space-y-6">
    <h2 class="text-xl font-semibold">Paso 6: Garantías</h2>

    <div class="grid grid-cols-4 gap-4">
      <template
        x-for="(gar, idx) in formData.garantias"
        x-bind:key="idx"
      >
        <div
          class="border rounded p-4 cursor-pointer transition-colors"
          :class="activeGarantia === idx 
            ? 'border-indigo-600 bg-indigo-50' 
            : 'border-gray-300 hover:border-gray-500'"
          @click="activeGarantia = idx"
        >
          <p class="font-medium">Garantía <span x-text="idx + 1"></span></p>
        </div>
      </template>
    </div>
  </div>

  {{-- Modal: dentro del mismo x-data --}}
  <div
    x-show="activeGarantia !== null"
    x-cloak
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div
      x-show="activeGarantia !== null"
      x-transition
      class="bg-white rounded-lg p-6 max-w-lg w-full relative"
    >
      {{-- Cerrar --}}
      <button
        @click="activeGarantia = null"
        class="absolute top-3 right-3 text-gray-500 hover:text-gray-800"
      >✕</button>

      <h3 class="text-lg font-semibold mb-4">
        Garantía <span x-text="activeGarantia + 1"></span>
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- 1. Tipo --}}
        <div>
          <label class="block text-sm font-medium text-gray-700">Tipo</label>
          <select
            x-model="formData.garantias[activeGarantia].tipo"
            x-bind:name="`garantias[${activeGarantia}][tipo]`"
            class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
          >
            <option value="">— Seleccione tipo —</option>
            <option value="Refrigerador">Refrigerador</option>
            <option value="Lavadora">Lavadora</option>
            <option value="Televisor">Televisor</option>
            <option value="Equipo de sonido">Equipo de sonido</option>
          </select>
        </div>

        {{-- 2. Marca --}}
        <div>
          <label class="block text-sm font-medium text-gray-700">Marca</label>
          <input
            type="text"
            x-model="formData.garantias[activeGarantia].marca"
            x-bind:name="`garantias[${activeGarantia}][marca]`"
            class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
          />
        </div>

        {{-- 3. Nº Serie --}}
        <div>
          <label class="block text-sm font-medium text-gray-700">Número de serie</label>
          <input
            type="text"
            x-model="formData.garantias[activeGarantia].num_serie"
            x-bind:name="`garantias[${activeGarantia}][num_serie]`"
            class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
          />
        </div>

        {{-- 4. Modelo --}}
        <div>
          <label class="block text-sm font-medium text-gray-700">Modelo</label>
          <input
            type="text"
            x-model="formData.garantias[activeGarantia].modelo"
            x-bind:name="`garantias[${activeGarantia}][modelo]`"
            class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
          />
        </div>

        {{-- 5. Antigüedad --}}
        <div>
          <label class="block text-sm font-medium text-gray-700">Antigüedad</label>
          <select
            x-model="formData.garantias[activeGarantia].antiguedad"
            x-bind:name="`garantias[${activeGarantia}][antiguedad]`"
            class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
          >
            <option value="">— Seleccione antigüedad —</option>
            <option value="Menos de 1 año">Menos de 1 año</option>
            <option value="1–3 años">1–3 años</option>
            <option value="Más de 3 años">Más de 3 años</option>
          </select>
        </div>

        {{-- 6. Monto garantizado --}}
        <div>
          <label class="block text-sm font-medium text-gray-700">Monto garantizado</label>
          <input
            type="number"
            step="0.01"
            x-model.number="formData.garantias[activeGarantia].monto"
            x-bind:name="`garantias[${activeGarantia}][monto]`"
            class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
          />
        </div>

        {{-- 7. Fotografía --}}
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Fotografía</label>
          <input
            type="file"
            accept="image/*"
            x-bind:name="`garantias[${activeGarantia}][foto]`"
            class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
          />
        </div>
      </div>

      {{-- Botones modal --}}
      <div class="flex justify-end mt-6 space-x-2">
        <button
          type="button"
          @click="activeGarantia = null"
          class="px-4 py-2 bg-gray-200 rounded"
        >Cancelar</button>
        <button
          type="button"
          @click="activeGarantia = null"
          class="px-4 py-2 bg-blue-600 text-white rounded"
        >Guardar</button>
      </div>
    </div>
  </div>
</div>
