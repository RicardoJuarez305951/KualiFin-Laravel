{{-- resources/views/mobile/supervisor/cartera/cartera_inactiva.blade.php --}}

<x-layouts.mobile.mobile-layout>
    <div x-data="{ showDetalle: null }" class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8">
        <section class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4 text-center shadow">
            <h1 class="text-xl font-bold text-slate-900">Cartera Inactiva</h1>
        </section>

        @foreach($blocks as $promotor)
            <section class="space-y-3 rounded-3xl border border-slate-200 bg-white shadow">
                {{-- Header Promotor --}}
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-[12px] font-bold text-gray-700">
                            {{ $loop->iteration }}
                        </span>
                        @php $horarioPago = trim((string) data_get($promotor, 'horario_pago', '')); @endphp
                        <div>
                            <span class="block text-[15px] font-semibold text-gray-900">{{ $promotor['nombre'] }}</span>
                            <span class="text-xs text-gray-500">Promotor</span>
                            @if($horarioPago !== '')
                                <span class="text-[11px] text-gray-500">Horario de pago: {{ $horarioPago }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Lista de clientes inactivos --}}
                <div class="space-y-2 px-3 pb-4">
                    @foreach($promotor['clientes'] as $idx => $cliente)
                        <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50/70 px-3 py-3 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[14px] font-medium text-gray-800">{{ $cliente['nombre'] }}</p>
                                    <p class="text-[12px] text-gray-500">Fallas: {{ $cliente['fallas'] }}</p>
                                </div>

                                <div class="flex gap-2">
                                    <button @click="showDetalle === {{ $loop->parent->iteration . $idx }} ? showDetalle = null : showDetalle = {{ $loop->parent->iteration . $idx }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-blue-500 text-blue-500 transition hover:bg-blue-50"
                                            title="Detalle">
                                        D
                                    </button>

                                    <a href="tel:{{ $cliente['telefono'] ?? '' }}"
                                       class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-green-500 text-green-500 transition hover:bg-green-50"
                                       title="Llamar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a.75.75 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143.75.75 0 0 1 .38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            {{-- Card Detalles --}}
                            <div x-show="showDetalle === {{ $loop->parent->iteration . $idx }}" x-transition
                                 class="space-y-1 rounded-xl border border-slate-200 bg-white px-3 py-3 text-[13px] text-gray-700 shadow-sm">
                                <p><span class="font-semibold">Nombre:</span> {{ $cliente['nombre'] }}</p>
                                <p><span class="font-semibold">CURP:</span> {{ $cliente['curp'] }}</p>
                                <p><span class="font-semibold">Fecha Nacimiento:</span> {{ $cliente['fecha_nac'] }}</p>
                                <p><span class="font-semibold">Dirección:</span> {{ $cliente['direccion'] }}</p>
                                <p><span class="font-semibold">Último Crédito:</span> {{ $cliente['ultimo_credito'] }} – ${{ number_format($cliente['monto_credito'], 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="space-y-3">
            <div>
                {{ $promotoresPaginator->withQueryString()->links() }}
            </div>

            {{-- Botón Regresar --}}
            <a href="{{ route("mobile.$role.cartera", array_merge($supervisorContextQuery ?? [], [])) }}"
               class="inline-flex w-full items-center justify-center rounded-xl border border-gray-300 bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                Regresar
            </a>
        </div>
    </div>
</x-layouts.mobile.mobile-layout>
