<x-layouts.authenticated>
    @php
        // Datos de ejemplo para el panel de administración
        $adminStats = [
            [ 'title' => 'Total Empleados',    'value' => 12,     'change' => '+2',    'changeType' => 'positive' ],
            [ 'title' => 'Activos',           'value' => 10,     'change' => '+1',    'changeType' => 'positive' ],
            [ 'title' => 'En Capacitación',   'value' => 2,      'change' => '+1',    'changeType' => 'positive' ],
            [ 'title' => 'Departamentos',     'value' => 4,      'change' => '0',     'changeType' => 'neutral' ],
            [ 'title' => 'Nuevos Este Mes',   'value' => 3,      'change' => '+3',    'changeType' => 'positive' ],
        ];

        function getChangeColor($changeType) {
            return match($changeType) {
                'positive' => 'text-green-600',
                'negative' => 'text-red-600',
                'neutral'  => 'text-gray-600',
                default    => 'text-gray-600'
            };
        }
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header -->
            <div class="text-center space-y-2">
                <h1 class="text-4xl font-bold text-gray-900">
                    Panel de Administración
                </h1>
                <p class="text-lg text-gray-600">
                    Gestión completa de empleados y recursos humanos
                </p>
            </div>

            <!-- Main Actions Section -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Agregar Nuevo Empleado -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200 hover:shadow-md transition-all duration-200 hover:scale-105">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-xl">👤</span>
                                </div>
                                <div class="text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-lg font-semibold text-gray-900">Agregar Empleado</h3>
                                <p class="text-sm text-gray-600 mb-4">
                                    Registra un nuevo miembro del equipo con toda su información personal y laboral
                                </p>
                                <a href="{{ route('admin.empleados.create') }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                                    <span class="text-lg">➕</span>
                                    Nuevo Empleado
                                </a>
                            </div>
                        </div>

                        <!-- Placeholder para futuras acciones -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 opacity-50">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-gray-400 rounded-full flex items-center justify-center">
                                    <span class="text-white text-xl">📊</span>
                                </div>
                                <div class="text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-lg font-semibold text-gray-500">Reportes</h3>
                                <p class="text-sm text-gray-400 mb-4">
                                    Próximamente disponible
                                </p>
                                <button disabled class="w-full bg-gray-300 text-gray-500 font-medium py-3 px-4 rounded-lg cursor-not-allowed">
                                    Próximamente
                                </button>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 opacity-50">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-gray-400 rounded-full flex items-center justify-center">
                                    <span class="text-white text-xl">🏢</span>
                                </div>
                                <div class="text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-lg font-semibold text-gray-500">Departamentos</h3>
                                <p class="text-sm text-gray-400 mb-4">
                                    Próximamente disponible
                                </p>
                                <button disabled class="w-full bg-gray-300 text-gray-500 font-medium py-3 px-4 rounded-lg cursor-not-allowed">
                                    Próximamente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.authenticated>