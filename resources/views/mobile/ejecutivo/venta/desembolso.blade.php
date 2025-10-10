{{-- resources/views/mobile/ejecutivo/venta/desembolso.blade.php --}}
@php
    $payload = $payload ?? null;
    $contexto = $payload['contexto'] ?? [];
    $listas = $payload['listas'] ?? [];
    $totales = $payload['totales'] ?? [];
    $cierres = $payload['cierres'] ?? [];
    $cobranza = $payload['cobranza'] ?? [];
    $rango = $contexto['rango'] ?? [];

    $fechaReporte = $contexto['fecha_reporte'] ?? null;
    $semanaVenta = $contexto['semana_venta'] ?? null;

    $ejecutivoNombre = \Illuminate\Support\Arr::get($contexto, 'ejecutivo.nombre', '');
    $supervisorNombre = \Illuminate\Support\Arr::get($contexto, 'supervisor.nombre', '');
    $promotorNombre = \Illuminate\Support\Arr::get($contexto, 'promotor.nombre', '');

    $falloItems = \Illuminate\Support\Arr::get($listas, 'fallo.items', []);
    $falloTotal = \Illuminate\Support\Arr::get($listas, 'fallo.total', \Illuminate\Support\Arr::get($totales, 'fallo', 0));
    $prestamos = \Illuminate\Support\Arr::get($listas, 'prestamos', []);
    $desembolsos = \Illuminate\Support\Arr::get($listas, 'desembolsos', []);

    $recreditosItems = \Illuminate\Support\Arr::get($listas, 'recreditos.items', []);
    $recreditosTotal = \Illuminate\Support\Arr::get($listas, 'recreditos.total', \Illuminate\Support\Arr::get($totales, 'recreditos', 0));
    $recreditosTotalNuevo = \Illuminate\Support\Arr::get($listas, 'recreditos.total_nuevo', 0);
    $recreditosTotalAnterior = \Illuminate\Support\Arr::get($listas, 'recreditos.total_anterior', 0);

    $adelantosItems = \Illuminate\Support\Arr::get($listas, 'adelantos.items', []);
    $adelantosTotal = \Illuminate\Support\Arr::get($listas, 'adelantos.total', \Illuminate\Support\Arr::get($totales, 'adelantos', 0));

    $recuperacionItems = \Illuminate\Support\Arr::get($listas, 'recuperacion.items', []);
    $recuperacionTotal = \Illuminate\Support\Arr::get($listas, 'recuperacion.total', \Illuminate\Support\Arr::get($totales, 'recuperacion', 0));

    $cobranzaDias = \Illuminate\Support\Arr::get($cobranza, 'dias', []);
    $cobranzaTotal = \Illuminate\Support\Arr::get($cobranza, 'total', \Illuminate\Support\Arr::get($totales, 'cobranza', 0));

    $promotoresDisponibles = collect($promotoresDisponibles ?? []);
    $supervisorContextQuery = $supervisorContextQuery ?? [];
    $periodo = $periodo ?? ['inicio' => null, 'fin' => null];

    $formatCurrency = static function ($value): string {
        $number = is_numeric($value) ? (float) $value : 0.0;

        return '$' . number_format($number, 2, '.', ',');
    };

    $formatDate = static function ($value): ?string {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('d/m/Y');
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
            } catch (\Throwable) {
                return $value;
            }
        }

        return null;
    };

    $pdfUrl = null;
    if ($promotorSeleccionado) {
        $pdfParams = array_merge($supervisorContextQuery, ['promotor' => $promotorSeleccionado->id]);
        $pdfRouteCandidates = [
            'mobile.ejecutivo.desembolso.pdf',
            'mobile.ejecutivo.venta.desembolso.pdf',
        ];

        foreach ($pdfRouteCandidates as $candidateName) {
            if (\Illuminate\Support\Facades\Route::has($candidateName)) {
                $pdfUrl = route($candidateName, $pdfParams);
                break;
            }
        }
    }
@endphp

