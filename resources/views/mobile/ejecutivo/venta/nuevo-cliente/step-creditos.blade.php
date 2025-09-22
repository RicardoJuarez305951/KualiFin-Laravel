<div x-show="currentStep === 2" x-cloak x-transition class="space-y-4">
  <h3 class="text-sm font-semibold text-gray-700">Creditos</h3>

  <div class="grid grid-cols-1 gap-3">
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto total <span class="text-red-500">*</span></label>
      <input type="number" step="0.01" min="0"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.credito.monto_total" name="credito[monto_total]">
    </div>
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Periodicidad <span class="text-red-500">*</span></label>
      <select class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              x-model="form.credito.periodicidad" name="credito[periodicidad]">
        <option value="">Selecciona una opci?n</option>
        <option value="14 Semanas">14 Semanas</option>
        <option value="15 Semanas">15 Semanas</option>
        <option value="22 Semanas">22 Semanas</option>
        <option value="Mes">Mes</option>
      </select>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha inicio <span class="text-red-500">*</span></label>
        <input type="date"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.credito.fecha_inicio" name="credito[fecha_inicio]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha final <span class="text-red-500">*</span></label>
        <input type="date"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.credito.fecha_final" name="credito[fecha_final]">
      </div>
    </div>
  </div>
</div>
