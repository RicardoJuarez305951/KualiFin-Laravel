{{-- resources/views/mobile/ejecutivo/venta/cards/horarios.blade.php --}}
@php
    use Carbon\Carbon;

    $horarios = collect($horarios ?? []);
@endphp

<section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
  <div class="p-5">
    <h2 class="text-base font-bold text-gray-900 mb-3">Horarios de Cobro</h2>

    @forelse($horarios as $fecha)
      @php
          $fechaValor = data_get($fecha, 'date');
          $fechaTexto = null;

          if ($fechaValor instanceof Carbon) {
              $fechaTexto = $fechaValor->format('d/m/Y');
          } elseif (!empty($fechaValor)) {
              try {
                  $fechaTexto = Carbon::parse($fechaValor)->format('d/m/Y');
              } catch (\Throwable $e) {
                  $fechaTexto = (string) $fechaValor;
              }
          }

          $supervisoresDia = collect(data_get($fecha, 'supervisores', []));
      @endphp

      <div class="mb-4">
        <p class="text-sm font-semibold text-gray-800 mb-2">{{ $fechaTexto ?? 'Sin fecha programada' }}</p>

        @forelse($supervisoresDia as $sup)
          @php
              $promotores = collect(data_get($sup, 'promotores', []));
          @endphp

          <div class="rounded-xl border border-gray-100 p-3 mb-2">
            <p class="text-sm font-semibold text-gray-900">{{ data_get($sup, 'nombre', 'Sin supervisor') }}</p>

            @if($promotores->isNotEmpty())
              <ul class="mt-2 divide-y divide-gray-100">
                @foreach($promotores as $idx => $prom)
                  <li class="py-2 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <span class="inline-flex items-center justify-center w-6 h-6 text-[11px] font-bold rounded-full bg-indigo-100 text-indigo-700">
                        {{ $idx + 1 }}
                      </span>
                      <span class="text-sm text-gray-800">{{ data_get($prom, 'nombre', 'Sin promotor') }}</span>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">{{ data_get($prom, 'hora', '—') }}</span>
                  </li>
                @endforeach
              </ul>
            @else
              <p class="text-sm text-gray-500">No hay promotores programados.</p>
            @endif
          </div>
        @empty
          <p class="text-sm text-gray-500">No hay supervisores programados.</p>
        @endforelse
      </div>
    @empty
      <p class="text-sm text-gray-500">No hay horarios de cobro registrados.</p>
    @endforelse
  </div>
</section>