<x-layouts.mobile.mobile-layout :title="'Reporte de Desembolso'">
  <div
    x-data="desembolsoPage({
        registerUrl: '{{ route('mobile.ejecutivo.desembolso.registrar_pago') }}',
        falloRegisterUrl: '{{ route('mobile.ejecutivo.desembolso.registrar_fallos_recuperados') }}',
        promotorId: {{ $promotorSeleccionado?->id ?? 'null' }},
        supervisorQuery: @js($supervisorContextQuery ?? []),
        pdfUrl: @js($pdfUrl),
        ejecutivoId: {{ request()->has('ejecutivo_id') ? (int) request('ejecutivo_id') : 'null' }},
    })"
    class="p-4 w-full max-w-md mx-auto space-y-5"
  >
    @if(session('status'))
      <div class="px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-800">
        {{ session('status') }}
      </div>
    @endif

    @if($errors->any())
      <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-800 space-y-1">
        <p class="font-semibold">No se pudo completar la acción solicitada.</p>
        <ul class="list-disc list-inside space-y-1">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="GET"
          action="{{ route('mobile.ejecutivo.desembolso') }}"
          class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800">Filtros del reporte</h2>
        <span class="text-[10px] uppercase tracking-wide text-gray-400">Solo lectura</span>
      </div>

      @foreach($supervisorContextQuery as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
      @endforeach

      @if(request()->has('ejecutivo_id'))
        <input type="hidden" name="ejecutivo_id" value="{{ request('ejecutivo_id') }}">
      @endif

      <div class="space-y-3">
        <div>
          <label for="promotor" class="block text-xs font-semibold text-gray-600 uppercase mb-1">
            Promotora
          </label>
          @if($promotoresDisponibles->isNotEmpty())
            <select id="promotor"
                    name="promotor"
                    class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
              @foreach($promotoresDisponibles as $promotor)
                <option value="{{ $promotor['id'] }}"
                        @selected($promotorSeleccionado && $promotorSeleccionado->id === $promotor['id'])>
                  {{ $promotor['nombre'] }}
                </option>
              @endforeach
            </select>
          @else
            <p class="text-sm text-gray-500">No hay promotoras disponibles para el supervisor seleccionado.</p>
          @endif
        </div>

        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-3 py-2 text-xs text-gray-600">
          <p class="uppercase font-semibold text-[10px] text-gray-500 mb-1">Periodo de reporte</p>
          <p class="font-medium text-gray-800">
            {{ $formatDate($periodo['inicio'] ?? null) ?? '---' }}
            <span class="text-gray-400">al</span>
            {{ $formatDate($periodo['fin'] ?? null) ?? '---' }}
          </p>
          <p class="mt-1 text-[11px]">
            El rango se calcula automáticamente del sábado anterior hasta hoy.
          </p>
        </div>
      </div>

      <div class="flex items-center justify-end">
        <button type="submit"
                class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
          Actualizar
        </button>
      </div>
    </form>

    @if(!$payload)
      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center space-y-3">
        <h3 class="text-sm font-semibold text-gray-800">Reporte sin datos</h3>
        <p class="text-sm text-gray-600">
          Selecciona una promotora y, si aplica, ajusta el rango de fechas para mostrar la información de desembolso.
        </p>
      </section>
    @else
      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-4">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-[11px] uppercase text-gray-500">Promotora</p>
            <p class="text-sm font-semibold text-gray-800">{{ $promotorNombre ?: 'No disponible' }}</p>
          </div>
          <div class="text-right">
            <p class="text-[11px] uppercase text-gray-500">Fecha de reporte</p>
            <p class="text-sm font-semibold text-gray-800">{{ $formatDate($fechaReporte) ?? '---' }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
          <div>
            <p class="text-[11px] uppercase text-gray-500">Supervisor</p>
            <p class="font-semibold">{{ $supervisorNombre ?: 'No disponible' }}</p>
          </div>
          <div>
            <p class="text-[11px] uppercase text-gray-500">Ejecutivo</p>
            <p class="font-semibold">{{ $ejecutivoNombre ?: 'No disponible' }}</p>
          </div>
          <div>
            <p class="text-[11px] uppercase text-gray-500">Semana de venta</p>
            <p class="font-semibold">{{ $semanaVenta ?? '---' }}</p>
          </div>
          <div>
            <p class="text-[11px] uppercase text-gray-500">Rango del reporte</p>
            <p class="font-semibold">
              {{ $formatDate($rango['inicio'] ?? null) ?? '---' }}
              <span class="text-gray-400">al</span>
              {{ $formatDate($rango['fin'] ?? null) ?? '---' }}
            </p>
          </div>
        </div>
      </section>

      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-800">Fallo</h3>
          <span class="text-xs font-semibold text-gray-700">{{ $formatCurrency($falloTotal) }}</span>
        </div>

        @if(empty($falloItems))
          <p class="text-sm text-gray-600">No hay fallas registradas en el periodo seleccionado.</p>
        @else
          <p class="text-xs text-gray-500">Gestiona cada fallo registrando pagos recuperados o confirmando el adeudo pendiente.</p>

          <div class="space-y-3">
            @foreach($falloItems as $item)
              @php
                $falloId = $item['id'] ?? null;
                $faltante = isset($item['monto']) ? (float) $item['monto'] : 0.0;
              @endphp

              @continue(empty($falloId))

              <article class="rounded-xl border border-gray-200 p-3 bg-white space-y-3">
                <div class="flex items-start justify-between gap-3">
                  <div class="space-y-0.5">
                    <p class="text-sm font-semibold text-gray-800">{{ $item['cliente'] ?? 'Sin nombre' }}</p>
                    <p class="text-xs text-gray-500">{{ $item['fecha_texto'] ?? '---' }}</p>
                  </div>

                  <div class="text-right">
                    <p class="text-[11px] uppercase text-gray-500">Faltante</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $formatCurrency($faltante) }}</p>
                  </div>
                </div>

                <div class="space-y-2">
                  {{-- Comentario: este botón abre la calculadora modal reutilizando la lógica de pagos de desembolsos. --}}
                  <div class="flex items-center justify-end">
                    <button
                      type="button"
                      class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white font-semibold text-lg hover:bg-emerald-700 transition disabled:opacity-60"
                      title="Registrar pago recuperado"
                      :disabled="processingPayment"
                      @click="openPaymentModal(
                        { id: {{ $falloId }}, nombre: '{{ addslashes($item['cliente'] ?? 'Sin nombre') }}' },
                        { type: 'fallo', falloId: {{ $falloId }}, pendingAmount: @js($faltante) }
                      )"
                    >
                      <span aria-hidden="true">$</span>
                      <span class="sr-only">Registrar pago recuperado</span>
                    </button>
                  </div>

                  <form method="POST"
                        action="{{ route('mobile.ejecutivo.desembolso.registrar_fallos_recuperados') }}"
                        class="flex justify-end">
                    @csrf

                    @foreach($supervisorContextQuery as $key => $value)
                      <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    @if(request()->has('ejecutivo_id'))
                      <input type="hidden" name="ejecutivo_id" value="{{ request('ejecutivo_id') }}">
                    @endif

                    @if($promotorSeleccionado)
                      <input type="hidden" name="promotor_id" value="{{ $promotorSeleccionado->id }}">
                    @endif

                    <input type="hidden" name="accion" value="confirmar_fallo">
                    <input type="hidden" name="fallo_id" value="{{ $falloId }}">

                    <button type="submit"
                            class="px-3 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:border-gray-400 hover:text-gray-900 transition">
                      Confirmar
                    </button>
                  </form>

                  <p class="text-[11px] text-gray-500 text-right">Faltante actual: {{ $formatCurrency($faltante) }}</p>
                </div>
              </article>
            @endforeach
          </div>

          {{-- Comentario: mostramos mensajes de retroalimentación tras intentar registrar pagos de fallos. --}}
          <template x-if="feedbackMessage">
            <div
              class="mt-3 px-3 py-2 rounded-lg text-xs"
              :class="feedbackClass"
              x-text="feedbackMessage"
            ></div>
          </template>
        @endif
      </section>

      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-800">Prestamos registrados</h3>
          <span class="text-xs font-semibold text-gray-700">{{ $formatCurrency($totales['prestamos'] ?? 0) }}</span>
        </div>

        @if(empty($prestamos))
          <p class="text-sm text-gray-600">No hay prestamos en el periodo seleccionado.</p>
        @else
          <div class="text-xs font-semibold text-gray-500 grid grid-cols-12 gap-2 uppercase">
            <span class="col-span-3">Fecha</span>
            <span class="col-span-5">Cliente</span>
            <span class="col-span-2 text-center">Estado</span>
            <span class="col-span-2 text-right">Monto</span>
          </div>
          <div class="divide-y divide-gray-200 text-sm">
            @foreach($prestamos as $item)
              <div class="grid grid-cols-12 gap-2 py-2">
                <span class="col-span-3 text-gray-700">{{ $item['fecha_texto'] ?? '---' }}</span>
                <span class="col-span-5 text-gray-700">{{ $item['cliente'] ?? 'Sin nombre' }}</span>
                <span class="col-span-2 text-center text-gray-600 text-xs uppercase">{{ $item['estado'] ?? '---' }}</span>
                <span class="col-span-2 text-right font-medium text-gray-800">{{ $formatCurrency($item['monto'] ?? 0) }}</span>
              </div>
            @endforeach
          </div>
        @endif
      </section>

      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-800">Desembolsos</h3>
          <span class="text-xs font-semibold text-gray-700">{{ $formatCurrency($totales['desembolso'] ?? 0) }}</span>
        </div>

        {{-- Comentario: mostramos un aviso en lenguaje natural cuando no existen créditos desembolsados. --}}
        @if(empty($desembolsos))
          <p class="text-sm text-gray-600">No hay créditos desembolsados registrados.</p>
        @else
          <div class="text-xs font-semibold text-gray-500 grid grid-cols-12 gap-2 uppercase">
            <span class="col-span-3">Fecha</span>
            <span class="col-span-4">Cliente</span>
            <span class="col-span-3 text-center">Pago</span>
            <span class="col-span-2 text-right">Monto</span>
          </div>

          {{-- Comentario: en la lista de desembolsos solo dejamos visibles las decisiones de aceptar o rechazar. --}}
          <div class="divide-y divide-gray-200 text-sm space-y-0">
            @foreach($desembolsos as $item)
              <div
                class="grid grid-cols-12 gap-2 py-3 px-2 rounded-xl"
                :class="rowClasses({{ $item['id'] ?? 'null' }})"
              >
                <span class="col-span-3 text-gray-700">{{ $item['fecha_texto'] ?? '---' }}</span>
                <div class="col-span-4 text-gray-700">
                  <p class="font-medium">{{ $item['cliente'] ?? 'Sin nombre' }}</p>
                  <p class="text-xs text-gray-500">Crédito #{{ $item['id'] ?? 'N/A' }}</p>
                </div>
                <div class="col-span-3 flex items-center justify-center">
                  <div class="flex items-center gap-1">
                    <button
                      type="button"
                      class="w-8 h-8 rounded-full border-2 border-green-500 text-green-600 flex items-center justify-center"
                      title="Aceptar cliente"
                      @click="setDecision({{ $item['id'] ?? 'null' }}, 'accepted')"
                    >
                      ✅
                    </button>
                    <button
                      type="button"
                      class="w-8 h-8 rounded-full border-2 border-red-500 text-red-600 flex items-center justify-center"
                      title="Rechazar cliente"
                      @click="setDecision({{ $item['id'] ?? 'null' }}, 'rejected')"
                    >
                      ❌
                    </button>
                  </div>
                </div>
                <div class="col-span-2 text-right font-medium text-gray-800">
                  {{ $formatCurrency($item['monto'] ?? 0) }}
                </div>
              </div>
            @endforeach
          </div>

          {{-- Comentario: mostramos un resumen del estado temporal de los clientes aceptados y rechazados. --}}
          <div class="mt-3 text-xs text-gray-600 space-y-1">
            <p><strong>Clientes aceptados:</strong> <span x-text="acceptedIds.length"></span></p>
            <p><strong>Clientes rechazados:</strong> <span x-text="rejectedIds.length"></span></p>
          </div>

          <template x-if="feedbackMessage">
            <div
              class="mt-3 px-3 py-2 rounded-lg text-xs"
              :class="feedbackClass"
              x-text="feedbackMessage"
            ></div>
          </template>
        @endif
      </section>

      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-800">Recreditos</h3>
          <span class="text-xs font-semibold text-gray-700">{{ $formatCurrency($recreditosTotal) }}</span>
        </div>

        @if(empty($recreditosItems))
          <p class="text-sm text-gray-600">No se registraron recreditos en el periodo seleccionado.</p>
        @else
          <div class="text-xs font-semibold text-gray-500 grid grid-cols-12 gap-2 uppercase">
            <span class="col-span-4">Cliente</span>
            <span class="col-span-2 text-center">Fecha</span>
            <span class="col-span-2 text-right">Nuevo</span>
            <span class="col-span-2 text-right">Anterior</span>
            <span class="col-span-2 text-right">Saldo</span>
          </div>
          <div class="divide-y divide-gray-200 text-sm">
            @foreach($recreditosItems as $item)
              <div class="grid grid-cols-12 gap-2 py-2">
                <span class="col-span-4 text-gray-700">{{ $item['cliente'] ?? 'Sin nombre' }}</span>
                <span class="col-span-2 text-center text-gray-600">{{ $item['fecha_texto'] ?? '---' }}</span>
                <span class="col-span-2 text-right text-gray-700">{{ $formatCurrency($item['monto_nuevo'] ?? 0) }}</span>
                <span class="col-span-2 text-right text-gray-700">{{ $formatCurrency($item['monto_anterior'] ?? 0) }}</span>
                <span class="col-span-2 text-right font-medium text-gray-800">{{ $formatCurrency($item['saldo_post'] ?? 0) }}</span>
              </div>
            @endforeach
          </div>
          <div class="pt-3 text-xs text-gray-600 space-y-1">
            <div class="flex justify-between">
              <span>Total nuevo</span>
              <span class="font-semibold text-gray-800">{{ $formatCurrency($recreditosTotalNuevo) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Total anterior</span>
              <span class="font-semibold text-gray-800">{{ $formatCurrency($recreditosTotalAnterior) }}</span>
            </div>
          </div>
        @endif
      </section>

      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-800">Cobranza semanal</h3>
          <span class="text-xs font-semibold text-gray-700">{{ $formatCurrency($cobranzaTotal) }}</span>
        </div>

        @if(empty($cobranzaDias))
          <p class="text-sm text-gray-600">Sin pagos registrados en la semana.</p>
        @else
          <div class="divide-y divide-gray-200 text-sm">
            @foreach($cobranzaDias as $dia)
              <div class="flex items-center justify-between py-2">
                <div>
                  <p class="text-xs uppercase text-gray-500">{{ $dia['dia'] ?? 'Dia' }}</p>
                  <p class="text-sm text-gray-700">{{ $dia['fecha_texto'] ?? '---' }}</p>
                </div>
                <p class="text-sm font-semibold text-gray-800">{{ $formatCurrency($dia['total'] ?? 0) }}</p>
              </div>
            @endforeach
          </div>
        @endif
      </section>

      <section class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">Adelantos</h3>
            <span class="text-xs font-semibold text-gray-700">{{ $formatCurrency($adelantosTotal) }}</span>
          </div>

          @if(empty($adelantosItems))
            <p class="text-sm text-gray-600">Sin adelantos registrados.</p>
          @else
            <div class="divide-y divide-gray-200 text-sm">
              @foreach($adelantosItems as $item)
                <div class="py-2 space-y-1">
                  <p class="text-sm text-gray-700 font-medium">{{ $item['cliente'] ?? 'Sin nombre' }}</p>
                  <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $item['fecha_texto'] ?? '---' }}</span>
                    <span class="text-sm font-semibold text-gray-800">{{ $formatCurrency($item['monto'] ?? 0) }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">Recuperacion</h3>
            <span class="text-xs font-semibold text-gray-700">{{ $formatCurrency($recuperacionTotal) }}</span>
          </div>

          @if(empty($recuperacionItems))
            <p class="text-sm text-gray-600">Sin pagos de recuperacion registrados.</p>
          @else
            <div class="divide-y divide-gray-200 text-sm">
              @foreach($recuperacionItems as $item)
                <div class="py-2 space-y-1">
                  <p class="text-sm text-gray-700 font-medium">{{ $item['cliente'] ?? 'Sin nombre' }}</p>
                  <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $item['fecha_texto'] ?? '---' }}</span>
                    <span class="text-sm font-semibold text-gray-800">{{ $formatCurrency($item['monto'] ?? 0) }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </section>

      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <h3 class="text-sm font-semibold text-gray-800">Sumatorias</h3>

        <div class="space-y-2 text-sm text-gray-700">
          <div class="flex items-center justify-between">
            <span>Cartera real</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($totales['cartera_real'] ?? 0) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Total fallo</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($totales['fallo'] ?? $falloTotal) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Total prestamos</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($totales['prestamos'] ?? 0) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Total recuperacion</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($totales['recuperacion'] ?? $recuperacionTotal) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Total adelantos</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($totales['adelantos'] ?? $adelantosTotal) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Total recreditos</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($totales['recreditos'] ?? $recreditosTotal) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Total desembolso</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($totales['desembolso'] ?? 0) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Fondo de ahorro</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($totales['fondo_ahorro'] ?? ($cierres['fondo_ahorro'] ?? 0)) }}</span>
          </div>
        </div>

        <div class="pt-3 border-t border-gray-200 space-y-2 text-sm">
          <div class="flex items-center justify-between text-indigo-600 font-semibold">
            <span>Total lado izquierdo</span>
            <span>{{ $formatCurrency($totales['total_izquierdo'] ?? 0) }}</span>
          </div>
          <div class="flex items-center justify-between text-indigo-600 font-semibold">
            <span>Total final</span>
            <span>{{ $formatCurrency($totales['total_final'] ?? 0) }}</span>
          </div>
        </div>
      </section>

      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <h3 class="text-sm font-semibold text-gray-800">Cierres</h3>
        <div class="space-y-2 text-sm text-gray-700">
          <div class="flex items-center justify-between">
            <span>Fondo de ahorro</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($cierres['fondo_ahorro'] ?? 0) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Comision promotora</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($cierres['comisiones_prom'] ?? 0) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Comision supervisor</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($cierres['comisiones_superv'] ?? 0) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Otros (diferidos)</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($cierres['otros'] ?? 0) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Inversion</span>
            <span class="font-semibold text-gray-800">{{ $formatCurrency($cierres['inversion'] ?? 0) }}</span>
          </div>
        </div>
      </section>

      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-4">
        <h3 class="text-sm font-semibold text-gray-800">Firmas</h3>
        <div class="grid grid-cols-1 gap-6 text-center">
          <div>
            <div class="h-12 border-b border-gray-300"></div>
            <p class="mt-1 text-[11px] text-gray-500 uppercase">Nombre y firma supervisor</p>
          </div>
          <div>
            <div class="h-12 border-b border-gray-300"></div>
            <p class="mt-1 text-[11px] text-gray-500 uppercase">Nombre y firma promotora</p>
          </div>
          <div>
            <div class="h-12 border-b border-gray-300"></div>
            <p class="mt-1 text-[11px] text-gray-500 uppercase">Nombre y firma del validador</p>
          </div>
        </div>
      </section>
    @endif

    <section class="grid grid-cols-2 gap-3">
      <a href="{{ route('mobile.index') }}"
         class="text-center px-4 py-3 rounded-2xl border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
        Regresar
      </a>

      @if($pdfUrl)
        <button
           type="button"
           class="text-center px-4 py-3 rounded-2xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
           @click="exportPdf"
        >
          Exportar PDF
        </button>
      @else
        <span class="text-center px-4 py-3 rounded-2xl border border-dashed border-gray-300 text-sm text-gray-400">
          Exportar PDF
        </span>
      @endif
    </section>
    @include('mobile.modals.calculadora')
  </div>

  {{-- Comentario: este script define la lógica interactiva para pagos y selección temporal de clientes. --}}
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('desembolsoPage', (config) => ({
        registerUrl: config.registerUrl,
        falloRegisterUrl: config.falloRegisterUrl ?? null,
        promotorId: config.promotorId,
        supervisorQuery: config.supervisorQuery ?? {},
        pdfUrl: config.pdfUrl ?? null,
        ejecutivoId: config.ejecutivoId ?? null,
        decisions: {},
        feedbackMessage: null,
        feedbackType: 'success',
        processingPayment: false,
        currentPaymentContext: null,
        get acceptedIds() {
          return Object.keys(this.decisions).filter((id) => this.decisions[id] === 'accepted');
        },
        get rejectedIds() {
          return Object.keys(this.decisions).filter((id) => this.decisions[id] === 'rejected');
        },
        get feedbackClass() {
          return this.feedbackType === 'error'
            ? 'bg-red-100 border border-red-200 text-red-700'
            : 'bg-green-100 border border-green-200 text-green-700';
        },
        rowClasses(id) {
          const decision = this.decisions[id];
          if (decision === 'accepted') {
            return 'bg-green-50 border border-green-200';
          }
          if (decision === 'rejected') {
            return 'bg-red-50 border border-red-200';
          }
          return '';
        },
        setDecision(id, value) {
          if (id === null || id === undefined) {
            return;
          }
          this.decisions[id] = value;
        },
        normalizeAmount(value) {
          if (typeof value === 'number') {
            return Number.isFinite(value) ? Math.round(value * 100) / 100 : null;
          }

          if (typeof value === 'string') {
            const sanitized = value.replace(/[^0-9,.-]+/g, '').replace(',', '.');
            if (!sanitized.length) {
              return null;
            }

            const numeric = Number(sanitized);
            return Number.isFinite(numeric) ? Math.round(numeric * 100) / 100 : null;
          }

          return null;
        },
        openPaymentModal(cliente, options = {}) {
          if (!this.promotorId) {
            this.feedbackType = 'error';
            this.feedbackMessage = 'Selecciona una promotora antes de registrar pagos.';
            return;
          }

          const calcStore = Alpine.store('calc');
          if (!calcStore) {
            this.feedbackType = 'error';
            this.feedbackMessage = 'No se encontró la calculadora de pagos.';
            return;
          }

          const contextType = options?.type ?? 'desembolso';
          const pendingAmount = options?.pendingAmount ?? null;
          const falloId = options?.falloId ?? null;

          this.currentPaymentContext = {
            type: contextType,
            cliente,
            falloId,
            pendingAmount,
          };
          this.feedbackMessage = null;

          calcStore.open({
            client: cliente?.nombre ?? 'Cliente sin nombre',
            context: {
              mode: contextType === 'fallo' ? 'falloPayment' : 'singleDesembolsoPayment',
              pendingAmount,
              falloId,
            },
            clientData: { id: cliente?.id ?? null },
            onAccept: (payload) => this.submitPayment(cliente, payload),
          });
        },
        submitPayment(cliente, payload) {
          const contextType = this.currentPaymentContext?.type ?? 'desembolso';

          if (contextType === 'fallo') {
            this.submitFalloPayment(payload);
            return;
          }

          if (!cliente?.id || !this.registerUrl) {
            this.feedbackType = 'error';
            this.feedbackMessage = 'No se pudo identificar el crédito para registrar el pago.';
            this.currentPaymentContext = null;
            return;
          }

          const tipo = payload?.mode === 'deferred' ? 'diferido' : 'completo';
          const monto = tipo === 'diferido' ? payload?.amount ?? null : null;

          this.processingPayment = true;
          this.feedbackMessage = null;

          window.axios.post(this.registerUrl, {
            promotor_id: this.promotorId,
            credito_id: cliente.id,
            tipo,
            monto,
            ejecutivo_id: this.ejecutivoId,
          }, {
            params: this.supervisorQuery,
          })
            .then((response) => {
              const data = response?.data ?? {};
              this.feedbackType = 'success';
              this.feedbackMessage = data?.message ?? 'Pago registrado correctamente.';
            })
            .catch((error) => {
              this.feedbackType = 'error';
              const response = error?.response;
              const data = response?.data ?? {};

              if (data?.errors && typeof data.errors === 'object') {
                const firstError = Object.values(data.errors)
                  .flat()
                  .find((value) => typeof value === 'string' && value.trim().length);

                if (firstError) {
                  this.feedbackMessage = firstError;
                  this.currentPaymentContext = null;
                  return;
                }
              }

              if (data?.message) {
                this.feedbackMessage = data.message;
              } else {
                this.feedbackMessage = 'No se pudo registrar el pago, intenta nuevamente.';
              }
            })
            .finally(() => {
              this.processingPayment = false;
              this.currentPaymentContext = null;
            });
        },
        submitFalloPayment(payload) {
          if (!this.falloRegisterUrl) {
            this.feedbackType = 'error';
            this.feedbackMessage = 'No se configuró la ruta para registrar pagos de fallos.';
            this.currentPaymentContext = null;
            return;
          }

          const context = this.currentPaymentContext ?? {};
          const falloId = context.falloId ?? payload?.clientId ?? context?.cliente?.id ?? null;

          if (!falloId) {
            this.feedbackType = 'error';
            this.feedbackMessage = 'No se pudo identificar el fallo a recuperar.';
            this.currentPaymentContext = null;
            return;
          }

          const tipo = payload?.mode === 'deferred' ? 'diferido' : 'completo';
          let monto = null;

          if (tipo === 'diferido') {
            monto = this.normalizeAmount(payload?.amount ?? null);
          } else {
            monto = this.normalizeAmount(context.pendingAmount ?? null);
          }

          if (monto === null || monto <= 0) {
            this.feedbackType = 'error';
            this.feedbackMessage = 'Ingresa un monto válido para registrar el pago recuperado.';
            this.currentPaymentContext = null;
            return;
          }

          this.processingPayment = true;
          this.feedbackMessage = null;

          window.axios.post(this.falloRegisterUrl, {
            promotor_id: this.promotorId,
            accion: 'registrar_pago',
            fallo_id: falloId,
            monto,
            ejecutivo_id: this.ejecutivoId,
          }, {
            params: this.supervisorQuery,
          })
            .then((response) => {
              const data = response?.data ?? {};
              this.feedbackType = 'success';
              this.feedbackMessage = data?.message ?? 'Pago de fallo registrado correctamente.';
            })
            .catch((error) => {
              this.feedbackType = 'error';
              const response = error?.response;
              const data = response?.data ?? {};

              if (data?.errors && typeof data.errors === 'object') {
                const firstError = Object.values(data.errors)
                  .flat()
                  .find((value) => typeof value === 'string' && value.trim().length);

                if (firstError) {
                  this.feedbackMessage = firstError;
                  return;
                }
              }

              if (data?.message) {
                this.feedbackMessage = data.message;
              } else {
                this.feedbackMessage = 'No se pudo registrar el pago recuperado, intenta nuevamente.';
              }
            })
            .finally(() => {
              this.processingPayment = false;
              this.currentPaymentContext = null;
            });
        },
        exportPdf() {
          if (!this.pdfUrl) {
            return;
          }

          const target = new URL(this.pdfUrl, window.location.origin);
          if (this.acceptedIds.length) {
            target.searchParams.set('aceptados', this.acceptedIds.join(','));
          } else {
            target.searchParams.delete('aceptados');
          }

          window.location.href = target.toString();
        },
      }));
    });
  </script>
</x-layouts.mobile.mobile-layout>
