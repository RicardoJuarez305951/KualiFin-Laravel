{{-- resources/views/mobile/supervisor/cartera/cartera_activa.blade.php --}}

@php
    // Helper para badge de estatus
    if (!function_exists('badgeClasses')) {
        function badgeClasses($s) {
            return match ($s) {
                'V'  => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
                '!'  => 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-600/20',
                'F'  => 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20',
                'Ad' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-600/20',
                default => 'bg-gray-50 text-gray-700 ring-1 ring-gray-300',
            };
        }
    }
@endphp

<x-layouts.mobile.mobile-layout>
    <div class="p-4 space-y-5">
        {{-- Título --}}
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold tracking-tight text-gray-900">Detalles: Cartera Activa</h1>
            {{-- Leyenda estatus --}}
            <div class="flex items-center gap-2 text-[11px]">
                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">V: Pagado</span>
                <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 ring-1 ring-amber-600/20">!: En Tiempo</span>
                <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">F: Falla</span>
                <span class="px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-600/20">Ad: Adelanto</span>
            </div>
        </div>

        @foreach($blocks as $promotor)
            <div class="rounded-2xl border border-gray-100 bg-white/80 backdrop-blur-sm shadow-sm">
                {{-- Header del promotor --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 text-[12px] font-bold rounded-full bg-indigo-100 text-indigo-700">
                            {{ $loop->iteration }}
                        </span>
                        @php $diasPago = trim((string) data_get($promotor, 'dias_de_pago', '')); @endphp
                        <div class="flex flex-col">
                            <span class="text-[15px] font-semibold text-gray-900">{{ $promotor['nombre'] }}</span>
                            <span class="text-xs text-gray-500">Promotor</span>
                            @if($diasPago !== '')
                                <span class="text-[11px] text-gray-500">Días de pago: {{ $diasPago }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="block text-sm font-semibold text-gray-900">
                            ${{ number_format($promotor['dinero'], 2) }}
                        </span>
                        <span class="block text-[11px] text-gray-500">Créditos activos</span>
                    </div>
                </div>

                {{-- Tabla (grid) de clientes --}}
                <div class="px-3 pb-3">
                    {{-- Encabezados de columnas --}}
                    <div class="grid grid-cols-12 gap-x-3 text-[11px] font-semibold text-gray-600 px-1 pt-2 pb-1">
                        <div class="col-span-5">Cliente</div>
                        <div class="col-span-2 text-right">Crédito</div>
                        <div class="col-span-2 text-center">Sem.</div>
                        <div class="col-span-2 text-right">Semanal</div>
                        <div class="col-span-1 text-center">St</div>
                    </div>

                    <div class="rounded-xl overflow-hidden ring-1 ring-gray-100">
                        @foreach($promotor['clientes'] as $i => $cliente)
                            <div class="grid grid-cols-12 gap-x-3 items-center px-2 py-2.5
                                        text-[13px] sm:text-sm
                                        {{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                {{-- Nombre (truncate para que no rompa el layout) --}}
                                <div class="col-span-5 min-w-0">
                                    <span class="block truncate text-gray-800 font-medium">
                                        {{ $cliente['nombre'] }}
                                    </span>
                                </div>

                                {{-- Monto crédito --}}
                                <div class="col-span-2 text-right tabular-nums text-gray-700">
                                    ${{ number_format($cliente['monto'], 2) }}
                                </div>

                                {{-- Semana --}}
                                <div class="col-span-2 text-center tabular-nums text-gray-700">
                                    {{ $cliente['semana'] }}
                                </div>

                                {{-- Pago semanal --}}
                                <div class="col-span-2 text-right tabular-nums text-gray-900 font-semibold">
                                    ${{ number_format($cliente['pago_semanal'], 2) }}
                                </div>

                                {{-- Estatus (badge) --}}
                                <div class="col-span-1 flex justify-center">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ badgeClasses($cliente['status']) }}">
                                        {{ $cliente['status'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Notas de estatus (accesible y claro) --}}
                    {{-- <div class="flex flex-wrap items-center gap-3 text-[11px] text-gray-500 mt-2 px-1">
                        <span><span class="font-semibold">V</span>: Pagado</span>
                        <span><span class="font-semibold">!</span>: En tiempo</span>
                        <span><span class="font-semibold">F</span>: En falla</span>
                        <span><span class="font-semibold">Ad</span>: Adelantado</span>
                    </div> --}}
                </div>
            </div>
        @endforeach

        <div class="mt-4">
            {{ $promotoresPaginator->withQueryString()->links() }}
        </div>
        {{-- <a href="{{ url()->previous() }}" --}}
        <a href="{{ route("mobile.$role.cartera", array_merge($supervisorContextQuery ?? [], [])) }}"
        class="flex items-center justify-center rounded-xl border border-gray-300 text-white text-sm font-semibold px-3 py-2 bg-blue-600 hover:bg-blue-700 shadow-sm">
            Regresar
        </a>
    </div>
</x-layouts.mobile.mobile-layout>
