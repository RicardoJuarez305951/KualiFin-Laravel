<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="{{ $sectionId ?? 'vista-7' }}">
    <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">7. Reportes</p>
            <h2 class="text-lg font-semibold text-gray-900">Mensuales y anuales</h2>
            <p class="text-sm text-gray-500">Listado de documentos clave por periodo.</p>
        </div>
        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Indicadores</span>
    </header>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Nombre</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Periodo</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Responsable</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Estatus</th>
                    <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Descarga</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($reports as $report)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $report['nombre'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $report['periodo'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $report['responsable'] }}</td>
                        <td class="px-6 py-4">
                            <span @class([
                                'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                'bg-emerald-100 text-emerald-700' => $report['estatus'] === 'Entregado',
                                'bg-amber-100 text-amber-700' => $report['estatus'] === 'En validacion',
                                'bg-sky-100 text-sky-700' => $report['estatus'] === 'Programado',
                            ])>
                                {{ $report['estatus'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ $report['descarga'] }}" class="text-sm font-semibold text-blue-600 hover:underline">
                                Descargar
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
