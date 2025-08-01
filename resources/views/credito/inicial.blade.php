{{-- resources/views/credito/inicial.blade.php --}}
<x-layouts.authenticated title="Nueva Solicitud de Crédito">
  <x-slot name="header">
    <h2 class="text-xl font-semibold text-gray-900">Solicitud de Crédito</h2>
  </x-slot>

  <form method="POST" action="{{ route('credito.store') }}"
        enctype="multipart/form-data"
        x-data="creditoWizard()"
        x-cloak
  >
    @csrf

    {{-- Paso 1 --}}
    <div x-show="currentStep === 1" x-cloak>
      @include('credito.partials._step1')
    </div>

    {{-- Paso 2 … Paso 7 iguales a este formato --}}
    {{-- … --}}

    <div class="flex justify-between mt-6">
      <button type="button" x-show="currentStep > 1" @click="prevStep()"
              class="px-4 py-2 bg-gray-200 rounded">Anterior</button>
      <button type="button" x-show="currentStep < 7" @click="nextStep()"
              class="px-4 py-2 bg-primary text-white rounded">Siguiente</button>
      <button type="submit" x-show="currentStep === 7"
              class="px-4 py-2 bg-green-600 text-white rounded">Enviar</button>
    </div>
  </form>

  @push('scripts')
  <script>
    function creditoWizard() {
      return {
        currentStep: 1,
        selectedPromotora: null,
        clientes: [],
        errors: {},
        nextStep() { this.currentStep++ },
        prevStep() { this.currentStep-- },
        fetchClientes(promotoraId) {
          this.clientes = []
          if (!promotoraId) return
          fetch(`/api/promotora/${promotoraId}/clientes`)
            .then(res => res.json())
            .then(data => this.clientes = data)
            .catch(() => this.errors.cliente_id = 'No se pudieron cargar clientes.')
        },
      }
    }
  </script>
  @endpush

</x-layouts.authenticated>
