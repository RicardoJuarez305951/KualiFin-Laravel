{{-- resources/views/mobile/supervisor/venta/recibo_desembolso.blade.php --}}
@php
    $clienteRows = $clienteRows ?? [];
    $totalPrestamoSolicitado = $totalPrestamoSolicitado ?? collect($clienteRows)->sum(fn ($row) => $row['prestamo_solicitado'] ?? 0);
    $comisionPromotor = isset($comisionPromotor) ? (float) $comisionPromotor : 0.0;
    $comisionSupervisor = isset($comisionSupervisor) ? (float) $comisionSupervisor : 0.0;
    $carteraActual = isset($carteraActual) ? (float) $carteraActual : 0.0;
    $inversion = $comisionPromotor + $comisionSupervisor + $totalPrestamoSolicitado - $carteraActual;
    $cancelRouteName = $cancelRouteName ?? null;
    $puedeCancelarCreditos = $puedeCancelarCreditos ?? false;
    $supervisorQuery = $supervisorContextQuery ?? [];
    $reciboPdfRoute = '#';
    $clienteRowsCollection = collect($clienteRows);

    $ultimoCancelado = $clienteRowsCollection
        ->filter(fn ($row) => isset($row['estado']) && strtolower((string) $row['estado']) === 'cancelado' && !empty($row['cancelado_en'] ?? null))
        ->sortByDesc(function ($row) {
            try {
                return \Illuminate\Support\Carbon::parse($row['cancelado_en'])->timestamp;
            } catch (\Throwable $e) {
                return PHP_INT_MIN;
            }
        })
        ->first();

    $motivoCancelacion = $ultimoCancelado['motivo_cancelacion'] ?? '';
    $motivoCancelacionCliente = $ultimoCancelado['nombre'] ?? '';
    $motivoCancelacionFecha = $ultimoCancelado['cancelado_en'] ?? '';

    if (isset($promotor) && $promotor?->id && \Illuminate\Support\Facades\Route::has('mobile.supervisor.venta.recibo_desembolso.pdf')) {
        $reciboPdfRoute = route('mobile.supervisor.venta.recibo_desembolso.pdf', array_merge($supervisorQuery, [
            'promotor' => $promotor->id,
        ]));
    }
@endphp

