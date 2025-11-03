<x-layouts.authenticated title="Probables Aperturas">
    <div class="mx-auto max-w-7xl py-10 space-y-8">
        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Pipeline de expansión</h2>
                    <p class="text-sm text-gray-500">Proyectos en curso para habilitar nuevas plazas operativas.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Expansión</span>
            </header>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Ciudad</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Estatus</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Apertura estimada</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Responsable</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Comentarios</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($pipeline as $location)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $location['ciudad'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                                        {{ $location['estatus'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ $location['apertura_estimado'] }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $location['responsable'] }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $location['comentarios'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="lg:col-span-2 rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Proyección regional</h2>
                        <p class="text-sm text-gray-500">Resumen de presencia actual y metas de expansión.</p>
                    </div>
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">Planeación</span>
                </header>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Región</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Sucursales activas</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Proyección</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Riesgo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($regionalSummary as $region)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $region['region'] }}</td>
                                    <td class="px-6 py-4">{{ $region['sucursales_activas'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $region['proyeccion'] }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            @class([
                                                'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                                'bg-emerald-100 text-emerald-700' => $region['riesgo'] === 'Bajo',
                                                'bg-amber-100 text-amber-700' => $region['riesgo'] === 'Medio',
                                                'bg-rose-100 text-rose-700' => $region['riesgo'] === 'Alto',
                                            ])
                                        >
                                            {{ $region['riesgo'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Siguientes pasos</h2>
                    <p class="text-sm text-gray-500">Agenda ejecutiva para los próximos hitos de aprobación.</p>
                </header>

                <ul class="space-y-4 px-6 py-5">
                    @foreach ($nextSteps as $step)
                        <li class="rounded-lg border border-dashed border-gray-300 p-4">
                            <div class="flex items-center justify-between">
                                <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold uppercase text-gray-700">
                                    {{ $step['fecha'] }}
                                </span>
                                <span class="text-xs font-medium text-gray-500">{{ $step['responsable'] }}</span>
                            </div>
                            <p class="mt-3 text-sm font-semibold text-gray-900">{{ $step['actividad'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
</x-layouts.authenticated>
