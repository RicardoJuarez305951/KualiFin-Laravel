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
              <h3 class="text-[13px] font-bold text-gray-700">Recreditos</h3>
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
                <p class="px-3 py-4 text-sm text-gray-500 text-center">Sin solicitudes de recredito</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-2xl bg-white border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 shadow-sm">
        No se encontraron clientes prospectados bajo tu supervision.
      </div>
    @endforelse

    <template x-if="showModal">
      <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
        <div class="relative w-full sm:max-w-md bg-white text-gray-900 border border-gray-200 shadow-2xl sm:rounded-2xl rounded-t-2xl p-5 sm:mx-auto space-y-4"
             x-trap.noscroll="showModal" x-transition>
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
            <p x-show="selected.telefono"><span class="font-semibold">Telefono:</span> <span x-text="selected.telefono"></span></p>
            <p x-show="selected.direccion"><span class="font-semibold">Direccion:</span> <span x-text="selected.direccion"></span></p>
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

          <div class="grid grid-cols-2 gap-3">
            <button
              class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold shadow-sm transition"
              @click="rechazar()">Rechazar</button>
            <button
              class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm transition"
              @click="aceptar()">Aceptar</button>
          </div>
        </div>
      </div>
    </template>
  </div>

  <script>
    function prospectado() {
      return {
        showModal: false,
        selected: {},
        init() {
          this.selected = this.defaultSelected();
        },
        defaultSelected() {
          return {
            id: null,
            nombre: '',
            curp: '',
            estatus: '',
            fecha_nacimiento: '',
            telefono: '',
            direccion: '',
            monto: null,
            monto_maximo: null,
            documentos: { ine: null, comprobante: null },
            documentos_detalle: [],
            aval: null,
            tiene_credito_activo: false,
            activo: false,
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
          };
          this.showModal = true;
        },
        closeModal() {
          this.showModal = false;
          this.selected = this.defaultSelected();
        },
        aceptar() {
          console.log('ACEPTAR', this.selected);
          this.closeModal();
        },
        rechazar() {
          console.log('RECHAZAR', this.selected);
          this.closeModal();
        },
        formatCurrency(value) {
          const number = Number(value || 0);
          return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', minimumFractionDigits: 2 }).format(number);
        },
      }
    }
  </script>
</x-layouts.mobile.mobile-layout>
