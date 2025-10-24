{{-- resources/views/mobile/ejecutivo/venta/venta.blade.php --}}
@php
    use Carbon\Carbon;

    $role       = $role       ?? 'ejecutivo';
    $nombre     = $nombre     ?? 'N/A';
    $apellido_p = $apellido_p ?? '';
    $apellido_m = $apellido_m ?? '';

    $fechaVenta = isset($fechaVenta) && $fechaVenta
        ? ($fechaVenta instanceof Carbon ? $fechaVenta : Carbon::parse($fechaVenta))
        : null;
    $fechaVentaStr = $fechaVenta ? $fechaVenta->format('d/m/Y') : 'N/A';

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
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-1">
        <p class="text-xs font-semibold text-slate-500">$title</p>
        <p class="text-base font-bold text-slate-900">$value</p>
        {$sub}
      </div>
    HTML;
    };

    $pill = function(string $href, string $text) {
        return '<a href="'.e($href).'"'
             . ' class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm transition hover:bg-blue-500"'
             . ' title="'.e($text).'">'.e($text).'</a>';
    };

    $btn = function(string $href, string $text, string $variant = 'primary') {
        $base   = 'inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold shadow-sm transition';
        $styles = match($variant) {
            'outline' => 'border border-slate-300 bg-slate-100 text-slate-700 hover:bg-slate-200',
            'indigo'  => 'bg-indigo-600 text-white hover:bg-indigo-500',
            default   => 'bg-blue-600 text-white hover:bg-blue-500',
        };

        return '<a href="'.e($href).'" class="'.$base.' '.$styles.'">'.e($text).'</a>';
    };
@endphp

<x-layouts.mobile.mobile-layout title="Cartera - Ejecutivo">
  <div x-data="{ showHorarios:false }"
       x-effect="document.body.style.overflow = showHorarios ? 'hidden' : ''"
       class="mx-auto w-full max-w-md space-y-8 px-5 py-10">

    {{-- Ejecutivo --}}
    <section class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
      <div class="space-y-2">
        <h2 class="text-base font-semibold text-slate-900">Ejecutivo</h2>
        <p class="flex flex-wrap gap-1 text-sm text-slate-700">
          <span class="font-semibold text-slate-900">{{ $nombre }}</span>
          @if($apellido_p !== '')
            <span>{{ $apellido_p }}</span>
          @endif
          @if($apellido_m !== '')
            <span>{{ $apellido_m }}</span>
          @endif
        </p>
        <p class="text-sm font-semibold text-slate-700">
          Venta Registrada para fecha: <span class="text-slate-900">{{ $fechaVentaStr }}</span>
        </p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        {!! $statSimple('Debe Operativo',  mx_money($debeOperativo)) !!}
        {!! $statSimple('Debe Proyectado', mx_money($debeProyectado)) !!}

        {!! $statSimple('Falla Real',      mx_money($fallaReal)) !!}
        {!! $statSimple('% Falla',         number_format($fallaPct, 2).' %') !!}

        {!! $statSimple('Cobranza',        mx_money($cobranza)) !!}
        {!! $statSimple('% Cobranza',      number_format($cobranzaPct, 2).' %') !!}
      </div>
    </section>

    {{-- Supervisores --}}
    <section class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
      <h2 class="text-base font-semibold text-slate-900">Supervisores</h2>

      <div class="space-y-4">
        @forelse($supervisores as $s)
          <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0 space-y-1">
                <p class="text-sm font-semibold text-slate-900">{{ $s['nombre'] ?? 'Sin nombre' }}</p>
                <p class="text-xs text-slate-500">Fecha: <span class="font-semibold text-slate-800">{{ $s['fecha'] ?? 'N/A' }}</span></p>
                <p class="text-xs text-slate-500">Horario: <span class="font-semibold text-slate-800">{{ $s['horario'] ?? 'N/A' }}</span></p>
              </div>
              <div class="flex shrink-0 items-center gap-2">
                {!! $pill(!empty($s['id']) ? route('mobile.supervisor.venta', array_merge($supervisorContextQuery ?? [], ['supervisor' => $s['id']])) : '#', 'D') !!}
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-slate-600">Venta Registrada</p>
                <p class="text-sm font-bold text-slate-900">{{ mx_money($s['ventaRegistrada'] ?? 0) }}</p>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-slate-600">Debe Operativo</p>
                <p class="text-sm font-bold text-slate-900">{{ mx_money($s['debeOperativo'] ?? 0) }}</p>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-slate-600">Debe Proyectado</p>
                <p class="text-sm font-bold text-slate-900">{{ mx_money($s['debeProyectado'] ?? 0) }}</p>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-slate-600">Cobranza %</p>
                <p class="text-sm font-bold text-slate-900">{{ number_format($s['cobranzaPct'] ?? 0, 2) }}%</p>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-slate-600">Falla %</p>
                <p class="text-sm font-bold text-slate-900">{{ number_format($s['fallaPct'] ?? 0, 2) }}%</p>
              </div>
            </div>
          </div>
        @empty
          <p class="text-sm text-slate-500">No hay supervisores registrados.</p>
        @endforelse
      </div>
    </section>

    {{-- Botones --}}
    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
      {!! $btn(route('mobile.index', $supervisorContextQuery ?? []), 'Regresar', 'outline') !!}
      {!! $btn(url()->current(), 'Actualizar', 'primary') !!}
      <button type="button"
              @click="showHorarios = true"
              class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
        Horarios
      </button>
    </section>

    {{-- Card de Horarios (toggle) --}}
    <div x-data="{ open: false }" x-show="open" x-cloak class="mt-6">
      @include('mobile.ejecutivo.venta.cards.horarios')
    </div>

    {{-- Modal Horarios --}}
    <div x-show="showHorarios" x-cloak
         @keydown.escape.window="showHorarios = false"
         class="fixed inset-0 z-50 flex items-center justify-center px-4">

      <div class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"
           @click="showHorarios = false"
           x-transition.opacity></div>

      <div x-transition class="relative z-10 w-full max-w-md">
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
          <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-900">Horarios de Cobro</h3>
            <button type="button"
                    @click="showHorarios = false"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
              <span class="sr-only">Cerrar</span>
              &times;
            </button>
          </div>

          <div class="max-h-[70vh] overflow-y-auto p-4">
            @include('mobile.ejecutivo.venta.cards.horarios')
          </div>

          <div class="flex justify-end border-t border-slate-200 px-4 py-3">
            <button type="button"
                    @click="showHorarios = false"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</x-layouts.mobile.mobile-layout>
