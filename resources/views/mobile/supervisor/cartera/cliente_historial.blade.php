{{-- resources/views/promotora/promotora_historial.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker         = Faker::create('es_MX');
    $clientName    = $faker->name();
    $curp          = strtoupper($faker->regexify('[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}'));
    $promotora     = auth()->user()->name;
    $supervisora   = $faker->name();
    $totalLoan     = $faker->randomFloat(2, 20000, 100000);
    $creditDate    = now()->subWeeks(rand(1, 20))->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $totalWeeks    = rand(17, 17);
    $currentWeek   = rand(1, $totalWeeks);
    $zone          = strtoupper($faker->bothify('Z##'));
    $weeklyAmount  = $totalLoan / $totalWeeks;

    function formatCurrency($value) {
        return '$' . number_format($value, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout title="Historial de {{ $clientName }}">
  <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md mx-auto space-y-6">

    {{-- 1. INFO DEL CRÉDITO --}}
    <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
      <div class="space-y-1">
        <label class="block font-semibold">Supervisor</label>
        <div class="border-b border-gray-300 h-6 leading-6">{{ $supervisora }}</div>
      </div>
      <div class="space-y-1">
        <label class="block font-semibold">Semanas del crédito</label>
        <div class="border-b border-gray-300 h-6 leading-6">{{ $totalWeeks }}</div>
      </div>
      <div class="space-y-1">
        <label class="block font-semibold">Fecha de crédito</label>
        <div class="border-b border-gray-300 h-6 leading-6">{{ $creditDate }}</div>
      </div>
      <div class="space-y-1">
        <label class="block font-semibold">Semana actual</label>
        <div class="border-b border-gray-300 h-6 leading-6">sem {{ $currentWeek }}</div>
      </div>
    </div>

    {{-- 2. MONTO Y ZONA --}}
    <div class="grid grid-cols-3 gap-4 text-sm text-gray-800">
      <div class="col-span-2 space-y-1">
        <label class="block font-semibold">Cantidad</label>
        <div class="border-b border-gray-300 h-6 leading-6">
          <span class="text-green-700 font-bold">{{ formatCurrency($totalLoan) }}</span>
        </div>
      </div>
      <div class="space-y-1">
        <label class="block font-semibold">Zona</label>
        <div class="border border-gray-300 rounded h-6 flex items-center justify-center">{{ $zone }}</div>
      </div>
    </div>

    {{-- 3. CLIENTE --}}
    <div class="space-y-1 text-sm text-gray-800">
      <label class="block font-semibold">Cliente</label>
      <div class="border-b border-gray-300 h-6 leading-6">{{ $clientName }}</div>
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
          @for($i = 1; $i <= $totalWeeks; $i++)
            @php
              if($i < $currentWeek) {
                $statusList = ['Pagado','Anticipo','Atrasado'];
                $status = $statusList[array_rand($statusList)];
              } else {
                $status = 'Pagar';
              }
            @endphp
            <tr class="text-xs border-b hover:bg-gray-50">
              <td class="py-2 px-4">sem {{ $i }}</td>
              <td class="py-2 px-4 text-right">{{ formatCurrency($weeklyAmount) }}</td>
              <td class="py-2 px-4 text-center">
                @if($status === 'Pagado')
                  <span class="inline-block bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold tracking-wide">Pagado</span>
                @elseif($status === 'Anticipo')
                  <span class="inline-block bg-yellow-400 text-black px-3 py-1 rounded-full text-xs font-semibold tracking-wide">Anticipo</span>
                @elseif($status === 'Atrasado')
                  <span class="inline-block bg-red-600 text-white px-3 py-1 rounded-full text-xs font-semibold tracking-wide">Atrasado</span>
                @else
                  <span class="inline-block bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold tracking-wide">Pagar</span>
                @endif
              </td>
            </tr>
          @endfor
        </tbody>
      </table>
    </div>

    {{-- 5. BOTÓN REGRESAR --}}
    <a href="{{ route('mobile.cartera') }}"
       class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl text-center shadow-md transition ring-1 ring-blue-900/30 focus:outline-none focus:ring-2 focus:ring-blue-700">
      REGRESAR
    </a>

  </div>
</x-layouts.mobile.mobile-layout>
