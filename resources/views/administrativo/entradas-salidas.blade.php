<x-layouts.authenticated title="Entradas y Salidas">
    <div class="mx-auto max-w-7xl py-10 space-y-8">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Control financiero</p>
                <h1 class="text-3xl font-semibold text-gray-900">Entradas y Salidas</h1>
                <p class="text-sm text-gray-600">
                    Revisa los movimientos por periodo y registra nuevos eventos de forma manual.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Ultima actualización</span>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">24 ene 2025</span>
            </div>
        </header>

        @php
            $seleccionado = collect($periodos)->firstWhere('key', $periodoActivo) ?? ($periodos[0] ?? null);
            $movimientos = $seleccionado['movimientos'] ?? [];
        @endphp

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Resumen por periodo</h2>
                    <p class="text-sm text-gray-500">
                        Cambia el periodo para ver la tabla con los movimientos correspondientes.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ($periodos as $periodo)
                        <a
                            href="{{ route('administrativo.entradas_salidas', ['periodo' => $periodo['key']]) }}"
                            class="rounded-lg border px-3 py-2 text-xs font-semibold transition-colors {{ $periodoActivo === $periodo['key'] ? 'border-blue-600 bg-blue-600 text-white shadow' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50' }}"
                        >
                            {{ $periodo['label'] }}
                        </a>
                    @endforeach
                </div>
            </header>

            <div class="space-y-6 px-6 py-6">
                <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                    <p class="font-semibold text-gray-900">{{ $seleccionado['label'] ?? '' }}</p>
                    <p>{{ $seleccionado['descripcion'] ?? 'Sin descripción disponible.' }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Tipo</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Concepto</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Notas</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500 text-right">Costo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($movimientos as $movimiento)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-md px-2 py-1 text-xs font-semibold uppercase {{ $movimiento['tipo'] === 'Entrada' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}"
                                        >
                                            {{ $movimiento['tipo'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $movimiento['concepto'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $movimiento['notas'] }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ $movimiento['costo'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">
                                        No hay movimientos registrados para este periodo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Registrar nuevo movimiento</h2>
                    <p class="text-sm text-gray-500">Completa la información</p>
                </div>
                <span class="text-xs uppercase tracking-wide text-gray-500">Captura manual</span>
            </header>

            <form class="space-y-5 px-6 py-6" action="#" method="POST" enctype="multipart/form-data">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="flex flex-col gap-2 text-sm font-semibold text-gray-700">
                        Tipo
                        <select class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <option value="">Seleccionar</option>
                            <option value="Entrada">Entrada</option>
                            <option value="Salida">Salida</option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-2 text-sm font-semibold text-gray-700">
                        Concepto
                        <input type="text" placeholder="Ej. Cobranza semanal" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </label>

                    <label class="flex flex-col gap-2 text-sm font-semibold text-gray-700 md:col-span-2">
                        Notas
                        <textarea rows="3" placeholder="Describe el movimiento, responsable o referencia interna" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"></textarea>
                    </label>

                    <label class="flex flex-col gap-2 text-sm font-semibold text-gray-700">
                        Costo
                        <input type="number" step="0.01" min="0" placeholder="Ej. 15000.00" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </label>

                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="reset" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Limpiar
                    </button>
                    <button type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Guardar movimiento
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-layouts.authenticated>
