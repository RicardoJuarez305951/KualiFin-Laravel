<ul class="divide-y divide-gray-200">
    @forelse($inactivos as $c)
        <li class="flex items-center justify-between py-2">
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800">
                    {{ $c['apellido'] ?? $c->apellido ?? '' }} {{ $c['nombre'] ?? $c->nombre ?? '' }}
                </p>
            </div>

            <div class="flex items-center space-x-2 ml-2">
                <a href="#"
                   class="w-8 h-8 border-2 border-blue-500 text-blue-500 rounded-full flex items-center justify-center"
                   title="Detalle">
                    D
                </a>
                <a href="tel:{{ $c['telefono'] ?? $c->telefono ?? '' }}"
                   class="w-8 h-8 border-2 border-green-500 text-green-500 rounded-full flex items-center justify-center"
                   title="Llamar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a.75.75 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143.75.75 0 0 1 .38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                </a>
            </div>
        </li>
    @empty
        <li class="py-2 text-center text-sm text-gray-500">Sin clientes inactivos</li>
    @endforelse
</ul>
