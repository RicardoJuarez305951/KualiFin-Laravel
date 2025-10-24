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
@endphp

<x-layouts.mobile.mobile-layout title="Cartera Supervisor">
  @php
    $alertasPromotores = collect($alertasPromotores ?? [])->filter(fn ($alerta) => !empty($alerta));
  @endphp

  <div class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8">

    {{-- ===================== Resumen ===================== --}}
    @include('mobile.supervisor.cartera.cartera_resumen')

    @if($alertasPromotores->isNotEmpty())
      <section class="space-y-3 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 shadow">
        <div class="flex items-center gap-2 text-red-800">
          <span class="text-lg" aria-hidden="true">&#9888;</span>
          <h2 class="text-sm font-semibold">Alertas por fallas consecutivas</h2>
        </div>
        <ul class="space-y-3 text-sm text-red-900">
          @foreach($alertasPromotores as $alerta)
            <li class="space-y-1 rounded-2xl border border-red-100 bg-white px-4 py-3 shadow-sm">
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
      </section>
    @endif

    {{-- ===================== Promotores ===================== --}}
    <section class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow">
      <header class="text-center">
        <h2 class="text-2xl font-bold text-slate-900 leading-tight">Promotores</h2>
      </header>

      <div class="space-y-3">
        @forelse($promotores as $p)
          @php
            $alertaFalla = (bool) ($p->alerta_falla_semana ?? false);
            $objetivoSemanal = (float) ($p->venta_maxima ?? 0);
            $ventaSemanal = (float) ($p->venta_real_semana ?? 0);
            $porcentajeBase = (float) ($p->porcentaje_semana ?? 0);
            if ($porcentajeBase <= 0 && $objetivoSemanal > 0) {
              $porcentajeBase = ($ventaSemanal / $objetivoSemanal) * 100;
            }
            $porcentajeBase = is_finite($porcentajeBase) ? $porcentajeBase : 0;
            $porcentaje = number_format($porcentajeBase, 2, '.', '');
            $width = max(0, min(100, $porcentajeBase));
            $horarioPago = trim((string) ($p->horario_pago_resumen ?? ''));
          @endphp
          <a href="{{ route('mobile.promotor.cartera', array_merge($supervisorContextQuery ?? [], ['promotor' => $p->id])) }}"
             class="block space-y-3 rounded-2xl border {{ $alertaFalla ? 'border-red-200 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-4 py-4 shadow-sm transition hover:shadow">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-start gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-[11px] font-bold text-indigo-700">
                  {{ $loop->iteration }}
                </span>
                <div class="space-y-1">
                  <span class="text-sm font-semibold text-slate-900">
                    {{ $p->nombre }} {{ $p->apellido_p }} {{ $p->apellido_m }}
                  </span>
                  @if($horarioPago !== '')
                    <span class="text-[11px] text-gray-500">Horario de pago: {{ $horarioPago }}</span>
                  @endif
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <div class="grid grid-cols-1 gap-2 text-xs text-gray-600">
                <div class="flex items-center justify-between">
                  <span>Objetivo semanal</span>
                  <span class="font-semibold text-gray-900">{{ money_mx($objetivoSemanal) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span>Venta semanal</span>
                  <span class="font-semibold text-gray-900">{{ money_mx($ventaSemanal) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span>Objetivo ejercicio</span>
                  <span class="font-semibold text-gray-900">{{ money_mx($p->venta_proyectada_objetivo ?? 0) }}</span>
                </div>
              </div>

              <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-gray-600">
                  <span>Avance semanal</span>
                  <span class="font-semibold text-gray-900">{{ $porcentaje }}%</span>
                </div>
                <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
                  <div class="h-full bg-indigo-500 transition-all" style="width: {{ number_format($width, 2, '.', '') }}%;"></div>
                </div>
                <p class="text-xs text-gray-500">
                  Faltan {{ money_mx($p->faltante_semana ?? 0) }} para alcanzar el objetivo semanal.
                </p>
                @if($alertaFalla)
                  <p class="text-xs font-semibold text-red-700">
                    &#9888; Falla de {{ number_format((float) ($p->failure_rate_semana ?? 0), 2) }}% por {{ $p->failure_streak_semana ?? 0 }} semanas consecutivas.
                  </p>
                @endif
              </div>
            </div>
          </a>
        @empty
          <p class="text-sm text-gray-500">No hay promotores registrados.</p>
        @endforelse
      </div>
    </section>

    {{-- ===================== Acciones ===================== --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
      <a href="{{ route('mobile.index', array_merge($supervisorContextQuery ?? [], [])) }}"
         class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow transition hover:bg-blue-700">
        Regresar
      </a>
      <a href="{{ route('mobile.supervisor.cartera', array_merge($supervisorContextQuery ?? [], [])) }}"
         class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow transition hover:bg-blue-700">
        Actualizar
      </a>
      <a href="{{ route('mobile.supervisor.reporte', array_merge($supervisorContextQuery ?? [], [])) }}"
         class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow transition hover:bg-indigo-700">
        Reporte
      </a>
    </div>

  </div>
</x-layouts.mobile.mobile-layout>
