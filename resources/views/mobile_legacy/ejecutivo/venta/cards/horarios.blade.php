{{-- resources/views/mobile/ejecutivo/venta/cards/horarios.blade.php --}}
@php
    use Carbon\Carbon;

    if (!defined('__HORARIOS_FAKE_SEED__')) {
        define('__HORARIOS_FAKE_SEED__', true);
        mt_srand(20250918);
    }

    $rndHora = function () {
        $h = mt_rand(9, 19);
        $m = [0, 15, 30, 45][mt_rand(0,3)];
        return str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
    };

    $fechas = [];
    for ($i = 0; $i < 4; $i++) {
        $fecha = Carbon::now()->addDays($i)->format('d/m/y');
        $supervisores = [];
        for ($s = 1; $s <= mt_rand(2,3); $s++) {
            $promotores = [];
            for ($p = 1; $p <= mt_rand(2,4); $p++) {
                $promotores[] = [
                    'nombre' => "Promotor {$s}.{$p}",
                    'hora'   => $rndHora(),
                ];
            }
            $supervisores[] = [
                'nombre'     => "Supervisor {$s}",
                'promotores' => $promotores,
            ];
        }
        $fechas[] = [
            'date'         => $fecha,
            'supervisores' => $supervisores,
        ];
    }
@endphp

<section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
  <div class="p-5">
    <h2 class="text-base font-bold text-gray-900 mb-3">Horarios de Cobro</h2>

    @foreach($fechas as $fecha)
      <div class="mb-4">
        <p class="text-sm font-semibold text-gray-800 mb-2">{{ $fecha['date'] }}</p>

        @foreach($fecha['supervisores'] as $sup)
          <div class="rounded-xl border border-gray-100 p-3 mb-2">
            <p class="text-sm font-semibold text-gray-900">{{ $sup['nombre'] }}</p>
            <ul class="mt-2 divide-y divide-gray-100">
              @foreach($sup['promotores'] as $idx => $prom)
                <li class="py-2 flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 text-[11px] font-bold rounded-full bg-indigo-100 text-indigo-700">
                      {{ $idx+1 }}
                    </span>
                    <span class="text-sm text-gray-800">{{ $prom['nombre'] }}</span>
                  </div>
                  <span class="text-sm font-semibold text-gray-900">{{ $prom['hora'] }}</span>
                </li>
              @endforeach
            </ul>
          </div>
        @endforeach
      </div>
    @endforeach
  </div>
</section>
