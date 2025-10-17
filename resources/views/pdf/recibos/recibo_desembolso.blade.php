@php
    use App\Support\ReciboDesembolsoFormatter as Formatter;

    $promotorNombre = $promotorNombre ?? '';
    $supervisorNombre = $supervisorNombre ?? '';
    $ejecutivoNombre = $ejecutivoNombre ?? ''; // necesario para el bloque de firmas superior
    $fechaHoy = $fechaHoy ?? now()->format('d/m/Y');

    $rows = collect($clienteRows ?? [])->map(function ($row) {
        $estado = strtolower((string) ($row['estado'] ?? ''));
        $estadoNormalizado = str_replace('_', ' ', $estado);

        $row['_estado_texto'] = $estadoNormalizado !== '' ? ucwords($estadoNormalizado) : '';
        $row['_cancelado'] = in_array($estado, [
            'cancelado', 'cancelada', 'rechazado', 'rechazada', 'eliminado', 'eliminada',
        ], true);
        $row['_motivo_cancelacion'] = trim((string) ($row['motivo_cancelacion'] ?? ''));
        $row['_cancelado_por'] = trim((string) ($row['cancelado_por'] ?? ''));
        $row['_cancelado_en'] = $row['cancelado_en'] ?? null;

        return $row;
    });

    $cancelledRows = $rows->filter(fn ($row) => !empty($row['_cancelado']));
    $activeRows = $rows->reject(fn ($row) => !empty($row['_cancelado']));

    $sumKeys = [
        'prestamo_anterior',
        'prestamo_solicitado',
        'comision_cinco',
        'total_prestamo',
        'recredito_nuevo',
        'total_recredito',
        'saldo_post_recredito',
    ];

    $totals = array_merge($totalesTabla ?? [], []);
    foreach ($sumKeys as $key) {
        $totals[$key] = $activeRows->sum(fn ($row) => (float) ($row[$key] ?? 0));
    }

    $totalPrestamoSolicitado = (float) ($totals['prestamo_solicitado'] ?? 0);
    $comisionPromotor = (float) ($totals['comision_cinco'] ?? 0);
    $comisionSupervisor = round($totalPrestamoSolicitado * 0.10, 2);
    $carteraActual = (float) ($totals['saldo_post_recredito'] ?? 0);
    $inversion = $comisionPromotor + $comisionSupervisor + $totalPrestamoSolicitado - $carteraActual;

    // Evita caracteres raros al generar el PDF
    $receiptIssuer = 'MARCO ANTONIO GOMEZ ABUD';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Recibo de desembolso</title>
