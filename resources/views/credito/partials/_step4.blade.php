<div class="space-y-6">
  <h2 class="text-xl font-semibold">Paso 4: Información Familiar</h2>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- 1. Nombre de cónyuge --}}
    <div>
      <label for="conyuge_nombre" class="block text-sm font-medium text-gray-700">Nombre de cónyuge</label>
      <input
        type="text"
        id="conyuge_nombre"
        x-model="formData.step_4.conyuge_nombre"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 2. Celular cónyuge --}}
    <div>
      <label for="conyuge_celular" class="block text-sm font-medium text-gray-700">Celular cónyuge</label>
      <input
        type="tel"
        id="conyuge_celular"
        x-model="formData.step_4.conyuge_celular"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 3. Número de hijos --}}
    <div>
      <label for="num_hijos" class="block text-sm font-medium text-gray-700">Número de hijos</label>
      <input
        type="number"
        id="num_hijos"
        x-model.number="formData.step_4.num_hijos"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 4. Actividad cónyuge --}}
    <div>
      <label for="conyuge_actividad" class="block text-sm font-medium text-gray-700">Actividad cónyuge</label>
      <input
        type="text"
        id="conyuge_actividad"
        x-model="formData.step_4.conyuge_actividad"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 5. Ingresos semanales del cónyuge --}}
    <div>
      <label for="conyuge_ingresos" class="block text-sm font-medium text-gray-700">Ingresos semanales del cónyuge</label>
      <input
        type="text"
        id="conyuge_ingresos"
        x-model="formData.step_4.conyuge_ingresos"
        placeholder="Ej. $2,000"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 6. Domicilio de trabajo del cónyuge --}}
    <div class="md:col-span-2">
      <label for="conyuge_domicilio_trabajo" class="block text-sm font-medium text-gray-700">Domicilio de trabajo del cónyuge</label>
      <input
        type="text"
        id="conyuge_domicilio_trabajo"
        x-model="formData.step_4.conyuge_domicilio_trabajo"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 7. Personas en domicilio --}}
    <div>
      <label for="personas_domicilio" class="block text-sm font-medium text-gray-700">Personas en domicilio</label>
      <input
        type="number"
        id="personas_domicilio"
        x-model.number="formData.step_4.personas_domicilio"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 8. Dependientes económicos --}}
    <div>
      <label for="dependientes" class="block text-sm font-medium text-gray-700">Dependientes económicos</label>
      <input
        type="number"
        id="dependientes"
        x-model.number="formData.step_4.dependientes"
        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200"
      />
    </div>

    {{-- 9. Cónyuge vive con usted --}}
    <div class="md:col-span-2">
      <label class="inline-flex items-center mt-4">
        <input
          type="checkbox"
          x-model="formData.step_4.conyuge_vive"
          class="form-checkbox text-indigo-600"
        />
        <span class="ml-2 text-gray-700">¿Cónyuge vive con usted?</span>
      </label>
    </div>
  </div>
</div>
