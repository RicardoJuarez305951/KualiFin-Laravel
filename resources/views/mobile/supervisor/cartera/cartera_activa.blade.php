{{-- resources/views/mobile/cartera/vigente.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    // Mock de promotores
    $promotores = collect(range(1, 8))->map(fn($i) => [
        'id'               => $i,
        'lastName'         => $faker->lastName(),
        'debe'             => $faker->randomFloat(2, 2000, 10000),
        'falla'            => $faker->randomFloat(2, 0, 5000),
        'pagos_realizados' => $faker->randomFloat(2, 0, 10000),
        'pagos_faltantes'  => $faker->randomFloat(2, 0, 5000),
    ])->toArray();

    function formatCurrency($v) {
        return '$' . number_format($v, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout title="Cartera Vigente">
  <div class="w-full max-w-xl mx-auto p-4 space-y-6">
    <h2 class="text-xl font-bold text-gray-800 text-center">Cartera Vigente - Resumen Promotores</h2>

    <div class="bg-white rounded-2xl shadow-md overflow-x-auto">
      <table class="w-full text-sm divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3 px-4 text-left font-medium">Apellido Promotor</th>
            <th class="py-3 px-4 text-right font-medium">Debe</th>
            <th class="py-3 px-4 text-right font-medium">Falla</th>
            <th class="py-3 px-4 text-right font-medium">Pagos Realizados</th>
            <th class="py-3 px-4 text-right font-medium">Pagos Faltantes</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($promotores as $p)
            <tr class="hover:bg-gray-50 cursor-pointer"
                onclick="window.location='{{ route("mobile.$role.historial_promotor", ['promotor' => $p['id']]) }}'">
              <td class="py-2 px-4 text-gray-800 font-semibold">{{ $p['lastName'] }}</td>
              <td class="py-2 px-4 text-right text-gray-700">{{ formatCurrency($p['debe']) }}</td>
              <td class="py-2 px-4 text-right text-red-600">{{ formatCurrency($p['falla']) }}</td>
              <td class="py-2 px-4 text-right text-green-600">{{ formatCurrency($p['pagos_realizados']) }}</td>
              <td class="py-2 px-4 text-right text-yellow-600">{{ formatCurrency($p['pagos_faltantes']) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <a href="{{ route("mobile.$role.cartera") }}"
       class="block text-center py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-lg hover:from-blue-700 hover:to-blue-600 transition">
      ← Regresar a Cartera
    </a>
  </div>
</x-layouts.mobile.mobile-layout>
