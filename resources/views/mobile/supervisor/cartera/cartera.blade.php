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
  @php
    $alertasPromotores = collect($alertasPromotores ?? [])->filter(fn ($alerta) => !empty($alerta));
  @endphp

  <div class="mx-auto w-[22rem] sm:w-[26rem] p-4 sm:p-6 space-y-6">

    {{-- ===================== Resumen ===================== --}}
    @include('mobile.supervisor.cartera.cartera_resumen')

    @if($alertasPromotores->isNotEmpty())
      <section class="bg-red-50 border border-red-200 rounded-2xl shadow-inner">
        <div class="p-4 space-y-2">
          <div class="flex items-center gap-2 text-red-800">
            <span class="text-lg" aria-hidden="true">⚠️</span>
            <h2 class="text-sm font-semibold">Alertas por fallas consecutivas</h2>
          </div>
          <ul class="space-y-2 text-sm text-red-900">
            @foreach($alertasPromotores as $alerta)
              <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                <span>
                  <strong>{{ $alerta['nombre'] ?? '' }}</strong> acumula una falla del {{ number_format((float) ($alerta['failure_rate'] ?? 0), 2) }}% durante {{ $alerta['streak'] ?? 0 }} semanas consecutivas.
                </span>
                <a
                  href="{{ route('mobile.promotor.cartera', array_merge($supervisorContextQuery ?? [], ['promotor' => $alerta['id'] ?? null])) }}"
                  class="inline-flex items-center gap-1 text-xs font-semibold text-red-700 hover:text-red-800"
                >
                  Ver promotora &rarr;
                </a>
              </li>
            @endforeach
          </ul>
        </div>
      </section>
    @endif

    {{-- ===================== Promotores ===================== --}}
    <section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
      <div class="p-5">
        <h2 class="text-base font-bold text-gray-900 mb-3">Promotores</h2>

        <div class="space-y-3">
          @forelse($promotores as $p)
            @php
              $alertaFalla = (bool) ($p->alerta_falla_semana ?? false);
            @endphp
            <a href="{{ route('mobile.promotor.cartera', array_merge($supervisorContextQuery ?? [], ['promotor' => $p->id])) }}"
               class="block rounded-xl p-3 shadow-md hover:shadow transition {{ $alertaFalla ? 'border border-red-200 bg-red-50' : 'border border-gray-100' }}">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center justify-center w-6 h-6 text-[11px] font-bold rounded-full bg-indigo-100 text-indigo-700">
                    {{ $loop->iteration }}
                  </span>
                  <div class="flex flex-col">
                    <span class="text-sm font-semibold text-gray-900">
                      {{ $p->nombre }} {{ $p->apellido_p }} {{ $p->apellido_m }}
                    </span>
                    @php $horarioPago = trim((string) ($p->horario_pago_resumen ?? '')); @endphp
                      @if($horarioPago !== '')
                      <span class="text-[11px] text-gray-500">Horario de pago: {{ $horarioPago }}</span>
                      @endif
                  </div>
                </div>
              </div>
              <div class="mt-3 space-y-2">
                
                @php
                  $porcentaje = number_format((float) ($p->porcentaje_semana ?? 0), 2);
                  $width = min(100, (float) ($p->porcentaje_semana ?? 0));
                @endphp

                <div class="space-y-2">
                  <div class="grid grid-cols-1 gap-1 text-xs text-gray-600">
                    <div class="flex items-center justify-between">
                      <span>Objetivo semanal</span>
                      <span class="font-semibold text-gray-900">{{ money_mx($p->venta_maxima ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                      <span>Venta semanal</span>
                      <span class="font-semibold text-gray-900">{{ money_mx($p->venta_real_semana ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                      <span>Objetivo ejercicio</span>
                      <span class="font-semibold text-gray-900">{{ money_mx($p->venta_proyectada_objetivo ?? 0) }}</span>
                    </div>
                  </div>

                  <div class="pt-1">
                  <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-600">Avance semanal</span>
                    <span class="text-xs font-semibold text-gray-900">{{ $porcentaje }}%</span>
                  </div>
                  <div class="mt-1 h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                    <div class="h-2 bg-indigo-500" style="width: {{ $width }}%;"></div>
                  </div>
                  <p class="mt-1 text-xs text-gray-500">
                    Faltan {{ money_mx($p->faltante_semana ?? 0) }} para alcanzar el objetivo semanal.
                  </p>
                  @if($alertaFalla)
                    <p class="mt-2 text-xs font-semibold text-red-700">
                      ⚠️ Falla de {{ number_format((float) ($p->failure_rate_semana ?? 0), 2) }}% por {{ $p->failure_streak_semana ?? 0 }} semanas consecutivas.
                    </p>
                  @endif
                </div>

              </div>
            </a>
          @empty
            <p class="text-sm text-gray-500">No hay promotores registrados.</p>
          @endforelse
        </div>
      </div>
    </section>

    {{-- ===================== Acciones ===================== --}}
    <section class="grid grid-cols-3 gap-3">
      {!! $btn(route('mobile.index', array_merge($supervisorContextQuery ?? [], [])), 'Regresar', 'outline-primary') !!}
      {!! $btn(route('mobile.supervisor.cartera', array_merge($supervisorContextQuery ?? [], [])), 'Actualizar', 'primary') !!}
      {!! $btn(route('mobile.supervisor.reporte', array_merge($supervisorContextQuery ?? [], [])), 'Reporte', 'indigo') !!}
    </section>

  </div>
</x-layouts.mobile.mobile-layout>
