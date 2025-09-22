{{-- resources/views/mobile/promotor/cartera/cliente_historial.blade.php --}}
@php
    use Illuminate\Support\Carbon;

    $user = auth()->user();
    $clienteNombre = trim(collect([$cliente->nombre, $cliente->apellido_p, $cliente->apellido_m])->filter()->implode(' '));
    $clienteNombre = $clienteNombre !== '' ? $clienteNombre : 'Cliente sin nombre';

    $credito = $cliente->credito;
    $pagos = $credito?->pagosProyectados?->sortBy('semana') ?? collect();
    $totalSemanas = $pagos->count();
    $montoTotal = (float) ($credito->monto_total ?? 0);

    $now = now();
    $proximoPago = $pagos->first(function ($pago) use ($now) {
        $fechaLimite = $pago->fecha_limite instanceof Carbon
            ? $pago->fecha_limite
            : ($pago->fecha_limite ? Carbon::parse($pago->fecha_limite) : null);

        return $fechaLimite && $fechaLimite->endOfDay()->greaterThanOrEqualTo($now);
    });

    $semanaActual = $proximoPago?->semana ?? ($pagos->last()?->semana ?? null);
    $semanaActualTexto = $semanaActual ? 'sem ' . $semanaActual : 'N/A';

    $fechaCredito = $credito && $credito->fecha_inicio
        ? $credito->fecha_inicio
            ?->clone()
            ?->locale('es')
            ?->isoFormat('D [de] MMMM [de] YYYY')
        : null;

    $formatCurrency = static function ($value): string {
        return '$' . number_format((float) $value, 2, '.', ',');
    };

    $estadoBadge = static function (?string $estado) {
        $estado = strtolower((string) $estado);
        return match ($estado) {
            'pagado' => ['Pagado', 'bg-green-500 text-white'],
            'anticipo', 'pagado_parcial' => ['Parcial', 'bg-yellow-400 text-black'],
            'atrasado', 'mora', 'vencido' => ['Atrasado', 'bg-red-600 text-white'],
            'pendiente', 'proyectado', 'por_cobrar' => ['Pendiente', 'bg-gray-200 text-gray-700'],
            default => [$estado !== '' ? ucfirst(str_replace('_', ' ', $estado)) : 'Sin registro', 'bg-gray-200 text-gray-700'],
        };
    };

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

      {{-- 4. TABLA DE SEMANAS --}}
      <div class="overflow-x-auto border rounded-lg shadow-sm">
        <table class="w-full text-sm table-auto border-collapse">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left py-3 px-4 border-b">Semana</th>
              <th class="text-right py-3 px-4 border-b">Monto</th>
              <th class="text-center py-3 px-4 border-b">Estado</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($pagos as $pago)
              @php
                $estado = $estadoBadge($pago->estado);
                $monto = $formatCurrency($pago->monto_proyectado);
              @endphp
              <tr class="text-xs border-b hover:bg-gray-50">
                <td class="py-2 px-4">sem {{ $pago->semana }}</td>
                <td class="py-2 px-4 text-right">{{ $monto }}</td>
                <td class="py-2 px-4 text-center">
                  <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold tracking-wide {{ $estado[1] }}">
                    {{ $estado[0] }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="py-4 px-4 text-center text-xs text-gray-500">No hay pagos proyectados registrados.</td>
              </tr>
            @endforelse
          </tbody>
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
