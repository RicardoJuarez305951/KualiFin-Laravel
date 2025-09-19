{{-- resources/views/mobile/supervisor/dashboard.blade.php --}}
@php
  /** Vars esperadas desde el controlador (con defaults seguros) */
  $role               = $role              ?? 'supervisor';
  $nombre_supervisor  = $nombre_supervisor ?? (auth()->user()->name ?? 'Supervisor');
  $cartera_activa     = $cartera_activa    ?? 0;
  $cartera_falla      = $cartera_falla     ?? 0;
  $cartera_vencida    = $cartera_vencida   ?? 0;
  $cartera_inactivaP  = $cartera_inactivaP ?? 0;

  $porcentaje_fallo   = $cartera_activa > 0
      ? round(($cartera_falla / max(1, (float)$cartera_activa)) * 100, 2)
      : 0;

  if (!function_exists('money_mx')) {
    function money_mx($v){ return '$' . number_format((float)$v, 2, '.', ','); }
  }

  /** Helpers inline (reemplazo sencillo de X-Components) */
  $statRow = function(string $label, $value = null, $slotHtml = null) {
      $left  = '<span class="text-sm text-gray-600">'.e($label).'</span>';
      $right = !is_null($value)
          ? '<span class="text-sm font-semibold text-gray-900">'.e($value).'</span>'
          : ($slotHtml ?? '');
      return '<div class="flex items-center justify-between">'.$left.$right.'</div>';
  };

  $pillLink = function(string $href, string $text = 'D') {
      return '<a href="'.e($href).'"
                class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full bg-blue-600 text-white hover:bg-blue-700 transition"
                title="Detalles">'.$text.'</a>';
  };

  $btn = function(string $href, string $text, string $variant = 'primary') {
      $base   = 'inline-flex items-center justify-center rounded-xl text-sm font-semibold px-3 py-2 shadow';
      $styles = match($variant) {
        'outline-primary' => 'border border-gray-300 text-white bg-blue-600 hover:bg-blue-700 shadow-sm',
        'indigo'          => 'bg-indigo-600 text-white hover:bg-indigo-700',
        default           => 'bg-blue-600 text-white hover:bg-blue-700',
      };
      return '<a href="'.e($href).'" class="'.$base.' '.$styles.'">'.$text.'</a>';
  };
@endphp

<x-layouts.mobile.mobile-layout title="Cartera Supervisor">
  <div class="mx-auto w-[22rem] sm:w-[26rem] p-4 sm:p-6 space-y-6">

    {{-- ===================== Resumen ===================== --}}
    @include('mobile.supervisor.cartera.cartera_resumen')

    {{-- ===================== Promotores ===================== --}}
    <section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
      <div class="p-5">
        <h2 class="text-base font-bold text-gray-900 mb-3">Promotores</h2>

        <div class="space-y-3">
          @forelse($promotores as $p)
            <a href="{{ route('mobile.promotor.cartera', $p->id) }}"
               class="block rounded-xl border border-gray-100 p-3 shadow-md hover:shadow transition">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center justify-center w-6 h-6 text-[11px] font-bold rounded-full bg-indigo-100 text-indigo-700">
                    {{ $loop->iteration }}
                  </span>
                  <span class="text-sm font-semibold text-gray-900">
                    {{ $p->nombre }} {{ $p->apellido_p }} {{ $p->apellido_m }}
                  </span>
                </div>
              </div>
              {{-- Si luego quieres barra de progreso por promotor, calcúlala en el controlador y expón $p->progress --}}
            </a>
          @empty
            <p class="text-sm text-gray-500">No hay promotores registrados.</p>
          @endforelse
        </div>
      </div>
    </section>

    {{-- ===================== Acciones ===================== --}}
    <section class="grid grid-cols-3 gap-3">
      {!! $btn(route('mobile.index'), 'Regresar', 'outline-primary') !!}
      {!! $btn(url()->current(), 'Actualizar', 'primary') !!}
      {!! $btn(url()->current(), 'Reporte', 'indigo') !!}
    </section>

  </div>
</x-layouts.mobile.mobile-layout>
