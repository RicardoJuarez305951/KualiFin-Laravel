<div x-show="currentStep === 1" x-cloak x-transition class="space-y-4">
  <h3 class="text-sm font-semibold text-gray-700">Clientes</h3>

  <div class="space-y-1">
    <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">CURP <span class="text-red-500">*</span></label>
    <div class="flex items-center gap-2">
      <input type="text"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.cliente.curp" name="cliente[curp]" readonly>
      <a href="https://www.gob.mx/curp/" target="_blank"
         class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg border border-indigo-500 text-indigo-600 hover:bg-indigo-50">
        Consultar
      </a>
    </div>
    <p class="text-[11px] text-gray-500">Dato cargado desde el prospecto seleccionado.</p>
  </div>

  <div class="grid grid-cols-1 gap-3">
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Nombre <span class="text-red-500">*</span></label>
      <input type="text"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.cliente.nombre" name="cliente[nombre]">
    </div>
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Apellido paterno <span class="text-red-500">*</span></label>
      <input type="text"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.cliente.apellido_p" name="cliente[apellido_p]">
    </div>
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Apellido materno <span class="text-red-500">*</span></label>
      <input type="text"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.cliente.apellido_m" name="cliente[apellido_m]">
    </div>
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha de nacimiento <span class="text-red-500">*</span></label>
      <input type="date"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             x-model="form.cliente.fecha_nacimiento" name="cliente[fecha_nacimiento]">
    </div>
    <div class="space-y-1">
      <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Horario de pago <span class="text-red-500">*</span></label>
      <input type="time"
             class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
             step="60"
             required
             x-model="form.cliente.horario_de_pago" name="cliente[horario_de_pago]">
    </div>
  </div>
</div>
