{{-- resources/views/supervisor/objetivo.blade.php --}}
@php
    /** @var string|null $supervisorNombre */
    /** @var float|null $objetivoSemanalTotal */
    /** @var float|null $ventaSemanalTotal */
    /** @var float|null $faltanteSemanalTotal */
    /** @var float|null $porcentajeSemanalTotal */
    /** @var float|null $objetivoEjercicioTotal */
    /** @var float|null $ventaEjercicioTotal */
    /** @var float|null $faltanteEjercicioTotal */
    /** @var float|null $porcentajeEjercicioTotal */
    /** @var \Illuminate\Support\Collection<int, array<string, mixed>>|array $promotoresResumen */

    $formatCurrency = static fn ($value) => '$' . number_format((float) $value, 2, '.', ',');
    $formatPercentage = static fn ($value) => number_format((float) $value, 1, '.', ',') . '%';

    $supervisorLabel = $supervisorNombre ?? (auth()->user()->name ?? 'Supervisor');
    $promotores = collect($promotoresResumen ?? []);

    $pctSem = min(100, max(0, (float) ($porcentajeSemanalTotal ?? 0)));
    $pctEje = min(100, max(0, (float) ($porcentajeEjercicioTotal ?? 0)));
@endphp

<x-layouts.mobile.mobile-layout title="Objetivo – {{ $supervisorLabel }}">
  <div class="p-4 w-full max-w-md mx-auto space-y-6">

    {{-- DIV1: Semana --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
      <h2 class="text-base font-bold text-gray-900 mb-3">Semana (ventas)</h2>
      <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
        <div>
          <p class="text-gray-500">Supervisor</p>
          <p class="font-semibold">{{ $supervisorLabel }}</p>
        </div>
        <div>
          <p class="text-gray-500">Objetivo Sem</p>
          <p class="font-bold text-indigo-700">{{ $formatCurrency($objetivoSemanalTotal ?? 0) }}</p>
        </div>
        <div>
          <p class="text-gray-500">Venta Sem</p>
          <p class="font-semibold text-green-700">{{ $formatCurrency($ventaSemanalTotal ?? 0) }}</p>
        </div>
        <div>
          <p class="text-gray-500">Para llegar</p>
          <p class="font-semibold text-amber-700">{{ $formatCurrency($faltanteSemanalTotal ?? 0) }}</p>
        </div>
      </div>

      <div class="mt-4">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
          <span>Progreso semanal</span>
          <span>{{ $formatPercentage($porcentajeSemanalTotal ?? 0) }}</span>
        </div>
        <div class="h-2.5 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600" style="width: {{ $pctSem }}%"></div>
        </div>
      </div>
    </div>

    {{-- DIV2: Ejercicio --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
      <h2 class="text-base font-bold text-gray-900 mb-3">Ejercicio (acumulado)</h2>
      <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
        <div>
          <p class="text-gray-500">Objetivo Ejercicio</p>
          <p class="font-bold text-indigo-700">{{ $formatCurrency($objetivoEjercicioTotal ?? 0) }}</p>
        </div>
        <div>
          <p class="text-gray-500">Venta en Ejercicio</p>
          <p class="font-semibold text-green-700">{{ $formatCurrency($ventaEjercicioTotal ?? 0) }}</p>
        </div>
        <div>
          <p class="text-gray-500">Para llegar</p>
          <p class="font-semibold text-amber-700">{{ $formatCurrency($faltanteEjercicioTotal ?? 0) }}</p>
        </div>
      </div>

      <div class="mt-4">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
          <span>Progreso ejercicio</span>
          <span>{{ $formatPercentage($porcentajeEjercicioTotal ?? 0) }}</span>
        </div>
        <div class="h-2.5 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full bg-gradient-to-r from-emerald-500 to-green-600" style="width: {{ $pctEje }}%"></div>
        </div>
      </div>
    </div>

    {{-- Barra divisora --}}
    <div class="relative">
      <div class="h-0.5 bg-gray-200"></div>
    </div>

    {{-- DIV3: Promotores --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
      <h2 class="text-base font-bold text-gray-900 mb-3">Promotores</h2>

      <div class="space-y-3">
        @foreach($promotores as $idx => $p)
          @php
            $promotorPct = min(100, max(0, (float) ($p['porcentaje'] ?? 0)));
          @endphp
          <div class="rounded-xl border border-gray-100 p-3 shadow-sm">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-7 h-7 text-[11px] font-bold rounded-full bg-gray-200 text-gray-700 flex items-center justify-center">{{ $loop->iteration }}</span>
                <div>
                  <p class="text-[14px] font-semibold text-gray-900">{{ $p['nombre'] ?? 'Promotor' }}</p>
                  <p class="text-[12px] text-gray-500">
                    Obj: <span class="font-medium">{{ $formatCurrency($p['objetivo'] ?? 0) }}</span> ·
                    Venta: <span class="font-medium text-green-700">{{ $formatCurrency($p['venta'] ?? 0) }}</span> ·
                    Para llegar: <span class="font-medium text-amber-700">{{ $formatCurrency($p['faltante'] ?? 0) }}</span>
                  </p>
                </div>
              </div>
              <span class="text-[12px] font-semibold text-gray-600">{{ $formatPercentage($p['porcentaje'] ?? 0) }}</span>
            </div>

            {{-- Barra porcentaje: ventas / objetivo --}}
            <div class="mt-2 h-2.5 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-sky-500 to-blue-600" style="width: {{ $promotorPct }}%"></div>
            </div>

            @if(!empty($p['route']) && $p['route'] !== '#')
              <div class="mt-3">
                <a href="{{ $p['route'] }}"
                   class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                  Ver objetivo
                </a>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>

    {{-- DIV4: Botones --}}
    <div class="grid grid-cols-3 gap-3">
      <a href="{{ route("mobile.index") }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-semibold shadow-sm transition">Regresar</a>
      <a href="{{ url()->current() }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm transition">Actualizar</a>
      <a href="{{ route("mobile.$role.objetivo") }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow-sm transition">Reporte</a>
    </div>

  </div>
</x-layouts.mobile.mobile-layout>