<style>
    @page{ margin:6mm; }
    *{ box-sizing:border-box; }
    html,body{ margin:0; padding:0; }
    body{
        font-family:"DejaVu Sans", Arial, sans-serif;
        font-size:8.3px; line-height:1.1; color:#000; margin:2mm;
        -webkit-text-size-adjust:100%; word-wrap:break-word; overflow-wrap:break-word;
    }
    b{ font-weight:700; }
    .u{ text-transform:uppercase; }
    table{ width:100%; border-collapse:collapse; }

    .notice{
        border:0.35px solid #000; padding:2px 3px; text-align:center;
        font-weight:700; font-size:7.8px; margin:0 0 4px 0; text-transform:uppercase;
    }

    .info-table td{ border:0.35px solid #000; padding:2px 3px; width:33.33%; vertical-align:top; }
    .label{ font-size:7.4px; text-transform:uppercase; margin:0 0 1px 0; }
    .value{ font-size:9px; font-weight:700; }
    .text-right{ text-align:right; }
    .text-center{ text-align:center; }
    .small{ font-size:7.2px; }
    .section{ margin-top:4px; }

    .clients-table th,.clients-table td{
        border:0.35px solid #000; padding:1.5px 2px; line-height:1.05; white-space:nowrap;
    }
    .clients-table th{ font-size:7.2px; font-weight:700; text-align:center; text-transform:uppercase; }
    .clients-table .cancelled-row td{ color:#8a8a8a; }
    .clients-table .cancelled-row td.text-right{ color:#8a8a8a; }
    .cancelled-label{ display:block; font-size:6.8px; font-weight:700; text-transform:uppercase; color:#b91c1c; margin-top:1px; }
    .cancelled-meta{ display:block; font-size:6.5px; color:#6b7280; }

    .signatures{ width:100%; border-collapse:collapse; }
    .signatures td{ border:0.35px solid #000; padding:2px 3px; width:50%; vertical-align:top; }
    .signature-box{
        margin-top:6px; height:30px; border:0.35px solid #000;
        text-align:center; vertical-align:bottom; padding-bottom:4px; font-size:7.2px;
    }

    .summary-table th,.summary-table td{ border:0.35px solid #000; padding:2px 3px; }
    .summary-table th{ text-transform:uppercase; text-align:left; width:65%; font-size:8px; }
    .summary-total{ font-size:9.5px; font-weight:700; }

    .receipt-wrapper td{ width:50%; vertical-align:top; padding:2px; }
    .receipt-table{ border:0.35px solid #000; width:100%; border-collapse:collapse; }
    .receipt-table td{ padding:2px 3px; border-bottom:0.35px solid #000; }
    .receipt-table tr:last-child td{ border-bottom:none; }
    .receipt-meta td:first-child{ font-weight:700; }
    .receipt-meta td:last-child{ text-align:right; }
    .receipt-title{ font-weight:700; text-align:center; text-transform:uppercase; margin:0 0 3px 0; }
    .receipt-amount{ border:0.35px solid #000; padding:1px 3px; font-weight:700; display:inline-block; }
    .cut-line{ border-top:0.35px dashed #000; margin:6px 0; }
</style>
</head>
<body>
    <div class="notice">Para cr&eacute;ditos nuevos mayores a $3,000 presentar comprobante adicional.</div>

    {{-- INFO (se mantiene Promotor / Supervisor / Fecha) --}}
    <table class="info-table">
        <tr>
            <td>
                <div class="label">Promotor</div>
                <div class="value">{{ $promotorNombre !== '' ? $promotorNombre : '---' }}</div>
            </td>
            <td>
                <div class="label">Supervisor</div>
                <div class="value">{{ $supervisorNombre !== '' ? $supervisorNombre : '---' }}</div>
            </td>
            <td>
                <div class="label">Ejecutivo</div>
                <div class="value">{{ $ejecutivoNombre !== '' ? $ejecutivoNombre : '---' }}</div>
            </td>
            <td>
                <div class="label">Fecha</div>
                <div class="value text-right">{{ $fechaHoy }}</div>
            </td>
        </tr>
    </table>

    {{-- TABLA DE CLIENTES --}}
    <div class="section">
        <table class="clients-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Pr&eacute;st. ant.</th>
                    <th>Pr&eacute;st. solic.</th>
                    <th>-5% com.</th>
                    <th>Total pr&eacute;st.</th>
                    <th>Recr&eacute;dito</th>
                    <th>Total recr&eacute;dito</th>
                    <th>Saldo post</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $i => $row)
                    @php
                        $cancelado = !empty($row['_cancelado']);
                        $estadoTexto = $row['_estado_texto'] ?? '';
                        $motivoCancelacionFila = $row['_motivo_cancelacion'] ?? '';
                        $canceladoEn = $row['_cancelado_en'] ?? null;
                        $canceladoPor = $row['_cancelado_por'] ?? '';
                    @endphp
                    <tr class="{{ $cancelado ? 'cancelled-row' : '' }}">
                        <td>
                            <div>{{ $i + 1 }}. {{ $row['nombre'] ?? 'Sin nombre' }}</div>
                            @if($cancelado)
                                <span class="cancelled-label">{{ $estadoTexto !== '' ? $estadoTexto : 'Cancelado' }}</span>
                                <span class="cancelled-meta">
                                    Motivo: {{ $motivoCancelacionFila !== '' ? $motivoCancelacionFila : 'Sin motivo registrado' }}
                                </span>
                                @if($canceladoEn)
                                    <span class="cancelled-meta">Fecha: {{ $canceladoEn }}</span>
                                @endif
                                @if($canceladoPor !== '')
                                    <span class="cancelled-meta">Registró: {{ $canceladoPor }}</span>
                                @endif
                            @endif
                        </td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['prestamo_anterior'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['prestamo_solicitado'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['comision_cinco'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['total_prestamo'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['recredito_nuevo'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['total_recredito'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['saldo_post_recredito'] ?? null) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center small">Sin clientes registrados.</td></tr>
                @endforelse
                <tr>
                    <td class="text-right u"><b>Total</b></td>
                    <td class="text-right">{{ Formatter::currency($totals['prestamo_anterior'] ?? 0) }}</td>
                    <td class="text-right">{{ Formatter::currency($totals['prestamo_solicitado'] ?? 0) }}</td>
                    <td class="text-right">{{ Formatter::currency($totals['comision_cinco'] ?? 0) }}</td>
                    <td class="text-right">{{ Formatter::currency($totals['total_prestamo'] ?? 0) }}</td>
                    <td class="text-right">{{ Formatter::currency($totals['recredito_nuevo'] ?? 0) }}</td>
                    <td class="text-right">{{ Formatter::currency($totals['total_recredito'] ?? 0) }}</td>
                    <td class="text-right">{{ Formatter::currency($totals['saldo_post_recredito'] ?? 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- BLOQUE DE FIRMAS SUPERIOR: Promotor y Ejecutivo --}}
    <div class="section">
        <table class="signatures">
            <tr>
                <td>
                    <div class="label">Promotor</div>
                    <div class="value">{{ $promotorNombre !== '' ? $promotorNombre : '---' }}</div>
                    <div class="signature-box">Firma promotor</div>
                </td>
                <td>
                    <div class="label">Ejecutivo</div>
                    <div class="value">{{ $ejecutivoNombre !== '' ? $ejecutivoNombre : '---' }}</div>
                    <div class="signature-box">Firma ejecutivo</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- RESUMEN --}}
    <div class="section">
        <table class="summary-table">
            <tbody>
                <tr><th>Comisi&oacute;n promotor</th><td class="text-right">{{ Formatter::currency($comisionPromotor) }}</td></tr>
                <tr><th>Comisi&oacute;n supervisor (10%)</th><td class="text-right">{{ Formatter::currency($comisionSupervisor) }}</td></tr>
                <tr><th>Total pr&eacute;stamos solicitados</th><td class="text-right">{{ Formatter::currency($totalPrestamoSolicitado) }}</td></tr>
                <tr><th>Cartera actual promotor</th><td class="text-right">{{ Formatter::currency($carteraActual) }}</td></tr>
                <tr><th>Inversi&oacute;n</th><td class="text-right summary-total">{{ Formatter::currency($inversion) }}</td></tr>
            </tbody>
        </table>
        <div class="small" style="margin-top:2px;">
            <b>Nota:</b> Inversi&oacute;n = comisiones (promotor + supervisor 10%) + &uacute;ltimos cr&eacute;ditos - cartera actual. Las sumas excluyen cr&eacute;ditos cancelados.
        </div>
    </div>

    <div class="cut-line" aria-hidden="true"></div>

    {{-- RECIBOS: SOLO UN ROL POR RECIBO (Promotor | Supervisor) --}}
    <div class="section">
        <table class="receipt-wrapper">
            <tr>
                {{-- ===== RECIBO 1: PROMOTOR ===== --}}
                <td>
                    <div class="receipt-title">Recibo de dinero</div>
                    <table class="receipt-table">
                        <tbody>
                            <tr class="receipt-meta">
                                <td>No. 1</td>
                                <td>Fecha: {{ $fechaHoy }}</td>
                            </tr>
                            <tr><td colspan="2"><b>Recib&iacute; de:</b> {{ $receiptIssuer }}</td></tr>
                            <tr>
                                <td colspan="2">
                                    La cantidad de:
                                    <span class="receipt-amount">{{ Formatter::currency($totalPrestamoSolicitado) }}</span>
                                </td>
                            </tr>
                            {{-- Firma SOLO del Promotor --}}
                            <tr>
                                <td colspan="2" style="padding:0;">
                                    <table class="signatures" style="width:100%; border-left:none; border-right:none; border-bottom:none;">
                                        <tr>
                                            <td style="width:100%;">
                                                <div class="label">Promotor</div>
                                                <div class="value">{{ $promotorNombre !== '' ? $promotorNombre : '---' }}</div>
                                                <div class="signature-box">Firma promotor</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p class="small u text-center" style="margin:2px 0 0;">
                                        Por concepto de: Operaci&oacute;n financiera para pr&eacute;stamos individuales de las personas mencionadas en este desembolso.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>

                {{-- ===== RECIBO 2: SUPERVISOR ===== --}}
                <td>
                    <div class="receipt-title">Recibo de dinero</div>
                    <table class="receipt-table">
                        <tbody>
                            <tr class="receipt-meta">
                                <td>No. 2</td>
                                <td>Fecha: {{ $fechaHoy }}</td>
                            </tr>
                            <tr><td colspan="2"><b>Recib&iacute; de:</b> {{ $receiptIssuer }}</td></tr>
                            <tr>
                                <td colspan="2">
                                    La cantidad de:
                                    <span class="receipt-amount">{{ Formatter::currency($totalPrestamoSolicitado) }}</span>
                                </td>
                            </tr>
                            {{-- Firma SOLO del Supervisor --}}
                            <tr>
                                <td colspan="2" style="padding:0;">
                                    <table class="signatures" style="width:100%; border-left:none; border-right:none; border-bottom:none;">
                                        <tr>
                                            <td style="width:100%;">
                                                <div class="label">Supervisor</div>
                                                <div class="value">{{ $supervisorNombre !== '' ? $supervisorNombre : '---' }}</div>
                                                <div class="signature-box">Firma supervisor</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p class="small u text-center" style="margin:2px 0 0;">
                                        Por concepto de: Operaci&oacute;n financiera para pr&eacute;stamos individuales de las personas mencionadas en este desembolso.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>



