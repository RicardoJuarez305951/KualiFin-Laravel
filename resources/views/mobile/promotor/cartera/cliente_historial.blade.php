{{-- resources/views/mobile/promotor/cartera/cliente_historial.blade.php --}}
@php
    use Illuminate\Support\Carbon;

    $formatCurrency = static function ($value): string {
        return '$' . number_format((float) $value, 2, '.', ',');
    };

    $clienteNombre = trim(collect([$cliente->nombre, $cliente->apellido_p, $cliente->apellido_m])->filter()->implode(' '));
    $clienteNombre = $clienteNombre !== '' ? $clienteNombre : 'Cliente sin nombre';

    $credito = $cliente->credito;
    $pagosProyectados = $credito?->pagosProyectados?->sortBy('semana') ?? collect();
    $totalSemanas = $pagosProyectados->count();
    $montoTotal = $resumenFinanciero['prestamo'] ?? 0.0;
    $semanaActualTexto = $semanaActual ? 'sem ' . $semanaActual : 'N/A';

    $fechaCredito = $credito && $credito->fecha_inicio
        ? $credito->fecha_inicio instanceof Carbon
            ? $credito->fecha_inicio->clone()->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
            : Carbon::parse($credito->fecha_inicio)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
        : null;

    $supervisorName = optional($cliente->promotor?->supervisor?->user)->name;
    $promotorName = optional($cliente->promotor?->user)->name;
    $zona = $cliente->promotor->colonia ?? 'Sin definir';
@endphp

