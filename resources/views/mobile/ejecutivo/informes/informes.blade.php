{{-- resources/views/mobile/ejecutivo/informes.blade.php --}}
@php
  use Faker\Factory as Faker;
  $faker = Faker::create('es_MX');

  // ====== Datos base del ejecutivo (o los que vengan del controlador) ======
  $role       = $role       ?? 'ejecutivo';
  $nombre     = $nombre     ?? 'Paul';
  $apellido_p = $apellido_p ?? 'Castillo';

  // ====== Generación de datos Faker ======
  $nSupervisores = 3;
  $supervisores  = [];
  for ($i=0; $i<$nSupervisores; $i++) {
      $supNombre = $faker->firstName().' '.$faker->lastName();
      $nProms = $faker->numberBetween(3,6);
      $proms = [];
      for ($j=0; $j<$nProms; $j++) {
          $venta     = $faker->numberBetween(40_000, 180_000);
          $objetivo  = $faker->numberBetween(120_000, 240_000);
          $cumpl     = min(100, round(($venta / max(1,$objetivo))*100));
          $cobranza  = $faker->numberBetween(70, 98);
          $falla     = $faker->numberBetween(0, 20);
          $fallaAc   = min(100, $falla + $faker->numberBetween(0, 10));
          $rq        = $faker->numberBetween(60, 99);
          $debeOP    = $faker->numberBetween(5_000, 40_000);
          $debePag   = $faker->numberBetween(3_000, 30_000);
          $crec      = $faker->numberBetween(-5, 25); // %
          $proms[] = [
              'nombre'      => $faker->firstName().' '.$faker->lastName(),
              'venta'       => $venta,
              'objetivo'    => $objetivo,
              'cumpl'       => $cumpl,
              'cobranza'    => $cobranza,
              'falla'       => $falla,
              'falla_ac'    => $fallaAc,
              'rq'          => $rq,
              'debe_op'     => $debeOP,
              'debe_pag'    => $debePag,
              'crec'        => $crec,
          ];
      }

      // agregados del supervisor (agregados simples sobre sus promotores)
      $ventaTot   = array_sum(array_column($proms,'venta'));
      $objTot     = array_sum(array_column($proms,'objetivo'));
      $cumplTot   = $objTot ? round(($ventaTot/$objTot)*100) : 0;
      $cobAvg     = round(array_sum(array_column($proms,'cobranza'))/count($proms));
      $fallaAvg   = round(array_sum(array_column($proms,'falla'))/count($proms));
      $fallaAcAvg = round(array_sum(array_column($proms,'falla_ac'))/count($proms));
      $rqAvg      = round(array_sum(array_column($proms,'rq'))/count($proms));
      $debeOPTot  = array_sum(array_column($proms,'debe_op'));
      $debePagTot = array_sum(array_column($proms,'debe_pag'));
      $crecAvg    = round(array_sum(array_column($proms,'crec'))/count($proms));

      $supervisores[] = [
          'nombre'      => $supNombre,
          'venta'       => $ventaTot,
          'objetivo'    => $objTot,
          'cumpl'       => $cumplTot,
          'cobranza'    => $cobAvg,
          'falla'       => $fallaAvg,
          'falla_ac'    => $fallaAcAvg,
          'rq'          => $rqAvg,
          'debe_op'     => $debeOPTot,
          'debe_pag'    => $debePagTot,
          'crec'        => $crecAvg,
          'promotores'  => $proms,
      ];
  }

  // Helper formateo $
  $money = fn($v) => '$'.number_format((float)$v, 2, '.', ',');
@endphp

