<div x-show="currentStep === 4" x-cloak x-transition class="space-y-4">
  <h3 class="text-sm font-semibold text-gray-700">Datos de contacto</h3>

  <div class="grid grid-cols-1 gap-3">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Calle <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.calle" name="contacto[calle]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Número exterior <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.numero_ext" name="contacto[numero_ext]">
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Número interior</label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.numero_int" name="contacto[numero_int]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto mensual <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" min="0"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.monto_mensual" name="contacto[monto_mensual]">
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Colonia <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.colonia" name="contacto[colonia]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Municipio <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.municipio" name="contacto[municipio]">
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.estado" name="contacto[estado]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Código postal <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.cp" name="contacto[cp]">
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Tiempo en residencia <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.tiempo_en_residencia" name="contacto[tiempo_en_residencia]" placeholder="Ej. 3 años">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Teléfono fijo</label>
        <input type="tel"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.tel_fijo" name="contacto[tel_fijo]">
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Teléfono celular <span class="text-red-500">*</span></label>
        <input type="tel"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.contacto.tel_cel" name="contacto[tel_cel]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Tipo de vivienda <span class="text-red-500">*</span></label>
        <select class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                x-model="form.contacto.tipo_de_vivienda" name="contacto[tipo_de_vivienda]">
          <option value="">Selecciona una opción</option>
          <option value="Propia">Propia</option>
          <option value="Rentada">Rentada</option>
          <option value="Familiar">Familiar</option>
        </select>
      </div>
    </div>
  </div>
</div>
