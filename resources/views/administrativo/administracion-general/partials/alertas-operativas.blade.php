<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="{{ $sectionId ?? 'vista-6' }}">
    <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">6. Historial de fallo</p>
            <h2 class="text-lg font-semibold text-gray-900">Clientes con semana extra</h2>
            <p class="text-sm text-gray-500">Seguimiento a clientes con atraso fuera de calendario.</p>
        </div>
    </header>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Cliente</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Folio</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Promotor</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Semanas extra</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Saldo pendiente</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Ultimo pago</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($failureHistory as $failure)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-700">{{ $failure['cliente'] }}</td>
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $failure['folio'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $failure['promotor'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $failure['semanas_extra'] }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $failure['monto_pendiente'] }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $failure['ultimo_pago'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
