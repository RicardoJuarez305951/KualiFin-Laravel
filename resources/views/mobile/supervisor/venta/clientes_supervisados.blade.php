{{-- resources/views/mobile/supervisor/venta/clientes_supervisados.blade.php --}}
@php
    if (!function_exists('formatMoneyMx')) {
        function formatMoneyMx($value) {
            return '$' . number_format((float) $value, 2, '.', ',');
        }
    }
@endphp

<x-layouts.mobile.mobile-layout title="Clientes Supervision">
  <div x-data="clientesSupervision()" x-init="init()" class="p-4 w-full max-w-md mx-auto space-y-6">
    @forelse($promotores as $promotor)
      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-7 h-7 text-[12px] font-bold rounded-full bg-gray-200 text-gray-700">{{ $loop->iteration }}</span>
          <div>
            <p class="text-[15px] font-semibold text-gray-900">{{ $promotor['nombre'] }}</p>
            <p class="text-[12px] text-gray-500">Clientes supervisados</p>
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
                  <div class="grid grid-cols-[70%_30%] items-center gap-2">
                    <div class="min-w-0">
                      <p class="truncate text-[14px] font-semibold text-gray-900">{{ $cliente['nombre'] }}</p>
                      <div class="flex items-center gap-2 text-[12px] text-gray-600">
                        <span>Tel. {{ $cliente['telefono'] ?? 'Sin telefono' }}</span>
                        <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                          {{ formatMoneyMx($cliente['monto_credito'] ?? $cliente['monto'] ?? 0) }}
                        </span>
                      </div>
                    </div>
                    <div>
                      <button
                        type="button"
                        class="w-full px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm"
                        x-on:click="openModal(@js($cliente))">
                        Revisar
                      </button>
                    </div>
                  </div>
                </div>
              @empty
                <p class="px-3 py-4 text-sm text-gray-500 text-center">Sin clientes nuevos en supervision</p>
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
                  <div class="grid grid-cols-[70%_30%] items-center gap-2">
                    <div class="min-w-0">
                      <p class="truncate text-[14px] font-semibold text-gray-900">{{ $cliente['nombre'] }}</p>
                      <div class="flex items-center gap-2 text-[12px] text-gray-600">
                        <span>Tel. {{ $cliente['telefono'] ?? 'Sin telefono' }}</span>
                        <span class="inline-block px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 ring-1 ring-blue-600/20">
                          {{ formatMoneyMx($cliente['monto_credito'] ?? $cliente['monto'] ?? 0) }}
                        </span>
                      </div>
                    </div>
                    <div>
                      <button
                        type="button"
                        class="w-full px-3 py-1.5 text-xs rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm"
                        x-on:click="openModal(@js($cliente))">
                        Revisar
                      </button>
                    </div>
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
        No hay clientes en supervision registrados para tus promotores.
      </div>
    @endforelse

    <template x-if="showModal">
      <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/40" x-on:click="closeModal()"></div>
        <div class="relative w-full sm:max-w-md bg-white text-gray-900 border border-gray-200 shadow-2xl sm:rounded-2xl rounded-t-2xl p-5 sm:mx-auto space-y-4"
             x-trap.noscroll="showModal" x-transition>
          <div class="flex items-start justify-between">
            <div>
              <p class="text-base font-semibold" x-text="selected.nombre"></p>
              <p class="text-xs text-gray-500" x-text="selected.curp"></p>
              <p class="text-xs text-gray-500" x-show="selected.promotor">Promotor: <span x-text="selected.promotor"></span></p>
            </div>
            <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200" x-on:click="closeModal()">Cerrar</button>
          </div>

          <div class="space-y-2 text-sm">
            <p x-show="selected.telefono"><span class="font-semibold">Telefono:</span> <span x-text="selected.telefono"></span></p>
            <p x-show="selected.horario_de_pago"><span class="font-semibold">Horario de pago:</span> <span x-text="selected.horario_de_pago"></span></p>
            <p x-show="selected.direccion"><span class="font-semibold">Direccion:</span> <span x-text="selected.direccion"></span></p>
            <p x-show="selected.credito && selected.credito.estado"><span class="font-semibold">Estado del credito:</span> <span x-text="selected.credito.estado"></span></p>
            <p x-show="selected.credito && selected.credito.fecha_inicio"><span class="font-semibold">Fecha inicio credito:</span> <span x-text="selected.credito.fecha_inicio"></span></p>
            <p x-show="selected.monto_credito || selected.monto"><span class="font-semibold">Monto:</span> <span x-text="formatCurrency(selected.monto_credito || selected.monto)"></span></p>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-bold text-gray-900">Documentos</h3>
              <button class="text-xs px-2 py-1 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold"
                      x-on:click="openFotos()" :disabled="!selected.documentos_detalle.length"
                      :class="{ 'opacity-50 cursor-not-allowed': !selected.documentos_detalle.length }">
                Ver lista
              </button>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <template x-if="selected.documentos_detalle && selected.documentos_detalle.length">
                <template x-for="(doc, idx) in selected.documentos_detalle.slice(0, 2)" :key="idx">
                  <div class="rounded-xl border border-gray-200 bg-gray-50 p-2">
                    <p class="text-center text-[11px] text-gray-600 mb-1" x-text="doc.titulo"></p>
                    <img :src="doc.url"
                         class="mx-auto object-contain cursor-pointer max-h-[200px] max-w-full rounded"
                         x-on:error="$event.target.replaceWith(Object.assign(document.createElement('a'), {href: doc.url, innerText: 'Abrir documento', className: 'text-blue-600 text-xs underline block text-center'}))"
                         x-on:click="zoomImg = doc.url">
                  </div>
                </template>
              </template>
              <p x-show="!selected.documentos_detalle || !selected.documentos_detalle.length"
                 class="text-xs text-gray-500 text-center col-span-2">Sin documentos cargados.</p>
            </div>
          </div>

          <div class="mt-5 grid grid-cols-2 gap-3">
            <a :href="selected.direccion ? `https://maps.google.com/?q=${encodeURIComponent(selected.direccion)}` : '#'" target="_blank"
               class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold"
               :class="{ 'opacity-50 pointer-events-none': !selected.direccion }">
              Ver ubicacion
            </a>
            <button class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold"
                    x-on:click="confirmarSupervision()">Confirmar</button>
          </div>
        </div>
      </div>
    </template>

    <template x-if="showFotos">
      <div class="fixed inset-0 z-[55] flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/40" x-on:click="closeFotos()"></div>

        <div class="relative w-full sm:max-w-md bg-white text-gray-900 border border-gray-200 shadow-2xl
                    sm:rounded-2xl rounded-t-2xl p-5 sm:mx-auto"
             x-trap.noscroll="showFotos" x-transition>
          <div class="flex items-start justify-between">
            <h3 class="text-base font-bold">Documentos</h3>
            <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200" x-on:click="closeFotos()">Cerrar</button>
          </div>
          <div class="mt-3 space-y-3 max-h-[70vh] overflow-y-auto">
            <template x-for="(item, i) in fotosList" :key="i">
              <div class="flex items-center justify-between gap-3 rounded-lg border p-2">
                <div class="min-w-0">
                  <p class="text-sm font-medium truncate" x-text="item.titulo"></p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <button class="px-2 py-1 text-xs rounded-md bg-purple-600 hover:bg-purple-700 text-white"
                          x-on:click="zoomImg = item.url">Ver</button>
                  <a :href="item.url" target="_blank"
                     class="px-2 py-1 text-xs rounded-md bg-gray-100 hover:bg-gray-200 text-gray-800">Abrir</a>
                </div>
              </div>
            </template>
            <p x-show="!fotosList.length" class="text-xs text-gray-500 text-center">Sin documentos pendientes.</p>
          </div>
        </div>
      </div>
    </template>

    <template x-if="zoomImg">
      <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80" x-on:click="zoomImg = null">
        <img :src="zoomImg" alt="Documento" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl">
      </div>
    </template>

     {{-- ===================== Navegación ===================== --}}
    <section class="grid grid-cols-3 gap-3">
      <a href="{{ url()->previous() }}" class="flex items-center justify-center rounded-xl border border-gray-300 text-white text-sm font-semibold px-3 py-2 bg-blue-600 hover:bg-blue-700 shadow-sm">
        Regresar
      </a>
      <a href="{{ url()->current() }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 text-white text-sm font-semibold px-3 py-2 hover:bg-blue-700 shadow">
        Actualizar
      </a>
      <a href="{{ route('mobile.supervisor.reporte') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 text-white text-sm font-semibold px-3 py-2 hover:bg-indigo-700 shadow">
        Reporte
      </a>
    </section>
  </div>

  <script>
    function clientesSupervision() {
      return {
        showModal: false,
        showFotos: false,
        zoomImg: null,
        selected: {},
        fotosList: [],
        init() {
          this.selected = this.defaultSelected();
        },
        defaultSelected() {
          return {
            id: null,
            nombre: '',
            curp: '',
            telefono: '',
            horario_de_pago: '',
            direccion: '',
            promotor: '',
            monto: null,
            monto_credito: null,
            documentos: { ine: null, comprobante: null },
            documentos_detalle: [],
            aval: null,
            credito: { id: null, monto_total: null, estado: null, fecha_inicio: null },
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
            credito: { ...defaults.credito, ...(data.credito || {}) },
          };
          this.fotosList = this.selected.documentos_detalle;
          this.showModal = true;
        },
        closeModal() {
          this.showModal = false;
          this.selected = this.defaultSelected();
          this.fotosList = [];
        },
        openFotos() {
          if (!this.selected.documentos_detalle.length) return;
          this.fotosList = this.selected.documentos_detalle;
          this.showFotos = true;
        },
        closeFotos() {
          this.showFotos = false;
          this.fotosList = [];
        },
        confirmarSupervision() {
          console.log('CONFIRMAR SUPERVISION', this.selected);
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
