{{-- resources/views/mobile/supervisor/cartera/cartera_activa.blade.php --}}

@php
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
    <div class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8">

        <section class="space-y-3 rounded-3xl border border-slate-200 bg-white px-5 py-4 shadow">
            {{-- T��tulo --}}
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold tracking-tight text-gray-900">Detalles: Cartera Activa</h1>
                {{-- Leyenda estatus --}}
                <div class="flex items-center gap-1.5 text-[11px]">
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">V: Pagado</span>
                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 ring-1 ring-amber-600/20">!: En Tiempo</span>
                    <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">F: Falla</span>
                    <span class="px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-600/20">Ad: Adelanto</span>
                </div>
            </div>
        </section>

        @foreach($blocks as $promotor)
            <section class="space-y-3 rounded-3xl border border-slate-200 bg-white shadow">
                {{-- Header del promotor --}}
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-[12px] font-bold text-indigo-700">
                            {{ $loop->iteration }}
                        </span>
                        @php $horarioPago = trim((string) data_get($promotor, 'horario_pago', '')); @endphp
                        <div class="flex flex-col">
                            <span class="text-[15px] font-semibold text-gray-900">{{ $promotor['nombre'] }}</span>
                            <span class="text-xs text-gray-500">Promotor</span>
                            @if($horarioPago !== '')
                                <span class="text-[11px] text-gray-500">Horario de pago: {{ $horarioPago }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-3 py-2 text-right shadow-inner">
                        <span class="block text-sm font-semibold text-slate-900">
                            ${{ number_format($promotor['dinero'], 2) }}
                        </span>
                        <span class="block text-[11px] text-gray-500">CrǸditos activos</span>
                    </div>
                </div>

                {{-- Tabla (grid) de clientes --}}
                <div class="space-y-2 px-3 pb-4">
                    {{-- Encabezados de columnas --}}
                    <div class="grid grid-cols-12 gap-x-3 rounded-xl bg-slate-50 px-2 py-2 text-[11px] font-semibold text-gray-600">
                        <div class="col-span-5">Cliente</div>
                        <div class="col-span-2 text-right">CrǸdito</div>
                        <div class="col-span-2 text-center">Sem.</div>
                        <div class="col-span-2 text-right">Semanal</div>
                        <div class="col-span-1 text-center">St</div>
                    </div>

                    <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-100">
                        @foreach($promotor['clientes'] as $i => $cliente)
                            <div class="grid grid-cols-12 items-center gap-x-3 px-2 py-3 text-[13px] sm:text-sm {{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50/80' }}">
                                {{-- Nombre (truncate para que no rompa el layout) --}}
                                <div class="col-span-5 min-w-0">
                                    <span class="block truncate font-medium text-gray-800">
                                        {{ $cliente['nombre'] }}
                                    </span>
                                </div>

                                {{-- Monto crǸdito --}}
                                <div class="col-span-2 text-right tabular-nums text-gray-700">
                                    ${{ number_format($cliente['monto'], 2) }}
                                </div>

                                {{-- Semana --}}
                                <div class="col-span-2 text-center tabular-nums text-gray-700">
                                    {{ $cliente['semana'] }}
                                </div>

                                {{-- Pago semanal --}}
                                <div class="col-span-2 text-right tabular-nums font-semibold text-gray-900">
                                    ${{ number_format($cliente['pago_semanal'], 2) }}
                                </div>

                                {{-- Estatus (badge) --}}
                                <div class="col-span-1 flex justify-center">
                                    <span class="px-2 py-0.5 text-[11px] font-bold {{ badgeClasses($cliente['status']) }}">
                                        {{ $cliente['status'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach

        <div class="space-y-3">
            <div>
                {{ $promotoresPaginator->withQueryString()->links() }}
            </div>
            <a href="{{ route("mobile.$role.cartera", array_merge($supervisorContextQuery ?? [], [])) }}"
               class="inline-flex w-full items-center justify-center rounded-xl border border-gray-300 bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                Regresar
            </a>
        </div>
    </div>
</x-layouts.mobile.mobile-layout>
