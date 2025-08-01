<div class="space-y-6">
  <h2 class="text-xl font-semibold">Paso 3: Ocupación</h2>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- 1. Actividad que realiza --}}
    <div>
      <label for="actividad" class="block text-sm font-medium text-gray-700">Actividad que realiza</label>
      <input
        type="text"
        id="actividad"
        x-model="formData.actividad"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 2. Nombre de la empresa --}}
    <div>
      <label for="empresa" class="block text-sm font-medium text-gray-700">Nombre de la empresa</label>
      <input
        type="text"
        id="empresa"
        x-model="formData.empresa"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 3. Domicilio de la empresa --}}
    <div class="md:col-span-2">
      <label for="empresa_domicilio" class="block text-sm font-medium text-gray-700">Domicilio de la empresa</label>
      <input
        type="text"
        id="empresa_domicilio"
        x-model="formData.empresa_domicilio"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 4. Colonia --}}
    <div>
      <label for="empresa_colonia" class="block text-sm font-medium text-gray-700">Colonia</label>
      <input
        type="text"
        id="empresa_colonia"
        x-model="formData.empresa_colonia"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 5. Municipio --}}
    <div>
      <label for="empresa_municipio" class="block text-sm font-medium text-gray-700">Municipio</label>
      <input
        type="text"
        id="empresa_municipio"
        x-model="formData.empresa_municipio"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 6. Estado --}}
    <div>
      <label for="empresa_estado" class="block text-sm font-medium text-gray-700">Estado</label>
      <input
        type="text"
        id="empresa_estado"
        x-model="formData.empresa_estado"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 7. Teléfono --}}
    <div>
      <label for="empresa_telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
      <input
        type="tel"
        id="empresa_telefono"
        x-model="formData.empresa_telefono"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 8. Antigüedad --}}
    <div>
      <label for="empresa_antiguedad" class="block text-sm font-medium text-gray-700">Antigüedad</label>
      <input
        type="text"
        id="empresa_antiguedad"
        x-model="formData.empresa_antiguedad"
        placeholder="Ej. 2 años"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 9. Sueldo --}}
    <div>
      <label for="sueldo" class="block text-sm font-medium text-gray-700">Sueldo</label>
      <input
        type="number"
        id="sueldo"
        x-model.number="formData.sueldo"
        step="0.01"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 10. Periodo --}}
    <div>
      <label for="periodo" class="block text-sm font-medium text-gray-700">Periodo</label>
      <select
        id="periodo"
        x-model="formData.periodo"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      >
        <option value="">— Seleccione periodo —</option>
        <option value="Semanal">Semanal</option>
        <option value="Quincenal">Quincenal</option>
        <option value="Mensual">Mensual</option>
      </select>
    </div>
  </div>

  {{-- Ingresos adicionales --}}
  <div class="mt-6">
    <label class="inline-flex items-center">
      <input
        type="checkbox"
        x-model="formData.ingresos_adicionales"
        class="form-checkbox text-indigo-600"
      />
      <span class="ml-2 text-gray-700">¿Tiene ingresos adicionales?</span>
    </label>

    <template x-if="formData.ingresos_adicionales">
      <div class="mt-4 space-y-4 border-t pt-4">
        {{-- Concepto --}}
        <div>
          <label for="ingreso_concepto" class="block text-sm font-medium text-gray-700">Concepto</label>
          <input
            type="text"
            id="ingreso_concepto"
            x-model="formData.ingreso_concepto"
            class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
          />
        </div>

        {{-- Monto --}}
        <div>
          <label for="ingreso_monto" class="block text-sm font-medium text-gray-700">Monto</label>
          <input
            type="number"
            id="ingreso_monto"
            x-model.number="formData.ingreso_monto"
            step="0.01"
            class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
          />
        </div>

        {{-- Frecuencia --}}
        <div>
          <label for="ingreso_frecuencia" class="block text-sm font-medium text-gray-700">Frecuencia</label>
          <select
            id="ingreso_frecuencia"
            x-model="formData.ingreso_frecuencia"
            class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
          >
            <option value="">— Seleccione frecuencia —</option>
            <option value="Semanal">Semanal</option>
            <option value="Quincenal">Quincenal</option>
            <option value="Mensual">Mensual</option>
          </select>
        </div>
      </div>
    </template>
  </div>
</div>
