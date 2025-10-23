<ul class="space-y-3">
    @forelse($inactivos as $c)
        <li class="flex items-center justify-between rounded-2xl border border-gray-300 bg-white px-4 py-3 shadow">
            <div class="space-y-1">
                <p class="text-sm font-semibold text-slate-900">
                    {{ ($c['apellido_p'] ?? $c->apellido_p ?? '') }} {{ ($c['apellido_m'] ?? $c->apellido_m ?? '') }} {{ ($c['nombre'] ?? $c->nombre ?? '') }}
                </p>
                <p class="text-xs text-slate-600">
                    {{ $c['telefono'] ?? $c->telefono ?? 'Sin tel&eacute;fono' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-blue-400 bg-blue-50 text-blue-600 transition hover:bg-blue-100"
                    title="Detalle"
                    @click="openInactivaDetail(@js($c))">
                    <span class="text-sm font-semibold">D</span>
                </button>

                <a href="tel&eacute;fono $c['tel&eacute;fono ?? $c->tel&eacute;fono ?? '' }}"
                   class="flex h-9 w-9 items-center justify-center rounded-full border border-emerald-300 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                   title="Llamar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a.75.75 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143.75.75 0 0 1 .38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                </a>
            </div>
        </li>
    @empty
        <li class="py-2 text-center text-sm text-slate-600">Sin clientes inactivos</li>
    @endforelse
</ul>



