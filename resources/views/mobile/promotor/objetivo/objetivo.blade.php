{{-- resources/views/mobile/promotor/objetivo/objetivo.blade.php --}}
@php
    /** @var \Illuminate\Support\Collection<int, array{label: string, range: string, total: float}> $ventasPorSemana */
    $ventasPorSemana = collect($ventasPorSemana ?? []);
    $formatCurrency = static fn ($value) => '$' . number_format((float) $value, 2, '.', ',');
    $avance = number_format((float) ($porcentajeActual ?? 0), 1, '.', ',') . '%';
@endphp

<x-layouts.mobile.mobile-layout title="Mi Objetivo">
    <div class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8 text-slate-900">
        <section class="rounded-3xl border border-gray-300 bg-white p-6 shadow space-y-4">
            <header class="space-y-2 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-600">Avance</p>
                <h1 class="text-2xl font-bold text-slate-900 leading-tight">Mi objetivo semanal</h1>
                <p class="text-sm text-slate-600">Revisa tus metas y monitorea tus ventas en tiempo real.</p>
            </header>
            <div class="grid grid-cols-3 gap-3 text-center text-sm">
                <div class="rounded-2xl border border-gray-200 bg-slate-50 p-3 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-slate-700">Objetivo</p>
                    <p class="mt-1 text-base font-semibold text-slate-900">{{ $formatCurrency($objetivoSemanal ?? 0) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-emerald-50 p-3 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-emerald-700">Registrado</p>
                    <p class="mt-1 text-base font-semibold text-emerald-700">{{ $formatCurrency($ventaActual ?? 0) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-slate-100 p-3 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-slate-700">Avance</p>
                    <p class="mt-1 text-base font-semibold text-slate-900">{{ $avance }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-300 bg-white p-4 shadow space-y-4">
            <header class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-[0.2em]">Detalles</h2>
                <span class="inline-flex items-center rounded-full border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow">
                    Obj. ejercicio: {{ $formatCurrency($objetivoEjercicio ?? 0) }}
                </span>
            </header>
            <ul class="space-y-2">
                @foreach ($ventasPorSemana as $semana)
                    <li class="rounded-2xl border border-gray-200 bg-slate-50 px-4 py-3 shadow-sm">
                        <div class="flex items-center justify-between text-sm font-semibold text-slate-900">
                            <span>{{ $semana['label'] }}</span>
                            <span>{{ $formatCurrency($semana['total'] ?? 0) }}</span>
                        </div>
                        <p class="text-xs text-slate-600">{{ $semana['range'] }}</p>
                    </li>
                @endforeach
            </ul>

            @if(!empty($fraseMotivacional))
                <blockquote class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-center text-sm italic text-slate-600 shadow-sm">
                    {{ $fraseMotivacional }}
                </blockquote>
            @endif
        </section>

        <a href="{{ route("mobile.$role.index") }}"
           class="inline-flex w-full items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow hover:bg-gray-50">
            Regresar
        </a>
    </div>
</x-layouts.mobile.mobile-layout>
