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
  <div class="p-4 w-full max-w-md mx-auto space-y-5">
    @if(session('status'))
      <div class="px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-800">
        {{ session('status') }}
      </div>
    @endif

    @if($errors->any())
      <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-800 space-y-1">
        <p class="font-semibold">No se pudieron registrar los fallos recuperados.</p>
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
          <p class="text-xs text-gray-500">Ajusta los montos recuperados y registra los pagos para actualizar el reporte.</p>

          <form method="POST"
                action="{{ route('mobile.ejecutivo.desembolso.registrar_fallos_recuperados') }}"
                class="space-y-3">
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

            <div class="text-xs font-semibold text-gray-500 grid grid-cols-12 gap-2 uppercase">
              <span class="col-span-4">Fecha</span>
              <span class="col-span-5">Cliente</span>
              <span class="col-span-3 text-right">Monto recuperado</span>
            </div>

            <div class="divide-y divide-gray-200 text-sm">
              @foreach($falloItems as $index => $item)
                <div class="grid grid-cols-12 gap-2 py-2">
                  <input type="hidden" name="fallos[{{ $index }}][id]"
                         value="{{ old('fallos.' . $index . '.id', $item['id'] ?? '') }}">

                  <span class="col-span-4 text-gray-700">{{ $item['fecha_texto'] ?? '---' }}</span>
                  <span class="col-span-5 text-gray-700">{{ $item['cliente'] ?? 'Sin nombre' }}</span>

                  <div class="col-span-3 text-right">
                    <label class="sr-only" for="fallo-{{ $index }}-monto">Monto recuperado</label>
                    <div class="relative">
                      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500">$</span>
                      <input id="fallo-{{ $index }}-monto"
                             name="fallos[{{ $index }}][monto]"
                             type="number"
                             step="0.01"
                             min="0"
                             inputmode="decimal"
                             value="{{ old('fallos.' . $index . '.monto', isset($item['monto']) ? number_format((float) $item['monto'], 2, '.', '') : '0.00') }}"
                             class="w-full rounded-lg border border-gray-300 bg-white py-1.5 pr-3 pl-6 text-sm text-right font-medium text-gray-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    </div>
                    <p class="mt-1 text-[11px] text-gray-500">Pendiente: {{ $formatCurrency($item['monto'] ?? 0) }}</p>
                    @error('fallos.' . $index . '.monto')
                      <p class="mt-1 text-xs text-red-600 text-right">{{ $message }}</p>
                    @enderror
                    @error('fallos.' . $index . '.id')
                      <p class="mt-1 text-xs text-red-600 text-right">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
              @endforeach
            </div>

            @error('fallos')
              <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="pt-2 flex justify-end">
              <button type="submit"
                      class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                Registrar fallos recuperados
              </button>
            </div>
          </form>
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

        @if(empty($desembolsos))
          <p class="text-sm text-gray-600">No hay créditos desembolsados registrados.</p>
        @else
          <div class="text-xs font-semibold text-gray-500 grid grid-cols-12 gap-2 uppercase">
            <span class="col-span-4">Fecha</span>
            <span class="col-span-6">Cliente</span>
            <span class="col-span-2 text-right">Monto</span>
          </div>
          <div class="divide-y divide-gray-200 text-sm">
            @foreach($desembolsos as $item)
              <div class="grid grid-cols-12 gap-2 py-2">
                <span class="col-span-4 text-gray-700">{{ $item['fecha_texto'] ?? '---' }}</span>
                <span class="col-span-6 text-gray-700">{{ $item['cliente'] ?? 'Sin nombre' }}</span>
                <span class="col-span-2 text-right font-medium text-gray-800">{{ $formatCurrency($item['monto'] ?? 0) }}</span>
              </div>
            @endforeach
          </div>
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
        <a href="{{ $pdfUrl }}"
           class="text-center px-4 py-3 rounded-2xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
          Exportar PDF
        </a>
      @else
        <span class="text-center px-4 py-3 rounded-2xl border border-dashed border-gray-300 text-sm text-gray-400">
          Exportar PDF
        </span>
      @endif
    </section>
  </div>
</x-layouts.mobile.mobile-layout>
