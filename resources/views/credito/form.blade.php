<x-layouts.authenticated title="Nueva Solicitud de Crédito">
  <div
    x-data="creditForm({{ json_encode($promotoras, JSON_UNESCAPED_SLASHES) }})"
    x-init="init()"
    class="space-y-6 relative"
  >
    <form
      x-ref="form"
      @submit.prevent="submitForm"
      method="POST"
      action="{{ route('credito.store') }}"
      enctype="multipart/form-data"
      class="space-y-6 pb-32" {{-- espacio para la barra fija --}}
    >
      @csrf
      <input type="hidden" name="form_data" :value="JSON.stringify(formData)">

      <div class="space-y-6">
        <template x-if="step === 1">
          @include('credito.partials._step1')
        </template>
        <template x-if="step === 2">
          @include('credito.partials._step2')
        </template>
        <template x-if="step === 3">
          @include('credito.partials._step3')
        </template>
        <template x-if="step === 4">
          @include('credito.partials._step4')
        </template>
        <template x-if="step === 5">
          @include('credito.partials._step5')
        </template>
        <template x-if="step === 6">
          @include('credito.partials._step6')
        </template>
        <template x-if="step === 7">
          @include('credito.partials._step7')
        </template>
        {{-- … resto de steps … --}}
      </div>
    </form>

    {{-- Barra de navegación fija dentro de Alpine --}}
    <div
      class="fixed bottom-0 left-0 right-0 bg-white border-t p-4"
      x-cloak
      x-show="true"
    >
      <div class="max-w-4xl mx-auto flex justify-between">
        <button
          type="button"
          @click="prevStep"
          :disabled="step === 1"
          class="px-4 py-2 bg-gray-200 rounded disabled:opacity-50"
        >
          Anterior
        </button>

        <button
          type="button"
          @click="nextStep"
          x-show="step < totalSteps"
          class="px-4 py-2 bg-blue-600 text-white rounded"
        >
          Siguiente
        </button>

        <button
          type="button"
          @click="submitForm"
          x-show="step === totalSteps"
          class="px-4 py-2 bg-green-600 text-white rounded"
        >
          Enviar
        </button>
      </div>
    </div>

  </div>

  @push('scripts')
  <script>
    function creditForm(promotorasData) {
      return {
        // datos
        promotorasData,
        step: {{ $currentStep ?? 1 }},
        totalSteps: {{ $totalSteps ?? 7 }},
        activeGarantia: null,

        formData: {
          promotora_id: null,
          cliente_id: null,
          clientes: [],
          documentos: {
            cliente: { ine: null, comprobante: null },
            aval:    { ine: null, comprobante: null },
          },
          documentosUrls: {
            cliente: { ine: '', comprobante: '' },
            aval:    { ine: '', comprobante: '' },
          },
          aval1: {
            nombre: '', apaterno: '', amaterno: '', curp: '',
            direccion: '', telefono: '', parentesco: ''
          },
          aval2: {
            nombre: '', apaterno: '', amaterno: '', curp: '',
            direccion: '', telefono: '', parentesco: ''
          },
          garantias: Array.from({ length: 8 }, () => ({
            tipo: '',
            marca: '',
            num_serie: '',
            modelo: '',
            antiguedad: '',
            monto: null,
          })),
          cancelled: false,
        },

        init() {
          // listener para cuando cambie promotora
          this.$watch('formData.promotora_id', id => {
            const p = this.promotorasData.find(x => x.id == id)
            this.formData.clientes = p ? p.clientes : []
            this.formData.cliente_id = null
            this.formData.cancelled = false
          })
          // listener para cuando cambie cliente
          this.$watch('formData.cliente_id', id => {
            const c = this.formData.clientes.find(x => x.id == id) || { docs: {} }
            this.formData.documentosUrls.cliente.ine         = c.docs.ine_cliente || ''
            this.formData.documentosUrls.cliente.comprobante = c.docs.domicilio_cliente || ''
            this.formData.documentosUrls.aval.ine            = c.docs.ine_aval || ''
            this.formData.documentosUrls.aval.comprobante    = c.docs.domicilio_aval || ''
            this.formData.cancelled = false
          })
        },

        nextStep() {
          if (this.step < this.totalSteps) this.step++
        },
        prevStep() {
          if (this.step > 1) this.step--
        },

        submitForm() {
          this.$refs.form.submit()
        },
      }
    }
  </script>
  @endpush
</x-layouts.authenticated>
