<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="vista-2">
    <header class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Agenda de induccion</h2>
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
