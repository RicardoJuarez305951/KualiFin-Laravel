<x-layouts.authenticated :title="'Posibles Aperturas - Vista ' . $vistaMeta['numero']">
    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-6xl space-y-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Vista {{ $vistaMeta['numero'] }}</p>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $vistaMeta['title'] }}</h1>
                    <p class="text-base text-gray-600">{{ $vistaMeta['description'] }}</p>
                </div>
                <a
                    href="{{ route('administrativo.probables_aperturas') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    <span>&larr;</span>
                    Regresar al panel
                </a>
            </div>

            @include('administrativo.probables-aperturas.partials.' . $vistaMeta['slug'])
        </div>
    </div>
</x-layouts.authenticated>
