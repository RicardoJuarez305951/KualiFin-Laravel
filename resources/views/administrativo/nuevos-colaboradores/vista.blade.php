@php
    $totalPipeline = count($pipeline);
    $totalVacantes = array_sum(array_column($openPositions, 'vacantes'));
    $sesionesInduccion = count($inductionSchedule);
@endphp

<x-layouts.authenticated :title="'Nuevos Colaboradores - Vista ' . $vistaMeta['numero']">
    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-6xl space-y-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Vista {{ $vistaMeta['numero'] }}</p>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $vistaMeta['title'] }}</h1>
                    <p class="text-base text-gray-600">{{ $vistaMeta['description'] }}</p>
                </div>
                <a
                    href="{{ route('administrativo.nuevos_colaboradores') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    <span>&larr;</span>
                    Regresar al panel
                </a>
            </div>

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
                    <p class="text-sm font-medium text-gray-600">Sesiones de induccion</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $sesionesInduccion }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-emerald-600">Proximos 10 dias</p>
                </div>
            </section>

            @include('administrativo.nuevos-colaboradores.partials.' . $vistaMeta['slug'])
        </div>
    </div>
</x-layouts.authenticated>
