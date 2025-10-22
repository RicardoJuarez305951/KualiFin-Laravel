<x-layouts.authenticated title="Asignaciones y jerarquias">
    @php
        $kpis = [
            [
                'label' => 'Promotores activos',
                'value' => 48,
                'delta' => '+6 esta semana',
            ],
            [
                'label' => 'Supervisores',
                'value' => 12,
                'delta' => 'Cobertura 95%',
            ],
            [
                'label' => 'Ejecutivos comerciales',
                'value' => 6,
                'delta' => '1 vacante abierta',
            ],
            [
                'label' => 'Zonas con riesgo',
                'value' => 3,
                'delta' => 'Reasignacion sugerida',
            ],
        ];

        $zones = [
            [
                'name' => 'Zona Centro CDMX',
                'supervisor' => 'Claudia Trevino',
                'promoters' => [
                    'Sergio Ortega',
                    'Ana Beltran',
                    'Luis Castaneda',
                    'Maria Villalobos',
                ],
                'coverage' => '100%',
                'pending' => 'Sin pendientes',
            ],
            [
                'name' => 'Zona Toluca',
                'supervisor' => 'Jorge Ramirez',
                'promoters' => [
                    'Josefina Diaz',
                    'Carlos Mendoza',
                ],
                'coverage' => '74%',
                'pending' => 'Reclutar 1 promotor',
            ],
            [
                'name' => 'Zona Puebla',
                'supervisor' => 'Erika Flores',
                'promoters' => [
                    'Marcos Rivera',
                    'Gabriela Sanchez',
                    'Rosa Mejia',
                ],
                'coverage' => '88%',
                'pending' => 'Capacitar nuevo ingreso',
            ],
        ];

        $alerts = [
            [
                'type' => 'warning',
                'title' => 'Sobrecarga en Toluca',
                'message' => 'Carlos Mendoza atiende 140% de la meta. Evalua mover prospectos a la nueva vacante.',
            ],
            [
                'type' => 'info',
                'title' => 'Rotacion reciente',
                'message' => '2 promotores cambiaron de zona entre el 15 y 18 ene. Actualiza listas de seguimiento.',
            ],
        ];
    @endphp

    <div class="mx-auto max-w-7xl py-10 space-y-10">
        <header class="space-y-3">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Asignaciones y jerarquias</h1>
                    <p class="text-gray-600">Visualiza como estan distribuidas las zonas, reorganiza equipos y detecta capacidades libres.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                        Descargar resumen
                    </button>
                    <button class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Crear reasignacion
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach ($kpis as $kpi)
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ $kpi['label'] }}</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ $kpi['value'] }}</p>
                        <p class="mt-2 text-xs font-medium text-blue-600">{{ $kpi['delta'] }}</p>
                    </div>
                @endforeach
            </div>
        </header>

        <section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            <div class="xl:col-span-8 space-y-6">
                @foreach ($zones as $zone)
                    <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">{{ $zone['name'] }}</h2>
                                <p class="text-sm text-gray-600">Supervisor: <span class="font-medium text-gray-900">{{ $zone['supervisor'] }}</span></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    Cobertura {{ $zone['coverage'] }}
                                </span>
                                <span class="inline-flex items-center rounded-full border border-yellow-200 bg-yellow-50 px-3 py-1 text-xs font-semibold text-yellow-700">
                                    {{ $zone['pending'] }}
                                </span>
                                <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">Editar</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($zone['promoters'] as $promoter)
                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                                    <p class="text-sm font-semibold text-gray-900">{{ $promoter }}</p>
                                    <p class="text-xs text-gray-500">Meta semestral: ${{ number_format(rand(320000, 420000), 0, ',', '.') }}</p>
                                    <div class="mt-2 h-2 rounded-full bg-gray-200">
                                        @php
                                            $progress = rand(45, 95);
                                        @endphp
                                        <div class="h-full rounded-full bg-blue-500" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">{{ $progress }}% avance</p>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="xl:col-span-4 space-y-6">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Alertas de asignacion</h2>
                            <p class="text-sm text-gray-600">Prioriza ajustes que mejoran cobertura y carga de trabajo.</p>
                        </div>
                        <button class="text-xs font-semibold text-blue-600 hover:text-blue-700">Ver historial</button>
                    </div>
                    <div class="space-y-4">
                        @foreach ($alerts as $alert)
                            @php
                                $color = $alert['type'] === 'warning'
                                    ? 'border-yellow-200 bg-yellow-50 text-yellow-700'
                                    : 'border-blue-200 bg-blue-50 text-blue-700';
                            @endphp
                            <div class="rounded-lg border {{ $color }} px-4 py-3">
                                <p class="text-sm font-semibold">{{ $alert['title'] }}</p>
                                <p class="mt-1 text-xs">{{ $alert['message'] }}</p>
                                <button class="mt-2 text-xs font-semibold text-blue-600 hover:text-blue-700">Aceptar sugerencia</button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Proximos movimientos</h2>
                        <button class="text-xs font-semibold text-blue-600 hover:text-blue-700">Administrar</button>
                    </div>
                    <ul class="space-y-4 text-sm text-gray-700">
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-blue-500"></span>
                            <div>
                                <p class="font-semibold text-gray-900">Ingreso de promotor junior</p>
                                <p class="text-xs text-gray-500">Asignar a Zona Toluca - 22 ene</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500"></span>
                            <div>
                                <p class="font-semibold text-gray-900">Rotacion de cartera Legacy</p>
                                <p class="text-xs text-gray-500">Mover 35 cuentas a Maria Villalobos - 24 ene</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-orange-500"></span>
                            <div>
                                <p class="font-semibold text-gray-900">Capacitacion de supervisores</p>
                                <p class="text-xs text-gray-500">Sesion virtual con Juridico - 26 ene</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </aside>
        </section>
    </div>
</x-layouts.authenticated>
