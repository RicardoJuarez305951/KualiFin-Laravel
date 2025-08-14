<ul class="divide-y divide-gray-200">
    @forelse($vencidos as $c)
        <li class="flex items-center justify-between py-2" x-data="{ c: @js($c) }">
            <div class="flex-1">
                <p class="text-base font-semibold text-gray-800">
                    {{ $c['apellido'] ?? $c->apellido ?? '' }} {{ $c['nombre'] ?? $c->nombre ?? '' }}
                </p>
            </div>

            <div class="w-24 text-right">
                <span class="text-base font-semibold text-red-600">
                    ${{ number_format($c['deuda_total'] ?? $c->deuda_total ?? 0, 2) }}
                </span>
            </div>

            <div class="flex items-center space-x-2 ml-2">
                <button
                    class="w-8 h-8 border-2 border-green-500 text-green-500 rounded-full flex items-center justify-center"
                    title="Registrar pago"
                    @click="openCalc(@js(($c['apellido'] ?? $c->apellido ?? '') . ' ' . ($c['nombre'] ?? $c->nombre ?? '')))">
                    $
                </button>
                <button
                   class="w-8 h-8 border-2 border-blue-500 text-blue-500 rounded-full flex items-center justify-center"
                   title="Detalle"
                   @click="openVencidaDetail(c)">
                    D
                </button>
            </div>
        </li>
    @empty
        <li class="py-2 text-center text-base text-gray-500">Sin clientes vencidos</li>
    @endforelse
</ul>
