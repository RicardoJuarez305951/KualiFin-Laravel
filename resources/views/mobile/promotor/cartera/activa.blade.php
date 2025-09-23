<ul class="divide-y divide-gray-200">
    @forelse($activos as $c)
        <li
            x-data="{ cliente: @js($c) }"
            :class="{ 'bg-blue-200': $store.multiPay.isSelected(cliente) }"
            @click="$store.multiPay.toggle(cliente)"
            class="flex items-center justify-between py-2"
        >
            <div class="flex items-center flex-1">
                <input
                    x-show="$store.multiPay.active"
                    x-cloak
                    type="checkbox"
                    class="mr-2"
                    @click.stop="$store.multiPay.toggle(cliente)"
                    :checked="$store.multiPay.isSelected(cliente)"
                >
                <div>
                    <p class="text-lg font-semibold text-gray-800">
                        {{ ($c['apellido_p'] ?? $c->apellido_p ?? '') }} {{ ($c['apellido_m'] ?? $c->apellido_m ?? '') }} {{ ($c['nombre'] ?? $c->nombre ?? '') }}
                    </p>
                    <p class="text-base text-gray-600">
                        Sem {{ $c['semana_credito'] ?? $c->semana_credito ?? '' }}
                    </p>
                    @php
                        $pagoPendiente = $c['pago_proyectado_pendiente'] ?? ($c->pago_proyectado_pendiente ?? null);
                        $pagoPendienteId = is_array($pagoPendiente) ? ($pagoPendiente['id'] ?? null) : ($pagoPendiente->id ?? null);
                        $pagoPendienteMonto = is_array($pagoPendiente) ? ($pagoPendiente['monto_proyectado'] ?? null) : ($pagoPendiente->monto_proyectado ?? null);
                        $pagoPendienteDeuda = is_array($pagoPendiente)
                            ? ($pagoPendiente['deuda_vencida'] ?? $pagoPendiente['deuda_total'] ?? null)
                            : ($pagoPendiente->deuda_vencida ?? $pagoPendiente->deuda_total ?? null);
                    @endphp
                    @if($pagoPendienteId)
                        <p class="text-xs text-gray-500">
                            Pago #{{ $pagoPendienteId }} · Proyectado: ${{ number_format((float) ($pagoPendienteMonto ?? 0), 2) }} · Deuda: ${{ number_format((float) ($pagoPendienteDeuda ?? 0), 2) }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="w-24 text-right">
                <span class="text-lg font-semibold text-gray-900">
                    ${{ number_format($c['monto_semanal'] ?? $c->monto_semanal ?? 0, 2) }}
                </span>
            </div>

            <div class="flex items-center space-x-2 ml-2" x-show="!$store.multiPay.active">
                <button
                    class="w-8 h-8 border-2 border-green-500 text-green-500 rounded-full flex items-center justify-center"
                    title="Registrar pago"
                    @click.stop="$store.calc.open(@js(($c['apellido_p'] ?? $c->apellido_p ?? '') . ' ' . ($c['apellido_m'] ?? $c->apellido_m ?? '') . ' ' . ($c['nombre'] ?? $c->nombre ?? '')))"
                >
                    $
                </button>
                <a href="{{ route('mobile.' . $role . '.cliente_historial', $c['id'] ?? $c->id) }}"
                   class="w-8 h-8 border-2 border-yellow-500 text-yellow-500 rounded-full flex items-center justify-center"
                   title="Historial"
                   @click.stop
                >
                    H
                </a>
            </div>
        </li>
    @empty
        <li class="py-2 text-center text-lg text-gray-500">Sin clientes activos</li>
    @endforelse
</ul>

