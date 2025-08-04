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
        activeGarantia: null,
        promotorasData,
        promotorasData,
      step: {{ $currentStep ?? 1 }},
      totalSteps: {{ $totalSteps ?? 7 }},

      formData: {
        step_1: {
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
        },
        step_2 : {
          calle: null,
          numero_ext: null,
          numero_int: null,
          colonia: null,
          cp: null,
          municipio: null,
          estado: null,
          tiempo_residencia: null,
          renta: null,
          telefono_fijo: null,
          telefono_celular: null,
          tipo_vivienda: null,
        }, // datos del paso 2
        step_3 : {
          actividad: null,
          empresa: null,
          empresa_domicilio: null,
          empresa_colonia: null,
          empresa_municipio: null,
          empresa_estado: null,
          empresa_telefono: null,
          empresa_antiguedad: null,
          sueldo: null,
          periodo: null,
          ingresos_adicionales: null,
          ingresos_adicionales: null,
          ingreso_concepto: null,
          ingreso_monto: null,
          ingreso_frecuencia: null,
        }, // datos del paso 3  
        step_4 : {
          conyuge_nombre: null,
          conyuge_celular: null,
          num_hijos: null,
          conyuge_actividad: null,
          conyuge_ingresos: null,
          conyuge_domicilio_trabajo: null,
          personas_domicilio: null,
          dependientes: null,
          conyuge_vive: null,
        }, // datos del paso 4
        step_5 : {
            aval1: {
            nombre: '', apaterno: '', amaterno: '', curp: '',
            direccion: '', telefono: '', parentesco: ''
          },
          aval2: {
            nombre: '', apaterno: '', amaterno: '', curp: '',
            direccion: '', telefono: '', parentesco: ''
          },
        }, // datos del paso 5
        step_6: {
          garantias: Array.from({ length: 8 }, () => ({
            tipo: '',
            marca: '',
            num_serie: '',
            modelo: '',
            antiguedad: '',
            monto: null,
            foto: null,
          })),
        }, // datos del paso 6

       
      },

        init() {
        // Cuando cambie promotora en step_1
        this.$watch('formData.step_1.promotora_id', id => {
          const p = this.promotorasData.find(x => x.id == id)
          this.formData.step_1.clientes     = p ? p.clientes : []
          this.formData.step_1.cliente_id   = null
          this.formData.cancelled           = false
        })

        // Cuando cambie cliente en step_1
        this.$watch('formData.step_1.cliente_id', id => {
          const c = this.formData.step_1.clientes.find(x => x.id == id) || { docs: {} }
          this.formData.step_1.documentosUrls.cliente.ine         = c.docs.ine_cliente || ''
          this.formData.step_1.documentosUrls.cliente.comprobante = c.docs.domicilio_cliente || ''
          this.formData.step_1.documentosUrls.aval.ine            = c.docs.ine_aval || ''
          this.formData.step_1.documentosUrls.aval.comprobante    = c.docs.domicilio_aval || ''
          this.formData.cancelled                                 = false
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
