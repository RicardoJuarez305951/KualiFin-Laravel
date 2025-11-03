@php
    $avancePromedio = count($initiatives) ? round(array_sum(array_column($initiatives, 'avance')) / count($initiatives)) : 0;
    $alertasCriticas = count(array_filter($alerts, fn ($alert) => $alert['tipo'] === 'danger'));
@endphp

<x-layouts.authenticated title="Administración General">
    <div class="mx-auto max-w-7xl py-10 space-y-8">
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-gray-600">Iniciativas activas</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ count($initiatives) }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-indigo-600">Portafolio operativo</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-gray-600">Avance promedio</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $avancePromedio }}%</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-emerald-600">Progreso al día</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-gray-600">Alertas críticas</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $alertasCriticas }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-rose-600">Revisión inmediata</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-gray-600">Pendientes compliance</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ count($compliance) }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-amber-600">Controles clave</p>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Portafolio de iniciativas</h2>
                    <p class="text-sm text-gray-500">Seguimiento ejecutivo de proyectos estratégicos.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">PMO</span>
            </header>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Nombre</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Avance</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Líder</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Riesgo</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Próximo hito</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($initiatives as $initiative)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $initiative['nombre'] }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-full rounded-full bg-gray-100">
                                            <div
                                                class="h-2 rounded-full bg-blue-600"
                                                style="width: {{ $initiative['avance'] }}%"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700">{{ $initiative['avance'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $initiative['lider'] }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        @class([
                                            'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                            'bg-emerald-100 text-emerald-700' => $initiative['riesgo'] === 'Bajo',
                                            'bg-amber-100 text-amber-700' => $initiative['riesgo'] === 'Medio',
                                            'bg-rose-100 text-rose-700' => $initiative['riesgo'] === 'Alto',
                                        ])
                                    >
                                        {{ $initiative['riesgo'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $initiative['proximo_hito'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Compliance y controles</h2>
                    <p class="text-sm text-gray-500">Documentación clave y fechas de entrega.</p>
                </header>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold lowercase tracking-wide text-xs text-gray-500 capitalize">Documento</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Estatus</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Responsable</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Compromiso</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($compliance as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $item['titulo'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $item['estatus'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $item['responsable'] }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $item['limite'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Alertas operativas</h2>
                    <p class="text-sm text-gray-500">Mensajes destacados para atención inmediata.</p>
                </header>

                <ul class="space-y-4 px-6 py-5">
                    @foreach ($alerts as $alert)
                        <li
                            @class([
                                'rounded-lg border p-4 shadow-sm',
                                'border-emerald-200 bg-emerald-50 text-emerald-700' => $alert['tipo'] === 'info',
                                'border-amber-200 bg-amber-50 text-amber-700' => $alert['tipo'] === 'warning',
                                'border-rose-200 bg-rose-50 text-rose-700' => $alert['tipo'] === 'danger',
                            ])
                        >
                            <p class="text-sm font-semibold">{{ $alert['mensaje'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
</x-layouts.authenticated>
