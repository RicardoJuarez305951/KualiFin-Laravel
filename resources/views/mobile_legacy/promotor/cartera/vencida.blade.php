<ul class="divide-y divide-gray-200">
    @forelse($vencidos as $c)
        <li
            x-data="{ cliente: @js($c) }"
            :class="{ 'bg-blue-200': $store.multiPay.clients.some(c => c.id === cliente.id) }"
            @click="$store.multiPay.active && $store.multiPay.toggle(cliente)"
            class="flex items-center justify-between py-2"
        >
            <div class="flex items-center flex-1">
                <input
                    x-show="$store.multiPay.active"
                    x-cloak
                    type="checkbox"
                    class="mr-2"
                    @click.stop="$store.multiPay.toggle(cliente)"
                    :checked="$store.multiPay.clients.some(c => c.id === cliente.id)"
                >
                <div>
                    <p class="text-base font-semibold text-gray-800">
                        {{ ($c['apellido_p'] ?? $c->apellido_p ?? '') }} {{ ($c['apellido_m'] ?? $c->apellido_m ?? '') }} {{ ($c['nombre'] ?? $c->nombre ?? '') }}
                    </p>
                </div>
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
                    @click.stop="$store.multiPay.active
                        ? $store.multiPay.openCalculator(cliente)
                        : $store.calc.open(@js(($c['apellido_p'] ?? $c->apellido_p ?? '') . ' ' . ($c['apellido_m'] ?? $c->apellido_m ?? '') . ' ' . ($c['nombre'] ?? $c->nombre ?? '')))
                    "
                >
                    $
                </button>

                <a href="{{ route('mobile.' . $role . '.cliente_historial', $c['id'] ?? $c->id) }}"
                   class="w-8 h-8 border-2 border-yellow-500 text-yellow-500 rounded-full flex items-center justify-center"
                   title="Historial"
                   @click.stop
                   x-show="!$store.multiPay.active"
                   x-cloak
                >
                    H
                </a>

                <button
                   class="w-8 h-8 border-2 border-blue-500 text-blue-500 rounded-full flex items-center justify-center"
                   title="Detalle"
                   @click.stop="openVencidaDetail(@js($c))"
                   x-show="!$store.multiPay.active"
                   x-cloak
                >
                    D
                </button>
            </div>
        </li>
    @empty
        <li class="py-2 text-center text-base text-gray-500">Sin clientes vencidos</li>
    @endforelse
</ul>

