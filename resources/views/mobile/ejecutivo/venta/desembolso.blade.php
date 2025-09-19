{{-- resources/views/mobile/ejecutivo/venta/desembolso.blade.php --}}
@php
  use Carbon\Carbon;
  use Faker\Factory as Faker;

  $faker = Faker::create('es_MX');

  // Helper para generar clientes faker
  $makeClientes = function ($n) use ($faker) {
      $clientes = [];
      for ($i = 0; $i < $n; $i++) {
          $clientes[] = [
              'id'     => $faker->unique()->randomNumber(),
              'nombre' => $faker->name,
              'monto'  => $faker->numberBetween(500, 5000),
          ];
      }
      return $clientes;
  };

  // ===== Variables con Faker si no vienen desde el controlador =====
  $role       = $role ?? 'ejecutivo';
  $ejecutivo  = $ejecutivo ?? ['nombre'=>'Ricardo','apellido_p'=>'Juárez','apellido_m'=>'Ramírez'];
  $fechaVenta = isset($fechaVenta) ? Carbon::parse($fechaVenta) : Carbon::now();
  $fechaStr   = $fechaVenta->format('d/m/Y');

  $listas = $listas ?? [
      'falla'       => $makeClientes(3),
      'adelantos'   => $makeClientes(2),
      'recuperados' => $makeClientes(2),
      'ventas'      => $makeClientes(3),
      'desembolso'  => $makeClientes(2),
  ];
@endphp

