<x-layouts.authenticated>
    <div 
        class="p-6 bg-gray-50 min-h-screen"
        x-data="{
            selectedPromotora: '{{ old('promotora_id', $solicitud['promotora_info']['id'] ?? '') }}',
            selectedCliente: '{{ old('cliente_id', $solicitud['cliente_info']['id'] ?? '') }}',
            clientes: [],
            allPromotoras: {{ json_encode($promotoras) }},
            updateClientes() {
                if (this.selectedPromotora) {
                    const promotora = this.allPromotoras.find(p => p.id == this.selectedPromotora);
                    this.clientes = promotora ? promotora.clientes : [];
                } else {
                    this.clientes = [];
                }
            }
        }"
        x-init="updateClientes()"
    >
        <div class="max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="text-center space-y-2 mb-8">
                <h1 class="text-4xl font-bold text-gray-900">
                    Nueva Solicitud de Crédito
                </h1>
                <p class="text-lg text-gray-600">
                    Sigue los pasos para completar la solicitud
                </p>
            </div>

            <!-- Card Principal -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Paso {{ $currentStep }} de 4
                    </h2>
                </div>

                <div class="p-6">
                    <!-- Barra de Progreso -->
                    <div class="mb-6">
                        <div class="bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ ($currentStep / 4) * 100 }}%;"></div>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 p-4 text-sm text-green-800 bg-green-100 rounded-lg" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('credito.store') }}" method="POST">
                        @csrf

                        <!-- Contenido dinámico del paso -->
                        @if ($currentStep == 1)
                            {{-- PASO 1: Selección de Promotora y Cliente --}}
                            <div class="space-y-6">
                                <div>
                                    <label for="promotora_id" class="block mb-2 text-sm font-medium text-gray-900">Selecciona una Promotora</label>
                                    <select 
                                        id="promotora_id" 
                                        name="promotora_id"
                                        x-model="selectedPromotora"
                                        @change="updateClientes(); selectedCliente = ''"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    >
                                        <option value="">-- Elige una promotora --</option>
                                        @foreach ($promotoras as $promotora)
                                            <option value="{{ $promotora['id'] }}">{{ $promotora['nombre'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('promotora_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="cliente_id" class="block mb-2 text-sm font-medium text-gray-900">Selecciona un Cliente</label>
                                    <select 
                                        id="cliente_id" 
                                        name="cliente_id"
                                        x-model="selectedCliente"
                                        :disabled="!selectedPromotora"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 disabled:bg-gray-200"
                                    >
                                        <option value="">-- Elige un cliente --</option>
                                        <template x-for="cliente in clientes" :key="cliente.id">
                                            <option :value="cliente.id" x-text="cliente.nombre"></option>
                                        </template>
                                    </select>
                                    @error('cliente_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @elseif ($currentStep == 2)
                            @include('credito.partials._step2')
                        @elseif ($currentStep == 3)
                             {{-- Aquí iría el include para el paso 3 --}}
                            <p>Paso 3: Información Laboral</p>
                        @elseif ($currentStep == 4)
                             {{-- Aquí iría el include para el paso 4 --}}
                            <p>Paso 4: Resumen</p>
                        @endif

                        <!-- Navegación -->
                        <div class="mt-8 pt-6 border-t flex justify-between items-center">
                            @if ($currentStep > 1)
                                <a href="{{ route('credito.back') }}" class="text-sm font-medium text-gray-700 bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                                    Anterior
                                </a>
                            @else
                                <div></div> <!-- Espaciador para alinear el botón de siguiente a la derecha -->
                            @endif

                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                {{ $currentStep == 4 ? 'Enviar Solicitud' : 'Siguiente' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
