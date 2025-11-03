<x-layouts.authenticated title="Autorización Operativa">
    <div class="mx-auto max-w-7xl py-10 space-y-8">
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($summary as $item)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-600">{{ $item['label'] }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $item['value'] }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-blue-600">{{ $item['badge'] }}</p>
                </div>
            @endforeach
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="lg:col-span-2 rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Solicitudes en revisión</h2>
                        <p class="text-sm text-gray-500">Prioriza las solicitudes según el riesgo y la antigüedad.</p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">En proceso</span>
                </header>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Folio</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Cliente</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Solicitud</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Monto</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Ingreso</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Responsable</th>
                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">Riesgo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($pendingRequests as $request)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $request['folio'] }}</td>
                                    <td class="px-6 py-4">{{ $request['cliente'] }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $request['solicitud'] }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $request['monto'] }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $request['ingreso'] }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                                            {{ $request['responsable'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            @class([
                                                'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                                'bg-emerald-100 text-emerald-700' => $request['riesgo'] === 'Bajo',
                                                'bg-amber-100 text-amber-700' => $request['riesgo'] === 'Medio',
                                                'bg-rose-100 text-rose-700' => $request['riesgo'] === 'Alto',
                                            ])
                                        >
                                            {{ $request['riesgo'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <header class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Aprobaciones recientes</h2>
                    <p class="text-sm text-gray-500">Bitácora de decisiones y comentarios clave.</p>
                </header>

                <ul class="space-y-4 px-6 py-5">
                    @foreach ($recentApprovals as $approval)
                        <li class="rounded-lg border border-gray-200 p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-blue-700">{{ $approval['folio'] }}</span>
                                <span class="text-xs font-medium text-gray-500">{{ $approval['fecha'] }}</span>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-gray-900">{{ $approval['accion'] }}</p>
                            <p class="mt-1 text-xs text-gray-500">Autorizó: {{ $approval['autorizo'] }}</p>
                            <p class="mt-3 text-sm text-gray-600">{{ $approval['comentarios'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
</x-layouts.authenticated>
