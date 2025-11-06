<x-layouts.authenticated title="Encuesta socioeconomica">
    <div class="mx-auto max-w-5xl py-10 space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-wide text-blue-600">Expediente {{ str_pad($promotor['id'], 3, '0', STR_PAD_LEFT) }}</p>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $promotor['promotor_aperturado'] }}</h1>
                <p class="text-sm text-gray-600">
                    Apertura impulsada por {{ $promotor['promotor_responsable'] }} en {{ $promotor['territorio'] }}.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                    {{ $promotor['fase'] }}
                </span>
                <span class="text-xs text-gray-500">Actualizacion {{ $promotor['ultima_actualizacion'] }}</span>
            </div>
        </div>

        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('administrativo.probables_aperturas') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Regresar al listado
            </a>
        </div>

        <section class="grid gap-6 md:grid-cols-2">
            <article class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="text-sm font-semibold text-gray-900">Resumen de apertura</h2>
                </div>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 px-5 py-4 text-sm text-gray-700">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Promotor aperturado</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $promotor['promotor_aperturado'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Promotor responsable</dt>
                        <dd class="mt-1 text-gray-800">{{ $promotor['promotor_responsable'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Territorio propuesto</dt>
                        <dd class="mt-1 text-gray-800">{{ $promotor['territorio'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Ultima actualizacion</dt>
                        <dd class="mt-1 text-gray-800">{{ $promotor['ultima_actualizacion'] }}</dd>
                    </div>
                </dl>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="text-sm font-semibold text-gray-900">Notas operativas</h2>
                </div>
                <div class="space-y-3 px-5 py-4 text-sm text-gray-700">
                    <p>{{ $promotor['resumen'] }}</p>
                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Siguiente paso sugerido</p>
                        <p class="mt-1 text-sm text-gray-800">{{ $promotor['siguiente_paso'] }}</p>
                    </div>
                </div>
            </article>
        </section>

        <section class="space-y-6">
            <h2 class="text-lg font-semibold text-gray-900">Encuesta socioeconomica</h2>

            <div class="grid gap-6 md:grid-cols-2">
                @foreach ($promotor['encuesta'] as $seccion)
                    <article class="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <header class="border-b border-gray-200 px-5 py-4">
                            <h3 class="text-sm font-semibold text-gray-900">{{ $seccion['titulo'] }}</h3>
                            @if (! empty($seccion['descripcion'] ?? null))
                                <p class="text-xs text-gray-500">{{ $seccion['descripcion'] }}</p>
                            @endif
                        </header>
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 px-5 py-4 text-sm text-gray-700">
                            @foreach ($seccion['items'] as $item)
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-gray-500">{{ $item['label'] }}</dt>
                                    <dd class="mt-1 text-gray-800">{{ $item['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.authenticated>
