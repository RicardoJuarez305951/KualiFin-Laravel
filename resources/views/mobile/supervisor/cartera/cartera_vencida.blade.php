{{-- resources/views/mobile/supervisor/cartera/vencida.blade.php --}}
@php
    use Faker\Factory as Faker;

    $faker = Faker::create('es_MX');

    // Genera promotores (nombre completo y plaza)
    $promotores = collect(range(1, 10))->map(function ($i) use ($faker) {
        $promotor   = $faker->firstName().' '.$faker->lastName();
        $plaza    = $faker->city();

        // Monto proyectado vs real (real puede ser mayor => hay "falla")
        $debeProy = $faker->randomFloat(2, 2_000, 15_000);
        $debeReal = $debeProy + $faker->randomFloat(2, 0, 6_000); // real >= proy
        $falla    = max(0, $debeReal - $debeProy);
        $porc     = $debeProy > 0 ? round(($falla / $debeProy) * 100, 1) : 0;

        return [
            'id'             => $i,
            'promotor'         => $promotor,
            'plaza'          => $plaza,
            'debe_proy'      => $debeProy,
            'debe_real'      => $debeReal,
            'falla'          => $falla,
            'porcentaje'     => $porc, // %
        ];
    })
    // Orden alfabético por nombre
    ->sortBy(fn($p) => mb_strtolower($p['promotor']))
    ->values()
    ->toArray();

    function formatCurrency($v) {
        return '$' . number_format($v, 2, '.', ',');
    }

    // Helper para color del badge según % de falla
    function fallaBadgeClass($pct) {
        if ($pct >= 30) return 'bg-red-600 text-white';
        if ($pct >= 10) return 'bg-yellow-400 text-black';
        return 'bg-green-500 text-white';
    }
@endphp

<x-layouts.mobile.mobile-layout title="Cartera Vencida">
  <div class="w-full max-w-xl mx-auto p-4 space-y-6">
    <h2 class="text-xl font-bold text-gray-800 text-center">Cartera Vencida - Resumen de Promotores</h2>

    <div class="bg-white rounded-2xl shadow-md overflow-x-auto">
      <table class="w-full text-sm divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3 px-4 text-left font-medium">Promotor</th>
            <th class="py-3 px-4 text-left font-medium">Plaza</th>
            <th class="py-3 px-4 text-right font-medium">Debe Proy.</th>
            <th class="py-3 px-4 text-right font-medium">Debe Real</th>
            <th class="py-3 px-4 text-right font-medium">Falla</th>
            <th class="py-3 px-4 text-center font-medium">% Falla</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          @foreach ($promotores as $p)
            @php
              $badgeClass = fallaBadgeClass($p['porcentaje']);
            @endphp
            <tr class="hover:bg-gray-200 cursor-pointer"
                onclick="window.location='{{ route("mobile.$role.historial_promotor", ['promotor' => $p['id']]) }}'">
              <td class="py-2 px-4 text-gray-900 font-semibold">{{ $p['promotor'] }}</td>
              <td class="py-2 px-4 text-gray-700">{{ $p['plaza'] }}</td>
              <td class="py-2 px-4 text-right text-gray-700">{{ formatCurrency($p['debe_proy']) }}</td>
              <td class="py-2 px-4 text-right text-gray-900 font-medium">{{ formatCurrency($p['debe_real']) }}</td>
              <td class="py-2 px-4 text-right text-red-600 font-semibold">{{ formatCurrency($p['falla']) }}</td>
              <td class="py-2 px-4 text-center">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badgeClass }}">
                  {{ $p['porcentaje'] }}%
                </span>
              </td>
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
