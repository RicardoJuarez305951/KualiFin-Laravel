<x-layouts.authenticated :title="'Autorizacion - Vista ' . $section['numero']">
    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-6xl space-y-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Vista {{ $section['numero'] }}</p>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $section['title'] }}</h1>
                    <p class="text-base text-gray-600">{{ $section['description'] }}</p>
                </div>
                <a
                    href="{{ route('administrativo.autorizacion') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    <span>&larr;</span>
                    Regresar al panel
                </a>
            </div>

            <section class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-600">Tipos de autorizacion</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['categorias'] ?? 0 }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-blue-600">Agrupados por motivo</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-600">Solicitudes en revision</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['solicitudes'] ?? 0 }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-indigo-600">Pendientes de decision</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-600">Alertas prioritarias</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['alertas_altas'] ?? 0 }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-rose-600">Revisar a detalle</p>
                </div>
            </section>

            @include('administrativo.autorizacion.partials.section', ['section' => $section])
        </div>
    </div>
</x-layouts.authenticated>
