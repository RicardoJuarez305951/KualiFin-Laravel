<div class="space-y-6">
  {{-- Selección de promotora + cliente --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label for="promotora" class="block text-sm font-medium text-gray-700">Promotora</label>
      <select
        id="promotora"
        x-model="formData.step_1.promotora_id"
        class="mt-1 block w-full border rounded px-3 py-2"
      >
        <option value="">— Seleccione promotora —</option>
        <template x-for="p in promotorasData" :key="p.id">
          <option :value="p.id" x-text="p.nombre"></option>
        </template>
      </select>
    </div>

    <div>
      <label for="cliente" class="block text-sm font-medium text-gray-700">Cliente</label>
      <select
        id="cliente"
        x-model="formData.step_1.cliente_id"
        class="mt-1 block w-full border rounded px-3 py-2"
      >
        <option value="">— Seleccione cliente —</option>
        <template x-for="c in formData.step_1.clientes" :key="c.id">
          <option :value="c.id" x-text="c.nombre"></option>
        </template>
      </select>
    </div>
  </div>

  {{-- Documentos (solo si hay cliente) --}}
  <template x-if="formData.step_1.cliente_id">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      {{-- Cliente --}}
      <div>
        <h3 class="text-lg font-semibold mb-4">Documentos Cliente</h3>
        <template x-for="key in Object.keys(formData.step_1.documentosUrls.cliente)" :key="key">
          <div class="mb-6">
            <img
              :src="formData.step_1.documentosUrls.cliente[key]"
              class="w-40 h-auto border rounded mb-2"
            />
            <div class="space-x-2">
              <button
                type="button"
                @click="formData.step_1.documentos.cliente[key] = 'accepted'"
                class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
              >Aceptar</button>
              <button
                type="button"
                @click="formData.step_1.documentos.cliente[key] = 'rejected'; formData.cancelled = true"
                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700"
              >Rechazar</button>
            </div>
          </div>
        </template>
      </div>

      {{-- Aval --}}
      <div>
        <h3 class="text-lg font-semibold mb-4">Documentos Aval</h3>
        <template x-for="key in Object.keys(formData.step_1.documentosUrls.aval)" :key="key">
          <div class="mb-6">
            <img
              :src="formData.step_1.documentosUrls.aval[key]"
              class="w-40 h-auto border rounded mb-2"
            />
            <div class="space-x-2">
              <button
                type="button"
                @click="formData.step_1.documentos.aval[key] = 'accepted'"
                class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
              >Aceptar</button>
              <button
                type="button"
                @click="formData.step_1.documentos.aval[key] = 'rejected'; formData.cancelled = true"
                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700"
              >Rechazar</button>
            </div>
          </div>
        </template>
      </div>
    </div>
  </template>
</div>
