<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="{{ $sectionId ?? 'vista-1' }}">
    <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">1. Desembolsos para inversion</p>
            <h2 class="text-lg font-semibold text-gray-900">Operaciones recientes orientadas a inversion</h2>
            <p class="text-sm text-gray-500">Datos de clientes e inversiones con folio INV.</p>
        </div>
        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Inversion</span>
    </header>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Folio</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Cliente</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Destino</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Monto</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Fecha</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Estatus</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($investmentDisbursements as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $item['folio'] }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $item['cliente'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $item['destino'] }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $item['monto'] }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $item['fecha'] }}</td>
                        <td class="px-6 py-4">
                            <span @class([
                                'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                'bg-emerald-100 text-emerald-700' => $item['estado'] === 'Desembolsado',
                                'bg-amber-100 text-amber-700' => $item['estado'] === 'Programado',
                                'bg-sky-100 text-sky-700' => $item['estado'] === 'En revision',
                            ])>
                                {{ $item['estado'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
