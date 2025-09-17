<div x-show="currentStep === 3" x-cloak x-transition class="space-y-4">
  <h3 class="text-sm font-semibold text-gray-700">Ocupaciones</h3>

  <div class="grid grid-cols-1 gap-3">
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Actividad <span class="text-red-500">*</span></label>
      <input type="text"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.ocupacion.actividad" name="ocupacion[actividad]">
    </div>

    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Nombre de la empresa <span class="text-red-500">*</span></label>
      <input type="text"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.ocupacion.nombre_empresa" name="ocupacion[nombre_empresa]">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Calle <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.ocupacion.calle" name="ocupacion[calle]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Número <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.ocupacion.numero" name="ocupacion[numero]">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Colonia <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.ocupacion.colonia" name="ocupacion[colonia]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Municipio <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.ocupacion.municipio" name="ocupacion[municipio]">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Teléfono <span class="text-red-500">*</span></label>
        <input type="tel"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.ocupacion.telefono" name="ocupacion[telefono]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Antigüedad <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.ocupacion.antiguedad" name="ocupacion[antiguedad]" placeholder="Ej. 2 años">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto percibido <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" min="0"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.ocupacion.monto_percibido" name="ocupacion[monto_percibido]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Periodo de pago <span class="text-red-500">*</span></label>
        <select class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                x-model="form.ocupacion.periodo_pago" name="ocupacion[periodo_pago]">
          <option value="">Selecciona una opción</option>
          <option value="Semanal">Semanal</option>
          <option value="Quincenal">Quincenal</option>
          <option value="Mensual">Mensual</option>
          <option value="Bimestral">Bimestral</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Ingresos adicionales (opcional con checkbox) -->
  <div class="space-y-3 border-t border-dashed border-gray-300 pt-3">
    <div class="flex items-center justify-between">
      <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
        <input type="checkbox"
               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
               x-model="form.ocupacion.tiene_ingresos_adicionales"
               @change="handleIngresosAdicionalesToggle()">
        <span>Ingresos adicionales</span>
      </label>

      <button type="button"
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition"
              :class="form.ocupacion.tiene_ingresos_adicionales ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-200 text-gray-500 cursor-not-allowed'"
              @click="addIngresoAdicional()"
              :disabled="!form.ocupacion.tiene_ingresos_adicionales">
        Agregar
      </button>
    </div>

    <template x-if="form.ocupacion.tiene_ingresos_adicionales">
      <div class="space-y-3">
        <template x-for="(ingreso, index) in form.ocupacion.ingresos_adicionales" :key="index">
          <div class="space-y-3 rounded-lg border border-gray-200 p-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Concepto <span class="text-red-500">*</span></label>
                <input type="text"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       :name="`ocupacion[ingresos_adicionales][${index}][concepto]`"
                       x-model="form.ocupacion.ingresos_adicionales[index].concepto">
              </div>
              <div class="space-y-1">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" min="0"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       :name="`ocupacion[ingresos_adicionales][${index}][monto]`"
                       x-model="form.ocupacion.ingresos_adicionales[index].monto">
              </div>
              <div class="space-y-1">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Frecuencia <span class="text-red-500">*</span></label>
                <input type="text"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       :name="`ocupacion[ingresos_adicionales][${index}][frecuencia]`"
                       x-model="form.ocupacion.ingresos_adicionales[index].frecuencia">
              </div>
            </div>
            <div class="flex justify-end">
              <button type="button"
                      class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-red-500 text-red-600 hover:bg-red-50"
                      @click="removeIngresoAdicional(index)"
                      x-show="form.ocupacion.ingresos_adicionales.length > 1">
                Eliminar
              </button>
            </div>
          </div>
        </template>

        <p class="text-[11px] text-gray-500">
          Captura tantos ingresos adicionales como necesites para completar la información del cliente.
        </p>
      </div>
    </template>

    <p class="text-[11px] text-gray-500" x-show="!form.ocupacion.tiene_ingresos_adicionales">
      Marca la casilla si el cliente cuenta con ingresos adicionales.
    </p>
  </div>
</div>
