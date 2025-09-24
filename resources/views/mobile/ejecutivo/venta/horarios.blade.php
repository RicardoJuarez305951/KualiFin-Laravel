{{-- resources/views/mobile/supervisor/venta/definir_horarios.blade.php --}}
@php
  use Carbon\Carbon;
  use Illuminate\Support\Str;

  /** ===== Vars con defaults seguros ===== */
  $role         = $role ?? 'supervisor';
  $venta_fecha  = $venta_fecha ?? now();         // string|Carbon
  $promotores   = $promotores ?? collect();      // colección de promotores
  $definirRoute = fn($id) => route('mobile.supervisor.horarios.definir', $id); // ajusta si usas otra ruta

  /** ===== Normaliza fecha a DD/MM/YY ===== */
  try {
    $venta_fecha = $venta_fecha instanceof Carbon ? $venta_fecha : Carbon::parse($venta_fecha);
  } catch (\Throwable $e) {
    $venta_fecha = now();
  }
  $venta_ddmmyy = $venta_fecha->format('d/m/y');

  /** ===== Helpers inline (estilo X-Components en un archivo) ===== */
  $card = function($title, $contentHtml){
    return <<<HTML
      <section class="bg-white rounded-2xl shadow ring-1 ring-gray-900/5 px-4 py-3">
        {$title}
        {$contentHtml}
      </section>
    HTML;
  };

  $row = function(string $label, $valueHtml){
    $labelHtml = '<span class="text-sm text-gray-600">'.e($label).'</span>';
    return '<div class="flex items-center justify-between">'.$labelHtml.$valueHtml.'</div>';
  };

  $pillNum = fn($n) =>
    '<span class="inline-flex items-center justify-center w-6 h-6 text-[11px] font-bold rounded-full bg-indigo-100 text-indigo-700">'.$n.'</span>';

  $diasSemana = [
    'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'
  ];
@endphp

<x-layouts.mobile.mobile-layout title="Definir Horarios">
  <div class="mx-auto w-[22rem] sm:w-[26rem] p-4 sm:p-6 space-y-4">

    {{-- ===== Encabezado: Definir Horarios ===== --}}
    {!! $card(
      '<h1 class="text-base font-bold text-gray-900 mb-1">Definir Horarios</h1>',
      ''
    ) !!}

    {{-- ===== % Venta (fecha) ===== --}}
    @php
      $ventaRow = $row('% Venta', '<span class="text-sm font-semibold text-gray-900">'.$venta_ddmmyy.'</span>');
    @endphp
    {!! $card('', $ventaRow) !!}

    {{-- ===== Lista de promotores con SelectList de días de semana ===== --}}
    @php ob_start(); @endphp
      <h2 class="text-base font-bold text-gray-900 mb-2">Nombre</h2>
      <div class="space-y-3">
        @forelse($promotores as $p)
          <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
              {!! $pillNum($loop->iteration) !!}
              <span class="text-sm font-semibold text-gray-900">
                {{ trim(($p->nombre ?? '').' '.($p->apellido_p ?? '').' '.($p->apellido_m ?? '')) ?: ($p->nombre_completo ?? '—') }}
              </span>
            </div>
            @php $horarioPago = trim((string) ($p->horario_pago_resumen ?? '')); @endphp
            @if($horarioPago !== '')
              <p class="ml-8 text-xs text-gray-500">Horario actual: {{ $horarioPago }}</p>
            @endif
            <form method="POST" action="{{ $definirRoute($p->id ?? 0) }}" class="ml-8 flex flex-wrap items-center gap-2">
              @csrf
              <select name="dia_de_pago" class="text-sm rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Seleccionar día</option>
                @foreach($diasSemana as $dia)
                  <option value="{{ $dia }}" @selected(Str::lower($p->dia_de_pago ?? '') === Str::lower($dia))>{{ $dia }}</option>
                @endforeach
              </select>
              <input
                type="time"
                name="hora_de_pago"
                value="{{ $p->hora_de_pago }}"
                class="text-sm rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                required
              />
              <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded-lg shadow">
                Guardar
              </button>
            </form>
          </div>
        @empty
          <p class="text-sm text-gray-500">Sin promotores.</p>
        @endforelse
      </div>
    @php $listaPromotoresHtml = ob_get_clean(); @endphp

    {!! $card('', $listaPromotoresHtml) !!}

    {{-- ===== Botón Regresar (a mobile.index) ===== --}}
    <div class="pt-2">
      <a href="{{ route('mobile.index') }}" class="inline-flex items-center justify-center rounded-xl font-semibold shadow text-sm px-3 py-2 border border-gray-300 bg-blue-600 text-white hover:bg-blue-700">
        Regresar
      </a>
    </div>

  </div>
</x-layouts.mobile.mobile-layout>
