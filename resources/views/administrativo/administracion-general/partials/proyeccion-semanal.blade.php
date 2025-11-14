<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="{{ $sectionId ?? 'vista-5' }}">
    <header class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">5. Proyeccion semanal</p>
            <h2 class="text-lg font-semibold text-gray-900">Prestamos y cobranza por semana</h2>
        </div>
    </header>
    <div class="grid gap-4 px-6 py-6 sm:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Semana actual</p>
            <ul class="mt-3 space-y-2 text-sm text-gray-700">
                <li><span class="font-semibold text-gray-900">Prestamos:</span> {{ $weeklyProjection['semana_actual']['prestamos'] }}</li>
                <li><span class="font-semibold text-gray-900">Cobrado:</span> {{ $weeklyProjection['semana_actual']['cobrado'] }}</li>
                <li><span class="font-semibold text-gray-900">Saldo activo:</span> {{ $weeklyProjection['semana_actual']['saldo_activo'] }}</li>
            </ul>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Semana siguiente</p>
            <ul class="mt-3 space-y-2 text-sm text-gray-700">
                <li><span class="font-semibold text-gray-900">Meta prestamos:</span> {{ $weeklyProjection['semana_siguiente']['meta_prestamos'] }}</li>
                <li><span class="font-semibold text-gray-900">Estimado de cobranza:</span> {{ $weeklyProjection['semana_siguiente']['estimado_cobranza'] }}</li>
                <li><span class="font-semibold text-gray-900">Saldo programado:</span> {{ $weeklyProjection['semana_siguiente']['saldo_programado'] }}</li>
            </ul>
        </div>
    </div>
    <div class="border-t border-gray-200 px-6 py-4 text-sm text-gray-600">
        {{ $weeklyProjection['notas'] }}
    </div>
</section>
