<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="{{ $sectionId ?? 'vista-3' }}">
    <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">3. Entradas y salidas</p>
            <h2 class="text-lg font-semibold text-gray-900">Movimientos de efectivo</h2>
            <p class="text-sm text-gray-500">Registros basados en la operacion con promotores e inversiones.</p>
        </div>
    </header>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Tipo</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Origen</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Monto</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($cashFlow as $flow)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span @class([
                                'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                'bg-emerald-100 text-emerald-700' => $flow['tipo'] === 'Entrada',
                                'bg-rose-100 text-rose-700' => $flow['tipo'] === 'Salida',
                            ])>
                                {{ $flow['tipo'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $flow['origen'] }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $flow['monto'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $flow['detalle'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
