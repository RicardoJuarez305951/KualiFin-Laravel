<div x-show="currentStep === 6" x-cloak x-transition class="space-y-4">
  <h3 class="text-sm font-semibold text-gray-700">Avales</h3>

  <div class="space-y-1">
    <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">CURP del aval <span class="text-red-500">*</span></label>
    <div class="flex items-center gap-2">
      <input type="text"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.aval.curp" name="aval[curp]" readonly>
      <a href="https://www.gob.mx/curp/" target="_blank"
         class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg border border-indigo-500 text-indigo-600 hover:bg-indigo-50">
        Consultar
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-3">
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Nombre <span class="text-red-500">*</span></label>
      <input type="text"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.aval.nombre" name="aval[nombre]">
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Apellido paterno <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.aval.apellido_p" name="aval[apellido_p]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Apellido materno <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.aval.apellido_m" name="aval[apellido_m]">
      </div>
    </div>
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha de nacimiento <span class="text-red-500">*</span></label>
      <input type="date"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.aval.fecha_nacimiento" name="aval[fecha_nacimiento]">
    </div>
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Direccion <span class="text-red-500">*</span></label>
      <input type="text"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.aval.direccion" name="aval[direccion]">
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Telefono <span class="text-red-500">*</span></label>
        <input type="tel"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.aval.telefono" name="aval[telefono]">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Parentesco <span class="text-red-500">*</span></label>
        <input type="text"
               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               x-model="form.aval.parentesco" name="aval[parentesco]">
      </div>
    </div>
  </div>
</div>
