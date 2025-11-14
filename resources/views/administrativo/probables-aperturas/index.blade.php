<x-layouts.authenticated title="Posibles Aperturas">
    @php
        $latestPromotorId = $promotores[0]['id'] ?? null;
        $latestDetailUrl = $latestPromotorId ? route('administrativo.probables_aperturas.show', $latestPromotorId) : null;

        $actionCards = [
            [
                'title' => 'Vista 1 - Lista completa',
                'description' => 'Consulta el pipeline y responsables de cada apertura.',
                'href' => route('administrativo.probables_aperturas.vista', ['vista' => 1]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-amber-50 to-amber-100',
                'borderColor' => 'border-amber-200',
                'iconBg' => 'bg-amber-500',
                'buttonColor' => 'bg-amber-500 hover:bg-amber-600',
                'iconText' => '01',
            ],
            [
                'title' => 'Vista 2 - Insights',
                'description' => 'Prioriza fases, territorios y pendientes de seguimiento.',
                'href' => route('administrativo.probables_aperturas.vista', ['vista' => 2]),
                'buttonLabel' => 'Ir a la vista',
                'gradient' => 'from-emerald-50 to-emerald-100',
                'borderColor' => 'border-emerald-200',
                'iconBg' => 'bg-emerald-500',
                'buttonColor' => 'bg-emerald-600 hover:bg-emerald-700',
                'iconText' => '02',
            ],
            [
                'title' => 'Ultimo expediente',
                'description' => 'Abre la encuesta mas reciente para completar evidencias.',
                'href' => $latestDetailUrl,
                'buttonLabel' => $latestDetailUrl ? 'Abrir expediente' : 'Sin registros',
                'gradient' => 'from-blue-50 to-blue-100',
                'borderColor' => 'border-blue-200',
                'iconBg' => 'bg-blue-500',
                'buttonColor' => 'bg-blue-600 hover:bg-blue-700',
                'iconText' => 'EX',
                'disabled' => ! $latestDetailUrl,
            ],
        ];
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-7xl space-y-10">
            <div class="text-center space-y-2">
                <h1 class="text-4xl font-bold text-gray-900">Posibles Aperturas</h1>
                <p class="text-lg text-gray-600">
                    Seguimiento operativo a promotores asignados, encuestas de territorio y responsables de apertura.
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
