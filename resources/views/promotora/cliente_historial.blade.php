{{-- resources/views/mobile/cliente_historial.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker         = Faker::create('es_MX');
    // Datos de encabezado
    $clientName    = $faker->name();
    $curp          = strtoupper($faker->regexify('[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}'));
    $promotora     = auth()->user()->name;
    $supervisora   = $faker->name();
    $totalLoan     = $faker->randomFloat(2, 20000, 100000);
    $creditDate    = now()->subWeeks(rand(1, 20))->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $totalWeeks    = rand(12, 24);
    $currentWeek   = rand(1, $totalWeeks);
    $weeklyAmount  = $totalLoan / $totalWeeks;
@endphp

<x-layouts.promotora_mobile.mobile-layout title="Historial de {{ $clientName }}">
  <div class="bg-gray-100 min-h-screen p-4 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow p-6 w-full max-w-md">
      {{-- Encabezado --}}
      <h2 class="text-center text-2xl font-bold text-black mb-4">{{ $clientName }}</h2>
      <p class="text-sm text-black mb-1"><span class="font-semibold">CURP:</span> {{ $curp }}</p>
      <p class="text-sm text-black mb-1"><span class="font-semibold">Promotora:</span> {{ $promotora }}</p>
      <p class="text-sm text-black mb-1"><span class="font-semibold">Supervisora:</span> {{ $supervisora }}</p>
      <p class="text-sm text-black mb-1 flex justify-between">
        <span>Total prestado:</span>
        <span class="font-semibold">${{ number_format($totalLoan,2) }}</span>
      </p>
      <p class="text-sm text-black mb-1 flex justify-between">
        <span>Fecha crédito:</span>
        <span class="font-semibold">{{ $creditDate }}</span>
      </p>
      <p class="text-sm text-black mb-4 flex justify-between">
        <span>Semanas crédito:</span>
        <span class="font-semibold">{{ $totalWeeks }}</span>
      </p>
      <p class="text-sm text-black mb-6 flex justify-between">
        <span>Semana actual:</span>
        <span class="font-semibold">sem {{ $currentWeek }}</span>
      </p>

      {{-- Tabla de semanas --}}
      <div class="overflow-x-auto">
        <table class="w-full text-sm table-auto">
          <thead>
            <tr class="border-b">
              <th class="text-left py-2">Semana</th>
              <th class="text-right py-2 pr-4">Monto</th>
              <th class="text-center py-2">Estado</th>
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
              <tr class="border-b">
                <td class="py-2">sem {{ $i }}</td>
                <td class="py-2 text-right pr-4">${{ number_format($weeklyAmount,2) }}</td>
                <td class="py-2 text-center">
                  @if($status === 'Pagado')
                    <span class="bg-green-500 text-white px-2 rounded">{{ $status }}</span>
                  @elseif($status === 'Anticipo')
                    <span class="bg-yellow-400 text-black px-2 rounded">{{ $status }}</span>
                  @elseif($status === 'Atrasado')
                    <span class="bg-red-600 text-white px-2 rounded">{{ $status }}</span>
                    
                  @endif
                </td>
              </tr>
            @endfor
          </tbody>
        </table>
      </div>

      {{-- Botones --}}
      <div class="mt-6 space-y-3">
        <a href="{{ route('promotora.cartera') }}"
           class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg text-center">
          REGRESAR
        </a>
      </div>
    </div>
  </div>
</x-layouts.promotora_mobile.mobile-layout>
