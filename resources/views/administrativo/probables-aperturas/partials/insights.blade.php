<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="vista-2">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-col gap-2">
        <h2 class="text-lg font-semibold text-gray-900">Insights y seguimiento</h2>
        <p class="text-sm text-gray-500">Prioriza fases del pipeline y organiza las proximas acciones con responsables.</p>
    </div>

    <div class="grid gap-6 px-6 py-6 lg:grid-cols-2">
        <article class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <h3 class="text-sm font-semibold text-gray-900">Distribucion por fase</h3>
            <p class="text-xs text-gray-500 mb-4">Identifica cuellos de botella en el proceso de apertura.</p>
            <ul class="space-y-2">
                @foreach ($phaseSummary as $fase)
                    <li class="flex items-center justify-between text-sm text-gray-700">
                        <span>{{ $fase['fase'] }}</span>
                        <span class="font-semibold text-gray-900">{{ $fase['total'] }}</span>
                    </li>
                @endforeach
            </ul>
        </article>

        <article class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <h3 class="text-sm font-semibold text-gray-900">Territorios con mas actividad</h3>
            <p class="text-xs text-gray-500 mb-4">Enfoca visitas y recursos en las zonas con mayor demanda.</p>
            <ul class="space-y-2">
                @foreach ($territorySummary as $territorio)
                    <li class="flex items-center justify-between text-sm text-gray-700">
                        <span>{{ $territorio['territorio'] }}</span>
                        <span class="font-semibold text-gray-900">{{ $territorio['total'] }}</span>
                    </li>
                @endforeach
            </ul>
        </article>
    </div>

    <div class="border-t border-gray-200 px-6 py-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Seguimiento sugerido</h3>
        <ul class="space-y-3">
            @foreach ($seguimientoSugerido as $tarea)
                <li class="rounded-lg border border-dashed border-gray-300 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-900">{{ $tarea['promotor'] }}</p>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $tarea['fase'] }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Responsable: {{ $tarea['responsable'] }} - Territorio: {{ $tarea['territorio'] }}</p>
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-600">
                        <span>{{ $tarea['proxima_accion'] }}</span>
                        <span>Ultima actualizacion: {{ $tarea['ultima_actualizacion'] }}</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
