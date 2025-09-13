<ul class="divide-y divide-gray-200">
    @forelse($activos as $c)
        <li class="flex items-center justify-between py-2">
            <div class="flex-1">
                <p class="text-lg font-semibold text-gray-800">
                    {{ $c['apellido'] ?? $c->apellido ?? '' }} {{ $c['nombre'] ?? $c->nombre ?? '' }}
                </p>
                <p class="text-base text-gray-600">
                    Sem {{ $c['semana_credito'] ?? $c->semana_credito ?? '' }}
                </p>
            </div>

            <div class="w-24 text-right">
                <span class="text-lg font-semibold text-gray-900">
                    ${{ number_format($c['monto_semanal'] ?? $c->monto_semanal ?? 0, 2) }}
                </span>
            </div>

            <div class="flex items-center space-x-2 ml-2">
                <button
                    class="w-8 h-8 border-2 border-green-500 text-green-500 rounded-full flex items-center justify-center"
                    title="Registrar pago"
                 @click="$store.calc.open(@js(($c['apellido'] ?? $c->apellido ?? '') . ' ' . ($c['nombre'] ?? $c->nombre ?? '')))">
                    $
                </button>
                <a href="{{ route("mobile.$role.cliente_historial", $c['id'] ?? $c->id) }}"
                   class="w-8 h-8 border-2 border-yellow-500 text-yellow-500 rounded-full flex items-center justify-center"
                   title="Historial">
                    H
                </a>
            </div>
        </li>
    @empty
        <li class="py-2 text-center text-lg text-gray-500">Sin clientes activos</li>
    @endforelse
</ul>
