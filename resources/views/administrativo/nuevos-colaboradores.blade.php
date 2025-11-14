<x-layouts.authenticated title="Nuevos Colaboradores">
    @php
        $actionCards = [
            [
                'title' => 'Vista 1 - Pipeline',
                'description' => 'Monitorea candidatos y etapas de incorporacion en un vistazo.',
                'href' => route('administrativo.nuevos_colaboradores.vista', ['vista' => 1]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-indigo-50 to-indigo-100',
                'borderColor' => 'border-indigo-200',
                'iconBg' => 'bg-indigo-500',
                'buttonColor' => 'bg-indigo-600 hover:bg-indigo-700',
                'iconText' => 'PL',
            ],
            [
                'title' => 'Capturar ingreso',
                'description' => 'Registra contrataciones confirmadas directo en el panel administrativo.',
                'href' => route('admin.empleados.create'),
                'buttonLabel' => 'Nuevo ingreso',
                'gradient' => 'from-blue-50 to-blue-100',
                'borderColor' => 'border-blue-200',
                'iconBg' => 'bg-blue-500',
                'buttonColor' => 'bg-blue-600 hover:bg-blue-700',
                'iconText' => 'HR',
            ],
            [
                'title' => 'Vista 2 - Agenda de induccion',
                'description' => 'Confirma asistentes y coordina a los ponentes.',
                'href' => route('administrativo.nuevos_colaboradores.vista', ['vista' => 2]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-emerald-50 to-emerald-100',
                'borderColor' => 'border-emerald-200',
                'iconBg' => 'bg-emerald-500',
                'buttonColor' => 'bg-emerald-600 hover:bg-emerald-700',
                'iconText' => 'AI',
            ],
            [
                'title' => 'Vista 3 - Vacantes prioritarias',
                'description' => 'Identifica las posiciones criticas para acelerar la contratacion.',
                'href' => route('administrativo.nuevos_colaboradores.vista', ['vista' => 3]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-amber-50 to-amber-100',
                'borderColor' => 'border-amber-200',
                'iconBg' => 'bg-amber-500',
                'buttonColor' => 'bg-amber-500 hover:bg-amber-600',
                'iconText' => 'VP',
            ],
            [
                'title' => 'Onboarding digital',
                'description' => 'Checklist automatizados y firmas electronicas (Proximamente).',
                'buttonLabel' => 'Proximamente',
                'gradient' => 'from-gray-50 to-gray-100',
                'borderColor' => 'border-gray-200',
                'iconBg' => 'bg-gray-400',
                'iconText' => 'OB',
                'disabled' => true,
            ],
        ];
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-7xl space-y-10">
            <div class="text-center space-y-2">
                <h1 class="text-4xl font-bold text-gray-900">
                    Centro de Nuevos Colaboradores
                </h1>
                <p class="text-lg text-gray-600">
                    Coordina vacantes, pipeline y agendas de induccion desde un unico tablero.
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                                :disabled="$card['disabled'] ?? false"
                                :tag="$card['tag'] ?? null"
                                :tag-color="$card['tagColor'] ?? 'text-gray-500'"
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

