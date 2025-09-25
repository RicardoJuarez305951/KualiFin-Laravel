{{-- resources/views/mobile/supervisor/cartera/cartera_falla.blade.php --}}

@php
    $role = $role ?? 'ejecutivo';
    $supervisorContextQuery = $supervisorContextQuery ?? [];
@endphp

<x-layouts.mobile.mobile-layout>
    
    <div x-data class="p-4 space-y-5">
        @include('mobile.modals.calculadora')
        @include('mobile.modals.detalle')
        <h1 class="text-xl font-bold text-gray-900">Cartera Falla</h1>
        @foreach($blocks as $promotor)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                {{-- Header Promotor --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 text-[12px] font-bold rounded-full bg-red-100 text-red-700">
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
                    <div class="text-right">
                        <span class="block text-sm font-semibold text-rose-600">
                            ${{ number_format($promotor['dinero'], 2) }}
                        </span>
                        <span class="block text-[11px] text-gray-500">Falla total ({{ $promotor['falla'] }}%)</span>
                    </div>
                </div>

                {{-- Lista de clientes --}}
                <div class="px-3 py-2">
                    @foreach($promotor['clientes'] as $cliente)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            {{-- Datos cliente --}}
                            <div>
                                <p class="text-[14px] font-medium text-gray-800">{{ $cliente['nombre'] }}</p>
                                <p class="text-[12px] text-gray-500">
                                    Monto fallado: ${{ number_format($cliente['monto'], 2) }}
                                </p>
                            </div>

                            {{-- Botones --}}
                            <div class="flex gap-2">
                                {{-- Botón Cobrar --}}
                                <button @click="$store.calc.open(@js($cliente['nombre']))"
                                    class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-600 text-white font-bold hover:bg-emerald-700 shadow-sm">
                                    $
                                </button>

                                {{-- Botón Historial --}}
                                <a href='{{ route("mobile.$role.cliente_historial", array_merge($supervisorContextQuery, ['cliente' => $cliente["id"]])) }}'
                                   class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500 text-white font-bold hover:bg-amber-600 shadow-sm">
                                    H
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="mt-4">
            {{ $promotoresPaginator->withQueryString()->links() }}
        </div>

        <a href="{{ route("mobile.$role.cartera", array_merge($supervisorContextQuery, [])) }}"
          class="flex items-center justify-center rounded-xl border border-gray-300 text-white text-sm font-semibold px-3 py-2 bg-blue-600 hover:bg-blue-700 shadow-sm">
          Regresar
        </a>
    </div>
    
</x-layouts.mobile.mobile-layout>
