<x-layouts.mobile.mobile-layout title="Cartera Promotor">
    <div class="mx-auto w-full max-w-md space-y-8 px-5 py-10">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <div class="space-y-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ $promotor->nombre }} {{ $promotor->apellido_p }} {{ $promotor->apellido_m }}</h2>
                    @php $horarioPago = trim((string) ($promotor->horario_pago_resumen ?? '')); @endphp
                    @if($horarioPago !== '')
                        <p class="text-xs text-slate-500">Horario de pago: {{ $horarioPago }}</p>
                    @endif
                </div>

                <div class="space-y-3">
                    @forelse($clientes as $c)
                        <a href="{{ route('mobile.supervisor.cliente_historial', $c->id) }}"
                           class="block rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-900">{{ $c->nombre }} {{ $c->apellido_p }} {{ $c->apellido_m }}</span>
                                @if($c->credito)
                                    <span class="text-xs text-slate-500">Crédito #{{ $c->credito->id }}</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">No hay clientes registrados.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <a href="{{ route('mobile.supervisor.cartera') }}"
               class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200">
                Regresar
            </a>
            <a href="{{ url()->current() }}"
               class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200">
                Actualizar
            </a>
            <a href="{{ route('mobile.supervisor.reporte') }}"
               class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                Reporte
            </a>
        </section>
    </div>
</x-layouts.mobile.mobile-layout>
