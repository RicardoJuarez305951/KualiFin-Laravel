@php
    $totalPipeline = count($pipeline);
    $totalVacantes = array_sum(array_column($openPositions, 'vacantes'));
    $sesionesInduccion = count($inductionSchedule);
@endphp

<x-layouts.authenticated title="Nuevos Colaboradores">
    <div class="mx-auto max-w-7xl py-10 space-y-8">
        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-gray-600">Candidatos activos</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $totalPipeline }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-indigo-600">En pipeline</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-gray-600">Vacantes abiertas</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $totalVacantes }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-rose-600">Prioriza cierres</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-gray-600">Sesiones de inducción</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $sesionesInduccion }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-emerald-600">Próximos 10 días</p>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Pipeline de incorporación</h2>
                    <p class="text-sm text-gray-500">Seguimiento detallado de candidatos en proceso.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">RRHH</span>
            </header>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Nombre</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Posición</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Estatus</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Ingreso estimado</th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Región</th>
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

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Agenda de inducción</h2>
                    <p class="text-sm text-gray-500">Sesiones confirmadas para nuevas incorporaciones.</p>
                </header>

                <ul class="space-y-4 px-6 py-5">
                    @foreach ($inductionSchedule as $session)
                        <li class="flex items-start gap-4 rounded-lg border border-gray-200 p-4 shadow-sm">
                            <span class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white">
                                {{ $session['fecha'] }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $session['tema'] }}</p>
                                <p class="mt-1 text-xs text-gray-500">Ponente: {{ $session['ponente'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Vacantes prioritarias</h2>
                    <p class="text-sm text-gray-500">Identifica las posiciones críticas para acelerar la contratación.</p>
                </header>

                <div class="space-y-4 px-6 py-5">
                    @foreach ($openPositions as $position)
                        <article class="rounded-lg border border-dashed border-gray-300 p-4">
                            <h3 class="text-sm font-semibold text-gray-900">{{ $position['puesto'] }}</h3>
                            <p class="mt-1 text-xs text-gray-500">Región: {{ $position['region'] }}</p>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-700">Vacantes: {{ $position['vacantes'] }}</span>
                                <span
                                    @class([
                                        'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                        'bg-rose-100 text-rose-700' => $position['prioridad'] === 'Alta',
                                        'bg-amber-100 text-amber-700' => $position['prioridad'] === 'Media',
                                        'bg-emerald-100 text-emerald-700' => $position['prioridad'] === 'Baja',
                                    ])
                                >
                                    {{ $position['prioridad'] }}
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-layouts.authenticated>
