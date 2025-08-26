@php
    $faker = \Faker\Factory::create('es_MX');
    $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $supervisora   = $faker->name();
    $ejecutivo     = $faker->name();
    $montoSemanal  = $faker->randomFloat(2, 10000, 50000);
    $ventas        = collect(range(1, 6))->map(fn() => [
        'name'   => $faker->name(),
        'amount' => $faker->randomFloat(2, 1000, 10000),
    ])->toArray();
    $total = array_sum(array_column($ventas, 'amount'));

    function formatCurrency($value) {
        return '$' . number_format($value, 2, '.', ',');
    }
@endphp

<x-layouts.mobile.mobile-layout title="Mi Venta">
    <div class="max-w-md mx-auto space-y-6">

        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-md p-6">
            <div class="text-center space-y-2">
                <h1 class="text-xl font-bold text-gray-900 uppercase">
                    Tu venta para el día
                </h1>
                <p class="text-lg font-semibold text-blue-700">
                    {{ $fecha }}
                </p>
            </div>
        </div>

        <!-- Team Info Card -->
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <!-- Reemplazo emoji por icono SVG (equipo) -->
                <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img">
                    <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a4 4 0 00-5-3.874M9 20H4v-2a4 4 0 015-3.874M12 12a4 4 0 100-8 4 4 0 000 8z"/>
                </svg>
                Equipo de Trabajo
            </h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Promotor:</span>
                    <span class="font-semibold text-gray-900">{{ auth()->user()->name }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Supervisora:</span>
                    <span class="font-semibold text-gray-900">{{ $supervisora }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Ejecutivo:</span>
                    <span class="font-semibold text-gray-900">{{ $ejecutivo }}</span>
                </div>
            </div>
        </div>

        <!-- Weekly Amount Card -->
        <div class="bg-gradient-to-r from-blue-700 to-blue-800 rounded-2xl shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-200 text-sm uppercase tracking-wide">Debe semanal</p>
                    <p class="text-2xl font-bold">{{ formatCurrency($montoSemanal) }}</p>
                </div>
                <div class="text-4xl opacity-90" aria-hidden="true">
                    <!-- Icono gráfico (bar chart) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12h3v6H3v-6zm6-6h3v12h-3V6zm6 3h3v9h-3V9z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Sales List Card -->
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <!-- Icono documento -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-700" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h5l5 5v9a2 2 0 01-2 2z"/>
                </svg>
                Lista de Ventas
            </h2>
            <div class="divide-y divide-gray-200">
                @foreach ($ventas as $venta)
                    <div class="flex justify-between items-center py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-700 rounded-full flex items-center justify-center text-white font-semibold text-sm select-none">
                                {{ strtoupper(substr($venta['name'], 0, 2)) }}
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $venta['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-bold text-gray-900">{{ formatCurrency($venta['amount']) }}</span>

                            <!-- Botón eliminar -->
                            <button type="button"
                                class="focus:outline-none"
                                aria-label="Eliminar venta">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-6 h-6 text-red-500 hover:text-red-700"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7" />
                                    <path d="M10 11v6M14 11v6" />
                                    <path d="M4 7h16" />
                                    <path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


        <!-- Total Card -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-200 text-sm uppercase tracking-wide">Cantidad total del préstamo</p>
                    <p class="text-2xl font-bold">{{ formatCurrency($total) }}</p>
                </div>
                <div class="text-4xl opacity-90" aria-hidden="true">
                    <!-- Icono dinero -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4-1.343 4-3-1.79-3-4-3z"/>
                        <path d="M12 3v2m0 14v2m8-8h-2M6 12H4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <a href="{{ route("mobile.$role.ingresar_cliente") }}"
                class="flex items-center justify-center gap-2 w-full bg-blue-800 hover:bg-blue-900 text-white 
                        font-semibold py-4 rounded-xl text-center transition ring-1 ring-blue-900/20 shadow-md focus:outline-none focus:ring-2 focus:ring-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
                    <path d="M12 4a4 4 0 100 8 4 4 0 000-8zm0 10c-4 0-8 2-8 6v2h16v-2c0-4-4-6-8-6z"/>
                </svg>
                <span>Ingresar Cliente</span>
            </a>

            <a href="{{ route("mobile.$role.solicitar_venta") }}"
                class="flex items-center justify-center gap-2 w-full bg-blue-800 hover:bg-blue-900 text-white
                        font-semibold py-4 rounded-xl text-center transition ring-1 ring-blue-900/20 shadow-md focus:outline-none focus:ring-2 focus:ring-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
                    <path d="M3 7h18M6 7v6a6 6 0 0012 0V7"/>
                    <path d="M9 10h6"/>
                </svg>
                <span>Ingresar Venta</span>
            </a>

            <a href="{{ route("mobile.$role.index") }}"
                class="flex items-center justify-center gap-2 w-full border-2 border-blue-800 text-blue-800
                        font-medium py-4 rounded-xl text-center hover:bg-blue-50 transition ring-1 ring-blue-900/20 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" role="img">
                    <path d="M3 12l6 6 12-12"/>
                </svg>
                <span>Regresar</span>
            </a>
        </div>

    </div>
</x-layouts.mobile.mobile-layout>