{{-- resources/views/supervisor/historial_cliente.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    $clientName = $faker->name();
    $avalName   = $faker->name();

    $supervisor = auth()->check() ? auth()->user()->name : $faker->name();
    $promotor   = $faker->name();

    $totalLoan   = $faker->randomFloat(2, 20000, 100000);
    $creditDate  = now()->subWeeks(rand(1, 20))->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $totalWeeks  = 17;
    $currentWeek = rand(1, $totalWeeks);

    $weeklyAmount = $totalLoan / $totalWeeks;

    $cliente = [
        'direccion' => $faker->streetAddress.', '.$faker->city,
        'telefono'  => $faker->numerify('55########'),
        'garantias' => ['Teléfono','Pantalla','Consola de videojuegos'],
    ];
    $aval = [
        'direccion' => $faker->streetAddress.', '.$faker->city,
        'telefono'  => $faker->numerify('55########'),
        'garantias' => ['Laptop','Motocicleta'],
    ];

    function formatCurrency($value) {
        return '$' . number_format($value, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout title="Historial de {{ $clientName }}">
  <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md mx-auto space-y-6">

    {{-- 1. INFO DEL CRÉDITO --}}
    <div class="bg-white rounded-2xl shadow-md p-4 space-y-3">
      <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
        <div>
          <p class="font-semibold">Supervisor</p>
          <p>{{ $supervisor }}</p>
        </div>
        <div>
          <p class="font-semibold">Promotor</p>
          <p>{{ $promotor }}</p>
        </div>
        <div>
          <p class="font-semibold">Semanas del crédito</p>
          <p>{{ $totalWeeks }}</p>
        </div>
        <div>
          <p class="font-semibold">Semana actual</p>
          <p>sem {{ $currentWeek }}</p>
        </div>
        <div>
          <p class="font-semibold">Fecha de crédito</p>
          <p>{{ $creditDate }}</p>
        </div>
        <div>
          <p class="font-semibold">Monto</p>
          <p class="font-bold text-green-600">{{ formatCurrency($totalLoan) }}</p>
        </div>
      </div>
    </div>

    {{-- 2. CLIENTE --}}
    <div class="bg-white rounded-2xl shadow-md p-4 space-y-4">
      <h2 class="text-lg font-bold text-gray-900">👤 Cliente</h2>
      <p class="text-sm font-semibold">{{ $clientName }}</p>

      {{-- Dirección --}}
      <div class="grid grid-cols-[90%_10%] gap-2 items-center">
        <div class="text-sm text-gray-800">
          {{ $cliente['direccion'] }}
        </div>
        <div>
          <a href="https://maps.google.com/?q={{ urlencode($cliente['direccion']) }}"
            target="_blank"
            class="w-full flex justify-center items-center px-3 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
            📍
          </a>
        </div>
      </div>

      {{-- Teléfono --}}
      <div class="grid grid-cols-[90%_10%] gap-2 items-center mt-2">
        <div class="text-sm text-gray-800">
          {{ $cliente['telefono'] }}
        </div>
        <div>
          <a href="tel:{{ $cliente['telefono'] }}"
            class="w-full flex justify-center items-center px-3 py-2 text-sm font-semibold bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
            📞
          </a>
        </div>
      </div>

      {{-- Garantías Cliente --}}
      <div class="bg-gray-50 rounded-xl p-3 shadow-inner space-y-2">
        <p class="font-semibold text-gray-700">Garantías</p>
        <ul class="space-y-2">
          @foreach($cliente['garantias'] as $g)
            <li class="flex justify-between items-center bg-white px-3 py-2 rounded-lg shadow-sm border">
              <span class="text-sm text-gray-800">{{ $g }}</span>
              <button class="text-purple-600 text-lg">📷</button>
            </li>
          @endforeach
        </ul>
        <button class="mt-3 w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 rounded-lg shadow">
          Archivo Fotografías 📷
        </button>
      </div>
    </div>

    {{-- 3. AVAL --}}
    <div class="bg-white rounded-2xl shadow-md p-4 space-y-4">
      <h2 class="text-lg font-bold text-gray-900">🧑‍🤝‍🧑 Aval</h2>
      <p class="text-sm font-semibold">{{ $avalName }}</p>

      <div class="grid grid-cols-[90%_10%] gap-2 items-center">
        <div class="text-sm text-gray-800">
          {{ $aval['direccion'] }}
        </div>
        <div>
          <a href="https://maps.google.com/?q={{ urlencode($aval['direccion']) }}"
            target="_blank"
            class="w-full flex justify-center items-center px-3 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
            📍
          </a>
        </div>
      </div>

      {{-- Teléfono --}}
      <div class="grid grid-cols-[90%_10%] gap-2 items-center mt-2">
        <div class="text-sm text-gray-800">
          {{ $aval['telefono'] }}
        </div>
        <div>
          <a href="tel:{{ $aval['telefono'] }}"
            class="w-full flex justify-center items-center px-3 py-2 text-sm font-semibold bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
            📞
          </a>
        </div>
      </div><div class="grid grid-cols-[90%_10%] gap-2 items-center">
        <div class="text-sm text-gray-800">
          {{ $cliente['direccion'] }}
        </div>
        <div>
          <a href="https://maps.google.com/?q={{ urlencode($cliente['direccion']) }}"
            target="_blank"
            class="w-full flex justify-center items-center px-3 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
            📍
          </a>
        </div>
      </div>

      {{-- Teléfono --}}
      <div class="grid grid-cols-[90%_10%] gap-2 items-center mt-2">
        <div class="text-sm text-gray-800">
          {{ $cliente['telefono'] }}
        </div>
        <div>
          <a href="tel:{{ $cliente['telefono'] }}"
            class="w-full flex justify-center items-center px-3 py-2 text-sm font-semibold bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
            📞
          </a>
        </div>
      </div>

      {{-- Garantías Aval --}}
      <div class="bg-gray-50 rounded-xl p-3 shadow-inner space-y-2">
        <p class="font-semibold text-gray-700">Garantías</p>
        <ul class="space-y-2">
          @foreach($aval['garantias'] as $g)
            <li class="flex justify-between items-center bg-white px-3 py-2 rounded-lg shadow-sm border">
              <span class="text-sm text-gray-800">{{ $g }}</span>
              <button class="text-purple-600 text-lg">📷</button>
            </li>
          @endforeach
        </ul>
        <button class="mt-3 w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 rounded-lg shadow">
          Archivo Fotografías 📷
        </button>
      </div>
    </div>

    {{-- 4. TABLA DE SEMANAS --}}
    <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-300 bg-white">
      <table class="w-full text-sm table-fixed border-collapse">
        <thead class="bg-gray-100 text-gray-700">
          <tr class="divide-x divide-gray-300">
            <th class="py-2 px-3 border-b border-gray-300 text-left">Semana</th>
            <th class="py-2 px-3 border-b border-gray-300 text-right">Monto</th>
            <th class="py-2 px-3 border-b border-gray-300 text-center">Estado</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          @for($i = 1; $i <= $totalWeeks; $i++)
            @php
              if($i < $currentWeek) {
                $statusList = ['Pagado','Anticipo','Atrasado'];
                $status = $statusList[array_rand($statusList)];
              } else {
                $status = 'Pagar';
              }
            @endphp
            <tr class="divide-x divide-gray-200">
              <td class="py-2 px-3 text-left">sem {{ $i }}</td>
              <td class="py-2 px-3 text-right">{{ formatCurrency($weeklyAmount) }}</td>
              <td class="py-2 px-3 text-center">
                @if($status === 'Pagado')
                  <span class="inline-block bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Pagado</span>
                @elseif($status === 'Anticipo')
                  <span class="inline-block bg-yellow-400 text-black px-3 py-1 rounded-full text-xs font-semibold">Anticipo</span>
                @elseif($status === 'Atrasado')
                  <span class="inline-block bg-red-600 text-white px-3 py-1 rounded-full text-xs font-semibold">Atrasado</span>
                @else
                  <span class="inline-block bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">Pagar</span>
                @endif
              </td>
            </tr>
          @endfor
        </tbody>
      </table>
    </div>

    {{-- 5. BOTÓN REGRESAR --}}
    <a href="{{ route('mobile.supervisor.cartera') }}"
       class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl text-center shadow-md transition">
      REGRESAR
    </a>

  </div>
</x-layouts.mobile.mobile-layout>
