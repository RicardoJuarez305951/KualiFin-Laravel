{{-- resources/views/mobile/ejecutivo/cartera/cartera_activa.blade.php --}}
@php
    $role = $role ?? 'ejecutivo';
    $supervisorContextQuery = $supervisorContextQuery ?? [];

    if (!function_exists('badgeClasses')) {
        function badgeClasses($s) {
            return match ($s) {
                'V'  => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-600/30',
                '!'  => 'bg-amber-100 text-amber-800 ring-1 ring-amber-600/30',
                'F'  => 'bg-rose-100 text-rose-700 ring-1 ring-rose-600/30',
                'Ad' => 'bg-sky-100 text-sky-700 ring-1 ring-sky-600/30',
                default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-300',
            };
        }
    }
@endphp

<x-layouts.mobile.mobile-layout>
    <div class="mx-auto w-full max-w-md space-y-8 px-5 py-10">
        {{-- Encabezado --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <div class="space-y-4">
                <h1 class="text-2xl font-semibold leading-tight text-slate-900">Detalles: Cartera Activa</h1>
                <div class="flex flex-wrap gap-2 text-[11px] text-slate-600">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 font-semibold text-emerald-700">V: Pagado</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 font-semibold text-amber-700">!: En Tiempo</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-3 py-1 font-semibold text-rose-700">F: Falla</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-3 py-1 font-semibold text-sky-700">Ad: Adelanto</span>
                </div>
        </section>

        {{-- Promotores --}}
        <section class="space-y-6">
            @foreach($blocks as $promotor)
                <article class="rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                    @php $horarioPago = trim((string) data_get($promotor, 'horario_pago', '')); @endphp
                    <header class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">
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
                            <p class="text-sm font-semibold text-slate-900">${{ number_format($promotor['dinero'], 2) }}</p>
                            <p class="text-[11px] text-slate-500">Créditos activos</p>
                        </div>
                    </header>

                    <div class="px-4 py-3">
                        <div class="grid grid-cols-12 gap-x-3 px-2 pb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <div class="col-span-5">Cliente</div>
                            <div class="col-span-2 text-right">Crédito</div>
                            <div class="col-span-2 text-center">Sem.</div>
                            <div class="col-span-2 text-right">Semanal</div>
                            <div class="col-span-1 text-center">St</div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-slate-200">
                            @foreach($promotor['clientes'] as $i => $cliente)
                                <div class="grid grid-cols-12 items-center gap-x-3 px-3 py-2.5 text-[13px] sm:text-sm {{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                                    <div class="col-span-5 min-w-0">
                                        <span class="block truncate font-medium text-slate-900">
                                            {{ $cliente['nombre'] }}
                                        </span>
                                    </div>
                                    <div class="col-span-2 text-right tabular-nums text-slate-700">
                                        ${{ number_format($cliente['monto'], 2) }}
                                    </div>
                                    <div class="col-span-2 text-center tabular-nums text-slate-700">
                                        {{ $cliente['semana'] }}
                                    </div>
                                    <div class="col-span-2 text-right tabular-nums font-semibold text-slate-900">
                                        ${{ number_format($cliente['pago_semanal'], 2) }}
                                    </div>
                                    <div class="col-span-1 flex justify-center">
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ badgeClasses($cliente['status']) }}">
                                            {{ $cliente['status'] }}
                                        </span>
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
