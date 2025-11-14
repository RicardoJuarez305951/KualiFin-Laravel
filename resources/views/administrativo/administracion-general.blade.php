<x-layouts.authenticated title="Administracion General">
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @php
        $actionCards = [
            [
                'title' => 'Vista 1 - Desembolsos',
                'description' => 'Operaciones INV recientes.',
                'href' => route('administrativo.administracion_general.vista', ['vista' => 1]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-emerald-50 to-emerald-100',
                'borderColor' => 'border-emerald-200',
                'iconBg' => 'bg-emerald-500',
                'buttonColor' => 'bg-emerald-600 hover:bg-emerald-700',
                'iconText' => '01',
            ],
            [
                'title' => 'Vista 2 - Sistema Kualifin',
                'description' => 'Jerarquias, KPIs y simuladores.',
                'href' => route('administrativo.administracion_general.vista', ['vista' => 2]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-blue-50 to-blue-100',
                'borderColor' => 'border-blue-200',
                'iconBg' => 'bg-blue-500',
                'buttonColor' => 'bg-blue-600 hover:bg-blue-700',
                'iconText' => '02',
            ],
            [
                'title' => 'Vista 3 - Entradas y salidas',
                'description' => 'Control de flujos semanales.',
                'href' => route('administrativo.administracion_general.vista', ['vista' => 3]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-amber-50 to-amber-100',
                'borderColor' => 'border-amber-200',
                'iconBg' => 'bg-amber-500',
                'buttonColor' => 'bg-amber-500 hover:bg-amber-600',
                'iconText' => '03',
            ],
            [
                'title' => 'Vista 4 - Gastos autorizados',
                'description' => 'Conceptos recientes y responsables.',
                'href' => route('administrativo.administracion_general.vista', ['vista' => 4]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-rose-50 to-rose-100',
                'borderColor' => 'border-rose-200',
                'iconBg' => 'bg-rose-500',
                'buttonColor' => 'bg-rose-500 hover:bg-rose-600',
                'iconText' => '04',
            ],
            [
                'title' => 'Vista 5 - Proyeccion semanal',
                'description' => 'Metas de prestamos y cobranza.',
                'href' => route('administrativo.administracion_general.vista', ['vista' => 5]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-indigo-50 to-indigo-100',
                'borderColor' => 'border-indigo-200',
                'iconBg' => 'bg-indigo-500',
                'buttonColor' => 'bg-indigo-600 hover:bg-indigo-700',
                'iconText' => '05',
            ],
            [
                'title' => 'Vista 6 - Historial de fallo',
                'description' => 'Clientes con semana extra.',
                'href' => route('administrativo.administracion_general.vista', ['vista' => 6]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-gray-50 to-gray-100',
                'borderColor' => 'border-gray-200',
                'iconBg' => 'bg-gray-500',
                'buttonColor' => 'bg-gray-700 hover:bg-gray-900',
                'iconText' => '06',
            ],
            [
                'title' => 'Vista 7 - Reportes',
                'description' => 'Documentos mensuales y anuales.',
                'href' => route('administrativo.administracion_general.vista', ['vista' => 7]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-sky-50 to-sky-100',
                'borderColor' => 'border-sky-200',
                'iconBg' => 'bg-sky-500',
                'buttonColor' => 'bg-sky-600 hover:bg-sky-700',
                'iconText' => '07',
            ],
        ];
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-7xl space-y-10">
            <div class="text-center space-y-2">
                <h1 class="text-4xl font-bold text-gray-900">Administracion General</h1>
                <p class="text-lg text-gray-600">
                    Consolida indicadores de inversion, cartera, flujos de efectivo y reportes criticos.
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($actionCards as $card)
                            <x-admin.action-card
                                :title="$card['title']"
                                :description="$card['description']"
                                :href="$card['href'] ?? null"
                                :button-label="$card['buttonLabel']"
                                :gradient="$card['gradient']"
                                :border-color="$card['borderColor']"
                                :icon-bg="$card['iconBg']"
                                :button-color="$card['buttonColor'] ?? 'bg-gray-300'"
                            >
                                <x-slot:icon>
                                    <span class="text-white font-semibold text-sm">{{ $card['iconText'] }}</span>
                                </x-slot:icon>
                            </x-admin.action-card>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

</x-layouts.authenticated>
