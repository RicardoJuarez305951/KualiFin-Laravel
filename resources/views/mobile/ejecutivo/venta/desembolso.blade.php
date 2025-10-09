<x-layouts.mobile.mobile-layout>
    <div class="p-4 overflow-x-auto bg-white rounded-lg shadow-md">
        <div class="flex items-center mb-4">
            <a href="{{ route('mobile.index') }}" class="text-blue-600 p-2 rounded-full hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-gray-800 text-center flex-grow">Entrega de Valores</h1>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <p class="text-center text-gray-500 mb-6">Fecha del Reporte: {{ now()->format('d/m/Y') }}</p>

        @if ($creditosParaDesembolso->isNotEmpty())
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Crédito</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Cliente y Promotora</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Monto</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($creditosParaDesembolso as $credito)
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $credito->id }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800">
                                    <div class="font-semibold">{{ $credito->cliente->nombre_completo }}</div>
                                    <div class="text-xs text-gray-500">Tel: {{ optional($credito->datoContacto)->tel_cel ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">Promotora: {{ optional($credito->cliente->promotor)->nombre ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-green-600 font-bold">$ {{ number_format($credito->monto_total, 2) }}</td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <form action="{{ route('mobile.ejecutivo.desembolso.update', $credito) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded-full hover:bg-green-700">
                                            Entregado
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center p-8 bg-white rounded-lg shadow-md">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay desembolsos pendientes</h3>
                <p class="mt-1 text-sm text-gray-500">No hay créditos autorizados en tu cartera por el momento.</p>
            </div>
        @endif
    </div>
</x-layouts.mobile.mobile-layout>
