{{-- resources/views/mobile/ejecutivo/cartera.blade.php --}}
@php
    use Carbon\Carbon;

    $role       = $role       ?? 'ejecutivo';
    $nombre     = $nombre     ?? '—';
    $apellido_p = $apellido_p ?? '';
    $apellido_m = $apellido_m ?? '';

    $fechaVenta = isset($fechaVenta) && $fechaVenta
        ? ($fechaVenta instanceof Carbon ? $fechaVenta : Carbon::parse($fechaVenta))
        : null;
    $fechaVentaStr = $fechaVenta ? $fechaVenta->format('d/m/Y') : '—';

    $debeOperativo  = (float) ($debeOperativo  ?? 0);
    $debeProyectado = (float) ($debeProyectado ?? 0);
    $fallaReal      = (float) ($fallaReal      ?? 0);
    $cobranza       = (float) ($cobranza       ?? 0);

    $pct = fn($num, $den) => $den > 0 ? max(0, min(100, round(($num / $den) * 100, 2))) : 0;
    $fallaPct    = isset($fallaPct) ? (float) $fallaPct : $pct($fallaReal, $debeProyectado);
    $cobranzaPct = isset($cobranzaPct) ? (float) $cobranzaPct : $pct($cobranza, $debeProyectado);

    $supervisores = collect($supervisores ?? []);

    if (!function_exists('mx_money')) {
        function mx_money($v){ return '$' . number_format((float)$v, 2, '.', ','); }
    }

    $statSimple = function(string $title, string $value, string $sub = '') {
        return <<<HTML
      <div class="rounded-xl border border-gray-100 p-3 shadow-sm">
        <p class="text-[12px] font-semibold text-gray-600">$title</p>
        <p class="text-[15px] font-bold text-gray-900">$value</p>
        {$sub}
      </div>
    HTML;
    };

    $pill = function(string $href, string $text) {
        return '<a href="'.e($href).'"'
             . ' class="inline-flex items-center justify-center text-xs font-bold rounded-full w-7 h-7 bg-blue-600 text-white hover:bg-blue-700"'
             . ' title="'.e($text).'">'.e($text).'</a>';
    };

    $btn = function(string $href, string $text, string $variant = 'primary') {
        $base   = 'inline-flex items-center justify-center rounded-2xl text-sm font-semibold px-3 py-2 shadow';
        $styles = match($variant) {
            'outline' => 'bg-white border border-blue-600 text-blue-700 hover:bg-blue-50',
            'indigo'  => 'bg-indigo-600 text-white hover:bg-indigo-700',
            default   => 'bg-blue-600 text-white hover:bg-blue-700',
        };

        return '<a href="'.e($href).'" class="'.$base.' '.$styles.'">'.e($text).'</a>';
    };
@endphp

