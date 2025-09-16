<div x-show="currentStep === 7" x-cloak x-transition class="space-y-4">
  <div class="flex items-center justify-between">
    <h3 class="text-sm font-semibold text-gray-700">Garantias</h3>
    <button type="button"
            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="addGarantia()" :disabled="!canAddGarantia()">Agregar garantia</button>
  </div>
  <p class="text-[11px] text-gray-500">Se pueden registrar hasta 8 garantias por credito.</p>

  <template x-for="(garantia, index) in form.garantias" :key="index">
    <div class="space-y-3 rounded-lg border border-gray-200 p-3">
      <div class="flex items-start justify-between">
        <p class="text-sm font-semibold text-gray-700" x-text="`Garantia ${index + 1}`"></p>
        <button type="button"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-red-500 text-red-600 hover:bg-red-50"
                @click="removeGarantia(index)"
                x-show="form.garantias.length > 1">Eliminar</button>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Propietario <span class="text-red-500">*</span></label>
          <input type="text"
                 class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 :name="`garantias[${index}][propietario]`"
                 x-model="form.garantias[index].propietario">
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Tipo <span class="text-red-500">*</span></label>
          <input type="text"
                 class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 :name="`garantias[${index}][tipo]`"
                 x-model="form.garantias[index].tipo">
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Marca</label>
          <input type="text"
                 class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 :name="`garantias[${index}][marca]`"
                 x-model="form.garantias[index].marca">
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Modelo</label>
          <input type="text"
                 class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 :name="`garantias[${index}][modelo]`"
                 x-model="form.garantias[index].modelo">
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Numero de serie</label>
          <input type="text"
                 class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 :name="`garantias[${index}][num_serie]`"
                 x-model="form.garantias[index].num_serie">
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Antiguedad <span class="text-red-500">*</span></label>
          <input type="text"
                 class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 :name="`garantias[${index}][antiguedad]`"
                 x-model="form.garantias[index].antiguedad">
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto garantizado <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" min="0"
                 class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 :name="`garantias[${index}][monto_garantizado]`"
                 x-model="form.garantias[index].monto_garantizado">
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Foto (URL) <span class="text-red-500">*</span></label>
          <input type="url"
                 class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                 :name="`garantias[${index}][foto_url]`"
                 x-model="form.garantias[index].foto_url" placeholder="https://">
        </div>
      </div>
    </div>
  </template>

  <p class="text-[11px] text-gray-500" x-show="!canAddGarantia()">Alcanzaste el limite de 8 garantias para este credito.</p>
</div>
