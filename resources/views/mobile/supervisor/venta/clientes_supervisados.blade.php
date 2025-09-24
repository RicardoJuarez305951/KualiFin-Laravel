{{-- resources/views/mobile/supervisor/venta/clientes_Prospectados.blade.php --}}
@php
    $formatMoney = fn($value) => '$' . number_format((float) $value, 2, '.', ',');
@endphp

<x-layouts.mobile.mobile-layout title="Clientes Supervisado">
  <div x-data="supervisado()" x-init="init()" class="p-4 w-full max-w-md mx-auto space-y-6">

    @forelse($promotores as $promotor)
      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-7 h-7 text-[12px] font-bold rounded-full bg-gray-200 text-gray-700">{{ $loop->iteration }}</span>
          <div>
            <p class="text-[15px] font-semibold text-gray-900">{{ $promotor['nombre'] }}</p>
            <p class="text-[12px] text-gray-500">Supervisado</p>
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
                      <p class="text-[11px] text-gray-500 uppercase">{{ $cliente['cartera_estado'] ?? 'Sin estado' }}</p>
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
              <h3 class="text-[13px] font-bold text-gray-700">Recréditos</h3>
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
                <p class="px-3 py-4 text-sm text-gray-500 text-center">Sin solicitudes de recrédito</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-2xl bg-white border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 shadow-sm">
        No se encontraron clientes Supervisado bajo tu supervisión.
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

          <template x-if="modalFeedback.show">
            <div class="rounded-lg border px-3 py-2 text-xs" :class="modalFeedback.type === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700'">
              <span x-text="modalFeedback.message"></span>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-2 text-sm">
            <p><span class="font-semibold">Estatus:</span> <span x-text="selected.cartera_estado || 'Sin definir'"></span></p>
            <p x-show="selected.fecha_nacimiento"><span class="font-semibold">Fecha nacimiento:</span> <span x-text="selected.fecha_nacimiento"></span></p>
            <p x-show="selected.telefono"><span class="font-semibold">Teléfono:</span> <span x-text="selected.telefono"></span></p>
            <p x-show="selected.horario_de_pago"><span class="font-semibold">Horario de pago:</span> <span x-text="selected.horario_de_pago"></span></p>
            <p x-show="selected.direccion"><span class="font-semibold">Dirección:</span> <span x-text="selected.direccion"></span></p>
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
              :class="actionInProgress ? 'cursor-not-allowed opacity-60' : ''"
              :disabled="actionInProgress !== null"
              @click="rechazar()">
              <span x-text="actionInProgress === 'reject' ? 'Procesando...' : 'Rechazar'"></span>
            </button>
            <button
              class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-sm transition"
              :class="actionInProgress ? 'cursor-not-allowed opacity-60' : ''"
              :disabled="actionInProgress !== null"
              @click="openForm()">CHECK</button>
            <button
              class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm transition"
              :class="actionInProgress ? 'cursor-not-allowed opacity-60' : ''"
              :disabled="actionInProgress !== null"
              @click="aceptar()">
              <span x-text="actionInProgress === 'approve' ? 'Procesando...' : 'Aceptar'"></span>
            </button>
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

    @php
      $contextQuery = $supervisorContextQuery ?? [];
    @endphp

    <div class="space-y-3">
      <a href="{{ route('mobile.supervisor.clientes_supervisados', $contextQuery) }}"
         class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
        Recargar
      </a>
      <a href="{{ route('mobile.supervisor.venta', $contextQuery) }}"
         class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
        Regresar a Venta
      </a>
    </div>
  </div>

  <script>
    function supervisado() {
      return {
        showModal: false,
        showFormModal: false,
        currentStep: 1,
        steps: [
          { id: 1, label: 'Clientes' },
          { id: 2, label: 'Créditos' },
          { id: 3, label: 'Ocupaciones' },
          { id: 4, label: 'Datos contacto' },
          { id: 5, label: 'Información familiares' },
          { id: 6, label: 'Avales' },
          { id: 7, label: 'Garantías' },
        ],
        feedback: { show: false, type: 'success', message: '' },
        modalFeedback: { show: false, type: 'success', message: '' },
        saving: false,
        actionInProgress: null,
        maxGarantias: 8,
        postUrl: '{{ route('mobile.supervisor.nuevo_cliente.store', $contextQuery) }}',
        registrarCreditoUrlTemplate: @js(route('mobile.supervisor.clientes_prospectados.registrar_credito', array_merge($contextQuery, ['cliente' => '__CLIENTE_ID__']))),
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
            nombre_simple: '',
            apellido_p: '',
            apellido_m: '',
            curp: '',
            cartera_estado: '',
            fecha_nacimiento: '',
            telefono: '',
            horario_de_pago: '',
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
              horario_de_pago: '',
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
              tiene_ingresos_adicionales: false,
              ingresos_adicionales: [],
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
              tiene_conyuge: false,
              nombre_conyuge: '',
              celular_conyuge: '',
              actividad_conyuge: '',
              ingresos_semanales_conyuge: '',
              domicilio_trabajo_conyuge: '',
              conyuge_vive_con_cliente: '',
              personas_en_domicilio: '',
              dependientes_economicos: '',
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
            foto_nombre: '',
            foto_archivo: null,
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
          this.modalFeedback = { show: false, type: 'success', message: '' };
          this.actionInProgress = null;
          this.showModal = true;
        },
        closeModal(resetSelected = true) {
          this.showModal = false;
          this.modalFeedback = { show: false, type: 'success', message: '' };
          this.actionInProgress = null;
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
          const normalize = value => (typeof value === 'string' ? value.trim() : (value ? String(value).trim() : ''));
          const form = this.defaultForm();

          const fullName = normalize(this.selected.nombre) || '';
          let nombres = normalize(this.selected.nombre_simple) || '';
          let apellidoP = normalize(this.selected.apellido_p);
          let apellidoM = normalize(this.selected.apellido_m);

          if (!nombres && fullName) {
            const parts = fullName.split(/\s+/).filter(Boolean);
            if (parts.length === 1) {
              nombres = parts[0];
            } else if (parts.length === 2) {
              nombres = parts[0];
              apellidoP = apellidoP || parts[1];
            } else if (parts.length > 2) {
              if (!apellidoP) apellidoP = parts[parts.length - 2];
              if (!apellidoM) apellidoM = parts[parts.length - 1];
              nombres = parts.slice(0, parts.length - 2).join(' ');
            }
          } else if (fullName) {
            const parts = fullName.split(/\s+/).filter(Boolean);
            const trimmed = [...parts];
            if (apellidoM && trimmed.length && trimmed[trimmed.length - 1].toLowerCase() === apellidoM.toLowerCase()) {
              trimmed.pop();
            }
            if (apellidoP && trimmed.length && trimmed[trimmed.length - 1].toLowerCase() === apellidoP.toLowerCase()) {
              trimmed.pop();
            }
            if (!nombres && trimmed.length) {
              nombres = trimmed.join(' ');
            }
          }

          form.cliente.curp = this.selected.curp || '';
          form.cliente.nombre = nombres;
          form.cliente.apellido_p = apellidoP || '';
          form.cliente.apellido_m = apellidoM || '';
          form.cliente.fecha_nacimiento = this.selected.fecha_nacimiento || '';
          form.cliente.horario_de_pago = this.selected.horario_de_pago || '';

          form.credito.monto_total = this.selected.monto_total ?? this.selected.monto ?? '';
          form.credito.periodicidad = normalize(this.selected.credito?.periodicidad) || '';
          form.credito.fecha_inicio = this.selected.credito?.fecha_inicio || '';
          form.credito.fecha_final = this.selected.credito?.fecha_final || '';

          const ocupacion = this.selected.ocupacion || {};
          Object.assign(form.ocupacion, {
            actividad: ocupacion.actividad ?? form.ocupacion.actividad,
            nombre_empresa: ocupacion.nombre_empresa ?? form.ocupacion.nombre_empresa,
            calle: ocupacion.calle ?? form.ocupacion.calle,
            numero: ocupacion.numero ?? form.ocupacion.numero,
            colonia: ocupacion.colonia ?? form.ocupacion.colonia,
            municipio: ocupacion.municipio ?? form.ocupacion.municipio,
            telefono: ocupacion.telefono ?? form.ocupacion.telefono,
            antiguedad: ocupacion.antiguedad ?? form.ocupacion.antiguedad,
            monto_percibido: ocupacion.monto_percibido ?? form.ocupacion.monto_percibido,
            periodo_pago: ocupacion.periodo_pago ?? form.ocupacion.periodo_pago,
          });
          const ingresos = Array.isArray(ocupacion.ingresos_adicionales) ? ocupacion.ingresos_adicionales : [];
          form.ocupacion.tiene_ingresos_adicionales = Boolean((typeof ocupacion.tiene_ingresos_adicionales === 'boolean' ? ocupacion.tiene_ingresos_adicionales : null) ?? (ingresos.length > 0));
          form.ocupacion.ingresos_adicionales = ingresos.map(item => ({
            concepto: normalize(item.concepto),
            monto: item.monto !== undefined && item.monto !== null ? String(item.monto) : '',
            frecuencia: normalize(item.frecuencia),
          })).filter(item => item.concepto || item.monto || item.frecuencia);
          if (form.ocupacion.tiene_ingresos_adicionales && !form.ocupacion.ingresos_adicionales.length) {
            form.ocupacion.ingresos_adicionales = [this.emptyIngreso()];
          }

          const contacto = this.selected.contacto || {};
          Object.assign(form.contacto, {
            calle: contacto.calle ?? form.contacto.calle,
            numero_ext: contacto.numero_ext ?? form.contacto.numero_ext,
            numero_int: contacto.numero_int ?? form.contacto.numero_int,
            monto_mensual: contacto.monto_mensual ?? form.contacto.monto_mensual,
            colonia: contacto.colonia ?? form.contacto.colonia,
            municipio: contacto.municipio ?? form.contacto.municipio,
            estado: contacto.estado ?? form.contacto.estado,
            cp: contacto.cp ?? form.contacto.cp,
            tiempo_en_residencia: contacto.tiempo_en_residencia ?? form.contacto.tiempo_en_residencia,
            tel_fijo: contacto.tel_fijo ?? form.contacto.tel_fijo,
            tel_cel: contacto.tel_cel ?? form.contacto.tel_cel,
            tipo_de_vivienda: contacto.tipo_de_vivienda ?? form.contacto.tipo_de_vivienda,
          });

          const familiares = this.selected.familiares || {};
          const conyugeFields = [
            normalize(familiares.nombre_conyuge),
            normalize(familiares.celular_conyuge),
            normalize(familiares.actividad_conyuge),
            familiares.ingresos_semanales_conyuge,
            normalize(familiares.domicilio_trabajo_conyuge),
          ];
          let hasConyuge = conyugeFields.some(value => value !== '' && value !== null && value !== undefined);
          if (typeof familiares.tiene_conyuge === 'boolean') {
            hasConyuge = familiares.tiene_conyuge;
          }
          form.familiares.tiene_conyuge = hasConyuge;
          if (hasConyuge) {
            form.familiares.nombre_conyuge = normalize(familiares.nombre_conyuge);
            form.familiares.celular_conyuge = normalize(familiares.celular_conyuge);
            form.familiares.actividad_conyuge = normalize(familiares.actividad_conyuge);
            form.familiares.ingresos_semanales_conyuge = familiares.ingresos_semanales_conyuge !== undefined && familiares.ingresos_semanales_conyuge !== null ? String(familiares.ingresos_semanales_conyuge) : '';
            form.familiares.domicilio_trabajo_conyuge = normalize(familiares.domicilio_trabajo_conyuge);
            const viveCon = familiares.conyuge_vive_con_cliente;
            if (typeof viveCon === 'boolean') {
              form.familiares.conyuge_vive_con_cliente = viveCon ? 'si' : 'no';
            } else if (typeof viveCon === 'string') {
              const lowered = viveCon.trim().toLowerCase();
              if (['si', 'true', '1', 'yes'].includes(lowered)) {
                form.familiares.conyuge_vive_con_cliente = 'si';
              } else if (['no', 'false', '0'].includes(lowered)) {
                form.familiares.conyuge_vive_con_cliente = 'no';
              } else {
                form.familiares.conyuge_vive_con_cliente = viveCon;
              }
            } else {
              form.familiares.conyuge_vive_con_cliente = '';
            }
          }
          form.familiares.personas_en_domicilio = familiares.personas_en_domicilio !== undefined && familiares.personas_en_domicilio !== null ? String(familiares.personas_en_domicilio) : form.familiares.personas_en_domicilio;
                    form.familiares.dependientes_economicos = familiares.dependientes_economicos !== undefined && familiares.dependientes_economicos !== null ? String(familiares.dependientes_economicos) : form.familiares.dependientes_economicos;

          if (this.selected.aval) {
            const aval = this.selected.aval || {};
            const avalFullName = normalize(aval.nombre) || normalize(aval.nombre_completo) || normalize(aval.nombres);
            let avalApellidoP = normalize(aval.apellido_p) || normalize(aval.apellido_paterno) || normalize(aval.apellidoP) || normalize(aval.apellidoPaterno);
            let avalApellidoM = normalize(aval.apellido_m) || normalize(aval.apellido_materno) || normalize(aval.apellidoM) || normalize(aval.apellidoMaterno);
            let avalNombres = avalFullName;

            if (!avalApellidoP && !avalApellidoM && avalFullName) {
              const parts = avalFullName.split(/\s+/).filter(Boolean);
              if (parts.length === 1) {
                avalNombres = parts[0];
              } else if (parts.length === 2) {
                avalNombres = parts[0];
                avalApellidoP = parts[1];
              } else if (parts.length > 2) {
                avalApellidoP = parts[parts.length - 2];
                avalApellidoM = parts[parts.length - 1];
                avalNombres = parts.slice(0, parts.length - 2).join(' ');
              }
            } else if (avalFullName) {
              const parts = avalFullName.split(/\s+/).filter(Boolean);
              const trimmed = [...parts];
              if (avalApellidoM && trimmed.length && trimmed[trimmed.length - 1].toLowerCase() === avalApellidoM.toLowerCase()) {
                trimmed.pop();
              }
              if (avalApellidoP && trimmed.length && trimmed[trimmed.length - 1].toLowerCase() === avalApellidoP.toLowerCase()) {
                trimmed.pop();
              }
              if (trimmed.length) {
                avalNombres = trimmed.join(' ');
              }
            }

            form.aval.curp = aval.curp || '';
            form.aval.nombre = avalNombres;
            form.aval.apellido_p = avalApellidoP || '';
            form.aval.apellido_m = avalApellidoM || '';
            form.aval.fecha_nacimiento = aval.fecha_nacimiento || '';
            form.aval.direccion = aval.direccion || '';
            form.aval.telefono = aval.telefono || '';
            form.aval.parentesco = aval.parentesco || '';
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
              foto_nombre: '',
              foto_archivo: null,
            }));
          }

          if (!form.garantias.length) {
            form.garantias = [this.emptyGarantia()];
          }

          return form;
        },
        nextStep() { if (this.currentStep < this.steps.length) this.currentStep += 1; },
        prevStep() { if (this.currentStep > 1) this.currentStep -= 1; },
        currentStepLabel() {
          const step = this.steps.find(item => item.id === this.currentStep);
          return step ? step.label : '';
        },
        handleIngresosAdicionalesToggle() {
          if (this.form.ocupacion.tiene_ingresos_adicionales) {
            if (!this.form.ocupacion.ingresos_adicionales.length) {
              this.form.ocupacion.ingresos_adicionales = [this.emptyIngreso()];
            }
          } else {
            this.form.ocupacion.ingresos_adicionales = [];
          }
        },
        addIngresoAdicional() {
          if (!this.form.ocupacion.tiene_ingresos_adicionales) {
            this.form.ocupacion.tiene_ingresos_adicionales = true;
            this.form.ocupacion.ingresos_adicionales = [this.emptyIngreso()];
            return;
          }
          this.form.ocupacion.ingresos_adicionales.push(this.emptyIngreso());
        },
        removeIngresoAdicional(index) {
          if (!this.form.ocupacion.tiene_ingresos_adicionales) {
            return;
          }
          if (this.form.ocupacion.ingresos_adicionales.length <= 1) {
            this.form.ocupacion.ingresos_adicionales = [this.emptyIngreso()];
            return;
          }
          this.form.ocupacion.ingresos_adicionales.splice(index, 1);
        },
        handleConyugeToggle() {
          if (!this.form.familiares.tiene_conyuge) {
            this.form.familiares.nombre_conyuge = '';
            this.form.familiares.celular_conyuge = '';
            this.form.familiares.actividad_conyuge = '';
            this.form.familiares.ingresos_semanales_conyuge = '';
            this.form.familiares.domicilio_trabajo_conyuge = '';
            this.form.familiares.conyuge_vive_con_cliente = '';
          } else if (!this.form.familiares.nombre_conyuge) {
            this.form.familiares.conyuge_vive_con_cliente = '';
          }
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
        handleGarantiaFileChange(event, index) {
          const garantia = this.form.garantias[index];
          if (!garantia) return;
          const file = event?.target?.files?.[0] || null;
          garantia.foto_archivo = file;
          garantia.foto_nombre = file ? file.name : '';
          if (file) {
            garantia.foto_url = '';
          }
        },
        canAddGarantia() { return this.form.garantias.length < this.maxGarantias; },
        showActionFeedback(type, message) {
          this.modalFeedback = { show: true, type, message };
        },
        async handleProspectoAction(type, url, successFallback, errorFallback) {
          if (!this.selected?.id || this.actionInProgress) {
            return;
          }
          this.actionInProgress = type;
          this.modalFeedback = { show: false, type: 'success', message: '' };
          try {
            const accion = type === 'approve' ? 'aprobar' : 'rechazar';
            const response = await fetch(url, {
              method: 'POST',
              headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({ accion }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
              const message = data?.message || errorFallback;
              throw new Error(message);
            }
            if (data?.cliente) {
              this.selected.cartera_estado = data.cliente.cartera_estado || this.selected.cartera_estado;
              this.selected.credito = this.selected.credito || {};
              this.selected.credito.estado = data.cliente.credito_estado || this.selected.credito.estado;
            } else if (type === 'approve') {
              this.selected.cartera_estado = 'activo';
              this.selected.credito = this.selected.credito || {};
              this.selected.credito.estado = 'Supervisado';
            } else if (type === 'reject') {
              this.selected.cartera_estado = 'inactivo';
              this.selected.credito = this.selected.credito || {};
              this.selected.credito.estado = 'Rechazado';
            }
            this.showActionFeedback('success', data?.message || successFallback);
            setTimeout(() => {
              this.actionInProgress = null;
              window.location.reload();
            }, 900);
          } catch (error) {
            this.showActionFeedback('error', (error && error.message) || errorFallback);
            this.actionInProgress = null;
          }
        },
        async aceptar() {
          if (!this.selected?.id) return;
          const url = this.registrarCreditoUrlTemplate.replace('__CLIENTE_ID__', this.selected.id);
          await this.handleProspectoAction('approve', url, 'Cliente supervisado correctamente.', 'No se pudo supervisar el cliente.');
        },
        async rechazar() {
          if (!this.selected?.id) return;
          const url = this.registrarCreditoUrlTemplate.replace('__CLIENTE_ID__', this.selected.id);
          await this.handleProspectoAction('reject', url, 'Cliente rechazado correctamente.', 'No se pudo rechazar el cliente.');
        },
        async submitForm() {
          if (this.saving) return;
          this.saving = true;
          this.feedback = { show: false, type: 'success', message: '' };
          try {
            const replacer = (key, value) => (key === 'foto_archivo' ? undefined : value);
            const formCopy = JSON.parse(JSON.stringify(this.form, replacer));

            if (!formCopy.ocupacion.tiene_ingresos_adicionales) {
              formCopy.ocupacion.ingresos_adicionales = [];
            }
            if (!formCopy.familiares.tiene_conyuge) {
              formCopy.familiares.nombre_conyuge = '';
              formCopy.familiares.celular_conyuge = '';
              formCopy.familiares.actividad_conyuge = '';
              formCopy.familiares.ingresos_semanales_conyuge = '';
              formCopy.familiares.domicilio_trabajo_conyuge = '';
              formCopy.familiares.conyuge_vive_con_cliente = false;
            }

            formCopy.garantias = formCopy.garantias.map(item => {
              const clone = { ...item };
              delete clone.foto_nombre;
              return clone;
            });

            const formData = new FormData();
            formData.append('cliente_id', this.selected.id || '');
            formData.append('form', JSON.stringify(formCopy));
            this.form.garantias.forEach((garantia, index) => {
              if (garantia.foto_archivo instanceof File) {
                formData.append(`garantia_archivos[${index}]`, garantia.foto_archivo);
              }
            });

            const response = await fetch(this.postUrl, {
              method: 'POST',
              headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
              },
              body: formData,
            });
            const data = await response.json().catch(() => ({ message: 'Respuesta inesperada del servidor.' }));
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
        formatCurrency(value) {
          const number = Number(value || 0);
          return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', minimumFractionDigits: 2 }).format(number);
        },
      }
    }
  </script>
</x-layouts.mobile.mobile-layout>
