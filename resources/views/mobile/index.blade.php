{{-- resources/views/mobile/index.blade.php --}}
@php
    $user = auth()->user();
    $userName = $user->name;
    $primaryRole = strtolower(collect($user?->getRoleNames() ?? [])->first() ?? 'promotor');
    $roleLabel = ucfirst($primaryRole);
    $dateLabel = now()->locale('es')->translatedFormat('d M Y');

    $accentColors = [
        'promotor' => ['#F59E0B', '#FEF3C7', '#EA580C'],
        'supervisor' => ['#0EA5E9', '#E0F2FE', '#0284C7'],
        'ejecutivo' => ['#6D28D9', '#EDE9FE', '#5B21B6'],
        'administrativo' => ['#2563EB', '#DBEAFE', '#1D4ED8'],
        'superadmin' => ['#7C3AED', '#EDE9FE', '#6D28D9'],
    ];

    [$accent, $accentSoft, $accentStrong] = $accentColors[$primaryRole] ?? ['#2563EB', '#E0E7FF', '#1D4ED8'];
@endphp

<x-layouts.mobile.mobile-layout title="Panel Mobile">
    <div class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8">
        {{-- Saludo --}}
        <section class="rounded-3xl bg-white shadow-lg border border-gray-200 p-6 space-y-5">
            <header class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-700">Bienvenida</p>
                <h1 class="text-3xl font-bold text-black leading-tight">Hola {{ $userName }}</h1>
                <p class="text-sm text-slate-600">{{ $mensajeDelDia }}</p>
            </header>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-2xl border border-gray-300 bg-white p-3">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-slate-700">Tu rol</p>
                    <p class="mt-1 font-semibold text-slate-700">{{ $roleLabel }}</p>
                </div>
                <div class="rounded-2xl border border-gray-300 bg-white p-3">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-slate-700">Hoy</p>
                    <p class="mt-1 font-semibold text-slate-700">{{ $dateLabel }}</p>
                </div>
            </div>
        </section>

        {{-- Acciones principales --}}
        <section class="space-y-4">
            <header class="flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-[0.35em] text-black">Panel de acci&oacute;n</h2>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                      style="background-color: {{ $accentSoft }}; color: {{ $accentStrong }};">
                    Prioridad diaria
                </span>
            </header>

            <div class="grid grid-cols-1 gap-3">
                @unlessrole('ejecutivo|administrativo|superadmin')
                    <a href="{{ route("mobile.$role.objetivo") }}"
                       class="flex items-center justify-between rounded-2xl border border-gray-300 bg-white p-4 shadow transition hover:shadow-md">
                        <div class="flex items-start gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl"
                                  style="background-color: {{ $accentSoft }}; color: {{ $accent }};">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 6a6 6 0 1 0 0 12 6 6 0 0 0 0-12Zm0 0V2.25m0 3.75a3 3 0 1 1-3 3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 15.75 3 3" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-base font-semibold text-black">Mi objetivo semanal</p>
                                <p class="text-sm text-slate-600">Revisa tu meta y porcentaje de avance.</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                @endunlessrole

                <a href="{{ route("mobile.$role.cartera") }}"
                   class="flex items-center justify-between rounded-2xl border border-gray-300 bg-white p-4 shadow transition hover:shadow-md">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 7.5A2.25 2.25 0 0 1 4.5 5.25h15a2.25 2.25 0 0 1 2.25 2.25v9a2.25 2.25 0 0 1-2.25 2.25h-15A2.25 2.25 0 0 1 2.25 16.5v-9Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a1.5 1.5 0 1 0 0-3" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-base font-semibold text-black">Mi cartera</p>
                            <p class="text-sm text-slate-600">Clientes activos, vencidos y seguimiento.</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>

                <a href="{{ route("mobile.$role.venta") }}"
                   class="flex items-center justify-between rounded-2xl border border-gray-300 bg-white p-4 shadow transition hover:shadow-md">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 4.5h3.382a1.5 1.5 0 0 1 1.415 1.036l.172.516a1.5 1.5 0 0 0 1.415 1.036H19.5a1.5 1.5 0 0 1 1.473 1.82l-1.2 6A1.5 1.5 0 0 1 18.3 16.5H8.25a1.5 1.5 0 0 1-1.473-1.18L5.1 7.5M8.25 19.5a.75.75 0 1 0 0 1.5.75.75 0 1 0 0-1.5Zm8.25 0a.75.75 0 1 0 0 1.5.75.75 0 1 0 0-1.5Z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-base font-semibold text-black">Registrar venta</p>
                            <p class="text-sm text-slate-600">Agrega clientes y env&iacute;a tus ventas.</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            </div>
        </section>

        {{-- Acciones extendidas por rol --}}
        <section class="space-y-3">
            <header class="flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-[0.35em] text-black">Herramientas</h2>
                <span class="inline-flex items-center rounded-full bg-slate-900 text-white px-3 py-1 text-xs font-semibold shadow">
                    Rol: {{ $roleLabel }}
                </span>
            </header>

            <div class="grid grid-cols-1 gap-3">
                @role('supervisor|ejecutivo|administrativo|superadmin')
                    <a href="{{ route("mobile.supervisor.busqueda") }}"
                       class="flex items-center justify-between rounded-2xl border border-gray-300 bg-white p-4 shadow transition hover:shadow-md">
                        <div class="flex items-start gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-base font-semibold text-black">B&uacute;squedas</p>
                                <p class="text-sm text-slate-600">Consulta bases, historial y referencias.</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>

                    @role('supervisor|administrativo|superadmin')
                        <a href="{{ route("mobile.supervisor.apertura") }}"
                           class="flex items-center justify-between rounded-2xl border border-gray-300 bg-white p-4 shadow transition hover:shadow-md">
                            <div class="flex items-start gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 11V7a3 3 0 0 0-6 0v4m-3 0h12v9H6v-9z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-base font-semibold text-black">Apertura</p>
                                    <p class="text-sm text-slate-600">Autoriza excepciones y gestiona solicitudes.</p>
                                </div>
                            </div>
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                            </svg>
                        </a>
                    @endrole
                @endrole

                @role('ejecutivo|administrativo|superadmin')
                    <a href="{{ route("mobile.$role.informes") }}"
                       class="flex items-center justify-between rounded-2xl border border-gray-300 bg-white p-4 shadow transition hover:shadow-md">
                        <div class="flex items-start gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V5.25A2.25 2.25 0 0 0 18.75 3H8.318a2.25 2.25 0 0 0-1.59.659L4.41 6A2.25 2.25 0 0 0 3.75 7.59v11.16A2.25 2.25 0 0 0 5.25 21Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 3.75h6" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-base font-semibold text-black">Informes</p>
                                <p class="text-sm text-slate-600">Descarga reportes y tableros operativos.</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                @endrole

                <a href="{{ route('mobile.misdesembolsos') }}"
                   class="flex items-center justify-between rounded-2xl border border-gray-300 bg-white p-4 shadow transition hover:shadow-md">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4.5 4.5h15a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75h-15a.75.75 0 0 1-.75-.75V5.25a.75.75 0 0 1 .75-.75Zm3 4.5h7.5M7.5 12h7.5m-7.5 3h4.5" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-base font-semibold text-black">Mis desembolsos</p>
                            <p class="text-sm text-slate-600">Consulta detalle de pr&eacute;stamos liberados.</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="rounded-2xl border border-gray-300 bg-white p-4 text-center shadow">
            <form method="POST" action="{{ route('logout') }}" class="space-y-3">
                @csrf
                <p class="text-xs uppercase tracking-[0.3em] text-slate-700">Sesión activa</p>
                <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-9A2.25 2.25 0 0 0 2.25 5.25v13.5A2.25 2.25 0 0 0 4.5 21h9a2.25 2.25 0 0 0 2.25-2.25V15M18 9l3 3-3 3M21 12H9" />
                    </svg>
                    Cerrar sesi&oacute;n
                </button>
            </form>
        </footer>
    </div>
</x-layouts.mobile.mobile-layout>








