<x-layouts.authenticated>
    <div 
        class="p-6 bg-gray-50 min-h-screen"
        x-data="{
            selectedPromotora: '{{ old('promotora_id', $solicitud['promotora_info']['id'] ?? '') }}',
            selectedClienteId: '{{ old('cliente_id', $solicitud['cliente_info']['id'] ?? '') }}',
            clientes: [],
            documentos: null,
            allPromotoras: {{ json_encode($promotoras) }},
            docStatus: {
                ine_cliente: '{{ old('ine_cliente_status') }}',
                domicilio_cliente: '{{ old('domicilio_cliente_status') }}',
                ine_aval: '{{ old('ine_aval_status') }}',
                domicilio_aval: '{{ old('domicilio_aval_status') }}'
            },
            formData: {
                nombre_completo: `{{ old('nombre_completo', $solicitud['nombre_completo'] ?? ($solicitud['cliente_info']['nombre'] ?? '')) }}`,
                email: `{{ old('email', $solicitud['email'] ?? '') }}`,
                empresa: `{{ old('empresa', $solicitud['empresa'] ?? '') }}`,
                ingreso_mensual: `{{ old('ingreso_mensual', $solicitud['ingreso_mensual'] ?? '') }}`
            },
            isStepValid() {
                const step = {{ $currentStep }};
                if (step === 1) {
                    if (!this.selectedPromotora || !this.selectedClienteId) return false;
                    if (this.documentos) {
                        // El botón solo se activa si todos los documentos están aprobados.
                        return Object.values(this.docStatus).every(s => s === 'aprobado');
                    }
                    return false;
                }
                if (step === 2) {
                    return this.formData.nombre_completo.trim() !== '' && emailRegex.test(this.formData.email);
                }
                if (step === 3) {
                    return this.formData.empresa.trim() !== '' && this.formData.ingreso_mensual.toString().trim() !== '';
                }
                return step === 4;
            },
            updateClientes() {
                this.selectedClienteId = '';
                this.documentos = null;
                if (this.selectedPromotora) {
                    const promotora = this.allPromotoras.find(p => p.id == this.selectedPromotora);
                    this.clientes = promotora ? promotora.clientes : [];
                } else {
                    this.clientes = [];
                }
            },
            updateDocumentos() {
                if(this.selectedClienteId) {
                    const promotora = this.allPromotoras.find(p => p.id == this.selectedPromotora);
                    if(promotora) {
                        const cliente = promotora.clientes.find(c => c.id == this.selectedClienteId);
                        this.documentos = cliente ? cliente.docs : null;
                    }
                } else {
                    this.documentos = null;
                }
            }
        }"
        x-init="updateClientes(); updateDocumentos();"
    >
        <div class="max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="text-center space-y-2 mb-8">
                <h1 class="text-4xl font-bold text-gray-900">Nueva Solicitud de Crédito</h1>
                <p class="text-lg text-gray-600">Sigue los pasos para completar la solicitud</p>
            </div>

            <!-- Card Principal -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900">Paso {{ $currentStep }} de 4</h2>
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
                            @include('credito.partials._step1')
                        @elseif ($currentStep == 2)
                            @include('credito.partials._step2')
                        @elseif ($currentStep == 3)
                            @include('credito.partials._step3')
                        @elseif ($currentStep == 4)
                            @include('credito.partials._step4')
                        @endif

                        <!-- Navegación -->
                        <div class="mt-8 pt-6 border-t flex justify-between items-center">
                            @if ($currentStep > 1)
                                <a href="{{ route('credito.back') }}" class="text-sm font-medium text-gray-700 bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">Anterior</a>
                            @else
                                <div></div>
                            @endif
                            <button 
                                type="submit" 
                                :disabled="!isStepValid()"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center disabled:bg-blue-400 disabled:cursor-not-allowed"
                            >
                                {{ $currentStep == 4 ? 'Enviar Solicitud' : 'Siguiente' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
