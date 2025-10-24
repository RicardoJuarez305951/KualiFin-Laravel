{{-- resources/views/mobile/ejecutivo/cartera/cartera_inactiva.blade.php --}}
@php
    $role = $role ?? 'ejecutivo';
    $supervisorContextQuery = $supervisorContextQuery ?? [];
@endphp

<x-layouts.mobile.mobile-layout>
    <div x-data="{ showDetalle: null }" class="mx-auto w-full max-w-md space-y-8 px-5 py-10">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <h1 class="text-2xl font-semibold leading-tight text-slate-900">Cartera Inactiva</h1>
        </section>

        <section class="space-y-6">
            @foreach($blocks as $promotor)
                @php $horarioPago = trim((string) data_get($promotor, 'horario_pago', '')); @endphp
                <article class="rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                    <header class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700">
                                {{ $loop->iteration }}
                            </span>
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-slate-900">{{ $promotor['nombre'] }}</p>
                                <p class="text-xs text-slate-500">Promotor</p>
                                @if($horarioPago !== '')
                                    <p class="text-[11px] text-slate-500">Horario de pago: {{ $horarioPago }}</p>
                                @endif
                            </div>
                        </div>
                    </header>

                    <div class="px-5 py-3">
                        <div class="space-y-4">
                            @foreach($promotor['clientes'] as $idx => $cliente)
                                <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-[14px] font-medium text-slate-900">{{ $cliente['nombre'] }}</p>
                                            <p class="text-[12px] text-slate-500">Fallas: {{ $cliente['fallas'] }}</p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button
                                                @click="showDetalle === {{ $loop->parent->iteration . $idx }} ? showDetalle = null : showDetalle = {{ $loop->parent->iteration . $idx }}"
                                                class="flex h-9 w-9 items-center justify-center rounded-xl border-2 border-blue-500 text-sm font-semibold text-blue-500 transition hover:bg-blue-50"
                                                title="Detalle"
                                            >
                                                D
                                            </button>
                                            <a href="tel:{{ $cliente['telefono'] ?? '' }}"
                                               class="flex h-9 w-9 items-center justify-center rounded-xl border-2 border-emerald-500 text-emerald-500 transition hover:bg-emerald-50"
                                               title="Llamar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a.75.75 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143.75.75 0 0 1 .38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    <div x-show="showDetalle === {{ $loop->parent->iteration . $idx }}" x-transition
                                         class="space-y-1 rounded-2xl border border-slate-200 bg-white p-3 text-[13px] text-slate-700">
                                        <p><span class="font-semibold">Nombre:</span> {{ $cliente['nombre'] }}</p>
                                        <p><span class="font-semibold">CURP:</span> {{ $cliente['curp'] }}</p>
                                        <p><span class="font-semibold">Fecha Nacimiento:</span> {{ $cliente['fecha_nac'] }}</p>
                                        <p><span class="font-semibold">Dirección:</span> {{ $cliente['direccion'] }}</p>
                                        <p><span class="font-semibold">Último Crédito:</span> {{ $cliente['ultimo_credito'] }} — ${{ number_format($cliente['monto_credito'], 2) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <div class="space-y-4">
            <div>
                {{ $promotoresPaginator->withQueryString()->links() }}
            </div>
            <a href="{{ route("mobile.$role.cartera", array_merge($supervisorContextQuery, [])) }}"
               class="flex items-center justify-center rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200">
                Regresar
            </a>
        </div>
    </div>
</x-layouts.mobile.mobile-layout>
