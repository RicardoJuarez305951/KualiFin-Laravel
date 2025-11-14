<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="{{ $sectionId ?? 'vista-4' }}">
    <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">4. Gastos</p>
            <h2 class="text-lg font-semibold text-gray-900">Conceptos autorizados</h2>
            <p class="text-sm text-gray-500">Resumen de gastos recientes con responsables.</p>
        </div>
    </header>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Concepto</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Monto</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Responsable</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Fecha</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Notas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($expenses as $expense)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $expense['concepto'] }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $expense['monto'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $expense['responsable'] }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $expense['fecha'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $expense['comentario'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
