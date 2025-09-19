{{-- resources/views/mobile/ejecutivo/reportes.blade.php --}}
@php
  use Faker\Factory as Faker;
  $faker = Faker::create('es_MX');

  // ===== Datos base simulados =====
  // Jerarquía: Ejecutivo -> Supervisores -> Promotores
  $ejecutivo   = ['nombre' => $nombre ?? 'Paul', 'apellido_p' => $apellido_p ?? 'Castillo'];
  $supervisores = [];
  $NUM_SUP = 3;

  // Generamos dataset por periodos: actual, semanal, mensual, ejercicio
  $periodos = ['actual','semanal','mensual','ejercicio'];

  for ($i = 0; $i < $NUM_SUP; $i++) {
    $supNombre = $faker->firstName().' '.$faker->lastName();
    $promotores = [];
    $NUM_P = $faker->numberBetween(4,6);

    for ($j=0; $j<$NUM_P; $j++) {
      $baseVenta = $faker->numberBetween(60_000, 200_000);
      $objetivo  = $faker->numberBetween(120_000, 250_000);
      $promPeriodo = [];

      foreach ($periodos as $per) {
        // variaciones por periodo
        $factor = match($per){
          'actual'    => 0.20,
          'semanal'   => 0.60,
          'mensual'   => 1.00,
          'ejercicio' => 4.00,
          default     => 1.0
        };
        $venta      = (int) round($baseVenta * $factor * $faker->randomFloat(2, 0.8, 1.2));
        $cobranza   = $faker->numberBetween(70, 98);
        $falla      = $faker->numberBetween(0, 20);
        $carteraV   = $faker->numberBetween(5_000, 50_000) * $factor;
        $ingresosR  = (int) round($venta * $faker->randomFloat(2, 0.85, 1.05));
        $recreditos = $faker->numberBetween(5, 35);
        $clientesN  = max(0, $recreditos - $faker->numberBetween(0, 10));
        $cancel     = $faker->randomFloat(2, 0, 10); // %
        $reinc      = $faker->numberBetween(0, 6);   // # fallas recurrentes
        $product    = $faker->numberBetween(60, 120); // índice arbitrario
        $zona       = $faker->randomElement(['Norte','Centro','Sur','Este','Oeste']);
        $tasa       = $faker->randomElement([2.5, 3.0, 3.5, 4.0]); // % mensual

        $promPeriodo[$per] = [
          'venta'        => $venta,
          'objetivo'     => $objetivo,
          'cumpl'        => min(100, (int)round(($venta / max(1,$objetivo))*100)),
          'cobranza'     => $cobranza,
          'falla'        => $falla,
          'cartera_v'    => (int)$carteraV,
          'ingresos_reg' => $ingresosR,
          'clientes_nue' => $clientesN,
          'recreditos'  => $recreditos,
          'cancel'       => $cancel,
          'reinc'        => $reinc,
          'product'      => $product,
          'zona'         => $zona,
          'tasa'         => $tasa,
          // métricas derivadas
          'seguimiento_vencida' => $cobranza - min(30, (int)($carteraV/10_000)), // índice simple
          'fidelizacion' => $faker->numberBetween(60, 95),
          'retencion'    => $faker->numberBetween(60, 95),
        ];
      }

      $promotores[] = [
        'nombre'   => $faker->firstName().' '.$faker->lastName(),
        'periodos' => $promPeriodo,
      ];
    }

    $supervisores[] = [
      'nombre'     => $supNombre,
      'promotores' => $promotores
    ];
  }

  $money = fn($v) => '$'.number_format((float)$v, 2, '.', ',');
@endphp

