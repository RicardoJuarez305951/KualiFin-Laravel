{{-- resources/views/credito/partials/_step1.blade.php --}}
<div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
  {{-- CABECERA --}}
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
      {{-- Usa aquí tu ícono preferido o un SVG --}}
      <span class="text-3xl">📄</span>
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Paso 1 de 7</h2>
        <p class="text-sm text-gray-500">Documentación cliente y aval</p>
      </div>
    </div>
    <span class="text-sm font-medium text-gray-600">Documentación</span>
  </div>

  {{-- SELECCIÓN PROMOTORA / CLIENTE --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
    <div>
      <label for="promotora_id" class="block text-sm font-medium text-gray-700">
        Selecciona promotora
      </label>
      <select
        id="promotora_id"
        name="promotora_id"
        x-model="selectedPromotora"
        @change="fetchClientes(selectedPromotora)"
        class="mt-1 block w-full border-gray-200 rounded-lg shadow-sm focus:ring-primary focus:border-primary"
      >
        <option value="">-- Elige una promotora --</option>
        @foreach($promotoras as $p)
          <option value="{{ data_get($p,'id') }}">{{ data_get($p,'name') }}</option>
        @endforeach
      </select>
      <p class="mt-1 text-xs text-red-600" x-text="errors.promotora_id"></p>
    </div>

    <div>
      <label for="cliente_id" class="block text-sm font-medium text-gray-700">
        Selecciona cliente
      </label>
      <select
        id="cliente_id"
        name="cliente_id"
        x-model="selectedCliente"
        :disabled="!clientes.length"
        class="mt-1 block w-full border-gray-200 rounded-lg shadow-sm focus:ring-primary focus:border-primary disabled:bg-gray-100"
      >
        <option value="">-- Elige un cliente --</option>
        <template x-for="c in clientes" :key="c.id">
          <option :value="c.id" x-text="c.nombre + ' ' + c.apellido_paterno"></option>
        </template>
      </select>
      <p class="mt-1 text-xs text-red-600" x-text="errors.cliente_id"></p>
    </div>
  </div>

  {{-- DOCUMENTOS EN COLUMNAS --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Documentos Cliente --}}
    <div class="bg-gray-50 rounded-xl p-5 space-y-4 border border-gray-100">
      <h3 class="text-lg font-semibold text-gray-800">Documentos Cliente</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">CURP</label>
          <input type="file" name="docs_cliente[curp]" accept=".pdf,image/*"
                 class="mt-1 block w-full text-sm text-gray-600"/>
          <p class="mt-1 text-xs text-red-600" x-text="errors['docs_cliente.curp']"></p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">INE</label>
          <input type="file" name="docs_cliente[ine]" accept=".pdf,image/*"
                 class="mt-1 block w-full text-sm text-gray-600"/>
          <p class="mt-1 text-xs text-red-600" x-text="errors['docs_cliente.ine']"></p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Comprobante domicilio</label>
          <input type="file" name="docs_cliente[domicilio]" accept=".pdf,image/*"
                 class="mt-1 block w-full text-sm text-gray-600"/>
          <p class="mt-1 text-xs text-red-600" x-text="errors['docs_cliente.domicilio']"></p>
        </div>
      </div>
    </div>

    {{-- Documentos Aval --}}
    <div class="bg-gray-50 rounded-xl p-5 space-y-4 border border-gray-100">
      <h3 class="text-lg font-semibold text-gray-800">Documentos Aval</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">CURP</label>
          <input type="file" name="docs_aval[curp]" accept=".pdf,image/*"
                 class="mt-1 block w-full text-sm text-gray-600"/>
          <p class="mt-1 text-xs text-red-600" x-text="errors['docs_aval.curp']"></p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">INE</label>
          <input type="file" name="docs_aval[ine]" accept=".pdf,image/*"
                 class="mt-1 block w-full text-sm text-gray-600"/>
          <p class="mt-1 text-xs text-red-600" x-text="errors['docs_aval.ine']"></p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Comprobante domicilio</label>
          <input type="file" name="docs_aval[domicilio]" accept=".pdf,image/*"
                 class="mt-1 block w-full text-sm text-gray-600"/>
          <p class="mt-1 text-xs text-red-600" x-text="errors['docs_aval.domicilio']"></p>
        </div>
      </div>
    </div>
  </div>
</div>
