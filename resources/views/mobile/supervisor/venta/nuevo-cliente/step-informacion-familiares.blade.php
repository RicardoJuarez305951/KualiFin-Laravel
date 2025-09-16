<div x-show="currentStep === 5" x-cloak x-transition class="space-y-4">
  <h3 class="text-sm font-semibold text-gray-700">Informacion familiares</h3>

  <div class="grid grid-cols-1 gap-3">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Nombre del conyuge <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.familiares.nombre_conyuge" name="familiares[nombre_conyuge]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Celular del conyuge <span class="text-red-500">*</span></label>
        <input type="tel"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.familiares.celular_conyuge" name="familiares[celular_conyuge]">
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Actividad del conyuge <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.familiares.actividad_conyuge" name="familiares[actividad_conyuge]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Ingresos semanales <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" min="0"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.familiares.ingresos_semanales_conyuge" name="familiares[ingresos_semanales_conyuge]">
      </div>
    </div>
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Domicilio de trabajo del conyuge <span class="text-red-500">*</span></label>
      <input type="text"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.familiares.domicilio_trabajo_conyuge" name="familiares[domicilio_trabajo_conyuge]">
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Personas en el domicilio <span class="text-red-500">*</span></label>
        <input type="number" min="0" step="1"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.familiares.personas_en_domicilio" name="familiares[personas_en_domicilio]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Dependientes economicos <span class="text-red-500">*</span></label>
        <input type="number" min="0" step="1"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.familiares.dependientes_economicos" name="familiares[dependientes_economicos]">
      </div>
    </div>
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">¿El conyuge vive con el cliente? <span class='text-red-500'>*</span>?</label>
      <select class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              x-model="form.familiares.conyuge_vive_con_cliente" name="familiares[conyuge_vive_con_cliente]">
        <option value="">Selecciona una opcion</option>
        <option value="si">Si</option>
        <option value="no">No</option>
      </select>
    </div>
  </div>
</div>