<x-layouts.mobile.mobile-layout :title="'Formato de Desembolso'">
  <div
    x-data="desembolsoPage({ listas: @js($listas) })"
    x-init="init()"
    class="p-4 w-full max-w-md mx-auto space-y-5"
  >
    {{-- Header Ejecutivo / Fecha / Totales --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 uppercase">Ejecutivo</p>
          <p class="text-sm font-semibold text-gray-800">
            {{ $ejecutivo['nombre'] }} {{ $ejecutivo['apellido_p'] }} {{ $ejecutivo['apellido_m'] }}
          </p>
        </div>
        <div class="text-right">
          <p class="text-xs text-gray-500 uppercase">Fecha</p>
          <p class="text-sm font-semibold text-gray-800">{{ $fechaStr }}</p>
        </div>
      </div>

      {{-- Totales principales (Falla, Adelantos, RQ) --}}
      <div class="mt-4 grid grid-cols-3 gap-2">
        <div class="p-3 rounded-xl bg-teal-50 border border-teal-100">
          <p class="text-[11px] text-teal-700">Total Falla</p>
          <p class="text-sm font-semibold text-teal-900" x-text="money(totales.falla)"></p>
        </div>
        <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-100">
          <p class="text-[11px] text-indigo-700">Total Adelantos</p>
          <p class="text-sm font-semibold text-indigo-900" x-text="money(totales.adelantos)"></p>
        </div>
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-100">
          <p class="text-[11px] text-amber-700">Total RQ</p>
          <p class="text-sm font-semibold text-amber-900" x-text="money(totales.recuperados)"></p>
        </div>
      </div>

      {{-- Totales de venta / desembolso --}}
      <div class="mt-3 grid grid-cols-2 gap-2">
        <div class="p-3 rounded-xl bg-sky-50 border border-sky-100">
          <p class="text-[11px] text-sky-700">Avance de Venta</p>
          <p class="text-sm font-semibold text-sky-900" x-text="money(totales.ventas)"></p>
        </div>
        <div class="p-3 rounded-xl bg-purple-50 border border-purple-100">
          <p class="text-[11px] text-purple-700">Avance Desembolso</p>
          <p class="text-sm font-semibold text-purple-900" x-text="money(totales.desembolso)"></p>
        </div>
      </div>

      <div class="mt-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
        <p class="text-[11px] text-gray-600">Total General</p>
        <p class="text-base font-bold text-gray-900" x-text="money(totales.general)"></p>
      </div>
    </div>

    {{-- === Avances de Falla / Adelantos / Recuperados (RQ) === --}}
    <template x-for="section in sectionsPagos" :key="section.key">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2">
            <span class="inline-flex w-2.5 h-2.5 rounded-full" :class="section.dot"></span>
            <h3 class="text-sm font-semibold" x-text="section.title"></h3>
          </div>
          <span class="text-[11px] text-gray-500" x-text="section.hint"></span>
        </div>

        <div class="divide-y divide-gray-100">
          <template x-if="items(section.key).length === 0">
            <p class="py-3 text-xs text-gray-500">Sin registros.</p>
          </template>

          <template x-for="cte in items(section.key)" :key="cte.id">
            <div class="py-3 flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate" x-text="cte.nombre"></p>
                <p class="text-xs text-gray-500" x-text="`Monto: ${money(cte.monto)}`"></p>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <button type="button"
                  class="px-3 py-1.5 text-xs rounded-full border border-emerald-600 text-emerald-700 hover:bg-emerald-50"
                  @click="pago(cte, section.key)">
                  Pago
                </button>
                <button type="button"
                  class="px-3 py-1.5 text-xs rounded-full border border-slate-600 text-slate-700 hover:bg-slate-50"
                  @click="historial(cte)">
                  Historial
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </template>

    {{-- === Avances de Venta (prellenado) === --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
          <span class="inline-flex w-2.5 h-2.5 rounded-full bg-sky-500"></span>
          <h3 class="text-sm font-semibold">Avances de Venta (prellenado)</h3>
        </div>
        <span class="text-[11px] text-gray-500">Continuar / Cancelar por cliente</span>
      </div>

      <div class="divide-y divide-gray-100">
        <template x-if="items('ventas').length === 0">
          <p class="py-3 text-xs text-gray-500">Sin registros.</p>
        </template>

        <template x-for="cte in items('ventas')" :key="cte.id">
          <div class="py-3 flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate" x-text="cte.nombre"></p>
              <p class="text-xs text-gray-500" x-text="`Monto: ${money(cte.monto)}`"></p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <button type="button"
                class="px-3 py-1.5 text-xs rounded-full bg-emerald-600 text-white hover:bg-emerald-700"
                @click="continuar(cte, 'ventas')">
                ✓ Continuar
              </button>
              <button type="button"
                class="px-3 py-1.5 text-xs rounded-full bg-rose-600 text-white hover:bg-rose-700"
                @click="abrirCancel(cte, 'ventas')">
                ✕ Cancelar
              </button>
            </div>
          </div>
        </template>
      </div>
    </div>

    {{-- === Avances de Desembolso (prellenado) === --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
          <span class="inline-flex w-2.5 h-2.5 rounded-full bg-purple-500"></span>
          <h3 class="text-sm font-semibold">Avances de Desembolso (prellenado)</h3>
        </div>
        <span class="text-[11px] text-gray-500">Continuar / Cancelar por cliente</span>
      </div>

      <div class="divide-y divide-gray-100">
        <template x-if="items('desembolso').length === 0">
          <p class="py-3 text-xs text-gray-500">Sin registros.</p>
        </template>

        <template x-for="cte in items('desembolso')" :key="cte.id">
          <div class="py-3 flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate" x-text="cte.nombre"></p>
              <p class="text-xs text-gray-500" x-text="`Monto: ${money(cte.monto)}`"></p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <button type="button"
                class="px-3 py-1.5 text-xs rounded-full bg-emerald-600 text-white hover:bg-emerald-700"
                @click="continuar(cte, 'desembolso')">
                ✓ Continuar
              </button>
              <button type="button"
                class="px-3 py-1.5 text-xs rounded-full bg-rose-600 text-white hover:bg-rose-700"
                @click="abrirCancel(cte, 'desembolso')">
                ✕ Cancelar
              </button>
            </div>
          </div>
        </template>
      </div>
    </div>

    {{-- Área de Firmas --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
      <h3 class="text-sm font-semibold text-gray-800 mb-3">Firmas</h3>
      <div class="grid grid-cols-3 gap-3">
        <template x-for="i in 3" :key="i">
          <div class="text-center">
            <div class="h-12 border-b border-gray-300"></div>
            <p class="mt-1 text-[11px] text-gray-500">Firma</p>
          </div>
        </template>
      </div>
    </div>

    {{-- Acciones finales --}}
    <section class="grid grid-cols-2 gap-3">
      <a href="{{ route('mobile.index') }}"
         class="text-center px-4 py-3 rounded-2xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">
        Regresar
      </a>
      <button type="button"
        class="px-4 py-3 rounded-2xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700"
        @click="enviar()">
        Continuar
      </button>
    </section>

    {{-- Modal Cancelación (popup para causa) --}}
    <div
      x-cloak
      x-show="modal.open"
      x-transition.opacity
      class="fixed inset-0 z-40 bg-black/40"
      @click.self="cerrarModal()"
    ></div>

    <div
      x-cloak
      x-show="modal.open"
      x-transition
      class="fixed inset-x-4 top-20 z-50 bg-white rounded-2xl shadow-2xl border border-gray-100 p-4 space-y-3"
    >
      <div class="flex items-start justify-between">
        <div>
          <h3 class="text-sm font-semibold text-gray-800">Cancelar cliente</h3>
          <p class="text-[11px] text-gray-500">
            Se cancelará del avance de <span class="font-medium" x-text="modal.context?.toUpperCase()"></span>
          </p>
        </div>
        <button class="text-gray-400 hover:text-gray-600" @click="cerrarModal()">✕</button>
      </div>
      <p class="text-xs text-gray-600">
        Cliente: <span class="font-medium" x-text="modal.cte?.nombre || '-'"></span>
      </p>
      <label class="text-xs font-medium text-gray-700">Motivo de cancelación</label>
      <textarea x-model="modal.motivo" rows="3"
        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500"
        placeholder="Escribe el motivo..."></textarea>
      <div class="flex items-center justify-end gap-2">
        <button type="button"
          class="px-3 py-2 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
          @click="cerrarModal()">Cerrar</button>
        <button type="button"
          class="px-3 py-2 rounded-xl bg-rose-600 text-white hover:bg-rose-700"
          @click="confirmarCancel()">Cancelar cliente</button>
      </div>
    </div>
  </div>

  {{-- ===== Alpine logic ===== --}}
  <script>
    function desembolsoPage({ listas }) {
      return {
        data: { ...listas },
        // Totales por sección
        totales: { falla: 0, adelantos: 0, recuperados: 0, ventas: 0, desembolso: 0, general: 0 },

        // Secciones con Pago + Historial
        sectionsPagos: [
          { key: 'falla',       title: 'Avances de Falla',         dot: 'bg-teal-500',    hint: 'Clientes Activos' },
          { key: 'adelantos',   title: 'Avances de Adelantos',     dot: 'bg-indigo-500',  hint: 'Clientes Activos' },
          { key: 'recuperados', title: 'Recuperados (RQ)',         dot: 'bg-amber-500',   hint: 'Clientes con Falla de sistema' },
        ],

        modal: { open: false, cte: null, motivo: '', context: null },

        init() { this.recalcular(); },
        items(key) { return this.data[key] ?? []; },
        money(v) { return '$' + Number(v ?? 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); },

        recalcular() {
          const sum = (arr) => arr.reduce((s, c) => s + Number(c.monto || 0), 0);
          this.totales.falla       = sum(this.items('falla'));
          this.totales.adelantos   = sum(this.items('adelantos'));
          this.totales.recuperados = sum(this.items('recuperados'));
          this.totales.ventas      = sum(this.items('ventas'));
          this.totales.desembolso  = sum(this.items('desembolso'));
          this.totales.general     = this.totales.falla + this.totales.adelantos + this.totales.recuperados + this.totales.ventas + this.totales.desembolso;
        },

        // ===== Botones (Pago / Historial) =====
        pago(cte, key) {
          // TODO: abrir flujo de pago/recibo
          alert(`💵 Pago - ${cte.nombre} (${key})`);
        },
        historial(cte) {
          // TODO: navegar a historial
          alert(`🧾 Historial - ${cte.nombre}`);
        },

        // ===== Venta / Desembolso: Continuar / Cancelar =====
        continuar(cte, context) {
          // Muestra confirmación (solo UI)
          alert(`✔ Continuar: ${cte.nombre} en ${context}`);
          // TODO: POST para marcar "continuado/confirmado"
        },
        abrirCancel(cte, context) {
          this.modal.open = true;
          this.modal.cte = cte;
          this.modal.context = context; // 'ventas' | 'desembolso'
          this.modal.motivo = '';
        },
        cerrarModal() {
          this.modal.open = false;
          this.modal.cte = null;
          this.modal.context = null;
          this.modal.motivo = '';
        },
        confirmarCancel() {
          if (!this.modal.motivo.trim()) { alert('Escribe un motivo.'); return; }
          // TODO: POST cancelación con motivo
          alert(`✕ Cancelado: ${this.modal.cte.nombre} en ${this.modal.context}\nMotivo: ${this.modal.motivo}`);
          this.cerrarModal();
        },

        // Envío final del formato
        enviar() {
          // TODO: POST de todo el formulario
          alert('Enviando Formato de Desembolso (prellenado)…');
        }
      }
    }
  </script>
</x-layouts.mobile.mobile-layout>
