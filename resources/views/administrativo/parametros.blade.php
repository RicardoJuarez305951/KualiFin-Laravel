<x-layouts.authenticated title="Parametros del sistema">
    @php
        $modules = [
            [
                'name' => 'Horarios operativos',
                'description' => 'Define ventanas de atencion presencial y telefonica.',
                'status' => 'Publicado',
                'updated_at' => '2025-01-18 09:20',
            ],
            [
                'name' => 'Topes de credito',
                'description' => 'Montos maximos por ciclo y segmento.',
                'status' => 'En revision',
                'updated_at' => '2025-01-17 16:45',
            ],
            [
                'name' => 'Alertas automaticas',
                'description' => 'Notificaciones de mora y desembolso.',
                'status' => 'Publicado',
                'updated_at' => '2025-01-15 11:10',
            ],
        ];

        $schedules = [
            ['day' => 'Lunes - Viernes', 'window' => '08:30 - 18:00', 'channel' => 'Atencion sucursal'],
            ['day' => 'Sabado', 'window' => '09:00 - 14:00', 'channel' => 'Atencion telefonica'],
            ['day' => 'Festivos', 'window' => 'Cerrado', 'channel' => 'Todos los canales'],
        ];

        $pendingApprovals = [
            [
                'id' => 'CFG-204',
                'change' => 'Actualizar tope microcredito a $65,000',
                'owner' => 'Ana Lopez',
                'impact' => 'Medio',
                'status' => 'Pendiente firma',
            ],
            [
                'id' => 'CFG-205',
                'change' => 'Activar alertas de vencimiento 48h',
                'owner' => 'Rogelio Nunez',
                'impact' => 'Alto',
                'status' => 'Requiere QA',
            ],
            [
                'id' => 'CFG-206',
                'change' => 'Nueva politica de horario extendido',
                'owner' => 'Maria Campos',
                'impact' => 'Bajo',
                'status' => 'Listo para publicar',
            ],
        ];
    @endphp

    <div class="mx-auto max-w-7xl py-10 space-y-10">
        <header class="space-y-2">
            <h1 class="text-3xl font-bold text-gray-900">Parametros del sistema</h1>
            <p class="text-gray-600">Controla configuraciones globales, topes y reglas operativas desde un unico panel.</p>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($modules as $module)
                <article class="bg-white border rounded-xl shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">{{ $module['name'] }}</h2>
                        <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ $module['status'] }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">{{ $module['description'] }}</p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Ultima actualizacion</span>
                        <span class="font-medium">{{ $module['updated_at'] }}</span>
                    </div>
                    <button class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Configurar
                    </button>
                </article>
            @endforeach
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <div class="bg-white border rounded-xl shadow-sm p-6 space-y-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Ventanas operativas</h2>
                        <p class="text-sm text-gray-600">Revisa rapidamente que canales estan activos cada dia.</p>
                    </div>
                    <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Editar</button>
                </div>

                <div class="space-y-4">
                    @foreach ($schedules as $slot)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $slot['day'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $slot['channel'] }}</p>
                                </div>
                                <span class="inline-flex items-center rounded-md bg-white px-3 py-1 text-sm font-medium text-gray-700 shadow-sm">
                                    {{ $slot['window'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white border rounded-xl shadow-sm p-6 space-y-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Cambios pendientes</h2>
                        <p class="text-sm text-gray-600">Workflow interno para publicar nuevos parametros.</p>
                    </div>
                    <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Crear cambio</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Folio</th>
                                <th class="px-4 py-3 text-left">Cambio</th>
                                <th class="px-4 py-3 text-left">Impacto</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($pendingApprovals as $item)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-900">{{ $item['id'] }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">{{ $item['change'] }}</p>
                                        <p class="text-xs text-gray-500">Responsable: {{ $item['owner'] }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            {{ $item['impact'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-blue-600">{{ $item['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-layouts.authenticated>
