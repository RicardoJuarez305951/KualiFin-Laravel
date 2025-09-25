{{-- resources/views/mobile/ejecutivo/cartera.blade.php --}}
@php
  /** ================= Vars esperadas desde el controlador (con defaults seguros) ================= */
  $role              = $role            ?? 'ejecutivo';
  $ejecutivo         = $ejecutivo       ?? null;
  $nombre            = $nombre          ?? ($ejecutivo->nombre      ?? auth()->user()->name ?? 'Ejecutivo');
  $apellido_p        = $apellido_p      ?? ($ejecutivo->apellido_p  ?? '');
  $apellido_m        = $apellido_m      ?? ($ejecutivo->apellido_m  ?? '');
  $supervisores      = $supervisores    ?? collect();

  // Totales/contadores (si no vienen, caen a 0)
  $cartera_activa    = $cartera_activa    ?? 0;
  $cartera_falla     = $cartera_falla     ?? 0;  // "Falla Actual"
  $cartera_vencida   = $cartera_vencida   ?? 0;
  $cartera_inactivaP = $cartera_inactivaP ?? 0;

  /** ================= Helpers inline (sustitutos simples de X-Components) ================= */
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

<x-layouts.mobile.mobile-layout title="Cartera Ejecutivo">
  <div class="mx-auto w-[22rem] sm:w-[26rem] p-4 sm:p-6 space-y-6">

    {{-- ===================== div1: Ejecutivo ===================== --}}
    <section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
      <div class="p-5 space-y-2">
        <h2 class="text-base font-bold text-gray-900">Ejecutivo</h2>
        <div class="grid grid-cols-1 gap-2">
          {!! $statRow('Nombre', $nombre) !!}
          {!! $statRow('Apellido Paterno', $apellido_p) !!}
          {!! $statRow('Apellido Materno', $apellido_m) !!}
        </div>
      </div>

      {{-- ===================== div1.2: Listado de opciones ===================== --}}
      <div class="px-5 pb-5 space-y-3">
        <h3 class="text-sm font-semibold text-gray-700">Opciones</h3>

        <div class="space-y-2">
          <div class="rounded-xl border border-gray-100 p-3 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-700">Cartera Activa</span>
              <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-900">{{ $cartera_activa }}</span>
                {!! $pillLink(route('mobile.'.$role.'.cartera_activa'), 'D') !!}
              </div>
            </div>
          </div>

          <div class="rounded-xl border border-gray-100 p-3 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-700">Falla Actual</span>
              <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-900">{{ $cartera_falla }}</span>
                {!! $pillLink(route('mobile.'.$role.'.cartera_falla'), 'D') !!}
              </div>
            </div>
          </div>

          <div class="rounded-xl border border-gray-100 p-3 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-700">Cartera Vencida</span>
              <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-900">{{ $cartera_vencida }}</span>
                {!! $pillLink(route('mobile.'.$role.'.cartera_vencida'), 'D') !!}
              </div>
            </div>
          </div>

          <div class="rounded-xl border border-gray-100 p-3 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-700">Cartera Inactiva</span>
              <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-900">{{ $cartera_inactivaP }}</span>
                {!! $pillLink(route('mobile.'.$role.'.cartera_inactiva'), 'D') !!}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- ===================== div2: Supervisores ===================== --}}
    <section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
      <div class="p-5">
        <h2 class="text-base font-bold text-gray-900 mb-3">Supervisores</h2>

        <div class="space-y-3">
          @forelse($supervisores as $s)
            <a href="{{ route('mobile.supervisor.cartera', ['supervisor' => $s->id ?? ($s['id'] ?? null)]) }}"
               class="block rounded-xl border border-gray-100 p-3 shadow-md hover:shadow transition">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center justify-center w-6 h-6 text-[11px] font-bold rounded-full bg-indigo-100 text-indigo-700">
                    {{ $loop->iteration }}
                  </span>
                  <span class="text-sm font-semibold text-gray-900">
                    {{ $s->nombre ?? ($s['nombre'] ?? 'Sin nombre') }}
                    {{ $s->apellido_p ?? ($s['apellido_p'] ?? '') }}
                    {{ $s->apellido_m ?? ($s['apellido_m'] ?? '') }}
                  </span>
                </div>
              </div>
              
              {{-- Progreso de cumplimiento --}}
              <div class="mt-3 space-y-2">
                <div class="pt-2 border-t border-gray-100">
                  <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-600">Progreso de supervisor</span>
                    <span class="text-xs font-semibold text-gray-900">{{ $s->promotores_cumplidos ?? 0 }} / {{ $s->total_promotores ?? 0 }}</span>
                  </div>
                  <div class="mt-1 h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                    <div class="h-2 bg-emerald-500" style="width: {{ $s->porcentaje_cumplimiento ?? 0 }}%;"></div>
                  </div>
                  <p class="mt-1 text-xs text-gray-500">
                    {{ number_format($s->porcentaje_cumplimiento ?? 0, 0) }}% de meta completada.
                  </p>
                </div>
              </div>

            </a>
          @empty
            <p class="text-sm text-gray-500">No hay supervisores registrados.</p>
          @endforelse
        </div>
      </div>
    </section>

    {{-- ===================== div3: Botón / Acciones ===================== --}}
    <section class="grid grid-cols-3 gap-3">
      {!! $btn(route('mobile.index'), 'Regresar', 'outline-primary') !!}
      {!! $btn(url()->current(), 'Actualizar', 'primary') !!}
      {!! $btn(url()->current(), 'Reporte', 'indigo') !!}
    </section>

  </div>
</x-layouts.mobile.mobile-layout>
