{{-- resources/views/credito/partials/_step1.blade.php --}}
<div class="space-y-6">
  {{-- div1: Selección de promotor + cliente --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label for="promotor" class="block text-sm font-medium text-gray-700">Promotor</label>
      <select
        id="promotor"
        x-model="formData.step_1.promotora_id"
        x-bind:name="`step_1[promotora_id]`"
        class="mt-1 block w-full border rounded px-3 py-2"
      >
        <option value="">— Seleccione promotor —</option>
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
        x-bind:name="`step_1[cliente_id]`"
        class="mt-1 block w-full border rounded px-3 py-2"
      >
        <option value="">— Seleccione cliente —</option>
        <template x-for="c in formData.step_1.clientes" :key="c.id">
          <option :value="c.id" x-text="c.nombre"></option>
        </template>
      </select>
    </div>
  </div>

  {{-- div2: Documentos y CURP --}}
  <template x-if="formData.step_1.cliente_id">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      {{-- div2.div1: Documentos Cliente + CURP --}}
      <div>
        <h3 class="text-lg font-semibold mb-4">Documentos Cliente</h3>
        <template x-for="key in Object.keys(formData.step_1.documentosUrls.cliente)" :key="key">
          <div class="mb-4">
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

        {{-- CURP Cliente --}}
        <div class="mt-4">
          <label for="cliente_CURP" class="block text-sm font-medium text-gray-700">CURP Cliente</label>
          <input
            type="text"
            id="cliente_CURP"
            x-model="formData.step_1.cliente_CURP"
            x-bind:name="`step_1[cliente_CURP]`"
            class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
            placeholder="Ej. ABCC010101HNEXXXA1"
            readonly
          />
        </div>
      </div>

      {{-- div2.div2: Documentos Aval + CURP --}}
      <div>
        <h3 class="text-lg font-semibold mb-4">Documentos Aval</h3>
        <template x-for="key in Object.keys(formData.step_1.documentosUrls.aval)" :key="key">
          <div class="mb-4">
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

        {{-- CURP Aval --}}
        <div class="mt-4">
          <label for="aval_CURP" class="block text-sm font-medium text-gray-700">CURP Aval</label>
          <input
            type="text"
            id="aval_CURP"
            x-model="formData.step_1.aval_CURP"
            x-bind:name="`step_1[aval_CURP]`"
            class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
            placeholder="Ej. ZXCV020202MNEYYYB2"
            readonly
          />
        </div>
      </div>
      <div>
      <label for="monto_total" class="block text-sm font-medium text-gray-700">Monto Solicitado</label>
      <input
        type="text"
        step="0.01"
        id="monto_total"
        x-model="formData.step_1.monto_total"
        x-bind:name="`step_1[monto_total]`"
        class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        placeholder="0.00"
        readonly
      />
    </div>
    <div>
      <label for="periodo" class="block text-sm font-medium text-gray-700">Periodo</label>
      <input
        type="text"
        id="periodo"
        x-model="formData.step_1.periodo"
        x-bind:name="`step_1[periodo]`"
        class="mt-1 block w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
        placeholder="Ej. 12 semanas"
        readonly
      />
    </div>
    </div>
  </template>

  {{-- div3: Monto Solicitado y Periodo --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
    
  </div>
</div>
