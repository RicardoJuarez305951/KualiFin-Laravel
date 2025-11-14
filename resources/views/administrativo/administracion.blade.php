<x-layouts.authenticated title="Centro de Administración">
    @php
        $administracionViews = $administracionViews ?? [];
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-7xl space-y-10">
            <div class="text-center space-y-2">
                <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Administración</p>
                <h1 class="text-4xl font-bold text-gray-900">Centro de Administración</h1>
                <p class="text-lg text-gray-600">
                    Selecciona la vista operativa requerida para desembolsos, cierres y seguimiento semanal.
                </p>
            </div>

            <div class="bg-white rounded-lg border shadow-sm">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($administracionViews as $view)
                            <x-admin.action-card
                                :title="sprintf('%s. %s', $view['numero'], $view['title'])"
                                :description="$view['description']"
                                :href="$view['route'] ?? null"
                                button-label="Ir a la vista"
                                :gradient="$view['gradient'] ?? 'from-gray-50 to-gray-100'"
                                :border-color="$view['borderColor'] ?? 'border-gray-200'"
                                :icon-bg="$view['iconBg'] ?? 'bg-gray-400'"
                                :button-color="$view['buttonColor'] ?? 'bg-blue-600 hover:bg-blue-700'"
                            >
                                <x-slot:icon>
                                    <span class="text-white font-semibold text-sm">{{ $view['numero'] }}</span>
                                </x-slot:icon>
                            </x-admin.action-card>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
