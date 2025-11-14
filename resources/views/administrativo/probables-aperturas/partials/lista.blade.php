<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="vista-1">
    <div class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Lista de promotores</h2>
        <p class="text-sm text-gray-500">Revisa quien impulsa cada apertura y consulta el detalle socioeconomico.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
            <thead class="bg-gray-50 uppercase text-xs font-semibold tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3">Promotor aperturado</th>
                    <th class="px-6 py-3">Promotor responsable</th>
                    <th class="px-6 py-3">Territorio</th>
                    <th class="px-6 py-3">Ultima actualizacion</th>
                    <th class="px-6 py-3 text-right">Encuesta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($promotores as $promotor)
                    @php
                        $detalleUrl = route('administrativo.probables_aperturas.show', $promotor['id']);
                    @endphp
                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $promotor['promotor_aperturado'] }}</div>
                            <div class="text-xs text-gray-500">Expediente {{ str_pad($promotor['id'], 3, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $promotor['promotor_responsable'] }}</div>
                            <div class="text-xs text-gray-500">Responsable de apertura</div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $promotor['territorio'] }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $promotor['ultima_actualizacion'] }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ $detalleUrl }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition-colors duration-150 hover:bg-blue-700">
                                Ver encuesta
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-500">
                            No hay promotores en proceso por el momento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
