<x-layouts.authenticated>
    @php
        $actionCards = [
            [
                'title' => 'Agregar Empleado',
                'description' => 'Registra un nuevo miembro del equipo con toda su información personal y laboral.',
                'href' => route('admin.empleados.create'),
                'buttonLabel' => 'Nuevo empleado',
                'gradient' => 'from-blue-50 to-blue-100',
                'borderColor' => 'border-blue-200',
                'iconBg' => 'bg-blue-500',
                'buttonColor' => 'bg-blue-600 hover:bg-blue-700',
                'iconText' => 'HR',
            ],
            [
                'title' => 'Entradas y Salidas',
                'description' => 'Consulta el flujo reciente de recursos y movimientos operativos clave.',
                'href' => route('administrativo.entradas_salidas'),
                'buttonLabel' => 'Ver registro',
                'gradient' => 'from-emerald-50 to-emerald-100',
                'borderColor' => 'border-emerald-200',
                'iconBg' => 'bg-emerald-500',
                'buttonColor' => 'bg-emerald-600 hover:bg-emerald-700',
                'iconText' => 'ES',
            ],
            [
                'title' => 'Posibles Aperturas',
                'description' => 'Visualiza el pipeline de expansión y las sucursales en evaluación.',
                'href' => route('administrativo.probables_aperturas'),
                'buttonLabel' => 'Ver pipeline',
                'gradient' => 'from-amber-50 to-amber-100',
                'borderColor' => 'border-amber-200',
                'iconBg' => 'bg-amber-500',
                'buttonColor' => 'bg-amber-500 hover:bg-amber-600',
                'iconText' => 'PA',
            ],
            [
                'title' => 'Reportes',
                'description' => 'Próximamente disponible',
                'buttonLabel' => 'Próximamente',
                'gradient' => 'from-gray-50 to-gray-100',
                'borderColor' => 'border-gray-200',
                'iconBg' => 'bg-gray-400',
                'iconText' => 'RP',
                'disabled' => true,
            ],
            [
                'title' => 'Departamentos',
                'description' => 'Próximamente disponible',
                'buttonLabel' => 'Próximamente',
                'gradient' => 'from-gray-50 to-gray-100',
                'borderColor' => 'border-gray-200',
                'iconBg' => 'bg-gray-400',
                'iconText' => 'DP',
                'disabled' => true,
            ],
        ];
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-7xl mx-auto space-y-8">
            <div class="text-center space-y-2">
                <h1 class="text-4xl font-bold text-gray-900">
                    Panel de Administración
                </h1>
                <p class="text-lg text-gray-600">
                    Gestión completa de empleados y recursos humanos
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
