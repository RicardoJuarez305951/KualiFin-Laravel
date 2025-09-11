{{-- resources/views/mobile/promotor/venta/venta.blade.php --}}
@php
    /** @var string $role */
    $role = isset($role) && $role ? $role : 'promotor';

    function money_mx($v) {
        return '$' . number_format($v, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout title="Venta">
    <div class="mx-auto w-[22rem] sm:w-[26rem] p-4 sm:p-6">
        <section class="bg-white/95 backdrop-blur rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
            {{-- Header --}}
            <div class="p-6">
                <div class="flex items-start justify-center gap-4">
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Venta registrada</h1>
                        <p class="mt-1 text-sm text-gray-600 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 text-blue-700 px-2.5 py-1 text-xs font-semibold ring-1 ring-inset ring-blue-200">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 2v3M16 2v3M3 10h18M4 7h16a1 1 0 011 1v11a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1z" />
                                </svg>
                                {{ $fecha }}
                            </span>
                        </p>
                    </div>
                    
                </div>

                {{-- Supervisor/Ejecutivo --}}
                <div class="mt-5 grid grid-cols-1 gap-3">
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5">
                        <span class="text-xs font-medium text-gray-600">Supervisor</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $supervisor }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5">
                        <span class="text-xs font-medium text-gray-600">Ejecutivo</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $ejecutivo }}</span>
                    </div>
                </div>
            </div>

            {{-- Clientes --}}
            <div class="px-4">
                <div class="hidden sm:grid grid-cols-7 gap-2 text-xs font-semibold text-gray-600 px-2 pb-2">
                    <div class="col-span-4 text-center">Cliente</div>
                    <div class="col-span-3 text-right">Monto</div>
                </div>
                <div class="space-y-2">
                    @foreach ($clientes as $cliente)
                        <div class="grid grid-cols-7 items-center gap-2 bg-white rounded-xl border border-gray-100 px-3 py-2.5 shadow-sm">
                            <div class="col-span-4 flex items-center gap-2">
                                <span class="inline-flex shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 items-center justify-center text-[11px] font-bold">
                                    {{ $loop->iteration }}
                                </span>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $cliente['nombre'] }}</p>
                            </div>
                            <div class="col-span-3 text-right">
                                <p class="text-sm font-semibold text-gray-900">{{ money_mx($cliente['monto']) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Total --}}
            <div class="mt-4 bg-gray-50 border-t border-gray-100 px-6 py-4 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-600">Total</span>
                <span class="text-xl font-extrabold text-gray-900">{{ money_mx($total) }}</span>
            </div>

            {{-- Botones --}}

            <div class="p-3 pt-3 flex flex-col sm:flex-row gap-3">
                <a href="{{ route("mobile.$role.ingresar_cliente") }}"
                   class="w-full inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white text-sm font-semibold px-4 py-3 shadow-lg hover:from-blue-700 hover:to-blue-600">
                    <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                    </svg>
                    Agregar cliente
                </a>
                <a href="{{ route("mobile.$role.index") }}"
                   class="w-full inline-flex items-center justify-center rounded-2xl border border-gray-300 text-gray-800 text-sm font-semibold px-4 py-3 hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Regresar
                </a>
            </div>
            <div class="p-3">
                <a href=""
                    class="w-2/3 inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 text-white text-sm font-semibold px-4 py-3 shadow-lg hover:from-blue-700 hover:to-blue-600">
                    Enviar mis ventas
                </a>
            </div>
        </section>
    </div>
</x-layouts.mobile.mobile-layout>