<x-layouts.mobile.mobile-layout title="Formato Recibo Desembolso">
    <style>
        @media print { .recibo-print-hidden { display:none !important; } }
    </style>

    {{-- SIN tocar el layout padre --}}
    <div class="p-4 mx-auto w-full flex flex-col items-center space-y-3 text-[10px] leading-tight">

        {{-- Header --}}
        <div class="bg-white rounded-xl shadow p-3 space-y-2 w-full max-w-5xl">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="space-y-0.5">
                    <h1 class="text-sm font-bold text-gray-800 tracking-tight">Formato Recibo Desembolso</h1>
                    <p class="text-[10px] text-gray-600">Promotor:
                        <span class="font-semibold text-gray-800">{{ $promotorNombre !== '' ? $promotorNombre : 'Sin promotor' }}</span>
                    </p>
                    <p class="text-[10px] text-gray-600">Supervisor:
                        <span class="font-semibold text-gray-800">{{ $supervisorNombre !== '' ? $supervisorNombre : 'Sin supervisor' }}</span>
                    </p>
                    <p class="text-[10px] text-gray-600">Ejecutivo:
                        <span class="font-semibold text-gray-800">{{ $ejecutivoNombre !== '' ? $ejecutivoNombre : 'Sin ejecutivo' }}</span>
                    </p>
                    
                </div>

                <div class="flex flex-col sm:items-end gap-2">
                    <div class="flex items-center gap-2 text-gray-700">
                        <span class="font-semibold">Fecha:</span>
                        <input type="text" name="fecha" value="{{ $fechaHoy }}" readonly
                               class="w-32 rounded-md border border-gray-300 px-2 py-1 text-[10px] font-semibold text-gray-800 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>
                    @if($reciboPdfRoute !== '#')
                        <a href="{{ $reciboPdfRoute }}" target="_blank" rel="noopener"
                           class="recibo-print-hidden inline-flex items-center justify-center rounded-md bg-blue-600 px-3 py-1.5 text-[10px] font-bold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-200">
                           Exportar PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tabla clientes --}}
        <div class="bg-white rounded-xl shadow p-3 space-y-2 w-full max-w-5xl">
            <h2 class="text-sm font-bold text-gray-700 tracking-tight">Detalle de últimos créditos</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 text-[9.5px] leading-tight">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-2 py-1 text-left font-bold">Cliente</th>
                            <th class="px-2 py-1 text-right font-bold whitespace-nowrap">Prést. ant.</th>
                            <th class="px-2 py-1 text-right font-bold whitespace-nowrap">Prést. solic.</th>
                            <th class="px-2 py-1 text-right font-bold whitespace-nowrap">-5% com.</th>
                            <th class="px-2 py-1 text-right font-bold whitespace-nowrap">Total prést.</th>
                            <th class="px-2 py-1 text-right font-bold whitespace-nowrap">Recréd. nuevo</th>
                            <th class="px-2 py-1 text-right font-bold whitespace-nowrap">Total recréd.</th>
                            <th class="px-2 py-1 text-right font-bold whitespace-nowrap">Saldo post</th>
                            <th class="px-2 py-1 text-left font-bold whitespace-nowrap">Estado</th>
                            <th class="px-2 py-1 text-left font-bold whitespace-nowrap recibo-print-hidden">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($clienteRows as $row)
                            @php
                                $estadoBruto = strtolower((string) ($row['estado'] ?? ''));
                                $estadoTexto = $estadoBruto !== ''
                                    ? (string) \Illuminate\Support\Str::of($row['estado'])->replace('_', ' ')->title()
                                    : 'Sin estado';
                                $cancelado = $estadoBruto === 'cancelado';
                                $canceladoEn = $row['cancelado_en'] ?? null;
                                $motivoFila = trim((string) ($row['motivo_cancelacion'] ?? ''));
                                $canceladoPorFila = trim((string) ($row['cancelado_por'] ?? ''));
                                $cancelRoute = $cancelRouteName && isset($promotor) && $promotor?->id && !empty($row['credito_id'])
                                    ? route($cancelRouteName, array_merge($supervisorQuery, ['promotor' => $promotor->id, 'credito' => $row['credito_id']]))
                                    : null;
                            @endphp
                            <tr class="text-gray-800 align-top">
                                <td class="px-2 py-1">{{ $row['nombre'] ?: 'Sin nombre' }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['prestamo_anterior']) }}</td>
                                <td class="px-2 py-1 text-right font-semibold">{{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['prestamo_solicitado']) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['comision_cinco']) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['total_prestamo']) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['recredito_nuevo']) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['total_recredito']) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currencyNullable($row['saldo_post_recredito']) }}</td>
                                <td class="px-2 py-1 text-left">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-semibold {{ $cancelado ? 'text-red-600' : 'text-emerald-600' }}">{{ $estadoTexto }}</span>
                                        @if($canceladoEn)
                                            <span class="text-[9px] text-gray-600">Cancelado el {{ $canceladoEn }}</span>
                                        @endif
                                        @if($canceladoPorFila !== '')
                                            <span class="text-[9px] text-gray-500">Registró: {{ $canceladoPorFila }}</span>
                                        @endif
                                        @if($motivoFila !== '')
                                            <span class="text-[9px] text-gray-600">Motivo: {{ $motivoFila }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 py-1 text-left recibo-print-hidden">
                                    @if($puedeCancelarCreditos && $cancelRoute && !empty($row['credito_id']) && !$cancelado)
                                        <button type="button"
                                                class="inline-flex items-center justify-center rounded-md bg-red-600 px-2 py-1 text-[9px] font-semibold text-white shadow hover:bg-red-700 focus:outline-none focus:ring focus:ring-red-200"
                                                data-credit-cancel-button
                                                data-route="{{ $cancelRoute }}"
                                                data-cliente="{{ e($row['nombre'] ?: 'Sin nombre') }}"
                                                data-credito-id="{{ $row['credito_id'] }}"
                                                data-monto="{{ $row['prestamo_solicitado'] ?? 0 }}"
                                                data-estado="{{ $estadoTexto }}"
                                                data-motivo="{{ e($motivoFila) }}">
                                            Rechazar crédito
                                        </button>
                                    @else
                                        <span class="text-[9px] text-gray-400">---</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-2 py-2 text-center text-gray-500 text-[9px]">Sin clientes registrados.</td>
                            </tr>
                        @endforelse

                        @if(!empty($clienteRows))
                            <tr class="bg-gray-100 text-gray-900 font-bold">
                                <td class="px-2 py-1 text-right uppercase">Total</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['prestamo_anterior'] ?? 0) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['prestamo_solicitado'] ?? 0) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['comision_cinco'] ?? 0) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['total_prestamo'] ?? 0) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['recredito_nuevo'] ?? 0) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['total_recredito'] ?? 0) }}</td>
                                <td class="px-2 py-1 text-right">{{ \App\Support\ReciboDesembolsoFormatter::currency($totalesTabla['saldo_post_recredito'] ?? 0) }}</td>
                                <td class="px-2 py-1"></td>
                                <td class="px-2 py-1 recibo-print-hidden"></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Validaciones y firmas --}}
        {{-- <div class="bg-white rounded-xl shadow p-3 space-y-2 w-full max-w-5xl">
            <h2 class="text-sm font-bold text-gray-700 tracking-tight">Validaciones y firmas</h2>

            <div class="grid sm:grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-semibold text-gray-700">Nombre de promotora de reconocimiento de clientes</label>
                    <input type="text" name="promotora_reconocimiento" value="{{ $promotorNombre }}" readonly
                           class="w-full rounded-md border border-gray-300 px-2 py-1 text-[10px] focus:border-blue-500 focus:ring focus:ring-blue-200"
                           placeholder="Nombre completo">
                    <div class="h-16 rounded-lg border border-dashed border-gray-300 flex items-end justify-center pb-2 text-[10px] text-gray-400">Firma</div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-semibold text-gray-700">Nombre de ejecutivo - Validar</label>
                    <input type="text" name="ejecutivo_validacion" value="{{ $ejecutivoNombre }}" readonly
                           class="w-full rounded-md border border-gray-300 px-2 py-1 text-[10px] focus:border-blue-500 focus:ring focus:ring-blue-200"
                           placeholder="Nombre completo">
                    <div class="h-16 rounded-lg border border-dashed border-gray-300 flex items-end justify-center pb-2 text-[10px] text-gray-400">Firma</div>
                </div>
            </div>
        </div> --}}

        {{-- Firmas --}}
        <div class="bg-white rounded-xl shadow p-3 space-y-2 w-full max-w-5xl">
            <h2 class="text-sm font-bold text-gray-700 tracking-tight">Firmas</h2>

            <div class="grid sm:grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-semibold text-gray-700" for="firma-promotor">Nombre de promotora</label>
                    <input type="text" id="firma-promotor" value="{{ $promotorNombre !== '' ? $promotorNombre : 'Sin promotor' }}" readonly
                           class="w-full rounded-md border border-gray-300 px-2 py-1 text-[10px] focus:border-blue-500 focus:ring focus:ring-blue-200"
                           placeholder="Nombre completo">
                    <div class="h-16 rounded-lg border border-dashed border-gray-300 flex items-end justify-center pb-2 text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Firma promotor</div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-semibold text-gray-700" for="firma-supervisor">Nombre de supervisor</label>
                    <input type="text" id="firma-supervisor" value="{{ $supervisorNombre !== '' ? $supervisorNombre : 'Sin supervisor' }}" readonly
                           class="w-full rounded-md border border-gray-300 px-2 py-1 text-[10px] focus:border-blue-500 focus:ring focus:ring-blue-200"
                           placeholder="Nombre completo">
                    <div class="h-16 rounded-lg border border-dashed border-gray-300 flex items-end justify-center pb-2 text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Firma supervisor</div>
                </div>
            </div>
        </div>

        {{-- Resumen financiero --}}
        <div class="bg-white rounded-xl shadow p-3 space-y-2 w-full max-w-5xl">
            <h2 class="text-sm font-bold text-gray-700 tracking-tight">Resumen financiero</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 text-[10px]">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <th class="px-2 py-1 text-left font-semibold text-gray-700">Comisión de promotor</th>
                            <td class="px-2 py-1 text-right">
                                <input type="number" step="0.01" name="comision_promotor"
                                       value="{{ number_format($comisionPromotor, 2, '.', '') }}" readonly
                                       class="w-full max-w-[160px] rounded-md border border-gray-300 px-2 py-1 text-[10px] text-right focus:border-blue-500 focus:ring focus:ring-blue-200">
                            </td>
                        </tr>
                        <tr>
                            <th class="px-2 py-1 text-left font-semibold text-gray-700">Comisión de supervisor</th>
                            <td class="px-2 py-1 text-right">
                                <input type="number" step="0.01" name="comision_supervisor"
                                       value="{{ number_format($comisionSupervisor, 2, '.', '') }}" readonly
                                       class="w-full max-w-[160px] rounded-md border border-gray-300 px-2 py-1 text-[10px] text-right focus:border-blue-500 focus:ring focus:ring-blue-200">
                            </td>
                        </tr>
                        <tr>
                            <th class="px-2 py-1 text-left font-semibold text-gray-700">Cartera actual del promotor</th>
                            <td class="px-2 py-1 text-right">
                                <input type="number" step="0.01" name="cartera_actual"
                                       value="{{ number_format($carteraActual, 2, '.', '') }}" readonly
                                       class="w-full max-w-[160px] rounded-md border border-gray-300 px-2 py-1 text-[10px] text-right focus:border-blue-500 focus:ring focus:ring-blue-200">
                            </td>
                        </tr>
                        
                        <tr>
                            <th class="px-2 py-1 text-left font-bold text-gray-800">Inversión</th>
                            <td class="px-2 py-1 text-right text-[12px] font-bold {{ $inversion >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ \App\Support\ReciboDesembolsoFormatter::currency($inversion) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-[9px] text-gray-500">La inversión se calcula como comisiones + total de últimos créditos − cartera actual.</p>
        </div>

        {{-- Recibos --}}
        {{-- <div class="grid gap-3 lg:grid-cols-2 w-full max-w-5xl">
            @foreach (['Promotor' => $promotorNombre, 'Supervisor' => $supervisorNombre] as $tipo => $nombre)
                <div class="bg-white rounded-xl shadow p-3 space-y-2">
                    <div class="space-y-0.5">
                        <h3 class="text-sm font-bold text-gray-700 text-center uppercase tracking-tight">Recibo de dinero</h3>
                        <p class="text-[10px] text-gray-600 text-center">RECIBÍ DE: {{ $reciboDeNombre !== '' ? strtoupper($reciboDeNombre) : '---' }}</p>
                    </div>

                    <div class="space-y-2 text-gray-800">
                        <div class="flex justify-between">
                            <span class="font-semibold">Fecha:</span>
                            <span>{{ $fechaHoy }}</span>
                        </div>
                        <div class="space-y-1">
                            <label class="block font-semibold">Nombre completo de {{ strtolower($tipo) }}</label>
                            <input type="text" value="{{ $nombre }}" readonly
                                   class="w-full rounded-md border border-gray-300 px-2 py-1 text-[10px] focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   placeholder="Nombre del {{ strtolower($tipo) }}">
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Monto recibido:</span>
                            <span class="font-bold">{{ \App\Support\ReciboDesembolsoFormatter::currency($totalPrestamoSolicitado) }}</span>
                        </div>
                    </div>

                    <div class="h-16 rounded-lg border border-dashed border-gray-300 flex items-end justify-center pb-2 text-[10px] text-gray-400">Firma</div>

                    <p class="text-center text-[10px] font-bold uppercase underline tracking-wide">
                        Por concepto de: operación financiera para préstamos individual de las personas mencionadas en este desembolso.
                    </p>
                </div>
            @endforeach
        </div> --}}

    </div>

    {{-- Modal ligero para capturar el motivo del rechazo de crédito --}}
    <div id="credit-cancel-modal" class="recibo-print-hidden fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
        <div class="w-full max-w-md rounded-lg bg-white p-4 shadow-xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Rechazar crédito</h3>
                    <p class="text-[10px] text-gray-500 leading-tight" data-credit-cancel-summary>
                        Describe el motivo del rechazo para dejar evidencia en el expediente del cliente.
                    </p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600" data-credit-cancel-close aria-label="Cerrar ventana de rechazo">
                    ✕
                </button>
            </div>

            <form method="POST" class="mt-3 space-y-3" data-credit-cancel-form>
                @csrf

                <div class="rounded-md bg-gray-50 p-2 text-[10px] text-gray-600">
                    <p class="leading-tight">
                        Cancelarás el crédito de
                        <span class="font-semibold text-gray-800" data-credit-cancel-client>—</span>.
                        Esta acción es definitiva.
                    </p>
                    <div class="mt-1 grid grid-cols-2 gap-y-1 text-gray-500">
                        <div class="col-span-2">
                            <span class="font-semibold text-gray-700">Estado actual:</span>
                            <span data-credit-cancel-state>—</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700">Monto solicitado:</span>
                            <span data-credit-cancel-amount>—</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="motivo_cancelacion_modal" class="text-[10px] font-semibold text-gray-700">Motivo del rechazo</label>
                    <textarea id="motivo_cancelacion_modal" name="motivo_cancelacion" rows="3" required
                              class="w-full rounded-md border border-gray-300 px-2 py-1 text-[10px] text-gray-800 focus:border-red-500 focus:ring focus:ring-red-200"
                              data-credit-cancel-motivo></textarea>
                    <p class="text-[9px] text-gray-500">Anota hechos concretos (por ejemplo, documento faltante, verificación no aprobada, etc.).</p>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-1.5 text-[10px] font-semibold text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring focus:ring-gray-200"
                            data-credit-cancel-close>Volver</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-red-600 px-3 py-1.5 text-[10px] font-semibold text-white shadow hover:bg-red-700 focus:outline-none focus:ring focus:ring-red-300"
                            data-credit-cancel-submit>Confirmar rechazo</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            // Controlador ligero en vanilla JS para abrir/cerrar el modal y precargar el formulario.
            const modal = document.getElementById('credit-cancel-modal');
            if (!modal) {
                return;
            }

            const openButtons = document.querySelectorAll('[data-credit-cancel-button]');
            if (!openButtons.length) {
                return;
            }

            const form = modal.querySelector('[data-credit-cancel-form]');
            const summary = modal.querySelector('[data-credit-cancel-summary]');
            const clientTarget = modal.querySelector('[data-credit-cancel-client]');
            const stateTarget = modal.querySelector('[data-credit-cancel-state]');
            const amountTarget = modal.querySelector('[data-credit-cancel-amount]');
            const motivoField = modal.querySelector('[data-credit-cancel-motivo]');
            const submitButton = modal.querySelector('[data-credit-cancel-submit]');
            const closeButtons = modal.querySelectorAll('[data-credit-cancel-close]');
            const formatter = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

            const toggleModal = (show) => {
                if (show) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    setTimeout(() => motivoField?.focus(), 50);
                    return;
                }

                modal.classList.add('hidden');
                modal.classList.remove('flex');
                form?.reset();
                form?.setAttribute('action', '#');
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Confirmar rechazo';
                }
            };

            const openModal = (button) => {
                const route = button.getAttribute('data-route');
                if (!route || !form) {
                    return;
                }

                form.setAttribute('action', route);

                const cliente = button.getAttribute('data-cliente') || 'Sin cliente';
                const estado = button.getAttribute('data-estado') || 'Sin estado';
                const monto = Number(button.getAttribute('data-monto') || 0);
                const motivoPrevio = button.getAttribute('data-motivo') || '';

                if (clientTarget) {
                    clientTarget.textContent = cliente;
                }

                if (stateTarget) {
                    stateTarget.textContent = estado;
                }

                if (amountTarget) {
                    amountTarget.textContent = formatter.format(Number.isFinite(monto) ? monto : 0);
                }

                if (motivoField) {
                    motivoField.value = motivoPrevio;
                }

                if (summary) {
                    summary.textContent = 'Describe el motivo del rechazo para dejar evidencia en el expediente del cliente.';
                }

                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Confirmar rechazo';
                }

                toggleModal(true);
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', () => openModal(button));
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', () => toggleModal(false));
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    toggleModal(false);
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    toggleModal(false);
                }
            });

            form?.addEventListener('submit', () => {
                if (!submitButton) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.textContent = 'Cancelando...';
            });
        })();
    </script>
</x-layouts.mobile.mobile-layout>
