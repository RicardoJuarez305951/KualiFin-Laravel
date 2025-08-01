<div class="space-y-6">
  <h2 class="text-xl font-semibold">Paso 2: Datos del Solicitante</h2>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Calle --}}
    <div>
      <label for="calle" class="block text-sm font-medium text-gray-700">Calle</label>
      <input
        type="text"
        id="calle"
        x-model="formData.calle"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Número Ext --}}
    <div>
      <label for="numero_ext" class="block text-sm font-medium text-gray-700">Número Ext.</label>
      <input
        type="number"
        id="numero_ext"
        x-model.number="formData.numero_ext"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Número Int --}}
    <div>
      <label for="numero_int" class="block text-sm font-medium text-gray-700">Número Int.</label>
      <input
        type="text"
        id="numero_int"
        x-model.number="formData.numero_int"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Colonia --}}
    <div>
      <label for="colonia" class="block text-sm font-medium text-gray-700">Colonia</label>
      <input
        type="text"
        id="colonia"
        x-model="formData.colonia"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Código Postal --}}
    <div>
      <label for="cp" class="block text-sm font-medium text-gray-700">C.P.</label>
      <input
        type="text"
        id="cp"
        x-model="formData.cp"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Municipio --}}
    <div>
      <label for="municipio" class="block text-sm font-medium text-gray-700">Municipio</label>
      <input
        type="text"
        id="municipio"
        x-model="formData.municipio"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Estado --}}
    <div>
      <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
      <input
        type="text"
        id="estado"
        x-model="formData.estado"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Tiempo de residencia --}}
    <div>
      <label for="tiempo_residencia" class="block text-sm font-medium text-gray-700">Tiempo de residencia</label>
      <input
        type="text"
        id="tiempo_residencia"
        x-model="formData.tiempo_residencia"
        placeholder="Ej. 2 años"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Renta mensual / crédito --}}
    <div>
      <label for="renta" class="block text-sm font-medium text-gray-700">Renta mensual / Crédito</label>
      <input
        type="number"
        id="renta"
        x-model.number="formData.renta"
        step="0.01"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Teléfono fijo --}}
    <div>
      <label for="telefono_fijo" class="block text-sm font-medium text-gray-700">Teléfono fijo</label>
      <input
        type="text"
        id="telefono_fijo"
        x-model="formData.telefono_fijo"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Teléfono celular --}}
    <div>
      <label for="telefono_celular" class="block text-sm font-medium text-gray-700">Teléfono celular</label>
      <input
        type="text"
        id="telefono_celular"
        x-model="formData.telefono_celular"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- Tipo de vivienda --}}
    <div>
      <label for="tipo_vivienda" class="block text-sm font-medium text-gray-700">Tipo de vivienda</label>
      <select
        id="tipo_vivienda"
        x-model="formData.tipo_vivienda"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      >
        <option value="">— Seleccione tipo —</option>
        <option value="Propia">Propia</option>
        <option value="Rentada">Rentada</option>
        <option value="Familiar">Familiar</option>
      </select>
    </div>
  </div>
</div>
