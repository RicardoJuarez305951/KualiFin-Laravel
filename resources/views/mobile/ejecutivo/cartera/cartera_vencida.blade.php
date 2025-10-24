{{-- resources/views/mobile/ejecutivo/cartera/cartera_vencida.blade.php --}}
@php
    $role = $role ?? 'ejecutivo';
    $supervisorContextQuery = $supervisorContextQuery ?? [];
@endphp

<x-layouts.mobile.mobile-layout>
    <div x-data class="mx-auto w-full max-w-md space-y-8 px-5 py-10">
        @include('mobile.modals.calculadora')
        @include('mobile.modals.detalle')

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <h1 class="text-2xl font-semibold leading-tight text-slate-900">Cartera Vencida</h1>
        </section>

        <section class="space-y-6">
            @foreach($blocks as $promotor)
                @php $horarioPago = trim((string) data_get($promotor, 'horario_pago', '')); @endphp
                <article class="rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                    <header class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700">
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
                        <div class="text-right">
                            <p class="text-sm font-semibold text-orange-600">${{ number_format($promotor['dinero'], 2) }}</p>
                            <p class="text-[11px] text-slate-500">Total vencido ({{ $promotor['vencido'] }}%)</p>
                        </div>
                    </header>

                    <div class="px-5 py-3">
                        <div class="space-y-3">
                            @foreach($promotor['clientes'] as $cliente)
                                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                                    <div>
                                        <p class="text-[14px] font-medium text-slate-900">{{ $cliente['nombre'] }}</p>
                                        <p class="text-[12px] text-slate-500">
                                            Monto vencido: ${{ number_format($cliente['monto'], 2) }}
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="$store.calc.open(@js($cliente['nombre']))"
                                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500"
                                            title="Cobrar">
                                            $
                                        </button>
                                        <a href='{{ route("mobile.$role.cliente_historial", array_merge($supervisorContextQuery, ['cliente' => $cliente["id"]])) }}'
                                           class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-sm font-bold text-white shadow-sm transition hover:bg-amber-400"
                                           title="Historial">
                                            H
                                        </a>
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
