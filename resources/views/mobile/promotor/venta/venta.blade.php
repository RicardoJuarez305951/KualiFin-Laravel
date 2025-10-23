{{-- resources/views/mobile/promotor/venta/venta.blade.php --}}
@php
    /** @var string $role */
    $role = isset($role) && $role ? $role : 'promotor';

    function money_mx($v) {
        return '$' . number_format($v, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout :title="'Venta registrada ' . $fecha">
    <div class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8 text-slate-900">
        <section class="rounded-3xl border border-gray-300 bg-white shadow overflow-hidden">
            {{-- Header --}}
            <div class="p-6">
                <div class="flex items-start justify-center gap-4">
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 text-center">Venta registrada</h1>
                        <p class="mt-1 text-sm text-slate-600 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 text-blue-700 px-2.5 py-1 text-xs font-semibold border border-blue-200">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 2v3M16 2v3M3 10h18M4 7h16a1 1 0 011 1v11a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1z" />
                                </svg>
                                Hoy es: {{ $fecha }}
                            </span>
                        </p>
                    </div>    
                </div>

                <div class="p-2 flex justify-center gap-4">
                    <h1 class="inline-flex items-center gap-1 rounded-full bg-orange-50 text-orange-700 px-2.5 py-1 text-xs font-semibold ring-1 ring-inset ring-orange-200">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            d="M8 2v3M16 2v3M3 10h18M4 7h16a1 1 0 011 1v11a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1z" />
                        </svg>
                        {{-- Se necesita obtener del EJECUTIVO --}}
                        Fecha limite: {{ $fecha }}
                    </h1>
                </div>

                {{-- Supervisor/Ejecutivo --}}
                <div class="mt-5 grid grid-cols-1 gap-3">
                    <div class="flex items-center justify-between rounded-xl border border-gray-300 bg-slate-50 px-3 py-2.5">
                        <span class="text-xs font-medium text-slate-600">Supervisor</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $supervisor }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-300 bg-slate-50 px-3 py-2.5">
                        <span class="text-xs font-medium text-slate-600">Ejecutivo</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $ejecutivo }}</span>
                    </div>
                </div>
            </div>

            {{-- Clientes --}}
            <div class="px-4">
                <div class="hidden sm:grid grid-cols-8 gap-2 text-xs font-semibold text-slate-600 px-2 pb-2">
                    <div class="col-span-4 text-center">Cliente</div>
                    <div class="col-span-3 text-right">Monto</div>
                    @role('ejecutivo|administrativo')
                    <div class="col-span-1 text-center">Búsqueda</div>
                    @endrole
                </div>
                <div class="space-y-2">
                    @foreach ($clientes as $cliente)
                        @php
                            $nombreCompleto = trim("{$cliente->nombre} {$cliente->apellido_p} {$cliente->apellido_m}");
                        @endphp
                        <div class="grid grid-cols-8 items-center gap-2 bg-white rounded-xl border border-gray-300 px-3 py-2.5 shadow-sm">
                            <div class="col-span-4 flex items-center gap-2">
                                <span class="inline-flex shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 items-center justify-center text-[11px] font-bold">
                                    {{ $loop->iteration }}
                                </span>
                                <p class="text-sm font-medium text-slate-900 truncate">{{ $nombreCompleto }}</p>
                            </div>
                            <div class="col-span-3 text-right">
                                <p class="text-sm font-semibold text-slate-900">{{ money_mx($cliente->credito->monto_total ?? $cliente->monto_maximo) }}</p>
                            </div>
                            @role('ejecutivo|administrativo')
                            <div class="col-span-1">
                                <a href="{{ route('consulta.deudores', ['cliente' => $nombreCompleto]) }}"
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800 flex items-center justify-center"
                                   title="Búsqueda">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                              d="M10 4a6 6 0 104.472 10.028l4.25 4.25a1 1 0 101.415-1.415l-4.25-4.25A6 6 0 0010 4z"/>
                                    </svg>
                                </a>
                            </div>
                            @endrole
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Total --}}
            <div class="mt-4 bg-slate-50 border-t border-gray-300 px-6 py-4 flex items-center justify-between">
                <span class="text-sm font-medium text-slate-600">Total</span>
                <span class="text-xl font-extrabold text-slate-900">{{ money_mx($total) }}</span>
            </div>

            <div 
                x-data="{
                    showModal: false,
                    modalSuccess: false,
                    modalMessage: '',
                    isLoading: false,
                    submitVentas() {
                        this.isLoading = true;
                        fetch('{{ route("mobile.promotor.enviar_ventas") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.modalSuccess = data.success;
                            this.modalMessage = data.message;
                            this.showModal = true;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.modalSuccess = false;
                            this.modalMessage = 'Error de conexión. Inténtalo de nuevo.';
                            this.showModal = true;
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                    },
                    handleModalContinue() {
                        if (this.modalSuccess) {
                            window.location.href = '{{ route("mobile.promotor.index") }}';
                        } else {
                            this.showModal = false;
                        }
                    }
                }"
            >
                
                {{-- Botones --}}

                <div class="p-3 pt-3 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route("mobile.$role.ingresar_cliente") }}"
                    class="w-full inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow hover:bg-black">
                        <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                        </svg>
                        Agregar cliente
                    </a>

                    <a href="{{ route("mobile.$role.index") }}"
                    class="w-full inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Regresar
                    </a>
                </div>

                {{-- Botón de Enviar Ventas --}}
                @if(count($clientes) > 0)
                    <div class="p-3">
                        <button
                            type="button"
                            @click="submitVentas()"
                            :disabled="isLoading"
                            class="w-full inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-green-600 to-green-500 text-white text-sm font-semibold px-4 py-3 shadow-lg hover:from-green-700 hover:to-green-600 disabled:opacity-50 disabled:cursor-wait"
                        >
                            <span x-show="!isLoading">Enviar mis ventas</span>
                            <span x-show="isLoading">Enviando...</span>
                        </button>
                    </div>
                @endif
                
                {{-- Inclusión del Modal (se queda dentro del div de Alpine para que funcione) --}}
                @include('mobile.promotor.venta.modal_enviar_clientes')

            </div>
        </section>
    </div>
</x-layouts.mobile.mobile-layout>






