<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="vista-1">
    <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Pipeline de incorporacion</h2>
            <p class="text-sm text-gray-500">Seguimiento detallado de candidatos en proceso.</p>
        </div>
        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">RRHH</span>
    </header>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Nombre</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Posicion</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Estatus</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Ingreso estimado</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Region</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Responsable</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($pipeline as $candidate)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $candidate['nombre'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $candidate['posicion'] }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                                {{ $candidate['estatus'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $candidate['ingreso_estimado'] }}</td>
                        <td class="px-6 py-4">{{ $candidate['region'] }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700">
                                {{ $candidate['responsable'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
