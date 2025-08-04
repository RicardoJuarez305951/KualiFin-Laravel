<div class="space-y-6">
  <h2 class="text-xl font-semibold">Paso 5: Avales</h2>

  {{-- Aval 1 --}}
  <div class="border rounded p-4">
    <h3 class="text-lg font-medium mb-4">Aval 1</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- Nombre --}}
      <div>
        <label for="aval1_nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
        <input
          type="text"
          id="aval1_nombre"
          x-model="formData.step_5.aval1.nombre"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- Apellido paterno --}}
      <div>
        <label for="aval1_apaterno" class="block text-sm font-medium text-gray-700">Apellido paterno</label>
        <input
          type="text"
          id="aval1_apaterno"
          x-model="formData.step_5.aval1.apaterno"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- Apellido materno --}}
      <div>
        <label for="aval1_amaterno" class="block text-sm font-medium text-gray-700">Apellido materno</label>
        <input
          type="text"
          id="aval1_amaterno"
          x-model="formData.step_5.aval1.amaterno"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- CURP --}}
      <div>
        <label for="aval1_curp" class="block text-sm font-medium text-gray-700">CURP</label>
        <input
          type="text"
          id="aval1_curp"
          x-model="formData.step_5.aval1.curp"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- Dirección --}}
      <div class="md:col-span-2">
        <label for="aval1_direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
        <input
          type="text"
          id="aval1_direccion"
          x-model="formData.step_5.aval1.direccion"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- Teléfono --}}
      <div>
        <label for="aval1_telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
        <input
          type="tel"
          id="aval1_telefono"
          x-model="formData.step_5.aval1.telefono"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- Parentesco --}}
      <div>
        <label for="aval1_parentesco" class="block text-sm font-medium text-gray-700">Parentesco</label>
        <select
          id="aval1_parentesco"
          x-model="formData.step_5.aval1.parentesco"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        >
          <option value="">— Seleccione parentesco —</option>
          <option value="Familiar">Familiar</option>
          <option value="Amigo">Amigo</option>
          <option value="Compañero de trabajo">Compañero de trabajo</option>
          <option value="Conocido">Conocido</option>
        </select>
      </div>
    </div>
  </div>

  {{-- Aval 2 --}}
  <div class="border rounded p-4">
    <h3 class="text-lg font-medium mb-4">Aval 2</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- Nombre --}}
      <div>
        <label for="aval2_nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
        <input
          type="text"
          id="aval2_nombre"
          x-model="formData.step_5.aval2.nombre"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- Apellido paterno --}}
      <div>
        <label for="aval2_apaterno" class="block text-sm font-medium text-gray-700">Apellido paterno</label>
        <input
          type="text"
          id="aval2_apaterno"
          x-model="formData.step_5.aval2.apaterno"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- Apellido materno --}}
      <div>
        <label for="aval2_amaterno" class="block text-sm font-medium text-gray-700">Apellido materno</label>
        <input
          type="text"
          id="aval2_amaterno"
          x-model="formData.step_5.aval2.amaterno"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- CURP --}}
      <div>
        <label for="aval2_curp" class="block text-sm font-medium text-gray-700">CURP</label>
        <input
          type="text"
          id="aval2_curp"
          x-model="formData.step_5.aval2.curp"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- Dirección --}}
      <div class="md:col-span-2">
        <label for="aval2_direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
        <input
          type="text"
          id="aval2_direccion"
          x-model="formData.step_5.aval2.direccion"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- Teléfono --}}
      <div>
        <label for="aval2_telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
        <input
          type="tel"
          id="aval2_telefono"
          x-model="formData.step_5.aval2.telefono"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        />
      </div>

      {{-- Parentesco --}}
      <div>
        <label for="aval2_parentesco" class="block text-sm font-medium text-gray-700">Parentesco</label>
        <select
          id="aval2_parentesco"
          x-model="formData.step_5.aval2.parentesco"
          class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        >
          <option value="">— Seleccione parentesco —</option>
          <option value="Familiar">Familiar</option>
          <option value="Amigo">Amigo</option>
          <option value="Compañero de trabajo">Compañero de trabajo</option>
          <option value="Conocido">Conocido</option>
        </select>
      </div>
    </div>
  </div>
</div>