<x-layouts.mobile.mobile-layout title="Cartera - Ejecutivo">
  <div x-data="{ showHorarios:false }"
       x-effect="document.body.style.overflow = showHorarios ? 'hidden' : ''"
       class="mx-auto w-[22rem] sm:w-[26rem] p-4 sm:p-6 space-y-6">

    {{-- ===================== div1: Ejecutivo ===================== --}}
    <section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
      <div class="p-5 space-y-1">
        <h2 class="text-base font-bold text-gray-900">Ejecutivo</h2>
        <p class="text-sm text-gray-700 flex flex-wrap gap-1">
          <span class="font-semibold">{{ $nombre }}</span>
          @if($apellido_p !== '')
            <span>{{ $apellido_p }}</span>
          @endif
          @if($apellido_m !== '')
            <span>{{ $apellido_m }}</span>
          @endif
        </p>
      </div>

      {{-- div1.2: Fecha de venta --}}
      <div class="px-5 pb-3">
        <p class="text-sm font-semibold text-gray-700">
          Venta Registrada para fecha: <span class="text-gray-900">{{ $fechaVentaStr }}</span>
        </p>
      </div>

      {{-- div1.3: Tablero 3x2 --}}
      <div class="px-5 pb-5">
        <div class="grid grid-cols-2 gap-3">
          {!! $statSimple('Debe Operativo',  mx_money($debeOperativo)) !!}
          {!! $statSimple('Debe Proyectado', mx_money($debeProyectado)) !!}

          {!! $statSimple('Falla Real',      mx_money($fallaReal)) !!}
          {!! $statSimple('% Falla',         number_format($fallaPct, 2).' %') !!}

          {!! $statSimple('Cobranza',        mx_money($cobranza)) !!}
          {!! $statSimple('% Cobranza',      number_format($cobranzaPct, 2).' %') !!}
        </div>
      </div>
    </section>

    {{-- ===================== div2: Supervisores ===================== --}}
    <section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
      <div class="p-5">
        <h2 class="text-base font-bold text-gray-900 mb-3">Supervisores</h2>

        <div class="space-y-3">
          @forelse($supervisores as $s)
            <div class="rounded-xl border border-gray-100 p-3 shadow-sm">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-gray-900">{{ $s['nombre'] ?? 'Sin nombre' }}</p>
                  <p class="text-[12px] text-gray-600">Fecha: <span class="font-semibold text-gray-800">{{ $s['fecha'] ?? '—' }}</span></p>
                  <p class="text-[12px] text-gray-600">Horario: <span class="font-semibold text-gray-800">{{ $s['horario'] ?? '—' }}</span></p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  {{-- Botón detalles "D" --}}
                  {!! $pill(!empty($s['id']) ? route('mobile.supervisor.venta', array_merge($supervisorContextQuery ?? [], ['supervisor' => $s['id']])) : '#', 'D') !!}
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2 mt-3 text-[12px]">
                <div class="rounded-lg bg-gray-50 p-2">
                  <p class="text-gray-600">Venta Registrada</p>
                  <p class="font-bold text-gray-900">{{ mx_money($s['ventaRegistrada'] ?? 0) }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-2">
                  <p class="text-gray-600">Debe Operativo</p>
                  <p class="font-bold text-gray-900">{{ mx_money($s['debeOperativo'] ?? 0) }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-2">
                  <p class="text-gray-600">Debe Proyectado</p>
                  <p class="font-bold text-gray-900">{{ mx_money($s['debeProyectado'] ?? 0) }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-2">
                  <p class="text-gray-600">Cobranza %</p>
                  <p class="font-bold text-gray-900">{{ number_format($s['cobranzaPct'] ?? 0, 2) }}%</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-2">
                  <p class="text-gray-600">Falla %</p>
                  <p class="font-bold text-gray-900">{{ number_format($s['fallaPct'] ?? 0, 2) }}%</p>
                </div>
              </div>
            </div>
          @empty
            <p class="text-sm text-gray-500">No hay supervisores registrados.</p>
          @endforelse
        </div>
      </div>
    </section>

    {{-- ===================== div3: Botones ===================== --}}
    <section class="grid grid-cols-3 gap-3">
      {!! $btn(route('mobile.index', $supervisorContextQuery ?? []), 'Regresar', 'outline') !!}
      {!! $btn(url()->current(), 'Actualizar', 'primary') !!}
      <button type="button"
              @click="showHorarios = true"
              class="inline-flex items-center justify-center rounded-2xl text-sm font-semibold px-3 py-2 shadow bg-indigo-600 text-white hover:bg-indigo-700">
        Horarios
      </button>
    </section>


    {{-- ===================== Card de Horarios (toggle con Alpine) ===================== --}}
    <div x-data="{ open: false }" x-show="open" x-cloak class="mt-6">
      @include('mobile.ejecutivo.venta.cards.horarios')
    </div>

    {{-- ===================== Modal flotante: Horarios ===================== --}}
<div x-show="showHorarios" x-cloak
     @keydown.escape.window="showHorarios = false"
     class="fixed inset-0 z-50 flex items-center justify-center px-4">

  {{-- Backdrop --}}
  <div class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"
       @click="showHorarios = false"
       x-transition.opacity></div>

  {{-- Contenido del modal --}}
  <div x-transition
       class="relative z-10 w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-gray-900/10 overflow-hidden">
      {{-- Header del modal --}}
      <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
        <h3 class="text-base font-semibold text-gray-900">Horarios de Cobro</h3>
        <button type="button"
                @click="showHorarios = false"
                class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100">
          <span class="sr-only">Cerrar</span>
          &times;
        </button>
      </div>

      {{-- Body: aquí va tu card --}}
      <div class="max-h-[70vh] overflow-y-auto p-4">
        @include('mobile.ejecutivo.venta.cards.horarios')
      </div>

      {{-- Footer opcional --}}
      <div class="px-4 py-3 border-t border-gray-100 flex justify-end">
        <button type="button"
                @click="showHorarios = false"
                class="inline-flex items-center justify-center rounded-xl text-sm font-semibold px-3 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>


  </div>
</x-layouts.mobile.mobile-layout>