<x-layouts.mobile.mobile-layout title="Historial de {{ $clienteNombre }}">
  <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md mx-auto space-y-6">

    @if (!$credito)
      <div class="text-center text-sm text-gray-600">
        Este cliente aun no cuenta con un credito registrado.
      </div>
    @else
      {{-- 1. INFO DEL CREDITO --}}
      <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
        @role('promotor')
          @if ($supervisorName)
            <div class="space-y-1">
              <label class="block font-semibold">Supervisor</label>
              <div class="border-b border-gray-300 h-6 leading-6">{{ $supervisorName }}</div>
            </div>
          @endif
        @endrole

        @hasanyrole('supervisor|ejecutivo|administrativo|superadmin')
          @if ($promotorName)
            <div class="space-y-1">
              <label class="block font-semibold">Promotor</label>
              <div class="border-b border-gray-300 h-6 leading-6">{{ $promotorName }}</div>
            </div>
          @endif
        @endhasanyrole

        <div class="space-y-1">
          <label class="block font-semibold">Semanas del credito</label>
          <div class="border-b border-gray-300 h-6 leading-6">{{ $totalSemanas ?: 'N/A' }}</div>
        </div>
        <div class="space-y-1">
          <label class="block font-semibold">Fecha de credito</label>
          <div class="border-b border-gray-300 h-6 leading-6">{{ $fechaCredito ?? 'Sin fecha' }}</div>
        </div>
        <div class="space-y-1">
          <label class="block font-semibold">Semana actual</label>
          <div class="border-b border-gray-300 h-6 leading-6">{{ $semanaActualTexto }}</div>
        </div>
      </div>
      
      {{-- 2. MONTO Y ZONA --}}
      <div class="grid grid-cols-3 gap-4 text-sm text-gray-800">
        <div class="col-span-2 space-y-1">
          <label class="block font-semibold">Monto del credito</label>
          <div class="border-b border-gray-300 h-6 leading-6">
            <span class="text-green-700 font-bold">{{ $formatCurrency($montoTotal) }}</span>
          </div>
        </div>
        <div class="space-y-1">
          <label class="block font-semibold">Zona</label>
          <div class="border border-gray-300 rounded h-6 flex items-center justify-center">{{ $zona }}</div>
        </div>
      </div>
      
      {{-- 3. CLIENTE --}}
      
      <div class="space-y-1 text-sm text-gray-800">
        <label class="block font-semibold">Cliente</label>
        <div class="border-b border-gray-300 h-6 leading-6">{{ $clienteNombre }}</div>
      </div>

      <div class="space-y-1 text-sm text-gray-800">
        <label class="block font-semibold">CURP</label>
        <div class="border-b border-gray-300 h-6 leading-6">{{ $cliente->CURP ?? 'Sin CURP' }}</div>
      </div>



      {{-- METRICAS --}}
      <div class="grid grid-cols-3 gap-3 text-xs sm:text-sm">
        <div class="p-3 rounded-xl bg-blue-50 border border-blue-100">
          <p class="font-semibold text-blue-700 uppercase tracking-wide text-[11px]">Prestamo pedido</p>
          <p class="text-lg font-bold text-blue-900">{{ $formatCurrency($resumenFinanciero['prestamo'] ?? 0) }}</p>
        </div>
        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100">
          <p class="font-semibold text-emerald-700 uppercase tracking-wide text-[11px]">Dinero recuperado</p>
          <p class="text-lg font-bold text-emerald-900">{{ $formatCurrency($resumenFinanciero['recuperado'] ?? 0) }}</p>
        </div>
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-100">
          <p class="font-semibold text-amber-700 uppercase tracking-wide text-[11px]">Debe proyectado a la fecha</p>
          <p class="text-lg font-bold text-amber-900">
            {{ $formatCurrency($resumenFinanciero['debe_proyectado'] ?? 0) }}
          </p>
        </div>
      </div>

      @if(($tablaDebeSemanal ?? collect())->isNotEmpty())
        <div class="border rounded-xl shadow-sm overflow-hidden">
          <div class="bg-gray-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-600">
            Debe proyectado semanal
          </div>
          <table class="w-full text-sm table-auto border-collapse">
            <thead class="bg-white">
              <tr>
                <th class="text-left py-2 px-4 border-b text-xs uppercase tracking-wide">Semana</th>
                <th class="text-right py-2 px-4 border-b text-xs uppercase tracking-wide">Monto</th>
              </tr>
            </thead>
            <tbody>
              @foreach($tablaDebeSemanal as $item)
                <tr class="text-xs border-b last:border-b-0">
                  <td class="py-2 px-4">Sem {{ $item['semana'] ?? 'N/A' }}</td>
                  <td class="py-2 px-4 text-right font-semibold text-gray-800">{{ $formatCurrency($item['monto'] ?? 0) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

      

      {{-- 4. TABLA DE SEMANAS --}}
      <div class="overflow-x-auto border rounded-lg shadow-sm">
        <table class="w-full text-sm table-auto border-collapse">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left py-3 px-4 border-b text-xs uppercase tracking-wide">Fecha</th>
              <th class="text-center py-3 px-4 border-b text-xs uppercase tracking-wide">Semana</th>
              <th class="text-left py-3 px-4 border-b text-xs uppercase tracking-wide">Movimiento</th>
              <th class="text-right py-3 px-4 border-b text-xs uppercase tracking-wide">Monto</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($historialPagos as $movimiento)
              <tr class="text-xs border-b hover:bg-gray-50">
                <td class="py-2 px-4">{{ $movimiento['fecha_texto'] ?? 'Sin fecha' }}</td>
                <td class="py-2 px-4 text-center">{{ $movimiento['semana'] ? 'Sem ' . $movimiento['semana'] : 'N/A' }}</td>
                <td class="py-2 px-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $movimiento['clase'] }}">
                    {{ $movimiento['etiqueta'] }}
                  </span>
                </td>
                <td class="py-2 px-4 text-right font-semibold text-gray-800">{{ $formatCurrency($movimiento['monto']) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="py-4 px-4 text-center text-xs text-gray-500">
                  Aun no se registran pagos para este credito.
                </td>
              </tr>
            @endforelse
          </tbody>
          <tfoot class="bg-gray-50 text-xs">
            @php
                $totalRecuperado = $resumenFinanciero['recuperado'] ?? 0;
                $debeAcumulado = $resumenFinanciero['debe_proyectado'] ?? 0;
                $faltante = $resumenFinanciero['saldo_proyectado'] ?? max(0, $debeAcumulado - $totalRecuperado);
            @endphp
            <tr>
              <th colspan="3" class="py-2 px-4 text-right uppercase tracking-wide text-gray-600">Total recuperado</th>
              <td class="py-2 px-4 text-right font-semibold text-emerald-700">{{ $formatCurrency($totalRecuperado) }}</td>
            </tr>
            <tr>
              <th colspan="3" class="py-2 px-4 text-right uppercase tracking-wide text-gray-600">Debe proyectado (acumulado)</th>
              <td class="py-2 px-4 text-right font-semibold text-amber-900">{{ $formatCurrency($debeAcumulado) }}</td>
            </tr>
            @php
                $diferencia = ($resumenFinanciero['recuperado'] ?? 0) - ($resumenFinanciero['debe_proyectado'] ?? 0);
                $diferenciaColor = 'text-amber-700';
                if ($diferencia > 0.01) {
                    $diferenciaColor = 'text-emerald-700';
                } elseif ($diferencia < -0.01) {
                    $diferenciaColor = 'text-red-600';
                }
            @endphp
            <tr class="border-t">
              <th colspan="3" class="py-2 px-4 text-right uppercase tracking-wide text-gray-600">Diferencia</th>
              <td class="py-2 px-4 text-right font-semibold {{ $diferenciaColor }}">
                {{ $formatCurrency($diferencia) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    @endif

    {{-- 5. BOTON REGRESAR --}}
    <a href="{{ route("mobile.$role.cartera") }}"
       class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl text-center shadow-md transition ring-1 ring-blue-900/30 focus:outline-none focus:ring-2 focus:ring-blue-700">
      REGRESAR
    </a>

  </div>
</x-layouts.mobile.mobile-layout>