<x-layouts.mobile.mobile-layout title="Informes del Ejecutivo">
  <div
    x-data="{
      vista: 'gerencia',            // gerencia | supervisor | promotor
      orden: 'cobranza',            // cobranza | falla | rq | debe_op | debe_pag | cumpl | crec
      supervisorIdx: 0,
      periodo: 'semana',            // semana | ejercicio
      mostrar: 'todos',             // todos | ejecutivo
      get listaSupervisores(){ return {{ json_encode(array_map(fn($s)=>$s['nombre'],$supervisores)) }}; },
    }"
    class="p-4 w-full max-w-md mx-auto space-y-5"
  >
    {{-- Encabezado --}}
    <header class="space-y-1">
      <h1 class="text-xl font-extrabold text-gray-900">PANTALLA INFORMES</h1>
      <p class="text-sm text-gray-600">
        Ejec. <span class="font-semibold">{{ $nombre }} {{ $apellido_p }}</span>
      </p>
    </header>

    {{-- Controles: Vistas y Orden --}}
    <section class="grid grid-cols-2 gap-3">
      <div class="rounded-2xl border border-gray-200 bg-white p-3 space-y-2">
        <h2 class="text-xs font-bold uppercase text-gray-700 tracking-wide">Vistas (del ejecutivo)</h2>
        <div class="flex flex-wrap gap-2">
          <button @click="vista='gerencia'"
                  :class="vista==='gerencia' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                  class="px-3 py-1.5 rounded-lg text-xs font-semibold">Gerencia actual</button>
          <button @click="vista='supervisor'"
                  :class="vista==='supervisor' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                  class="px-3 py-1.5 rounded-lg text-xs font-semibold">Supervisor actual</button>
          <button @click="vista='promotor'"
                  :class="vista==='promotor' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                  class="px-3 py-1.5 rounded-lg text-xs font-semibold">Promotor actual</button>
        </div>

        <div class="grid grid-cols-3 gap-2 pt-2">
          <div class="rounded-xl bg-gray-50 p-2">
            <p class="text-[10px] text-gray-500">%</p>
            <p class="text-[11px] font-semibold text-gray-800">Crecimiento</p>
          </div>
          <div class="rounded-xl bg-gray-50 p-2">
            <p class="text-[10px] text-gray-500">%</p>
            <p class="text-[11px] font-semibold text-gray-800">Venta</p>
          </div>
          <div class="rounded-xl bg-gray-50 p-2">
            <p class="text-[10px] text-gray-500">Objetivo</p>
            <p class="text-[11px] font-semibold text-gray-800">% cumplimiento</p>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-3 space-y-2">
        <h2 class="text-xs font-bold uppercase text-gray-700 tracking-wide">Orden</h2>
        <div class="grid grid-cols-2 gap-2">
          <button @click="orden='debe_op'"  :class="orden==='debe_op'  ? 'bg-rose-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-2 py-1.5 rounded-lg text-xs font-semibold">Debe OP</button>
          <button @click="orden='debe_pag'" :class="orden==='debe_pag' ? 'bg-rose-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-2 py-1.5 rounded-lg text-xs font-semibold">Debe pag.</button>
          <button @click="orden='cobranza'" :class="orden==='cobranza' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-2 py-1.5 rounded-lg text-xs font-semibold">Cobranza %</button>
          <button @click="orden='falla'"    :class="orden==='falla'    ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-2 py-1.5 rounded-lg text-xs font-semibold">Falla y %</button>
          <button @click="orden='falla_ac'" :class="orden==='falla_ac' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-2 py-1.5 rounded-lg text-xs font-semibold">Falla acumulada</button>
          <button @click="orden='rq'"       :class="orden==='rq'       ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-2 py-1.5 rounded-lg text-xs font-semibold">RQ y %</button>
          <button @click="orden='cumpl'"    :class="orden==='cumpl'    ? 'bg-sky-600 text-white'    : 'bg-gray-100 text-gray-700'" class="px-2 py-1.5 rounded-lg text-xs font-semibold">Cumplimiento %</button>
          <button @click="orden='crec'"     :class="orden==='crec'     ? 'bg-slate-800 text-white'  : 'bg-gray-100 text-gray-700'" class="px-2 py-1.5 rounded-lg text-xs font-semibold">% Crecimiento</button>
        </div>
      </div>
    </section>

    {{-- Selector de Supervisor (para vistas supervisor/promotor) --}}
    <template x-if="vista !== 'gerencia'">
      <div class="rounded-2xl border border-gray-200 bg-white p-3 space-y-2">
        <label class="text-xs font-semibold text-gray-700">Elegir Supervisor</label>
        <select x-model.number="supervisorIdx" class="w-full rounded-xl border-gray-300 text-sm">
          @foreach($supervisores as $idx => $s)
            <option value="{{ $idx }}">{{ $s['nombre'] }}</option>
          @endforeach
        </select>
      </div>
    </template>

    {{-- Tarjetas: info actual (agrupación y subtítulos según vista) --}}
    <section class="space-y-3">
      {{-- Vista: GERENCIA (lista de supervisores como tarjetas) --}}
      <template x-if="vista==='gerencia'">
        <div class="space-y-3">
          @php
            // Ordenamiento simple en PHP a partir de la variable 'orden' vía request fallback
            $orderKey = request('orden_key','cobranza');
            $ordered = $supervisores;
            usort($ordered, fn($a,$b)=>($b[$orderKey] ?? 0) <=> ($a[$orderKey] ?? 0));
          @endphp

          @foreach($ordered as $s)
            <article class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Supervisor: {{ $s['nombre'] }}</h3>
                <p class="text-[12px] text-gray-500">Promotores: {{ count($s['promotores']) }}</p>
              </div>
              <div class="p-4 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-gray-50 p-3">
                  <p class="text-[11px] text-gray-500">Venta</p>
                  <p class="font-bold">{{ $money($s['venta']) }}</p>
                  <p class="text-[11px] text-gray-500">Objetivo: {{ $money($s['objetivo']) }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3">
                  <p class="text-[11px] text-gray-500">Cumplimiento</p>
                  <p class="font-bold">{{ $s['cumpl'] }}%</p>
                  <p class="text-[11px] text-gray-500">Crecimiento: {{ $s['crec'] }}%</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3">
                  <p class="text-[11px] text-gray-500">Cobranza</p>
                  <p class="font-bold">{{ $s['cobranza'] }}%</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3">
                  <p class="text-[11px] text-gray-500">Falla / Acumulada</p>
                  <p class="font-bold">{{ $s['falla'] }}% / {{ $s['falla_ac'] }}%</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3">
                  <p class="text-[11px] text-gray-500">RQ</p>
                  <p class="font-bold">{{ $s['rq'] }}%</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3">
                  <p class="text-[11px] text-gray-500">Debe OP / Debe pag.</p>
                  <p class="font-bold">{{ $money($s['debe_op']) }} / {{ $money($s['debe_pag']) }}</p>
                </div>
              </div>
            </article>
          @endforeach
        </div>
      </template>

      {{-- Vista: SUPERVISOR (lista de promotores del supervisor seleccionado) --}}
      <template x-if="vista==='supervisor'">
        <div class="space-y-3">
          @foreach($supervisores as $idx => $s)
            <template x-if="supervisorIdx === {{ $idx }}">
              <div class="space-y-2">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide">
                  Supervisor: {{ $s['nombre'] }}
                </h3>
                @foreach($s['promotores'] as $p)
                  <article class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                      <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $p['nombre'] }}</p>
                        <p class="text-[12px] text-gray-500">Objetivo {{ $money($p['objetivo']) }} · Cumpl {{ $p['cumpl'] }}%</p>
                      </div>
                      <span class="text-[11px] px-2 py-1 rounded-full"
                            style="background: rgba(99,102,241,.12); color:#3730a3">
                        RQ {{ $p['rq'] }}%
                      </span>
                    </div>
                    <div class="p-4 grid grid-cols-2 gap-3 text-sm">
                      <div class="rounded-xl bg-gray-50 p-3">
                        <p class="text-[11px] text-gray-500">Venta</p>
                        <p class="font-bold">{{ $money($p['venta']) }}</p>
                      </div>
                      <div class="rounded-xl bg-gray-50 p-3">
                        <p class="text-[11px] text-gray-500">Cobranza</p>
                        <p class="font-bold">{{ $p['cobranza'] }}%</p>
                      </div>
                      <div class="rounded-xl bg-gray-50 p-3">
                        <p class="text-[11px] text-gray-500">Falla</p>
                        <p class="font-bold">{{ $p['falla'] }}% (Acum {{ $p['falla_ac'] }}%)</p>
                      </div>
                      <div class="rounded-xl bg-gray-50 p-3">
                        <p class="text-[11px] text-gray-500">Debe OP / Debe pag.</p>
                        <p class="font-bold">{{ $money($p['debe_op']) }} / {{ $money($p['debe_pag']) }}</p>
                      </div>
                      <div class="rounded-xl bg-gray-50 p-3 col-span-2">
                        <p class="text-[11px] text-gray-500">% Crecimiento</p>
                        <p class="font-bold">{{ $p['crec'] }}%</p>
                      </div>
                    </div>
                  </article>
                @endforeach
              </div>
            </template>
          @endforeach
        </div>
      </template>

      {{-- Vista: PROMOTOR (se agrupan por supervisor y ANTES va subtítulo con el nombre del supervisor) --}}
      <template x-if="vista==='promotor'">
        <div class="space-y-4">
          @foreach($supervisores as $s)
            <div class="space-y-2">
              <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide">
                Supervisor: {{ $s['nombre'] }}
              </h3>
              @foreach($s['promotores'] as $p)
                <article class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                  <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <div>
                      <p class="text-sm font-semibold text-gray-900">{{ $p['nombre'] }}</p>
                      <p class="text-[12px] text-gray-500">Obj {{ $money($p['objetivo']) }} · Cumpl {{ $p['cumpl'] }}%</p>
                    </div>
                    <span class="text-[11px] px-2 py-1 rounded-full"
                          style="background: rgba(16,185,129,.12); color:#065f46">
                      Cobranza {{ $p['cobranza'] }}%
                    </span>
                  </div>
                  <div class="p-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-gray-50 p-3">
                      <p class="text-[11px] text-gray-500">Venta</p>
                      <p class="font-bold">{{ $money($p['venta']) }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3">
                      <p class="text-[11px] text-gray-500">Falla</p>
                      <p class="font-bold">{{ $p['falla'] }}% (Acum {{ $p['falla_ac'] }}%)</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3">
                      <p class="text-[11px] text-gray-500">RQ</p>
                      <p class="font-bold">{{ $p['rq'] }}%</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3">
                      <p class="text-[11px] text-gray-500">Debe OP / Debe pag.</p>
                      <p class="font-bold">{{ $money($p['debe_op']) }} / {{ $money($p['debe_pag']) }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3 col-span-2">
                      <p class="text-[11px] text-gray-500">% Crecimiento</p>
                      <p class="font-bold">{{ $p['crec'] }}%</p>
                    </div>
                  </div>
                </article>
              @endforeach
            </div>
          @endforeach
        </div>
      </template>
    </section>

    {{-- Indicaciones del boceto (nota de diseño) --}}
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-3">
      <p class="text-[12px] text-gray-600">
        Se muestran tarjetas con info actual. En la vista <span class="font-semibold">promotor</span>,
        se incluye un subtítulo con <span class="font-semibold">nombre del supervisor</span>
        antes de todos los promotores.
      </p>
    </div>

    {{-- Históricos / Filtros --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-3 space-y-3">
      <div class="flex items-center justify-between">
        <h2 class="text-xs font-bold uppercase text-gray-700 tracking-wide">Históricos</h2>
        <button class="px-3 py-1.5 rounded-lg bg-gray-900 text-white text-xs font-semibold">Botón</button>
      </div>

      <div class="grid grid-cols-3 gap-2">
        <button class="w-full px-2 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700">General</button>
        <button class="w-full px-2 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700">Elegir Supervisor</button>
        <button class="w-full px-2 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700">Elegir Promotor</button>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-[11px] text-gray-600">Opciones</label>
          <select x-model="periodo" class="w-full rounded-xl border-gray-300 text-sm">
            <option value="semana">Por semana</option>
            <option value="ejercicio">Por ejercicio</option>
          </select>
        </div>
        <div>
          <label class="text-[11px] text-gray-600">Mostrar</label>
          <select x-model="mostrar" class="w-full rounded-xl border-gray-300 text-sm">
            <option value="todos">Todos</option>
            <option value="ejecutivo">Solo del ejecutivo</option>
          </select>
        </div>
      </div>
    </section>

    {{-- Acciones inferiores --}}
    <section class="grid grid-cols-3 gap-3">
      <a href="{{ route('mobile.index') }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 bg-white">
        Regresar
      </a>
      <a href="{{ url()->current() }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600">
        Actualizar
      </a>
      <a href="{{route("mobile.$role.reportes")}}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-sm font-semibold text-white bg-gray-900">
        Reporte
      </a>
    </section>
  </div>
</x-layouts.mobile.mobile-layout>
