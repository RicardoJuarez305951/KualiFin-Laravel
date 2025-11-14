<x-layouts.authenticated title="Autorizacion Operativa">
    @php
        $actionCards = collect($authorizations)
            ->map(function ($section) {
                return [
                    'title' => "{$section['numero']}. {$section['title']}",
                    'description' => $section['description'],
                    'href' => route('administrativo.autorizacion.vista', ['vista' => $section['numero']]),
                    'buttonLabel' => 'Ir a la vista',
                    'gradient' => 'from-indigo-50 to-indigo-100',
                    'borderColor' => 'border-indigo-200',
                    'iconBg' => 'bg-indigo-500',
                    'buttonColor' => 'bg-indigo-600 hover:bg-indigo-700',
                    'iconText' => str_pad($section['numero'], 2, '0', STR_PAD_LEFT),
                ];
            })
            ->values()
            ->all();
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-7xl mx-auto space-y-10">
            <div class="text-center space-y-2">
                <h1 class="text-4xl font-bold text-gray-900">
                    Centro de Autorizacion
                </h1>
                <p class="text-lg text-gray-600">
                    Supervisa solicitudes especiales, excedentes y alertas prioritarias desde un solo lugar.
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