<x-layouts.mobile.mobile-layout title="Reportes">
  <div
    x-data="reportes()"
    x-init="init()"
    class="p-4 w-full max-w-md mx-auto space-y-5"
  >
    <!-- Header -->
    <header class="space-y-1">
      <h1 class="text-xl font-extrabold text-gray-900">REPORTES</h1>
      <p class="text-sm text-gray-600">
        Ejecutivo: <span class="font-semibold">{{ $ejecutivo['nombre'] }} {{ $ejecutivo['apellido_p'] }}</span>
      </p>
    </header>

    <!-- Controles -->
    <section class="rounded-2xl border border-gray-200 bg-white p-3 space-y-3">
      <div class="grid grid-cols-1 gap-3">
        <div>
          <label class="text-xs font-semibold text-gray-700">Concepto</label>
          <select x-model="concepto" class="w-full rounded-xl border-gray-300 text-sm">
            <option value="cob_vs_falla">Cobranza vs Falla</option>
            <option value="cob_vs_cartera">Cobranza vs Cartera Vencida</option>
            <option value="obj_vs_ventas">Objetivo vs Ventas Realizadas</option>
            <option value="obj_vs_ingresos">Objetivos vs Ingresos Registrados</option>
            <option value="nuevos_vs_recreditos">Clientes nuevos vs Recréditos</option>
            <option value="tasa_cancel">Tasa de Cancelaciones</option>
            <option value="reincidencias">Clientes con más fallas/reincidencias</option>
            <option value="productividad">Productividad (sup y prom)</option>
            <option value="rend_zona">Rendimiento por zona</option>
            <option value="rend_tasa">Rendimiento por tasa</option>
            <option value="seguimiento_vencida">Índice de seguimiento cartera vencida</option>
            <option value="fidel_retencion">Fidelización y Retención</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-semibold text-gray-700">Período</label>
            <select x-model="periodo" class="w-full rounded-xl border-gray-300 text-sm">
              <option value="actual">Actual</option>
              <option value="semanal">Semanal</option>
              <option value="mensual">Mensual</option>
              <option value="ejercicio">Ejercicio</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-semibold text-gray-700">Filtro</label>
            <select x-model="filtro" class="w-full rounded-xl border-gray-300 text-sm">
              <option value="promotores">Promotores</option>
              <option value="supervisores">Supervisores</option>
              <option value="ejecutivo">Ejecutivo</option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 pt-1">
        <button @click="generar()"
                class="px-3 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600">
          Generar reporte
        </button>
      </div>
    </section>

    <!-- Resumen (tarjetas) -->
    <template x-if="resumen">
      <section class="grid grid-cols-2 gap-3">
        <template x-for="card in resumen" :key="card.label">
          <div class="rounded-2xl border border-gray-200 bg-white p-3">
            <p class="text-[11px] text-gray-500" x-text="card.label"></p>
            <p class="text-lg font-bold" x-text="card.valor"></p>
            <p class="text-[11px] text-gray-500" x-text="card.sub || ''"></p>
          </div>
        </template>
      </section>
    </template>

    <!-- Tabla de resultados -->
    <section class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-900">Resultados</h2>
        <div class="flex gap-2">
          <button @click="ordenarPor='principal'; aplicarOrden()"
                  class="px-2 py-1.5 rounded-lg text-xs bg-gray-100">Ordenar</button>
          <button @click="exportar()"
                  class="px-2 py-1.5 rounded-lg text-xs text-white bg-gray-900">Exportar CSV</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="text-left px-4 py-2">Entidad</th>
              <th class="text-right px-4 py-2" x-text="col1"></th>
              <th class="text-right px-4 py-2" x-text="col2"></th>
              <th class="text-right px-4 py-2" x-text="col3"></th>
            </tr>
          </thead>
          <tbody>
            <template x-if="rows.length === 0">
              <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Genera un reporte para ver datos</td></tr>
            </template>

            <template x-for="r in rows" :key="r.id">
              <tr class="border-t">
                <td class="px-4 py-2 font-medium text-gray-900" x-text="r.nombre"></td>
                <td class="px-4 py-2 text-right" x-text="r.v1"></td>
                <td class="px-4 py-2 text-right" x-text="r.v2"></td>
                <td class="px-4 py-2 text-right" x-text="r.v3 || '-'"></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Acciones inferiores -->
    <section class="grid grid-cols-3 gap-3">
      <a href="{{ route('mobile.index') }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 bg-white">
        Regresar
      </a>
      <a href="{{ url()->current() }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600">
        Actualizar
      </a>
      <a href="{{ url()->current() }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-sm font-semibold text-white bg-gray-900">
        Reporte
      </a>
    </section>
  </div>

  {{-- ===== AlpineJS state (inyectamos dataset PHP como JSON) ===== --}}
  <script>
    function reportes(){
      const DATA = @json($supervisores);
      const PERIODOS = @json($periodos);

      // helpers
      const money = (v)=> new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(v);
      const pct   = (v)=> `${Number(v).toFixed(1)}%`;

      function recolectar(filtro, periodo){
        // devuelve array de entidades según filtro, con métrica del periodo:
        if(filtro === 'supervisores'){
          return DATA.map((s,idx)=>{
            // agregamos las métricas del supervisor promediando/aggregate de sus promotores
            const prom = s.promotores.map(p=>p.periodos[periodo]);
            const sum = (k)=> prom.reduce((a,b)=>a + (Number(b[k])||0), 0);
            const avg = (k)=> prom.length? (sum(k)/prom.length):0;
            const venta = sum('venta');
            const objetivo = prom.length? prom[0].objetivo * prom.length : 0;
            return {
              id: 's'+idx,
              nombre: 'Supervisor: '+s.nombre,
              venta,
              objetivo,
              cumpl: objetivo? Math.min(100, Math.round((venta/objetivo)*100)):0,
              cobranza: Math.round(avg('cobranza')),
              falla: Math.round(avg('falla')),
              cartera_v: Math.round(avg('cartera_v')),
              ingresos_reg: Math.round(avg('ingresos_reg')),
              clientes_nue: Math.round(avg('clientes_nue')),
              recreditos: Math.round(avg('recreditos')),
              cancel: avg('cancel'),
              reinc: Math.round(avg('reinc')),
              product: Math.round(avg('product')),
              zona: '—',
              tasa: Math.round(avg('tasa')*10)/10,
              seguimiento_vencida: Math.round(avg('seguimiento_vencida')),
              fidelizacion: Math.round(avg('fidelizacion')),
              retencion: Math.round(avg('retencion')),
            };
          });
        } else if (filtro === 'ejecutivo') {
          // único agregado del ejecutivo a partir de todos los promotores
          const allProm = DATA.flatMap(s=>s.promotores.map(p=>p.periodos[periodo]));
          const sum = (k)=> allProm.reduce((a,b)=>a + (Number(b[k])||0), 0);
          const avg = (k)=> allProm.length? (sum(k)/allProm.length):0;
          const venta = sum('venta');
          const objetivo = allProm.length? allProm[0].objetivo * allProm.length : 0;
          return [{
            id:'e1',
            nombre:'Ejecutivo (agregado)',
            venta,
            objetivo,
            cumpl: objetivo? Math.min(100, Math.round((venta/objetivo)*100)):0,
            cobranza: Math.round(avg('cobranza')),
            falla: Math.round(avg('falla')),
            cartera_v: Math.round(avg('cartera_v')),
            ingresos_reg: Math.round(avg('ingresos_reg')),
            clientes_nue: Math.round(avg('clientes_nue')),
            recreditos: Math.round(avg('recreditos')),
            cancel: avg('cancel'),
            reinc: Math.round(avg('reinc')),
            product: Math.round(avg('product')),
            zona: '—',
            tasa: Math.round(avg('tasa')*10)/10,
            seguimiento_vencida: Math.round(avg('seguimiento_vencida')),
            fidelizacion: Math.round(avg('fidelizacion')),
            retencion: Math.round(avg('retencion')),
          }];
        }
        // promotores (default)
        return DATA.flatMap((s,si)=> s.promotores.map((p,pi)=>{
          const m = p.periodos[periodo];
          return {
            id: `p${si}_${pi}`,
            nombre: p.nombre,
            ...m
          };
        }));
      }

      // Mapeo de columnas por concepto
      const DEFINICIONES = {
        cob_vs_falla:   { col1:'Cobranza %', col2:'Falla %', col3:'—',
                          map:(e)=>({v1:pct(e.cobranza), v2:pct(e.falla), v3:null}),
                          resumen:(arr)=>[
                            {label:'Cobranza prom.', valor:pct(arr.reduce((a,b)=>a+b.cobranza,0)/Math.max(1,arr.length))},
                            {label:'Falla prom.', valor:pct(arr.reduce((a,b)=>a+b.falla,0)/Math.max(1,arr.length))}
                          ],
                          ordenKey:'cobranza' },
        cob_vs_cartera: { col1:'Cobranza %', col2:'Cartera Vencida', col3:'—',
                          map:(e)=>({v1:pct(e.cobranza), v2:money(e.cartera_v), v3:null}),
                          resumen:(arr)=>[
                            {label:'Cobranza prom.', valor:pct(arr.reduce((a,b)=>a+b.cobranza,0)/Math.max(1,arr.length))},
                            {label:'Cartera vencida', valor:money(arr.reduce((a,b)=>a+b.cartera_v,0))}
                          ],
                          ordenKey:'cobranza' },
        obj_vs_ventas:  { col1:'Venta', col2:'Objetivo', col3:'Cumpl %',
                          map:(e)=>({v1:money(e.venta), v2:money(e.objetivo), v3:pct(e.cumpl)}),
                          resumen:(arr)=>[
                            {label:'Venta total', valor:money(arr.reduce((a,b)=>a+b.venta,0))},
                            {label:'Cumpl. prom.', valor:pct(arr.reduce((a,b)=>a+b.cumpl,0)/Math.max(1,arr.length))}
                          ],
                          ordenKey:'venta' },
        obj_vs_ingresos:{ col1:'Ingresos Reg.', col2:'Objetivo', col3:'Cumpl %',
                          map:(e)=>({v1:money(e.ingresos_reg), v2:money(e.objetivo), v3:pct(e.cumpl)}),
                          resumen:(arr)=>[
                            {label:'Ingresos totales', valor:money(arr.reduce((a,b)=>a+b.ingresos_reg,0))},
                            {label:'Cumpl. prom.', valor:pct(arr.reduce((a,b)=>a+b.cumpl,0)/Math.max(1,arr.length))}
                          ],
                          ordenKey:'ingresos_reg' },
        nuevos_vs_recreditos: { col1:'Clientes Nuevos', col2:'Recréditos', col3:'Conversion %',
                          map:(e)=>({v1:e.clientes_nue, v2:e.recreditos, v3: (e.recreditos? pct((e.clientes_nue/e.recreditos)*100):'0%') }),
                          resumen:(arr)=>[
                            {label:'Nuevos', valor:arr.reduce((a,b)=>a+b.clientes_nue,0)},
                            {label:'Recréditos', valor:arr.reduce((a,b)=>a+b.recreditos,0)}
                          ],
                          ordenKey:'clientes_nue' },
        tasa_cancel:    { col1:'Cancelaciones %', col2:'—', col3:'—',
                          map:(e)=>({v1:pct(e.cancel), v2:null, v3:null}),
                          resumen:(arr)=>[
                            {label:'Cancelación prom.', valor:pct(arr.reduce((a,b)=>a+b.cancel,0)/Math.max(1,arr.length))}
                          ],
                          ordenKey:'cancel' },
        reincidencias:  { col1:'Reincidencias (#)', col2:'Falla %', col3:'—',
                          map:(e)=>({v1:e.reinc, v2:pct(e.falla), v3:null}),
                          resumen:(arr)=>[
                            {label:'Reincidencias tot.', valor:arr.reduce((a,b)=>a+b.reinc,0)},
                            {label:'Falla prom.', valor:pct(arr.reduce((a,b)=>a+b.falla,0)/Math.max(1,arr.length))}
                          ],
                          ordenKey:'reinc' },
        productividad:  { col1:'Productividad', col2:'Cobranza %', col3:'Cumpl %',
                          map:(e)=>({v1:e.product, v2:pct(e.cobranza), v3:pct(e.cumpl)}),
                          resumen:(arr)=>[
                            {label:'Prod. prom.', valor:(arr.reduce((a,b)=>a+b.product,0)/Math.max(1,arr.length)).toFixed(0)},
                            {label:'Cobranza prom.', valor:pct(arr.reduce((a,b)=>a+b.cobranza,0)/Math.max(1,arr.length))}
                          ],
                          ordenKey:'product' },
        rend_zona:      { col1:'Zona', col2:'Venta', col3:'Cobranza %',
                          map:(e)=>({v1:e.zona, v2:money(e.venta), v3:pct(e.cobranza)}),
                          resumen:(arr)=>[
                            {label:'Venta total', valor:money(arr.reduce((a,b)=>a+b.venta,0))}
                          ],
                          ordenKey:'venta' },
        rend_tasa:      { col1:'Tasa (%)', col2:'Venta', col3:'Cumpl %',
                          map:(e)=>({v1:`${e.tasa}%`, v2:money(e.venta), v3:pct(e.cumpl)}),
                          resumen:(arr)=>[
                            {label:'Venta total', valor:money(arr.reduce((a,b)=>a+b.venta,0))}
                          ],
                          ordenKey:'venta' },
        seguimiento_vencida: { col1:'Índice Seg.', col2:'Cartera Venc.', col3:'Cobranza %',
                          map:(e)=>({v1:e.seguimiento_vencida, v2:money(e.cartera_v), v3:pct(e.cobranza)}),
                          resumen:(arr)=>[
                            {label:'Índice prom.', valor:(arr.reduce((a,b)=>a+b.seguimiento_vencida,0)/Math.max(1,arr.length)).toFixed(0)},
                            {label:'Cartera vencida', valor:money(arr.reduce((a,b)=>a+b.cartera_v,0))}
                          ],
                          ordenKey:'seguimiento_vencida' },
        fidel_retencion:{ col1:'Fidelización %', col2:'Retención %', col3:'Cobranza %',
                          map:(e)=>({v1:pct(e.fidelizacion), v2:pct(e.retencion), v3:pct(e.cobranza)}),
                          resumen:(arr)=>[
                            {label:'Fidelización prom.', valor:pct(arr.reduce((a,b)=>a+b.fidelizacion,0)/Math.max(1,arr.length))},
                            {label:'Retención prom.', valor:pct(arr.reduce((a,b)=>a+b.retencion,0)/Math.max(1,arr.length))}
                          ],
                          ordenKey:'fidelizacion' },
      };

      return {
        // estado
        concepto: 'cob_vs_falla',
        periodo:  'actual',
        filtro:   'promotores',
        rows: [],
        resumen: null,
        col1: '', col2: '', col3: '—',
        ordenarPor: 'principal',

        init(){ /* nada por ahora */ },

        generar(){
          const def = DEFINICIONES[this.concepto];
          const arr = recolectar(this.filtro, this.periodo);

          // columnas
          this.col1 = def.col1; this.col2 = def.col2; this.col3 = def.col3;

          // filas
          this.rows = arr.map((e,idx)=>{
            const mapped = def.map(e);
            return {
              id: e.id || idx,
              nombre: e.nombre,
              v1: mapped.v1,
              v2: mapped.v2,
              v3: mapped.v3,
              principal: e[def.ordenKey] ?? 0 // para ordenar
            };
          });

          // resumen de tarjetas
          this.resumen = def.resumen(arr);
          this.aplicarOrden();
        },

        aplicarOrden(){
          this.rows.sort((a,b)=> (b[this.ordenarPor] ?? 0) - (a[this.ordenarPor] ?? 0));
        },

        exportar(){
          if(this.rows.length===0){ return; }
          const headers = ['Entidad', this.col1, this.col2, this.col3];
          const csvRows = [headers.join(',')];
          this.rows.forEach(r=>{
            csvRows.push([`"${r.nombre}"`, r.v1, r.v2, r.v3 ?? ''].join(','));
          });
          const blob = new Blob([csvRows.join('\n')], {type:'text/csv;charset=utf-8;'});
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url; a.download = 'reporte.csv';
          document.body.appendChild(a); a.click(); document.body.removeChild(a);
          URL.revokeObjectURL(url);
        },
      }
    }
  </script>
</x-layouts.mobile.mobile-layout>
