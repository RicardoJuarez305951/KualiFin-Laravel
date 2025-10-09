{{-- resources/views/mobile/supervisor/venta/recibo_desembolso.blade.php --}}
@php
    $clienteRows = $clienteRows ?? [];
    $totalPrestamoSolicitado = $totalPrestamoSolicitado ?? collect($clienteRows)->sum(fn ($row) => $row['prestamo_solicitado'] ?? 0);
    $comisionPromotor = isset($comisionPromotor) ? (float) $comisionPromotor : 0.0;
    $comisionSupervisor = isset($comisionSupervisor) ? (float) $comisionSupervisor : 0.0;
    $carteraActual = isset($carteraActual) ? (float) $carteraActual : 0.0;
    $inversion = $comisionPromotor + $comisionSupervisor + $totalPrestamoSolicitado - $carteraActual;
    $supervisorQuery = $supervisorContextQuery ?? [];
    $reciboPdfRoute = '#';

    if (isset($promotor) && $promotor?->id && \Illuminate\Support\Facades\Route::has('mobile.supervisor.venta.recibo_desembolso.pdf')) {
        $reciboPdfRoute = route('mobile.supervisor.venta.recibo_desembolso.pdf', array_merge($supervisorQuery, [
            'promotor' => $promotor->id,
        ]));
    }
@endphp

