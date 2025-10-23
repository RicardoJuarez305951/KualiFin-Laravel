<ul class="space-y-3">
    @forelse($vencidos as $c)
        @php
            $pagoPendiente = $c['pago_proyectado_pendiente'] ?? ($c->pago_proyectado_pendiente ?? null);
            $pagoPendienteId = is_array($pagoPendiente) ? ($pagoPendiente['id'] ?? null) : ($pagoPendiente->id ?? null);
            $pagoPendienteMonto = is_array($pagoPendiente) ? ($pagoPendiente['monto_proyectado'] ?? null) : ($pagoPendiente->monto_proyectado ?? null);
            $pagoPendienteDeuda = is_array($pagoPendiente)
                ? ($pagoPendiente['deuda_vencida'] ?? $pagoPendiente['deuda_total'] ?? null)
                : ($pagoPendiente->deuda_vencida ?? $pagoPendiente->deuda_total ?? null);
            $pagoPendienteDeuda = is_numeric($pagoPendienteDeuda) ? (float) $pagoPendienteDeuda : null;
            $pagoPendienteMonto = is_numeric($pagoPendienteMonto) ? (float) $pagoPendienteMonto : 0.0;
            $pagoPendienteAbonado = is_array($pagoPendiente)
                ? ($pagoPendiente['abonado'] ?? null)
                : ($pagoPendiente->abonado ?? null);
            $pagoPendienteAdelantado = is_array($pagoPendiente)
                ? ($pagoPendiente['adelantado'] ?? null)
                : ($pagoPendiente->adelantado ?? null);
            $pagoPendienteTotalPagado = is_array($pagoPendiente)
                ? ($pagoPendiente['pagado_total'] ?? null)
                : ($pagoPendiente->pagado_total ?? null);

            $pagoPendienteAbonado = is_numeric($pagoPendienteAbonado) ? (float) $pagoPendienteAbonado : null;
            $pagoPendienteAdelantado = is_numeric($pagoPendienteAdelantado) ? (float) $pagoPendienteAdelantado : null;
            $pagoPendienteTotalPagado = is_numeric($pagoPendienteTotalPagado) ? (float) $pagoPendienteTotalPagado : null;

            if ($pagoPendienteAbonado === null && $pagoPendienteDeuda !== null) {
                $pagoPendienteAbonado = max(0.0, $pagoPendienteMonto - $pagoPendienteDeuda);
            }
            if ($pagoPendienteAbonado === null) {
                $pagoPendienteAbonado = 0.0;
            }

            if ($pagoPendienteAdelantado === null && $pagoPendienteTotalPagado !== null) {
                $pagoPendienteAdelantado = max(0.0, $pagoPendienteTotalPagado - $pagoPendienteMonto);
            }
            if ($pagoPendienteAdelantado === null) {
                $pagoPendienteAdelantado = 0.0;
            }

            if ($pagoPendienteTotalPagado === null) {
                $pagoPendienteTotalPagado = $pagoPendienteAbonado + $pagoPendienteAdelantado;
            }
        @endphp

        <li
            x-data="{ cliente: @js($c) }"
            :class="{ 'border-rose-600 bg-rose-50 shadow-md': $store.multiPay.isSelected(cliente) }"
            @click="$store.multiPay.active && $store.multiPay.toggle(cliente)"
            class="flex items-center justify-between gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 shadow transition"
        >
            <div class="space-y-1">
                <p class="text-sm font-semibold text-rose-700">
                    {{ ($c['apellido_p'] ?? $c->apellido_p ?? '') }} {{ ($c['apellido_m'] ?? $c->apellido_m ?? '') }} {{ ($c['nombre'] ?? $c->nombre ?? '') }}
                </p>
                @if($pagoPendienteId)
                    <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white px-3 py-1 text-[11px] font-semibold text-rose-700">
                        <span>{{ number_format($pagoPendienteMonto, 2) }}</span>
                        <span class="text-rose-300">/</span>
                        <span>{{ number_format($pagoPendienteAbonado, 2) }}</span>
                        @if($pagoPendienteAdelantado > 0)
                            <span class="text-[10px] font-medium text-rose-500">(+{{ number_format($pagoPendienteAdelantado, 2) }})</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="text-right">
                <span class="text-base font-semibold text-rose-700">
                    ${{ number_format($c['deuda_total'] ?? $c->deuda_total ?? 0, 2) }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-emerald-500 bg-emerald-500 text-white transition hover:bg-emerald-600"
                    title="Registrar pago"
                    @click.stop="$store.multiPay.active
                        ? $store.multiPay.openCalculator(cliente)
                        : $store.multiPay.openSingleCalculator(cliente)"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m7.5-7.5h-15" />
                    </svg>
                </button>

                <a href="{{ route('mobile.' . $role . '.cliente_historial', $c['id'] ?? $c->id) }}"
                   class="flex h-9 w-9 items-center justify-center rounded-full border border-amber-400 bg-amber-50 text-amber-600 transition hover:bg-amber-100"
                   title="Historial"
                   @click.stop
                   x-show="!$store.multiPay.active"
                   x-cloak
                >
                    <span>H</span>
                </a>

                <button
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-blue-400 bg-blue-50 text-blue-600 transition hover:bg-blue-100"
                    title="Detalle"
                    @click.stop="openVencidaDetail(@js($c))"
                    x-show="!$store.multiPay.active"
                    x-cloak
                >
                    <span class="text-sm font-semibold">D</span>
                </button>

            </div>
        </li>
    @empty
        <li class="py-2 text-center text-sm text-slate-600">Sin clientes vencidos</li>
    @endforelse
</ul>
