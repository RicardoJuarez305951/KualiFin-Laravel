<div class="space-y-6">
    <!-- Selección -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="promotora_id" class="block mb-2 text-sm font-medium text-gray-900">1. Selecciona Promotora</label>
            <select id="promotora_id" name="promotora_id" x-model="selectedPromotora" @change="updateClientes()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                <option value="">-- Elige una promotora --</option>
                @foreach ($promotoras as $promotora)
                    <option value="{{ $promotora['id'] }}">{{ $promotora['nombre'] }}</option>
                @endforeach
            </select>
            @error('promotora_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="cliente_id" class="block mb-2 text-sm font-medium text-gray-900">2. Selecciona Cliente (CURP)</label>
            <select id="cliente_id" name="cliente_id" x-model="selectedClienteId" @change="updateDocumentos()" :disabled="!selectedPromotora" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 disabled:bg-gray-200">
                <option value="">-- Elige un cliente --</option>
                <template x-for="cliente in clientes" :key="cliente.id">
                    <option :value="cliente.id" x-text="cliente.curp"></option>
                </template>
            </select>
            @error('cliente_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <!-- Revisión de Documentos -->
    <div x-show="documentos" x-transition class="pt-6 border-t">
        <h3 class="text-md font-semibold text-gray-800 mb-4">3. Revisa y Aprueba los Documentos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Document Cards -->
            <template x-if="documentos">
                <div class="space-y-6">
                    <x-document-card type="ine_cliente" title="INE del Cliente" :image-url="'documentos.ine_cliente'" />
                    <x-document-card type="domicilio_cliente" title="Comprobante de Domicilio del Cliente" :image-url="'documentos.domicilio_cliente'" />
                </div>
            </template>
            <template x-if="documentos">
                <div class="space-y-6">
                    <x-document-card type="ine_aval" title="INE del Aval" :image-url="'documentos.ine_aval'" />
                    <x-document-card type="domicilio_aval" title="Comprobante de Domicilio del Aval" :image-url="'documentos.domicilio_aval'" />
                </div>
            </template>
        </div>
        @error('ine_cliente_status') <p class="mt-4 text-sm text-red-600">Debes aprobar o denegar todos los documentos para continuar.</p> @enderror
    </div>
</div>
