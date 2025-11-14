<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="vista-3">
    <header class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Vacantes prioritarias</h2>
        <p class="text-sm text-gray-500">Identifica las posiciones criticas para acelerar la contratacion.</p>
    </header>

    <div class="space-y-4 px-6 py-5">
        @foreach ($openPositions as $position)
            <article class="rounded-lg border border-dashed border-gray-300 p-4">
                <h3 class="text-sm font-semibold text-gray-900">{{ $position['puesto'] }}</h3>
                <p class="mt-1 text-xs text-gray-500">Region: {{ $position['region'] }}</p>
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
