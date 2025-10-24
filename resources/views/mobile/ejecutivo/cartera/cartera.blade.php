{{-- resources/views/mobile/ejecutivo/cartera/cartera.blade.php --}}
@php
    $role              = $role            ?? 'ejecutivo';
    $ejecutivo         = $ejecutivo       ?? null;
    $nombre            = $nombre          ?? ($ejecutivo->nombre      ?? auth()->user()->name ?? 'Ejecutivo');
    $apellido_p        = $apellido_p      ?? ($ejecutivo->apellido_p  ?? '');
    $apellido_m        = $apellido_m      ?? ($ejecutivo->apellido_m  ?? '');
    $supervisores      = $supervisores    ?? collect();

    $cartera_activa    = $cartera_activa    ?? 0;
    $cartera_falla     = $cartera_falla     ?? 0;
    $cartera_vencida   = $cartera_vencida   ?? 0;
    $cartera_inactivaP = $cartera_inactivaP ?? 0;

    $formatMoney = fn($value) => '$' . number_format((float) $value, 2, '.', ',');
    $formatPercentage = fn($value) => number_format((float) $value, 2, '.', ',') . '%';
@endphp

<x-layouts.mobile.mobile-layout title="Cartera Ejecutivo">
    <div class="mx-auto w-full max-w-md space-y-8 px-5 py-10">
        {{-- Ejecutivo --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <h1 class="text-2xl font-semibold leading-tight text-slate-900">Cartera Ejecutivo</h1>

            <div class="mt-6 space-y-3 rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-800">Nombre</span>
                    <span class="font-semibold text-black">{{ $nombre }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-800">Apellido Paterno</span>
                    <span class="font-semibold text-black">{{ $apellido_p }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-800">Apellido Materno</span>
                    <span class="font-semibold text-black">{{ $apellido_m }}</span>
                </div>
            </div>
        </section>

        {{-- Opciones --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <header class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-slate-900">Opciones</h2>
            </header>

            <div class="mt-4 space-y-3">
                <a href="{{ route('mobile.'.$role.'.cartera_activa') }}"
                   class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-blue-200 hover:shadow-md">
                    <p class="text-sm font-semibold text-slate-900">Cartera Activa</p>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-slate-900">{{ $formatMoney($cartera_activa) }}</span>
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white transition group-hover:bg-blue-700">D</span>
                    </div>
                </a>

                <a href="{{ route('mobile.'.$role.'.cartera_falla') }}"
                   class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-blue-200 hover:shadow-md">
                    <p class="text-sm font-semibold text-slate-900">Falla Actual</p>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-slate-900">{{ $formatMoney($cartera_falla) }}</span>
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white transition group-hover:bg-blue-700">D</span>
                    </div>
                </a>

                <a href="{{ route('mobile.'.$role.'.cartera_vencida') }}"
                   class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-blue-200 hover:shadow-md">
                    <p class="text-sm font-semibold text-slate-900">Cartera Vencida</p>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-slate-900">{{ $formatMoney($cartera_vencida) }}</span>
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white transition group-hover:bg-blue-700">D</span>
                    </div>
                </a>

                <a href="{{ route('mobile.'.$role.'.cartera_inactiva') }}"
                   class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-blue-200 hover:shadow-md">
                    <p class="text-sm font-semibold text-slate-900">Cartera Inactiva</p>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-slate-900">{{ $formatPercentage($cartera_inactivaP) }}</span>
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white transition group-hover:bg-blue-700">D</span>
                    </div>
                </a>
            </div>
        </section>

        {{-- Supervisores --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <h2 class="text-base font-semibold text-slate-900">Supervisores</h2>

            <div class="mt-4 space-y-3">
                @forelse($supervisores as $s)
                    <a href="{{ route('mobile.supervisor.cartera', ['supervisor' => $s->id ?? ($s['id'] ?? null)]) }}"
                       class="block rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-[11px] font-bold text-indigo-700">
                                    {{ $loop->iteration }}
                                </span>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ $s->nombre ?? ($s['nombre'] ?? 'Sin nombre') }}
                                    {{ $s->apellido_p ?? ($s['apellido_p'] ?? '') }}
                                    {{ $s->apellido_m ?? ($s['apellido_m'] ?? '') }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 rounded-2xl bg-slate-50 p-3">
                            <div class="flex items-center justify-between text-xs text-slate-600">
                                <span>Progreso de supervisor</span>
                                <span class="font-semibold text-slate-900">{{ $s->promotores_cumplidos ?? 0 }} / {{ $s->total_promotores ?? 0 }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                                <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $s->porcentaje_cumplimiento ?? 0 }}%;"></div>
                            </div>
                            <p class="text-xs text-slate-500">{{ number_format($s->porcentaje_cumplimiento ?? 0, 0) }}% de meta completada.</p>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No hay supervisores registrados.</p>
                @endforelse
            </div>
        </section>

        {{-- Acciones --}}
        <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <a href="{{ route('mobile.index') }}"
               class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200">
                Regresar
            </a>
            <a href="{{ url()->current() }}"
               class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200">
                Actualizar
            </a>
            <a href="{{ url()->current() }}"
               class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                Reporte
            </a>
        </section>
    </div>
</x-layouts.mobile.mobile-layout>
