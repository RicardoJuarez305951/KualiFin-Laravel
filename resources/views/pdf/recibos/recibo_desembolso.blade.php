@php
    use App\Support\ReciboDesembolsoFormatter as Formatter;

    $promotorNombre = $promotorNombre ?? '';
    $supervisorNombre = $supervisorNombre ?? '';
    $ejecutivoNombre = $ejecutivoNombre ?? '';
    $reciboDeNombre = $reciboDeNombre ?? '';
    $fechaHoy = $fechaHoy ?? now()->format('d/m/Y');
    $rows = collect($clienteRows ?? []);
    $totals = $totalesTabla ?? [];
    $totalPrestamoSolicitado = $totalPrestamoSolicitado ?? $rows->sum(fn ($row) => $row['prestamo_solicitado'] ?? 0);
    $comisionPromotor = $comisionPromotor ?? 0;
    $comisionSupervisor = $comisionSupervisor ?? 0;
    $carteraActual = $carteraActual ?? 0;
    $inversion = $inversion ?? 0;
    $motivoCancelacion = $motivoCancelacion ?? '';
    $receiptIssuer = 'MARCO ANTONIO GÜEMES ABUD';
    $receipts = [['number'=>1],['number'=>2]];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Recibo de desembolso</title>
<style>
    /* Márgenes mínimos para PDF */
    @page{ margin:6mm; }
    *{ box-sizing:border-box; }
    html,body{ margin:0; padding:0; }
    body{
        font-family:"DejaVu Sans", Arial, sans-serif;
        font-size:8.3px; line-height:1.1;
        color:#000; margin:2mm;
        -webkit-text-size-adjust:100%;
        word-wrap:break-word; overflow-wrap:break-word;
    }
    b{ font-weight:700; }
    .u{ text-transform:uppercase; }
    table{ width:100%; border-collapse:collapse; }

    /* Aviso compacto */
    .notice{
        border:0.35px solid #000; padding:2px 3px; text-align:center;
        font-weight:700; font-size:7.8px; margin:0 0 4px 0;
        text-transform:uppercase;
    }

    /* Tabla info superior */
    .info-table td{
        border:0.35px solid #000; padding:2px 3px; width:25%; vertical-align:top;
    }
    .label{ font-size:7.4px; text-transform:uppercase; margin:0 0 1px 0; }
    .value{ font-size:9px; font-weight:700; }

    /* Tabla clientes (muchas columnas) súper compacta */
    .clients-table th,.clients-table td{
        border:0.35px solid #000; padding:1.5px 2px; line-height:1.05;
        white-space:nowrap;
    }
    .clients-table th{
        font-size:7.2px; font-weight:700; text-align:center; text-transform:uppercase;
    }
    .text-right{ text-align:right; }
    .text-left{ text-align:left; }
    .text-center{ text-align:center; }
    .small{ font-size:7.2px; }
    .section{ margin-top:4px; }

    /* Firmas comprimidas */
    .signatures td{
        border:0.35px solid #000; padding:2px 3px; width:50%; vertical-align:top;
    }
    .signature-box{
        margin-top:6px; height:30px; border:0.35px solid #000;
        text-align:center; vertical-align:bottom; padding-bottom:4px; font-size:7.2px;
    }

    /* Resumen */
    .summary-table th,.summary-table td{ border:0.35px solid #000; padding:2px 3px; }
    .summary-table th{ text-transform:uppercase; text-align:left; width:65%; font-size:8px; }
    .summary-total{ font-size:9.5px; font-weight:700; }

    /* Recibos (2 por fila ya incluidos) */
    .receipt-wrapper td{ width:50%; vertical-align:top; padding:2px; }
    .receipt-table{ border:0.35px solid #000; width:100%; border-collapse:collapse; }
    .receipt-table td{ padding:2px 3px; border-bottom:0.35px solid #000; }
    .receipt-table tr:last-child td{ border-bottom:none; }
    .receipt-meta td:first-child{ font-weight:700; }
    .receipt-meta td:last-child{ text-align:right; }
    .receipt-title{ font-weight:700; text-align:center; text-transform:uppercase; margin:0 0 3px 0; }
    .receipt-amount{ border:0.35px solid #000; padding:1px 3px; font-weight:700; display:inline-block; }
    .receipt-signature-cell{
        height:26px; border-top:0.35px solid #000; text-align:center;
        vertical-align:bottom; padding:3px 0 4px; font-size:7.2px;
    }
    .cut-line{ border-top:0.35px dashed #000; margin:6px 0; }
</style>
</head>
<body>
    <div class="notice">Para cr&eacute;ditos nuevos mayores a $3,000 presentar comprobante adicional.</div>

    <table class="info-table">
        <tr>
            <td><div class="label">Cliente / Promotor</div><div class="value">{{ $promotorNombre !== '' ? $promotorNombre : '---' }}</div></td>
            <td><div class="label">Supervisor</div><div class="value">{{ $supervisorNombre !== '' ? $supervisorNombre : '---' }}</div></td>
            <td><div class="label">Ejecutivo</div><div class="value">{{ $ejecutivoNombre !== '' ? $ejecutivoNombre : '---' }}</div></td>
            <td><div class="label">Fecha</div><div class="value text-right">{{ $fechaHoy }}</div></td>
        </tr>
    </table>

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
                    <tr>
                        <td>{{ $i+1 }}. {{ $row['nombre'] ?? 'Sin nombre' }}</td>
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

    <div class="section">
        <table class="signatures">
            <tr>
                <td>
                    <div class="label">Promotora</div>
                    <div class="value">{{ $promotorNombre !== '' ? $promotorNombre : '---' }}</div>
                    <div class="signature-box">Firma promotora</div>
                </td>
                <td>
                    <div class="label">Supervisor</div>
                    <div class="value">{{ $supervisorNombre !== '' ? $supervisorNombre : '---' }}</div>
                    <div class="signature-box">Firma supervisor</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="summary-table">
            <tbody>
                <tr><th>Comisi&oacute;n promotor</th><td class="text-right">{{ Formatter::currency($comisionPromotor) }}</td></tr>
                <tr><th>Comisi&oacute;n supervisor</th><td class="text-right">{{ Formatter::currency($comisionSupervisor) }}</td></tr>
                <tr><th>Total pr&eacute;stamos solicitados</th><td class="text-right">{{ Formatter::currency($totalPrestamoSolicitado) }}</td></tr>
                <tr><th>Cartera actual promotor</th><td class="text-right">{{ Formatter::currency($carteraActual) }}</td></tr>
                <tr><th>Motivo de cancelaci&oacute;n</th><td class="text-left">{{ $motivoCancelacion !== '' ? $motivoCancelacion : '---' }}</td></tr>
                <tr><th>Inversi&oacute;n</th><td class="text-right summary-total">{{ Formatter::currency($inversion) }}</td></tr>
            </tbody>
        </table>
        <div class="small" style="margin-top:2px;">
            <b>Nota:</b> Inversi&oacute;n = comisiones + &uacute;ltimos cr&eacute;ditos − cartera actual.
        </div>
    </div>

    <div class="cut-line" aria-hidden="true"></div>

    <div class="section">
        <table class="receipt-wrapper">
            <tr>
                @foreach ($receipts as $receipt)
                    <td>
                        <div class="receipt-title">Recibo de dinero</div>
                        <table class="receipt-table">
                            <tbody>
                                <tr class="receipt-meta">
                                    <td>No. {{ $receipt['number'] }}</td>
                                    <td>Fecha: {{ $fechaHoy }}</td>
                                </tr>
                                <tr><td colspan="2"><b>Recib&iacute; de:</b> {{ $receiptIssuer }}</td></tr>
                                <tr><td colspan="2"><b>Promotor:</b> {!! $reciboDeNombre !== '' ? $reciboDeNombre : '---' !!}</td></tr>
                                <tr><td colspan="2">La cantidad de: <span class="receipt-amount">{{ Formatter::currency($totalPrestamoSolicitado) }}</span></td></tr>
                                <tr>
                                    <td class="receipt-signature-cell">Firma promotor</td>
                                    <td class="receipt-signature-cell">Firma supervisor</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="small u text-center" style="margin:2px 0 0;">
                                            Por concepto de: Operaci&oacute;n financiera para pr&eacute;stamos individual de las personas mencionadas en este desembolso.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>
</body>
</html>