<x-layouts.mobile.mobile-layout title="Formato Recibo Desembolso">
    <style>
        @media print {
            .recibo-print-hidden {
                display: none !important;
            }
        }
    </style>
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Formato Recibo Desembolso</h1>
                <p class="text-sm text-gray-500">
                    Promotor:
                    <span class="font-medium text-gray-700">
                        {{ $promotorNombre !== '' ? $promotorNombre : 'Sin promotor' }}
                    </span>
                </p>
                <p class="text-sm text-gray-500">
                    Supervisor:
                    <span class="font-medium text-gray-700">
                        {{ $supervisorNombre !== '' ? $supervisorNombre : 'Sin supervisor' }}
                    </span>
                </p>
                <p class="text-sm text-gray-500">
                    Ejecutivo:
                    <span class="font-medium text-gray-700">
                        {{ $ejecutivoNombre !== '' ? $ejecutivoNombre : 'Sin ejecutivo' }}
                    </span>
                </p>
            </div>
            <div class="flex flex-col sm:items-end gap-2">
                <div class="text-sm text-gray-500 flex items-center gap-2">
                    <span>Fecha:</span>
                    <input
                        type="text"
                        name="fecha"
                        value="{{ $fechaHoy }}"
                        readonly
                        class="w-32 rounded-lg border border-gray-300 px-3 py-1 text-sm font-semibold text-gray-700 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    >
                </div>
                @if($reciboPdfRoute !== '#')
                    <a
                        href="{{ $reciboPdfRoute }}"
                        target="_blank"
                        rel="noopener"
                        class="recibo-print-hidden inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-200"
                    >
                        Exportar PDF
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-gray-700">Detalle de ultimos creditos</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Cliente</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Prestamo credito anterior</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Prestamo solicitado</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">-5% comision</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Total prestamo</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Recredito nuevo</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Total recredito</th>
                            <th class="px-3 py-2 text-right font-semibold whitespace-nowrap">Total prestamo - recredito</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($clienteRows as $row)
                            <tr class="text-gray-700">
                                <td class="px-3 py-2">
                                    {{ $row['nombre'] !== '' ? $row['nombre'] : 'Sin nombre' }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['prestamo_anterior']) }}
                                </td>
                                <td class="px-3 py-2 text-right font-medium">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['prestamo_solicitado']) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['comision_cinco']) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['total_prestamo']) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['recredito_nuevo']) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['total_recredito']) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['saldo_post_recredito']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-4 text-center text-sm text-gray-500">
                                    Sin clientes registrados.
                                </td>
                            </tr>
                        @endforelse
                        @if(!empty($clienteRows))
                            <tr class="bg-gray-50 text-gray-700 font-semibold">
                                <td class="px-3 py-2 text-right uppercase">Total</td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['prestamo_anterior'] ?? 0) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['prestamo_solicitado'] ?? 0) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['comision_cinco'] ?? 0) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['total_prestamo'] ?? 0) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['recredito_nuevo'] ?? 0) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['total_recredito'] ?? 0) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['saldo_post_recredito'] ?? 0) }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-700">Validaciones y firmas</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-600">Nombre de promotora de reconocimiento de clientes</label>
                    <input
                        type="text"
                        name="promotora_reconocimiento"
                        value="{{ $promotorNombre }}"
                        readonly
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                        placeholder="Nombre completo"
                    >
                    <div class="h-20 rounded-xl border border-dashed border-gray-300 flex items-end justify-center pb-2 text-xs text-gray-400">
                        Firma
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-600">Nombre de ejecutivo - Validar</label>
                    <input
                        type="text"
                        name="ejecutivo_validacion"
                        value="{{ $ejecutivoNombre }}"
                        readonly
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                        placeholder="Nombre completo"
                    >
                    <div class="h-20 rounded-xl border border-dashed border-gray-300 flex items-end justify-center pb-2 text-xs text-gray-400">
                        Firma
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-700">Resumen financiero</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">Comision de promotor</th>
                            <td class="px-3 py-2 text-right">
                                <input
                                    type="number"
                                    step="0.01"
                                    name="comision_promotor"
                                    value="{{ number_format($comisionPromotor, 2, '.', '') }}"
                                    readonly
                                    class="w-full max-w-[160px] rounded-lg border border-gray-300 px-2 py-1 text-right focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    placeholder="0.00"
                                >
                            </td>
                        </tr>
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">Comision de supervisor</th>
                            <td class="px-3 py-2 text-right">
                                <input
                                    type="number"
                                    step="0.01"
                                    name="comision_supervisor"
                                    value="{{ number_format($comisionSupervisor, 2, '.', '') }}"
                                    readonly
                                    class="w-full max-w-[160px] rounded-lg border border-gray-300 px-2 py-1 text-right focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    placeholder="0.00"
                                >
                            </td>
                        </tr>
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">Cartera actual del promotor</th>
                            <td class="px-3 py-2 text-right">
                                <input
                                    type="number"
                                    step="0.01"
                                    name="cartera_actual"
                                    value="{{ number_format($carteraActual, 2, '.', '') }}"
                                    readonly
                                    class="w-full max-w-[160px] rounded-lg border border-gray-300 px-2 py-1 text-right focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    placeholder="0.00"
                                >
                            </td>
                        </tr>
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Inversion</th>
                            <td class="px-3 py-2 text-right text-lg font-semibold {{ $inversion >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ \App\Support\ReciboDesembolsoFormatter::currency($inversion) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500">La inversion se calcula como comisiones + total de ultimos creditos - cartera actual.</p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @foreach (['Promotor' => $promotorNombre, 'Supervisor' => $supervisorNombre] as $tipo => $nombre)
                <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 text-center uppercase">Recibo de dinero</h3>
                        <p class="text-sm text-gray-500 text-center">
                            RECIBI DE: {{ $reciboDeNombre !== '' ? strtoupper($reciboDeNombre) : '---' }}
                        </p>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between">
                            <span class="font-medium">Fecha:</span>
                            <span>{{ $fechaHoy }}</span>
                        </div>
                        <div class="space-y-1">
                            <label class="block font-medium">
                                Nombre completo de {{ strtolower($tipo) }}
                            </label>
                            <input
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                placeholder="Nombre del {{ strtolower($tipo) }}"
                                value="{{ $nombre }}"
                                readonly
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Monto recibido:</span>
                            <span class="font-semibold">{{ \App\Support\ReciboDesembolsoFormatter::currency($totalPrestamoSolicitado) }}</span>
                        </div>
                    </div>
                    <div class="h-20 rounded-xl border border-dashed border-gray-300 flex items-end justify-center pb-2 text-xs text-gray-400">
                        Firma
                    </div>
                    <p class="text-center text-xs sm:text-sm font-bold uppercase underline tracking-wide">POR CONCEPTO DE: OPERACION FINANCIERA PARA PRESTAMOS INDIVIDUAL DE LAS PERSONAS MENCIONADAS EN ESTE DESEMBOLSO.</p>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.mobile.mobile-layout>
