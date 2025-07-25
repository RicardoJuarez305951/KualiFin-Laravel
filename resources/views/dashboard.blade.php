<x-layouts.authenticated>
    @php
        $summaryCards = [
            [ 'title' => 'Inversión Total',   'value' => 850000, 'change' => '+12.5%', 'changeType' => 'positive' ],
            [ 'title' => 'Flujo Anterior',    'value' => 125000, 'change' => '+8.2%',  'changeType' => 'positive' ],
            [ 'title' => 'Total a Recuperar', 'value' => 975000, 'change' => '+15.7%', 'changeType' => 'positive' ],
            [ 'title' => 'Préstamo Real',     'value' => 720000, 'change' => '+5.3%',  'changeType' => 'positive' ],
            [ 'title' => 'Recréditos',        'value' => 15,     'change' => '-2.1%',  'changeType' => 'negative' ],
        ];

        $loanPortfolio = [
            [ 'cliente' => 'María Elena Rodríguez',   'monto' => 45000, 'estado' => 'Activo',       'comision' => 4500, 'vencimiento' => '2025-08-15', 'riesgo' => 'Bajo',    'avatar' => 'ME' ],
            [ 'cliente' => 'Carlos Alberto Mendoza',  'monto' => 32000, 'estado' => 'Supervisión',  'comision' => 3200, 'vencimiento' => '2025-07-22', 'riesgo' => 'Medio',   'avatar' => 'CM' ],
            [ 'cliente' => 'Ana Sofía Herrera',       'monto' => 28000, 'estado' => 'Pendiente',    'comision' => 2800, 'vencimiento' => '2025-09-10', 'riesgo' => 'Bajo',    'avatar' => 'AH' ],
            [ 'cliente' => 'Roberto Jiménez Silva',   'monto' => 55000, 'estado' => 'Desembolsado', 'comision' => 5500, 'vencimiento' => '2025-06-30', 'riesgo' => 'Alto',    'avatar' => 'RJ' ],
            [ 'cliente' => 'Lucía Fernanda Torres',   'monto' => 38000, 'estado' => 'Recrédito',    'comision' => 3800, 'vencimiento' => '2025-10-05', 'riesgo' => 'Alto',    'avatar' => 'LT' ],
        ];

        $recentTransactions = [
            [ 'cliente' => 'María Elena Rodríguez',   'tipo' => 'Desembolso',    'monto' => 45000, 'comision' => 4500, 'fecha' => '2025-01-15', 'estado' => 'Completado' ],
            [ 'cliente' => 'Carlos Alberto Mendoza',  'tipo' => 'Pago Parcial',  'monto' => 15000, 'comision' => 1500, 'fecha' => '2025-01-14', 'estado' => 'Procesando' ],
            [ 'cliente' => 'Ana Sofía Herrera',       'tipo' => 'Evaluación',    'monto' => 28000, 'comision' => 2800, 'fecha' => '2025-01-13', 'estado' => 'Pendiente' ],
            [ 'cliente' => 'Roberto Jiménez Silva',   'tipo' => 'Liquidación',   'monto' => 55000, 'comision' => 5500, 'fecha' => '2025-01-12', 'estado' => 'Completado' ],
        ];

        $financialSummary = [
            [ 'title' => 'Capital Activo', 'value' => 720000, 'subtitle' => 'En circulación', 'icon' => '📈' ],
            [ 'title' => 'Comisiones',     'value' => 18800,  'subtitle' => 'Total generadas','icon' => '💰' ],
            [ 'title' => 'Por Recuperar',  'value' => 255000, 'subtitle' => 'Pendiente',      'icon' => '🏦' ],
            [ 'title' => 'En Riesgo',      'value' => 93000,  'subtitle' => 'Recréditos',     'icon' => '⚠️' ],
        ];

        function formatCurrency($value) {
            return '$' . number_format($value, 0, ',', '.');
        }

        function getStatusBadgeColor($estado) {
            return match($estado) {
                'Activo', 'Completado'     => 'bg-green-100 text-green-800 border-green-300',
                'Supervisión', 'Procesando'=> 'bg-yellow-100 text-yellow-800 border-yellow-300',
                'Pendiente'                => 'bg-blue-100 text-blue-800 border-blue-300',
                'Desembolsado'             => 'bg-purple-100 text-purple-800 border-purple-300',
                'Recrédito'                => 'bg-red-100 text-red-800 border-red-300',
                default                    => 'bg-gray-100 text-gray-800 border-gray-300'
            };
        }

        function getRiskBadgeColor($riesgo) {
            return match($riesgo) {
                'Bajo'     => 'bg-green-100 text-green-800 border-green-300',
                'Medio'    => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                'Alto'     => 'bg-red-100 text-red-800 border-red-300',
                default    => 'bg-gray-100 text-gray-800 border-gray-300'
            };
        }
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header -->
            <div class="text-center space-y-2">
                <h1 class="text-4xl font-bold text-gray-900">
                    Dashboard Financiero
                </h1>
                <p class="text-lg text-gray-600">
                    Control total de inversiones y cartera de préstamos
                </p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach ($summaryCards as $card)
                    <div class="bg-white rounded-lg shadow-sm border p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between space-y-0 pb-2">
                            <div class="text-2xl">📊</div>
                            <div class="text-sm font-medium {{ $card['changeType'] === 'positive' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $card['change'] }}
                            </div>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-gray-600">{{ $card['title'] }}</p>
                            <p class="text-2xl font-bold">
                                {{ is_numeric($card['value']) && $card['value'] > 1000 ? formatCurrency($card['value']) : $card['value'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Loan Portfolio -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <span class="text-2xl">👥</span>
                        Cartera de Préstamos
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($loanPortfolio as $loan)
                            <div class="bg-gray-50 rounded-lg p-4 border">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium text-sm">
                                            {{ $loan['avatar'] }}
                                        </div>
                                        <div class="space-y-1">
                                            <p class="font-medium text-sm leading-none">{{ $loan['cliente'] }}</p>
                                            <div class="flex gap-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ getStatusBadgeColor($loan['estado']) }}">
                                                    {{ $loan['estado'] }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ getRiskBadgeColor($loan['riesgo']) }}">
                                                    {{ $loan['riesgo'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-700">Monto:</span>
                                        <span class="font-semibold">{{ formatCurrency($loan['monto']) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-700">Comisión:</span>
                                        <span class="font-semibold">{{ formatCurrency($loan['comision']) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-700 flex items-center gap-1">📅 Vencimiento:</span>
                                        <span>{{ $loan['vencimiento'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <span class="text-2xl">📊</span>
                        Resumen Financiero
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($financialSummary as $item)
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xl">{{ $item['icon'] }}</span>
                                    <span class="font-medium text-sm">{{ $item['title'] }}</span>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold">{{ formatCurrency($item['value']) }}</div>
                                    <div class="text-sm text-gray-600">{{ $item['subtitle'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <span class="text-2xl">📄</span>
                        Movimientos Recientes
                    </h2>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Cliente</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Tipo</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Monto</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Comisión</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Fecha</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentTransactions as $item)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3 px-4 font-medium">{{ $item['cliente'] }}</td>
                                        <td class="py-3 px-4">{{ $item['tipo'] }}</td>
                                        <td class="py-3 px-4 font-semibold">{{ formatCurrency($item['monto']) }}</td>
                                        <td class="py-3 px-4">{{ formatCurrency($item['comision']) }}</td>
                                        <td class="py-3 px-4">{{ $item['fecha'] }}</td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ getStatusBadgeColor($item['estado']) }}">
                                                {{ $item['estado'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.authenticated>
