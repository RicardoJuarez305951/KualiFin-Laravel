<x-layouts.authenticated :title="'Administracion General - Vista ' . $vistaMeta['numero']">
    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-6xl space-y-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Vista {{ $vistaMeta['numero'] }}</p>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $vistaMeta['title'] }}</h1>
                </div>
                <a
                    href="{{ route('administrativo.administracion_general') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    <span>&larr;</span>
                    Regresar al panel
                </a>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($summaryCards as $card)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-sm font-medium text-gray-600">{{ $card['title'] }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-blue-600">{{ $card['subtext'] }}</p>
                    </div>
                @endforeach
            </section>

            @include('administrativo.administracion-general.partials.' . $vistaMeta['slug'], ['sectionId' => 'vista-' . $vistaMeta['numero']])
        </div>
    </div>

    @include('administrativo.administracion-general.partials.kualifin-script')
</x-layouts.authenticated>
