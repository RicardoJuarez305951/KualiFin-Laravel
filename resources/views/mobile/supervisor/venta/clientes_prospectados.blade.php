{{-- resources/views/mobile/supervisor/venta/clientes_prospectados.blade.php --}}
@php
    $formatMoney = fn($value) => '$' . number_format((float) $value, 2, '.', ',');
@endphp

<x-layouts.mobile.mobile-layout title="Clientes Prospectados">
  <div x-data="prospectado()" x-init="init()" class="p-4 w-full max-w-md mx-auto space-y-6">

    @forelse($promotores as $promotor)
      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-7 h-7 text-[12px] font-bold rounded-full bg-gray-200 text-gray-700">{{ $loop->iteration }}</span>
          <div>
            <p class="text-[15px] font-semibold text-gray-900">{{ $promotor['nombre'] }}</p>
            <p class="text-[12px] text-gray-500">Prospectos</p>
          </div>
        </div>

        <div class="px-3 py-2 space-y-4">
          <div class="bg-gray-50 rounded-lg border border-gray-200">
            <div class="px-3 py-2 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-[13px] font-bold text-gray-700">Clientes Nuevos</h3>
              <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-200 text-gray-700">{{ $promotor['clientes']->count() }}</span>
            </div>
            <div>
              @forelse($promotor['clientes'] as $cliente)
                <div class="py-2 px-3">
                  <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                      <p class="truncate text-[14px] font-medium text-gray-800">{{ $cliente['nombre'] }}</p>
                      <p class="text-[11px] text-gray-500 uppercase">{{ $cliente['estatus'] }}</p>
                    </div>
                    <button
                      type="button"
                      class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm transition"
                      @click="openModal(@js($cliente))">
                      Revisar
                    </button>
                  </div>
                </div>
              @empty
                <p class="px-3 py-4 text-sm text-gray-500 text-center">Sin clientes nuevos</p>
              @endforelse
            </div>
          </div>

          <div class="bg-gray-50 rounded-lg border border-gray-200">
            <div class="px-3 py-2 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-[13px] font-bold text-gray-700">RecrÃ©ditos</h3>
              <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-200 text-gray-700">{{ $promotor['recreditos']->count() }}</span>
            </div>
            <div>
              @forelse($promotor['recreditos'] as $cliente)
                <div class="py-2 px-3">
                  <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                      <p class="truncate text-[14px] font-medium text-gray-800">{{ $cliente['nombre'] }}</p>
                      <p class="text-[11px] text-gray-500 uppercase">Monto solicitado {{ $formatMoney($cliente['monto']) }}</p>
                    </div>
                    <button
                      type="button"
                      class="px-3 py-1.5 text-xs rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm transition"
                      @click="openModal(@js($cliente))">
                      Revisar
                    </button>
                  </div>
                </div>
              @empty
                <p class="px-3 py-4 text-sm text-gray-500 text-center">Sin solicitudes de recrÃ©dito</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-2xl bg-white border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 shadow-sm">
        No se encontraron clientes prospectados bajo tu supervisiÃ³n.
      </div>
    @endforelse

    {{-- MODAL DETALLE --}}
    <template x-if="showModal">
      <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

        <div
          class="relative w-full sm:max-w-md bg-white text-gray-900 border border-gray-200 shadow-2xl sm:rounded-2xl rounded-t-2xl p-5 sm:mx-auto space-y-4"
          x-trap.noscroll="showModal" x-transition
          style="max-height: 85dvh; overflow: auto;"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-base font-semibold" x-text="selected.nombre"></p>
              <p class="text-xs text-gray-500" x-text="selected.curp"></p>
            </div>
            <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200" @click="closeModal()">Cerrar</button>
          </div>

          <div class="grid grid-cols-1 gap-2 text-sm">
            <p><span class="font-semibold">Estatus:</span> <span x-text="selected.estatus || 'Sin definir'"></span></p>
            <p x-show="selected.fecha_nacimiento"><span class="font-semibold">Fecha nacimiento:</span> <span x-text="selected.fecha_nacimiento"></span></p>
            <p x-show="selected.telefono"><span class="font-semibold">TelÃ©fono:</span> <span x-text="selected.telefono"></span></p>
            <p x-show="selected.direccion"><span class="font-semibold">DirecciÃ³n:</span> <span x-text="selected.direccion"></span></p>
            <p x-show="selected.monto"><span class="font-semibold">Monto:</span> <span x-text="formatCurrency(selected.monto)"></span></p>
          </div>

          <div>
            <h3 class="text-sm font-semibold mb-2 text-center">Documentos</h3>
            <div class="grid grid-cols-1 gap-2 text-sm">
              <template x-if="selected.documentos_detalle && selected.documentos_detalle.length">
                <template x-for="doc in selected.documentos_detalle" :key="doc.id">
                  <a :href="doc.url" target="_blank"
                     class="flex items-center justify-between px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    <span class="text-sm font-medium truncate" x-text="doc.titulo"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                      <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                  </a>
                </template>
              </template>
              <p x-show="!selected.documentos_detalle || !selected.documentos_detalle.length"
                 class="text-xs text-gray-500 text-center">Sin documentos cargados.</p>
            </div>
          </div>

          <div x-show="selected.aval" class="space-y-1">
            <h3 class="text-sm font-semibold mb-1 text-center">Datos del aval</h3>
            <p class="text-sm text-gray-700" x-text="selected.aval ? selected.aval.nombre : ''"></p>
            <p class="text-xs text-gray-500" x-show="selected.aval && selected.aval.curp" x-text="selected.aval ? selected.aval.curp : ''"></p>
            <p class="text-xs text-gray-500" x-show="selected.aval && selected.aval.telefono">
              Tel. <span x-text="selected.aval ? selected.aval.telefono : ''"></span>
            </p>
            <p class="text-xs text-gray-500" x-show="selected.aval && selected.aval.direccion">
              Dir. <span x-text="selected.aval ? selected.aval.direccion : ''"></span>
            </p>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <button
              class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold shadow-sm transition"
              @click="rechazar()">Rechazar</button>
            <button
              class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-sm transition"
              @click="openForm()">CHECK</button>
            <button
              class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm transition"
              @click="aceptar()">Aceptar</button>
          </div>
        </div>
      </div>
    </template>

    {{-- MODAL FORM (con scroll interno real) --}}
    <template x-if="showFormModal">
      <div class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="closeForm()"></div>

        <div class="relative w-full sm:max-w-lg bg-white text-gray-900 border border-gray-200 shadow-2xl sm:rounded-2xl rounded-t-2xl sm:mx-auto flex flex-col h-[90dvh] max-h-[90dvh]"
             x-trap.noscroll="showFormModal" x-transition>
          <div class="p-5 border-b border-gray-200">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h2 class="text-base font-semibold">Formulario de Cliente</h2>
                <p class="text-xs text-gray-500" x-text="`Paso ${currentStep} de ${steps.length}: ${currentStepLabel()}`"></p>
              </div>
              <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200" @click="closeForm()">Cerrar</button>
            </div>

            <div x-show="feedback.show" x-transition x-cloak
                 :class="feedback.type === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'"
                 class="px-3 py-2 rounded-lg text-sm mt-3">
              <p x-text="feedback.message"></p>
            </div>

            <div class="flex items-center gap-2 text-xs mt-3">
              <div class="flex-1 h-2 rounded-full bg-gray-200 overflow-hidden">
                <div class="h-full bg-indigo-500 transition-all" :style="`width: ${(currentStep / steps.length) * 100}%`"></div>
              </div>
              <span class="text-gray-500" x-text="`${currentStep}/${steps.length}`"></span>
            </div>
          </div>

          <div class="flex-1 min-h-0">
            <form class="flex flex-col h-full min-h-0 overflow-hidden" @submit.prevent="submitForm()">
              <input type="hidden" name="cliente_id" :value="selected.id || ''">

              <div class="flex-1 min-h-0 overflow-y-auto px-5 py-5 pr-2 space-y-4" style="max-height: calc(90dvh - 200px);">
                <p class="text-xs text-gray-500">Los campos marcados con <span class="text-red-500">*</span> son obligatorios.</p>
                @include('mobile.supervisor.venta.nuevo-cliente.step-clientes')
                @include('mobile.supervisor.venta.nuevo-cliente.step-creditos')
                @include('mobile.supervisor.venta.nuevo-cliente.step-ocupaciones')
                @include('mobile.supervisor.venta.nuevo-cliente.step-datos-contacto')
                @include('mobile.supervisor.venta.nuevo-cliente.step-informacion-familiares')
                @include('mobile.supervisor.venta.nuevo-cliente.step-avales')
                @include('mobile.supervisor.venta.nuevo-cliente.step-garantias')
              </div>

              <div class="px-5 py-4 border-t border-gray-200 flex items-center justify-between">
                <button type="button"
                        class="px-3 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                        @click="prevStep()" :disabled="currentStep === 1" x-cloak>Anterior</button>

                <div class="flex items-center gap-2">
                  <button type="button"
                          class="px-3 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                          x-show="currentStep < steps.length" x-cloak
                          @click="nextStep()">Siguiente</button>

                  <button type="submit"
                          class="px-3 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                          x-show="currentStep === steps.length" x-cloak
                          :disabled="saving">
                    <span x-show="!saving">Guardar</span>
                    <span x-show="saving">Guardando...</span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </template>








  </div>

  <script>
    function prospectado() {
      return {
        showModal: false,
        showFormModal: false,
        currentStep: 1,
        steps: [
          { id: 1, label: 'Clientes' },
          { id: 2, label: 'CrÃ©ditos' },
          { id: 3, label: 'Ocupaciones' },
          { id: 4, label: 'Datos contacto' },
          { id: 5, label: 'InformaciÃ³n familiares' },
          { id: 6, label: 'Avales' },
          { id: 7, label: 'GarantÃ­as' },
        ],
        feedback: { show: false, type: 'success', message: '' },
        saving: false,
        maxGarantias: 8,
        postUrl: '{{ route('mobile.supervisor.nuevo_cliente.store') }}',
        csrfToken: '{{ csrf_token() }}',
        selected: {},
        form: {},
        init() {
          this.selected = this.defaultSelected();
          this.form = this.defaultForm();
        },
        defaultSelected() {
          return {
            id: null,
            nombre: '',
            apellido_p: '',
            apellido_m: '',
            curp: '',
            estatus: '',
            fecha_nacimiento: '',
            telefono: '',
            direccion: '',
            monto: null,
            monto_total: null,
            monto_maximo: null,
            documentos: { ine: null, comprobante: null },
            documentos_detalle: [],
            aval: null,
            garantias: [],
            tiene_credito_activo: false,
            activo: false,
          };
        },
        defaultForm() {
          return {
            cliente: {
              curp: '',
              nombre: '',
              apellido_p: '',
              apellido_m: '',
              fecha_nacimiento: '',
            },
            credito: {
              monto_total: '',
              periodicidad: '',
              fecha_inicio: '',
              fecha_final: '',
            },
            ocupacion: {
              actividad: '',
              nombre_empresa: '',
              calle: '',
              numero: '',
              colonia: '',
              municipio: '',
              telefono: '',
              antiguedad: '',
              monto_percibido: '',
              periodo_pago: '',
              ingresos_adicionales: [this.emptyIngreso()],
            },
            contacto: {
              calle: '',
              numero_ext: '',
              numero_int: '',
              monto_mensual: '',
              colonia: '',
              municipio: '',
              estado: '',
              cp: '',
              tiempo_en_residencia: '',
              tel_fijo: '',
              tel_cel: '',
              tipo_de_vivienda: '',
            },
            familiares: {
              nombre_conyuge: '',
              celular_conyuge: '',
              actividad_conyuge: '',
              ingresos_semanales_conyuge: '',
              domicilio_trabajo_conyuge: '',
              personas_en_domicilio: '',
              dependientes_economicos: '',
              conyuge_vive_con_cliente: '',
            },
            aval: {
              curp: '',
              nombre: '',
              apellido_p: '',
              apellido_m: '',
              fecha_nacimiento: '',
              direccion: '',
              telefono: '',
              parentesco: '',
            },
            garantias: [this.emptyGarantia()],
          };
        },
        emptyIngreso() {
          return { concepto: '', monto: '', frecuencia: '' };
        },
        emptyGarantia() {
          return {
            propietario: '',
            tipo: '',
            marca: '',
            modelo: '',
            num_serie: '',
            antiguedad: '',
            monto_garantizado: '',
            foto_url: '',
          };
        },
        openModal(data) {
          const defaults = this.defaultSelected();
          this.selected = {
            ...defaults,
            ...data,
            documentos: { ...defaults.documentos, ...(data.documentos || {}) },
            documentos_detalle: data.documentos_detalle || [],
            aval: data.aval || null,
            garantias: Array.isArray(data.garantias) ? data.garantias : [],
          };
          this.showModal = true;
        },
        closeModal(resetSelected = true) {
          this.showModal = false;
          if (resetSelected) this.selected = this.defaultSelected();
        },
        openForm() {
          this.form = this.populateFormFromSelected();
          this.currentStep = 1;
          this.feedback = { show: false, type: 'success', message: '' };
          this.showFormModal = true;
          this.showModal = false;
        },
        closeForm() {
          this.showFormModal = false;
          this.form = this.defaultForm();
          this.feedback = { show: false, type: 'success', message: '' };
        },
        populateFormFromSelected() {
          const form = this.defaultForm();
          form.cliente.curp = this.selected.curp || '';
          form.cliente.nombre = this.selected.nombre || '';
          form.cliente.apellido_p = this.selected.apellido_p || '';
          form.cliente.apellido_m = this.selected.apellido_m || '';
          form.cliente.fecha_nacimiento = this.selected.fecha_nacimiento || '';
          form.credito.monto_total = this.selected.monto_total ?? this.selected.monto ?? '';
          form.credito.periodicidad = this.selected.credito?.periodicidad || '';
          form.credito.fecha_inicio = this.selected.credito?.fecha_inicio || '';
          form.credito.fecha_final = this.selected.credito?.fecha_final || '';
          if (this.selected.aval) {
            form.aval.curp = this.selected.aval.curp || '';
            form.aval.nombre = this.selected.aval.nombre || '';
            form.aval.apellido_p = this.selected.aval.apellido_p || '';
            form.aval.apellido_m = this.selected.aval.apellido_m || '';
            form.aval.fecha_nacimiento = this.selected.aval.fecha_nacimiento || '';
            form.aval.direccion = this.selected.aval.direccion || '';
            form.aval.telefono = this.selected.aval.telefono || '';
            form.aval.parentesco = this.selected.aval.parentesco || '';
          }
          if (Array.isArray(this.selected.garantias) && this.selected.garantias.length) {
            form.garantias = this.selected.garantias.map(item => ({
              propietario: item.propietario || '',
              tipo: item.tipo || '',
              marca: item.marca || '',
              modelo: item.modelo || '',
              num_serie: item.num_serie || '',
              antiguedad: item.antiguedad || '',
              monto_garantizado: item.monto_garantizado || '',
              foto_url: item.foto_url || '',
            }));
          }
          return form;
        },
        nextStep() { if (this.currentStep < this.steps.length) this.currentStep += 1; },
        prevStep() { if (this.currentStep > 1) this.currentStep -= 1; },
        currentStepLabel() {
          const step = this.steps.find(item => item.id === this.currentStep);
          return step ? step.label : '';
        },
        addIngresoAdicional() { this.form.ocupacion.ingresos_adicionales.push(this.emptyIngreso()); },
        removeIngresoAdicional(index) {
          if (this.form.ocupacion.ingresos_adicionales.length === 1) {
            this.form.ocupacion.ingresos_adicionales[0] = this.emptyIngreso();
            return;
          }
          this.form.ocupacion.ingresos_adicionales.splice(index, 1);
        },
        addGarantia() {
          if (this.form.garantias.length >= this.maxGarantias) return;
          this.form.garantias.push(this.emptyGarantia());
        },
        removeGarantia(index) {
          if (this.form.garantias.length === 1) {
            this.form.garantias[0] = this.emptyGarantia();
            return;
          }
          this.form.garantias.splice(index, 1);
        },
        canAddGarantia() { return this.form.garantias.length < this.maxGarantias; },
        async submitForm() {
          if (this.saving) return;
          this.saving = true;
          this.feedback = { show: false, type: 'success', message: '' };
          try {
            const payload = {
              cliente_id: this.selected.id,
              form: JSON.parse(JSON.stringify(this.form)),
            };
            const response = await fetch(this.postUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
              },
              body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (!response.ok) {
              const rawErrors = data && data.errors ? Object.values(data.errors).flat() : [];
              const firstError = rawErrors.find(Boolean);
              const message = firstError || data.message || 'No se pudo guardar la informacion.';
              throw new Error(message);
            }
            this.feedback = {
              show: true,
              type: 'success',
              message: data.message || 'Informacion guardada correctamente.',
            };
            this.currentStep = 1;
            this.form = this.defaultForm();
            setTimeout(() => {
              this.closeForm();
            }, 1200);
          } catch (error) {
            this.feedback = {
              show: true,
              type: 'error',
              message: (error && error.message) || 'Ocurrio un error inesperado.',
            };
          } finally {
            this.saving = false;
          }
        },
        aceptar() { console.log('ACEPTAR', this.selected); this.closeModal(); },
        rechazar() { console.log('RECHAZAR', this.selected); this.closeModal(); },
        formatCurrency(value) {
          const number = Number(value || 0);
          return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', minimumFractionDigits: 2 }).format(number);
        },
      }
    }
  </script>
</x-layouts.mobile.mobile-layout>



