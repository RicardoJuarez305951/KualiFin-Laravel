{{-- resources/views/mobile/supervisor/cartera/cartera_vencida.blade.php --}}

<x-layouts.mobile.mobile-layout>
    <div x-data class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8">
        {{-- Incluimos el modal calculadora --}}
        @include('mobile.modals.calculadora')
        @include('mobile.modals.detalle')

        <section class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-center shadow">
            <h1 class="text-xl font-bold text-amber-700">Cartera Vencida</h1>
        </section>

        @foreach($blocks as $promotor)
            <section class="space-y-3 rounded-3xl border border-amber-200 bg-white shadow">
                {{-- Header Promotor --}}
                <div class="flex items-center justify-between gap-3 border-b border-amber-100 px-4 py-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-orange-100 text-[12px] font-bold text-orange-700">
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
                    <div class="rounded-2xl bg-amber-50 px-3 py-2 text-right shadow-inner">
                        <span class="block text-sm font-semibold text-orange-600">
                            ${{ number_format($promotor['dinero'], 2) }}
                        </span>
                        <span class="block text-[11px] text-gray-500">Total vencido ({{ $promotor['vencido'] }}%)</span>
                    </div>
                </div>

                {{-- Lista de clientes --}}
                <div class="space-y-2 px-3 pb-4">
                    @foreach($promotor['clientes'] as $cliente)
                        <div class="space-y-3 rounded-2xl border border-amber-100 bg-amber-50/60 px-3 py-3 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[14px] font-medium text-gray-800">{{ $cliente['nombre'] }}</p>
                                    <p class="text-[12px] text-gray-500">
                                        Monto vencido: ${{ number_format($cliente['monto'], 2) }}
                                    </p>
                                </div>

                                <div class="flex gap-2">
                                    <button @click="$store.calc.open(@js($cliente['nombre']))"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white font-bold shadow-sm transition hover:bg-emerald-700"
                                        title="Cobrar">
                                        $
                                    </button>

                                    <a href='{{ route("mobile.$role.cliente_historial", array_merge($supervisorContextQuery ?? [], ['cliente' => $cliente["id"]])) }}'
                                       class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500 text-white font-bold shadow-sm transition hover:bg-amber-600"
                                       title="Historial">
                                        H
                                    </a>
                                </div>
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
