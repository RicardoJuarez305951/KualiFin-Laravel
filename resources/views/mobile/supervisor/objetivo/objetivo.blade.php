{{-- resources/views/mobile/objetivo_supervisora.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    //
    // Datos de Promotores (para filtro y listado)
    //
    $promotoresData = collect();
    for ($i = 1; $i <= 5; $i++) {
        $promotoresData->push((object)[
            'id'     => $i,
            'nombre' => $faker->name(),
        ]);
    }

    //
    // Metas globales de la supervisora
    //
    $weeklyMoneyTarget     = $faker->randomFloat(2, 25000, 100000);
    $weeklyClientTarget    = $faker->numberBetween(10, 30);
    $exerciseMoneyTarget   = $faker->randomFloat(2, 500000, 2000000);
    $exerciseClientTarget  = $faker->numberBetween(50, 150);

    //
    // Historial global (3 semanas)
    //
    $moneyHistory    = [
        $faker->randomFloat(2, 0, $weeklyMoneyTarget),
        $faker->randomFloat(2, 0, $weeklyMoneyTarget),
        $faker->randomFloat(2, 0, $weeklyMoneyTarget),
        $faker->randomFloat(2, 0, $weeklyMoneyTarget),
        $faker->randomFloat(2, 0, $weeklyMoneyTarget),
        $faker->randomFloat(2, 0, $weeklyMoneyTarget),
        $faker->randomFloat(2, 0, $weeklyMoneyTarget),
    ];
    $clientHistory   = [
        $faker->numberBetween(0, $weeklyClientTarget),
        $faker->numberBetween(0, $weeklyClientTarget),
        $faker->numberBetween(0, $weeklyClientTarget),
        $faker->numberBetween(0, $weeklyClientTarget),
        $faker->numberBetween(0, $weeklyClientTarget),
        $faker->numberBetween(0, $weeklyClientTarget),
        $faker->numberBetween(0, $weeklyClientTarget),
    ];

    //
    // Cálculos globales
    //
    $dueMoneyThisWeek      = max($weeklyMoneyTarget - $moneyHistory[0], 0);
    $dueClientsThisWeek    = max($weeklyClientTarget - $clientHistory[0], 0);
    $remainingMoneyTotal   = max($exerciseMoneyTarget - array_sum($moneyHistory), 0);
    $remainingClientsTotal = max($exerciseClientTarget - array_sum($clientHistory), 0);

    //
    // Listado por promotor
    //
    $promotorSummaries = $promotoresData->map(function($p) use ($faker, $exerciseMoneyTarget, $exerciseClientTarget) {
        return (object)[
            'id'       => $p->id,
            'nombre'   => $p->nombre,
            'ventas'   => $faker->randomFloat(2, 0, $exerciseMoneyTarget),
            'clientes' => $faker->numberBetween(0, $exerciseClientTarget),
        ];
    });

    function formatCurrency($v) {
        return '$' . number_format($v, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout title="Reporte Supervisora">
  <div class="w-full max-w-md mx-auto space-y-6 p-4">

    {{-- Filtro por promotor --}}
    <div>
      <label for="promotor_filter" class="block text-sm font-medium text-gray-700">Filtrar por promotor</label>
      <select id="promotor_filter"
              class="mt-1 block w-full border rounded px-3 py-2">
        <option value="">— Todas —</option>
        @foreach($promotoresData as $p)
          <option value="{{ $p->id }}">{{ $p->nombre }}</option>
        @endforeach
      </select>
    </div>

    {{-- Resumen global de metas y cumplimiento --}}
    <div class="grid grid-cols-2 gap-4">
      <div class="bg-gradient-to-br from-blue-600 to-blue-500 text-white rounded-2xl shadow-lg p-4 flex items-center gap-3">
        <span class="font-bold">$</span>
        <div>
          <p class="text-xs uppercase tracking-wider opacity-80">Meta semanal</p>
          <p class="text-base font-bold">{{ formatCurrency($weeklyMoneyTarget) }}</p>
        </div>
      </div>
      <div class="bg-gradient-to-br from-green-600 to-green-500 text-white rounded-2xl shadow-lg p-4 flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 opacity-90" fill="none" stroke="currentColor" stroke-width="1.5"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M12 12c1.657 0 3-1.343 3-3S13.657 6 12 6s-3 1.343-3 3 1.343 3 3 3z"/>
          <path d="M12 12v8m0-8H4m8 0h8"/>
        </svg>
        <div>
          <p class="text-xs uppercase tracking-wider opacity-80">Clientes semanales</p>
          <p class="text-base font-bold">{{ $weeklyClientTarget }}</p>
        </div>
      </div>
    </div>

    {{-- Tabla de metas vs cumplimiento --}}
    <div class="bg-white rounded-2xl shadow-md divide-y">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3 px-4 text-left font-medium">Dinero</th>
            <th class="py-3 px-4 text-left font-medium">Clientes</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr class="hover:bg-gray-50">
            <td class="py-3 px-4 font-semibold">Meta ejercicio</td>
            <td class="py-3 px-4 font-semibold">{{ $exerciseClientTarget }}</td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="py-3 px-4">
              {{ formatCurrency($remainingMoneyTotal) }}<br>
              <span class="text-xs text-gray-500">restante ejercicio</span>
            </td>
            <td class="py-3 px-4">
              {{ $remainingClientsTotal }}<br>
              <span class="text-xs text-gray-500">recaudado ejercicio</span>
            </td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="py-3 px-4 text-red-600">
              {{ formatCurrency($dueMoneyThisWeek) }}<br>
              <span class="text-xs text-red-400">falla semana</span>
            </td>
            <td class="py-3 px-4 text-red-600">
              {{ $dueClientsThisWeek }}<br>
              <span class="text-xs text-red-400">fallas semana</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- Historial global (7 sem) --}}
    <div class="bg-white rounded-2xl shadow-md p-4">
      <h3 class="text-sm font-semibold text-gray-800 mb-3">Historial (7 sem)</h3>
      <table class="w-full text-sm divide-y">
        <thead>
          <tr class="bg-gray-50">
            <th class="py-2 px-3">Semana</th>
            <th class="py-2 px-3 text-right">$</th>
            <th class="py-2 px-3 text-right">Clientes</th>
          </tr>
        </thead>
        <tbody>
          @for ($i = 0; $i < 7; $i++)
            <tr class="hover:bg-gray-50">
              <td class="py-2 px-3">{{ $i + 1 }}</td>
              <td class="py-2 px-3 text-right">{{ formatCurrency($moneyHistory[$i]) }}</td>
              <td class="py-2 px-3 text-right">{{ $clientHistory[$i] }}</td>
              
            </tr>
          @endfor
            <tr class="hover:bg-gray-50">
              <td class="py-2 px-3">Total</td> 
              <td class="py-2 px-3 text-right">{{ formatCurrency(array_sum($moneyHistory)) }}</td>
              <td class="py-2 px-3 text-right">{{ array_sum($clientHistory) }}</td>
            </tr>
        </tbody>
      </table>
    </div>

    {{-- Listado por promotor --}}
    <div class="bg-white rounded-2xl shadow-md p-4">
      <h3 class="text-sm font-semibold text-gray-800 mb-3">Por promotor (ejercicio)</h3>
      <table class="w-full text-sm divide-y">
        <thead>
          <tr class="bg-gray-50">
            <th class="py-2 px-3">Promotor</th>
            <th class="py-2 px-3 text-right">Ventas</th>
            <th class="py-2 px-3 text-right">Clientes</th>
          </tr>
        </thead>
        <tbody>
          @foreach($promotorSummaries as $s)
            <tr class="hover:bg-gray-50">
              <td class="py-2 px-3">{{ $s->nombre }}</td>
              <td class="py-2 px-3 text-right">{{ formatCurrency($s->ventas) }}</td>
              <td class="py-2 px-3 text-right">{{ $s->clientes }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Botón regresar --}}
    <a href="{{ route("mobile.$role.index") }}"
       class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
      ← Regresar
    </a>

  </div>
</x-layouts.mobile.mobile-layout>
