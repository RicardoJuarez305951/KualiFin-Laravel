
<section class="bg-white rounded-xl shadow-md border p-6">
    <div class="flex items-center gap-4 mb-6">
        <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center text-white text-xl">💼</div>
        <h2 class="text-2xl font-bold text-purple-600">Datos de Ocupación</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Actividad -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Actividad que Realiza <span class="text-red-500">*</span></label>
            <input type="text" wire:model="ocupacion.actividad" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500" placeholder="Ej. Comerciante, Empleado" />
        </div>
        <!-- Empresa -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Nombre de la Empresa</label>
            <input type="text" wire:model="ocupacion.empresa" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500" placeholder="Nombre de la empresa" />
        </div>
        <!-- Domicilio Secundario Calle -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Domicilio Secundario - Calle <span class="text-red-500">*</span></label>
            <input type="text" wire:model="ocupacion.domSecCalle" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500" placeholder="Dirección del trabajo" />
        </div>
        <!-- Colonia -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Colonia <span class="text-red-500">*</span></label>
            <input type="text" wire:model="ocupacion.domSecColonia" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500" placeholder="Colonia del trabajo" />
        </div>
        <!-- Municipio -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Municipio <span class="text-red-500">*</span></label>
            <input type="text" wire:model="ocupacion.domSecMunicipio" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500" placeholder="Municipio del trabajo" />
        </div>
        <!-- Teléfono -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Teléfono <span class="text-red-500">*</span></label>
            <input type="tel" wire:model="ocupacion.telefono" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500" placeholder="123-456-7890" />
        </div>
        <!-- Antigüedad -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Antigüedad <span class="text-red-500">*</span></label>
            <input type="text" wire:model="ocupacion.antiguedad" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500" placeholder="Ej. 2 años" />
        </div>
        <!-- Monto y Periodo -->
        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-slate-700 mb-2">Monto que Percibe <span class="text-red-500">*</span></label>
                <input type="number" wire:model="ocupacion.monto" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500" placeholder="0.00" />
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-slate-700 mb-2">Periodo <span class="text-red-500">*</span></label>
                <select wire:model="ocupacion.periodo" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500">
                    <option value="">--Seleccione--</option>
                    <option value="Semanal">Semanal</option>
                    <option value="Quincenal">Quincenal</option>
                    <option value="Mensual">Mensual</option>
                </select>
            </div>
        </div>
        <!-- Ingresos Adicionales -->
        <div class="md:col-span-2">
            <div class="flex items-center p-4 bg-purple-50 rounded-lg border border-purple-200">
                <input type="checkbox" wire:model="ocupacion.ingresosAdicionales" class="w-4 h-4 text-purple-600" />
                <span class="ml-2 font-medium text-purple-800">¿Tiene ingresos adicionales?</span>
            </div>
        </div>
        <!-- Campos adicionales si hay ingresos -->
        <template x-if="ocupacion.ingresosAdicionales">
            <>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Concepto</label>
                    <input type="text" wire:model="ocupacion.ingresoConcepto" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500" placeholder="Ej. Ventas" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Monto</label>
                    <input type="number" wire:model="ocupacion.ingresoMonto" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500" placeholder="0.00" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Frecuencia</label>
                    <select wire:model="ocupacion.ingresoFrecuencia" class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500">
                        <option value="">--Seleccione--</option>
                        <option value="Semanal">Semanal</option>
                        <option value="Quincenal">Quincenal</option>
                        <option value="Mensual">Mensual</option>
                        <option value="Bimestral">Bimestral</option>
                    </select>
                </div>
            </>
        </template>
    </div>
</section>
