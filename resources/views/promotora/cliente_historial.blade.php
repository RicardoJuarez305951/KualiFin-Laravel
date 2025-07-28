@php
    use Faker\Factory as Faker;
    $faker         = Faker::create('es_MX');
    $clientName    = $faker->name();
    $curp          = strtoupper($faker->regexify('[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}'));
    $promotora     = auth()->user()->name;
    $supervisora   = $faker->name();
    $totalLoan     = $faker->randomFloat(2, 20000, 100000);
    $creditDate    = now()->subWeeks(rand(1, 20))->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $totalWeeks    = rand(12, 24);
    $currentWeek   = rand(1, $totalWeeks);
    $weeklyAmount  = $totalLoan / $totalWeeks;

    function formatCurrency($value) {
        return '$' . number_format($value, 2, '.', ',');
    }
@endphp

<x-layouts.promotora_mobile.mobile-layout title="Historial de {{ $clientName }}">
  <div class="bg-gray-100 min-h-screen p-4 flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md space-y-6">

      {{-- Encabezado --}}
      <div class="text-center space-y-1">
        <h2 class="text-2xl font-bold text-gray-900">{{ $clientName }}</h2>
        <p class="text-sm text-gray-700"><span class="font-semibold">CURP:</span> {{ $curp }}</p>
      </div>

      {{-- Info Cliente --}}
      <div class="grid grid-cols-2 gap-x-4 text-sm text-gray-800">
        <div>
          <p><span class="font-semibold">Promotora:</span> {{ $promotora }}</p>
          <p><span class="font-semibold">Supervisora:</span> {{ $supervisora }}</p>
        </div>
        <div class="text-right">
          <p><span class="font-semibold">Total prestado:</span><br> <span class="text-lg font-bold text-green-700">{{ formatCurrency($totalLoan) }}</span></p>
          <p><span class="font-semibold">Fecha crédito:</span><br> {{ $creditDate }}</p>
          <p><span class="font-semibold">Semanas crédito:</span><br> {{ $totalWeeks }}</p>
          <p><span class="font-semibold">Semana actual:</span><br> sem {{ $currentWeek }}</p>
        </div>
      </div>

      {{-- Tabla de semanas --}}
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
              <tr class="border-b hover:bg-gray-50">
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

      {{-- Botón Regresar --}}
      <a href="{{ route('promotora.cartera') }}"
         class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl text-center shadow-md transition ring-1 ring-blue-900/30 focus:outline-none focus:ring-2 focus:ring-blue-700">
        REGRESAR
      </a>

    </div>
  </div>
</x-layouts.promotora_mobile.mobile-layout>
