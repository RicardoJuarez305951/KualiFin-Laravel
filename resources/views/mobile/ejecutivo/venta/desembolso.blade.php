{{-- resources/views/mobile/ejecutivo/venta/desembolso.blade.php --}}
@php
  use Carbon\Carbon;
  use Faker\Factory as Faker;

  $faker = Faker::create('es_MX');

  /** Helpers faker (solo demo si no vienen datos) */
  $makeList = fn ($n, $min=100, $max=5000) =>
      collect(range(1, $n))->map(fn($i)=>[
        'id'     => $i,
        'fecha'  => Carbon::now()->subDays(mt_rand(0,7))->format('d/m/Y'),
        'nombre' => $faker->name,
        'monto'  => mt_rand($min, $max),
      ])->toArray();

  $role = $role ?? 'ejecutivo';
  $fecha = Carbon::now()->format('d/m/Y');
  $ejecutivo = $ejecutivo ?? ['nombre'=>'Ricardo','apellido_p'=>'Juárez','apellido_m'=>'Ramírez'];
@endphp

<x-layouts.mobile.mobile-layout :title="'Reporte de Desembolso'">
  <div
    x-data="reporteDesembolso()"
    x-init="init()"
    class="p-4 w-full max-w-md mx-auto space-y-5"
  >
    {{-- Encabezado --}}
    <header class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] uppercase text-gray-500">Ejecutivo</p>
          <p class="text-sm font-semibold">
            {{ $ejecutivo['nombre'] }} {{ $ejecutivo['apellido_p'] }} {{ $ejecutivo['apellido_m'] }}
          </p>
        </div>
        <div class="text-right">
          <p class="text-[11px] uppercase text-gray-500">Fecha de Desembolso</p>
          <p class="text-sm font-semibold">{{ $fecha }}</p>
        </div>
      </div>
    </header>

    {{-- ===== BLOQUE: PROMOTORA / FALLO ===== --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
      <h3 class="text-sm font-semibold text-gray-800">Promotora</h3>
      <input type="text" x-model="form.promotora"
             class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm"
             placeholder="Nombre de la promotora">

      <div class="pt-2">
        <div class="flex items-center justify-between">
          <h4 class="text-sm font-semibold">Fallo</h4>
          <button type="button" class="text-xs px-2 py-1 rounded-lg border hover:bg-gray-50"
                  @click="addFallo()">+ agregar</button>
        </div>

        <div class="mt-2 space-y-2">
          <template x-for="(item, idx) in form.fallo" :key="item.uid">
            <div class="grid grid-cols-12 gap-2">
              <input x-model="item.fecha" class="col-span-4 px-2 py-2 border rounded-lg text-xs" placeholder="dd/mm/aaaa">
              <input x-model="item.cliente" class="col-span-5 px-2 py-2 border rounded-lg text-xs" placeholder="Cliente">
              <input x-model.number="item.cantidad" type="number" min="0"
                     class="col-span-3 px-2 py-2 border rounded-lg text-xs text-right" placeholder="$0">
            </div>
          </template>
        </div>

        <div class="mt-3 text-right text-xs text-gray-600">
          <span class="font-semibold">Total Fallo: </span>
          <span x-text="money(totales.fallo)"></span>
        </div>
      </div>
    </section>

    {{-- ===== BLOQUE: PRÉSTAMO ===== --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-semibold">Préstamo</h4>
        <button type="button" class="text-xs px-2 py-1 rounded-lg border hover:bg-gray-50"
                @click="addPrestamo()">+ agregar</button>
      </div>

      <div class="space-y-2">
        <template x-for="(p, i) in form.prestamos" :key="p.uid">
          <div class="grid grid-cols-12 gap-2">
            <input x-model.number="p.monto" type="number" min="0"
                   class="col-span-4 px-2 py-2 border rounded-lg text-xs text-right" placeholder="$0">
            <input x-model="p.nombre" class="col-span-8 px-2 py-2 border rounded-lg text-xs" placeholder="Nombre">
          </div>
        </template>
      </div>

      <div class="mt-3 text-right text-xs">
        <span class="font-semibold">Total Préstamo: </span>
        <span x-text="money(totales.prestamos)"></span>
      </div>
    </section>

    {{-- ===== BLOQUE: COBRANZA SEMANAL (1–6) ===== --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <h4 class="text-sm font-semibold mb-2">Cobranza semanal</h4>
      <div class="space-y-2">
        <template x-for="(v, i) in form.cobranza" :key="'cob'+i">
          <div class="flex items-center gap-2">
            <span class="w-6 text-xs text-gray-500" x-text="i+1"></span>
            <input x-model.number="form.cobranza[i]" type="number" min="0"
                   class="flex-1 px-2 py-2 border rounded-lg text-xs text-right" placeholder="$0">
          </div>
        </template>
      </div>
      <div class="mt-3 text-right text-xs">
        <span class="font-semibold">Total Cobranza: </span>
        <span x-text="money(totales.cobranza)"></span>
      </div>
    </section>

    {{-- ===== BLOQUE: SEMANA DE VENTA / SUPERVISOR / ADELANTOS vs RECUPERACIÓN ===== --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <div class="grid grid-cols-1 gap-2">
        <div>
          <p class="text-[11px] uppercase text-gray-500">Semana de venta</p>
          <input x-model="form.semana_venta" type="text"
                 class="w-full px-3 py-2 border rounded-xl text-sm" placeholder="Ej. 34/2025">
        </div>
        <div>
          <p class="text-[11px] uppercase text-gray-500">Supervisor</p>
          <input x-model="form.supervisor" type="text"
                 class="w-full px-3 py-2 border rounded-xl text-sm" placeholder="Nombre del supervisor">
        </div>
      </div>

      <div class="mt-3 grid grid-cols-2 gap-3">
        <div>
          <div class="flex items-center justify-between mb-1">
            <h5 class="text-xs font-semibold">Adelantos</h5>
            <button type="button" class="text-[11px] px-2 py-0.5 rounded border"
                    @click="addAdelanto()">+ agregar</button>
          </div>
          <template x-for="(a, i) in form.adelantos" :key="a.uid">
            <div class="mb-1 flex items-center gap-2">
              <span class="w-5 text-[11px]" x-text="i+1"></span>
              <input x-model.number="a.monto" type="number" min="0"
                     class="flex-1 px-2 py-1.5 border rounded-lg text-[11px] text-right" placeholder="$0">
            </div>
          </template>
          <p class="mt-2 text-right text-[11px] text-gray-700">
            <span class="font-semibold">Total: </span><span x-text="money(totales.adelantos)"></span>
          </p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <h5 class="text-xs font-semibold">Recuperación</h5>
            <button type="button" class="text-[11px] px-2 py-0.5 rounded border"
                    @click="addRecuperacion()">+ agregar</button>
          </div>
          <template x-for="(r, i) in form.recuperacion" :key="r.uid">
            <div class="mb-1 flex items-center gap-2">
              <span class="w-5 text-[11px]" x-text="i+1"></span>
              <input x-model.number="r.monto" type="number" min="0"
                     class="flex-1 px-2 py-1.5 border rounded-lg text-[11px] text-right" placeholder="$0">
            </div>
          </template>
          <p class="mt-2 text-right text-[11px] text-gray-700">
            <span class="font-semibold">Total: </span><span x-text="money(totales.recuperacion)"></span>
          </p>
        </div>
      </div>
    </section>

    {{-- ===== BLOQUE: DESEMBOLSO REAL ===== --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-semibold">Desembolso real</h4>
        <button type="button" class="text-xs px-2 py-1 rounded-lg border hover:bg-gray-50"
                @click="addDesembolso()">+ agregar</button>
      </div>
      <template x-for="(d, i) in form.desembolso" :key="d.uid">
        <div class="mb-1 grid grid-cols-12 gap-2">
          <span class="col-span-1 text-[11px] text-gray-500 flex items-center justify-center" x-text="i+1"></span>
          <input x-model.number="d.monto" type="number" min="0"
                 class="col-span-11 px-2 py-2 border rounded-lg text-xs text-right" placeholder="$0">
        </div>
      </template>
      <div class="mt-2 text-right text-xs">
        <span class="font-semibold">Total Desembolso: </span>
        <span x-text="money(totales.desembolso)"></span>
      </div>
    </section>

    {{-- ===== BLOQUE: RECREDITOS ===== --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-semibold">Recréditos</h4>
        <button type="button" class="text-xs px-2 py-1 rounded-lg border hover:bg-gray-50"
                @click="addRecredito()">+ agregar</button>
      </div>
      <template x-for="(rc, i) in form.recreditos" :key="rc.uid">
        <div class="mb-1 flex items-center gap-2">
          <span class="w-5 text-[11px]" x-text="i+1"></span>
          <input x-model.number="rc.monto" type="number" min="0"
                 class="flex-1 px-2 py-2 border rounded-lg text-xs text-right" placeholder="$0">
        </div>
      </template>
      <div class="mt-2 text-right text-xs">
        <span class="font-semibold">Total Recréditos: </span>
        <span x-text="money(totales.recreditos)"></span>
      </div>
    </section>

    {{-- ===== BLOQUE: RESUMENES (como en el papel) ===== --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <h4 class="text-sm font-semibold mb-2">Sumatorias</h4>
      <div class="space-y-2 text-sm">
        <div class="flex items-center justify-between"><span>Cartera real (+)</span><span x-text="money(form.cartera_real)"></span></div>
        <div class="flex items-center justify-between"><span>Fallo (-)</span><span x-text="money(totales.fallo)"></span></div>
        <div class="flex items-center justify-between"><span>Préstamo (-)</span><span x-text="money(totales.prestamos)"></span></div>
        <div class="flex items-center justify-between"><span>Recuperado (+)</span><span x-text="money(totales.recuperacion)"></span></div>
        <div class="flex items-center justify-between"><span>Adelantos (+)</span><span x-text="money(totales.adelantos)"></span></div>
        <div class="flex items-center justify-between"><span>Recréditos (+)</span><span x-text="money(totales.recreditos)"></span></div>
        <hr>
        <div class="flex items-center justify-between font-semibold">
          <span>Total lado izquierdo</span>
          <span x-text="money(totales.izquierdo)"></span>
        </div>
      </div>
    </section>

    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <h4 class="text-sm font-semibold mb-2">Cierres</h4>
      <div class="space-y-2 text-sm">
        <div class="flex items-center justify-between"><span>Fondo de ahorro (+)</span><input x-model.number="form.fondo_ahorro" type="number" class="w-32 px-2 py-1.5 border rounded-lg text-right text-xs"></div>
        <div class="flex items-center justify-between"><span>Comisiones PROM (-)</span><input x-model.number="form.comisiones_prom" type="number" class="w-32 px-2 py-1.5 border rounded-lg text-right text-xs"></div>
        <div class="flex items-center justify-between"><span>Comisiones SUPERV (-)</span><input x-model.number="form.comisiones_superv" type="number" class="w-32 px-2 py-1.5 border rounded-lg text-right text-xs"></div>
        <div class="flex items-center justify-between"><span>Otros (tarjeta, multa, etc.) (+)</span><input x-model.number="form.otros" type="number" class="w-32 px-2 py-1.5 border rounded-lg text-right text-xs"></div>
        <div class="flex items-center justify-between"><span>Inversión (+)</span><input x-model.number="form.inversion" type="number" class="w-32 px-2 py-1.5 border rounded-lg text-right text-xs"></div>
        <hr>
        <div class="flex items-center justify-between font-semibold">
          <span>Total</span>
          <span x-text="money(totales.totalFinal)"></span>
        </div>
      </div>
    </section>

    {{-- Firmas --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <h4 class="text-sm font-semibold mb-3">Firmas</h4>
      <div class="grid grid-cols-1 gap-6">
        <div class="text-center">
          <div class="h-12 border-b"></div>
          <p class="mt-1 text-[11px] text-gray-500">Nombre y Firma Supervisor</p>
        </div>
        <div class="text-center">
          <div class="h-12 border-b"></div>
          <p class="mt-1 text-[11px] text-gray-500">Nombre y Firma Promotora</p>
        </div>
        <div class="text-center">
          <div class="h-12 border-b"></div>
          <p class="mt-1 text-[11px] text-gray-500">Nombre y Firma del Validador</p>
        </div>
      </div>
    </section>

    {{-- Acciones --}}
    <section class="grid grid-cols-2 gap-3">
      <a href="{{ route('mobile.index') }}"
         class="text-center px-4 py-3 rounded-2xl border border-gray-300 bg-white hover:bg-gray-50">Regresar</a>
      <button type="button"
              class="px-4 py-3 rounded-2xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700"
              @click="submit()">
        Continuar
      </button>
    </section>
  </div>

  {{-- ===== Alpine ===== --}}
  <script>
    function uid(){ return Math.random().toString(36).slice(2); }

    function reporteDesembolso(){
      return {
        form: {
          promotora: '',
          semana_venta: '',
          supervisor: '',
          cartera_real: 0,
          // Secciones
          fallo: [
            {uid: uid(), fecha: '{{ now()->subDays(3)->format('d/m/Y') }}', cliente: '{{$faker->name}}', cantidad: 1200},
            {uid: uid(), fecha: '{{ now()->subDays(1)->format('d/m/Y') }}', cliente: '{{$faker->name}}', cantidad: 800},
          ],
          prestamos: [
            {uid: uid(), monto: 3000, nombre: '{{$faker->name}}'},
            {uid: uid(), monto: 1500, nombre: '{{$faker->name}}'},
          ],
          cobranza: [0,0,0,0,0,0],      // 1..6
          adelantos: [{uid: uid(), monto: 500},{uid: uid(), monto: 400}],
          recuperacion: [{uid: uid(), monto: 700},{uid: uid(), monto: 350}],
          desembolso: [{uid: uid(), monto: 2000},{uid: uid(), monto: 1800}],
          recreditos: [{uid: uid(), monto: 900}],
          // cierres
          fondo_ahorro: 0,
          comisiones_prom: 0,
          comisiones_superv: 0,
          otros: 0,
          inversion: 0,
        },
        totales: {
          fallo: 0, prestamos: 0, cobranza: 0,
          adelantos: 0, recuperacion: 0, desembolso: 0, recreditos: 0,
          izquierdo: 0, totalFinal: 0
        },

        init(){ this.recalc(); this.$watch('form', ()=>this.recalc(), {deep: true}); },

        // Adders
        addFallo(){ this.form.fallo.push({uid: uid(), fecha: '', cliente:'', cantidad:0}); },
        addPrestamo(){ this.form.prestamos.push({uid: uid(), monto:0, nombre:''}); },
        addAdelanto(){ this.form.adelantos.push({uid: uid(), monto:0}); },
        addRecuperacion(){ this.form.recuperacion.push({uid: uid(), monto:0}); },
        addDesembolso(){ this.form.desembolso.push({uid: uid(), monto:0}); },
        addRecredito(){ this.form.recreditos.push({uid: uid(), monto:0}); },

        // Utils
        sum(arr, key){ return (arr||[]).reduce((s, it)=> s + Number((key? it[key] : it) || 0), 0); },
        money(v){ return '$' + Number(v||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); },

        recalc(){
          this.totales.fallo        = this.sum(this.form.fallo, 'cantidad');
          this.totales.prestamos    = this.sum(this.form.prestamos, 'monto');
          this.totales.cobranza     = this.sum(this.form.cobranza);
          this.totales.adelantos    = this.sum(this.form.adelantos, 'monto');
          this.totales.recuperacion = this.sum(this.form.recuperacion, 'monto');
          this.totales.desembolso   = this.sum(this.form.desembolso, 'monto');
          this.totales.recreditos   = this.sum(this.form.recreditos, 'monto');

          // Total del lado izquierdo (papel): cartera_real (+) - fallo (-) - préstamo (-)
          // + recuperado (+) + adelantos (+) + recreditos (+)
          this.totales.izquierdo =
            Number(this.form.cartera_real||0)
            - this.totales.fallo
            - this.totales.prestamos
            + this.totales.recuperacion
            + this.totales.adelantos
            + this.totales.recreditos;

          // Total final (lado derecho) = desembolso + recreditos + fondo_ahorro
          // - comisiones prom - comisiones superv + otros + inversion
          this.totales.totalFinal =
            this.totales.desembolso
            + this.totales.recreditos
            + Number(this.form.fondo_ahorro||0)
            - Number(this.form.comisiones_prom||0)
            - Number(this.form.comisiones_superv||0)
            + Number(this.form.otros||0)
            + Number(this.form.inversion||0);
        },

        submit(){
          // Aquí harías un POST vía fetch/axios. Por ahora solo demo:
          alert('✅ Reporte de desembolso listo para enviar.\nTotal: ' + this.money(this.totales.totalFinal));
        }
      }
    }
  </script>
</x-layouts.mobile.mobile-layout>
