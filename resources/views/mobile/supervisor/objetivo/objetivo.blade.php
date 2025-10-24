{{-- resources/views/supervisor/objetivo.blade.php --}}
@php
    /** @var string|null $supervisorNombre */
    /** @var float|null $objetivoSemanalTotal */
    /** @var float|null $ventaSemanalTotal */
    /** @var float|null $faltanteSemanalTotal */
    /** @var float|null $porcentajeSemanalTotal */
    /** @var float|null $objetivoEjercicioTotal */
    /** @var float|null $ventaEjercicioTotal */
    /** @var float|null $faltanteEjercicioTotal */
    /** @var float|null $porcentajeEjercicioTotal */
    /** @var \Illuminate\Support\Collection<int, array<string, mixed>>|array $promotoresResumen */

    $formatCurrency = static fn ($value) => '$' . number_format((float) $value, 2, '.', ',');
    $formatPercentage = static fn ($value) => number_format((float) $value, 1, '.', ',') . '%';

    $supervisorLabel = $supervisorNombre ?? (auth()->user()->name ?? 'Supervisor');
    $promotores = collect($promotoresResumen ?? []);

    $pctSem = min(100, max(0, (float) ($porcentajeSemanalTotal ?? 0)));
    $pctEje = min(100, max(0, (float) ($porcentajeEjercicioTotal ?? 0)));
@endphp

<x-layouts.mobile.mobile-layout title="Objetivo – {{ $supervisorLabel }}">
    <div class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8 text-slate-900">

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow space-y-4">
            <header class="text-center">
                <h2 class="text-2xl font-bold text-slate-900 leading-tight">Semana (ventas)</h2>
            </header>
            <div class="grid grid-cols-2 gap-4 text-sm text-slate-800">
                <div class="space-y-1">
                    <p class="text-slate-500">Supervisor</p>
                    <p class="font-semibold">{{ $supervisorLabel }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-slate-500">Objetivo Sem</p>
                    <p class="font-bold text-indigo-700">{{ $formatCurrency($objetivoSemanalTotal ?? 0) }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-slate-500">Venta Sem</p>
                    <p class="font-semibold text-green-700">{{ $formatCurrency($ventaSemanalTotal ?? 0) }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-slate-500">Para llegar</p>
                    <p class="font-semibold text-amber-700">{{ $formatCurrency($faltanteSemanalTotal ?? 0) }}</p>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Progreso semanal</span>
                    <span>{{ $formatPercentage($porcentajeSemanalTotal ?? 0) }}</span>
                </div>
                <div class="h-2.5 rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600" style="width: {{ $pctSem }}%"></div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow space-y-4">
            <header class="text-center">
                <h2 class="text-2xl font-bold text-slate-900 leading-tight">Ejercicio (acumulado)</h2>
            </header>
            <div class="grid grid-cols-2 gap-4 text-sm text-slate-800">
                <div class="space-y-1">
                    <p class="text-slate-500">Objetivo Ejercicio</p>
                    <p class="font-bold text-indigo-700">{{ $formatCurrency($objetivoEjercicioTotal ?? 0) }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-slate-500">Venta en Ejercicio</p>
                    <p class="font-semibold text-green-700">{{ $formatCurrency($ventaEjercicioTotal ?? 0) }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-slate-500">Para llegar</p>
                    <p class="font-semibold text-amber-700">{{ $formatCurrency($faltanteEjercicioTotal ?? 0) }}</p>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Progreso ejercicio</span>
                    <span>{{ $formatPercentage($porcentajeEjercicioTotal ?? 0) }}</span>
                </div>
                <div class="h-2.5 rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-green-600" style="width: {{ $pctEje }}%"></div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow space-y-4">
            <header class="text-center">
                <h2 class="text-2xl font-bold text-slate-900 leading-tight">Promotores</h2>
            </header>
            <div class="space-y-3">
                @foreach($promotores as $idx => $p)
                    @php
                        $promotorPct = min(100, max(0, (float) ($p['porcentaje'] ?? 0)));
                    @endphp
                    <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-[11px] font-bold text-slate-700">{{ $loop->iteration }}</span>
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-slate-900">{{ $p['nombre'] ?? 'Promotor' }}</p>
                                    <p class="text-xs text-slate-500">
                                        Obj: <span class="font-medium">{{ $formatCurrency($p['objetivo'] ?? 0) }}</span> ·
                                        Venta: <span class="font-medium text-green-700">{{ $formatCurrency($p['venta'] ?? 0) }}</span> ·
                                        Para llegar: <span class="font-medium text-amber-700">{{ $formatCurrency($p['faltante'] ?? 0) }}</span>
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-slate-600">{{ $formatPercentage($p['porcentaje'] ?? 0) }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-200 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-sky-500 to-blue-600" style="width: {{ $promotorPct }}%"></div>
                        </div>
                        @if(!empty($p['route']) && $p['route'] !== '#')
                            <div class="flex justify-end">
                                <a href="{{ $p['route'] }}"
                                   class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                    Ver objetivo
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <a href="{{ route("mobile.index") }}"
               class="inline-flex items-center justify-center rounded-xl bg-slate-700 px-4 py-3 text-sm font-semibold text-white shadow transition hover:bg-slate-800">
                Regresar
            </a>
            <a href="{{ url()->current() }}"
               class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow transition hover:bg-blue-700">
                Actualizar
            </a>
            <a href="{{ route("mobile.$role.objetivo") }}"
               class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white shadow transition hover:bg-amber-700">
                Reporte
            </a>
        </div>

    </div>
</x-layouts.mobile.mobile-layout>
