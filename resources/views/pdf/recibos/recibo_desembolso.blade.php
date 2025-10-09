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
    $receiptIssuer = 'MARCO ANTONIO G&Uuml;EMES ABUD';
    $receipts = [
        ['number' => 1],
        ['number' => 2],
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo de desembolso</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 9px;
            line-height: 1.25;
            color: #000;
            margin: 12px;
        }
        .notice {
            border: 0.6px solid #000;
            padding: 4px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 8px;
            font-size: 8.5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            border: 0.6px solid #000;
            padding: 4px 6px;
            width: 25%;
            vertical-align: top;
        }
        .label {
            font-size: 8px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .value {
            font-size: 10px;
            font-weight: bold;
        }
        .clients-table th,
        .clients-table td {
            border: 0.6px solid #000;
            padding: 4px;
            line-height: 1.2;
        }
        .clients-table th {
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        .small { font-size: 8px; }
        .section {
            margin-top: 10px;
        }
        .signatures td {
            border: 0.6px solid #000;
            padding: 4px 6px;
            width: 50%;
            vertical-align: top;
        }
        .signature-box {
            margin-top: 14px;
            height: 45px;
            border: 0.6px solid #000;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 6px;
            font-size: 8px;
        }
        .summary-table th,
        .summary-table td {
            border: 0.6px solid #000;
            padding: 4px 6px;
        }
        .summary-table th {
            text-transform: uppercase;
            text-align: left;
            width: 68%;
        }
        .summary-total {
            font-size: 11px;
            font-weight: bold;
        }
        .receipt-table {
            border: 0.6px solid #000;
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-table td {
            padding: 3px 5px;
            border-bottom: 0.6px solid #000;
        }
        .receipt-table tr:last-child td {
            border-bottom: none;
        }
        .receipt-meta td:first-child {
            font-weight: bold;
        }
        .receipt-meta td:last-child {
            text-align: right;
        }
        .receipt-wrapper td {
            width: 50%;
            vertical-align: top;
            padding: 4px;
        }
        .receipt-section {
            margin-top: 4px;
        }
        .receipt-title {
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .receipt-amount {
            border: 0.6px solid #000;
            padding: 2px 6px;
            font-weight: bold;
            display: inline-block;
        }
        .receipt-signature-cell {
            height: 40px;
            border-top: 0.6px solid #000;
            text-align: center;
            vertical-align: bottom;
            padding-top: 4px;
            padding-bottom: 6px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="notice">
        Para cr&eacute;ditos nuevos mayores a $3,000 presentar comprobante adicional.
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="label">Cliente / Promotor</div>
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

    <div class="section">
        <table class="clients-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Pr&eacute;stamo anterior</th>
                    <th>Pr&eacute;stamo solicitado</th>
                    <th>-5% comisi&oacute;n</th>
                    <th>Total pr&eacute;stamo</th>
                    <th>Recr&eacute;dito nuevo</th>
                    <th>Total recr&eacute;dito</th>
                    <th>Total pr&eacute;stamo - recr&eacute;dito</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $index => $row)
                    <tr>
                        <td>{{ ($index + 1) }}. {{ $row['nombre'] ?? 'Sin nombre' }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['prestamo_anterior'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['prestamo_solicitado'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['comision_cinco'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['total_prestamo'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['recredito_nuevo'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['total_recredito'] ?? null) }}</td>
                        <td class="text-right">{{ Formatter::currencyNullable($row['saldo_post_recredito'] ?? null) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center small">Sin clientes registrados.</td>
                    </tr>
                @endforelse
                <tr>
                    <td class="text-right uppercase"><strong>Total</strong></td>
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
                    <div class="label">Nombre de promotora de reconocimiento de clientes</div>
                    <div class="value">{{ $promotorNombre !== '' ? $promotorNombre : '---' }}</div>
                    <div class="signature-box">Firma</div>
                </td>
                <td>
                    <div class="label">Nombre de ejecutivo - Validador</div>
                    <div class="value">{{ $ejecutivoNombre !== '' ? $ejecutivoNombre : '---' }}</div>
                    <div class="signature-box">Firma</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="summary-table">
            <tbody>
                <tr>
                    <th>Comisi&oacute;n de promotor</th>
                    <td class="text-right">{{ Formatter::currency($comisionPromotor) }}</td>
                </tr>
                <tr>
                    <th>Comisi&oacute;n de supervisor</th>
                    <td class="text-right">{{ Formatter::currency($comisionSupervisor) }}</td>
                </tr>
                <tr>
                    <th>Total pr&eacute;stamos solicitados</th>
                    <td class="text-right">{{ Formatter::currency($totalPrestamoSolicitado) }}</td>
                </tr>
                <tr>
                    <th>Cartera actual del promotor</th>
                    <td class="text-right">{{ Formatter::currency($carteraActual) }}</td>
                </tr>
                <tr>
                    <th>Inversi&oacute;n</th>
                    <td class="text-right summary-total">{{ Formatter::currency($inversion) }}</td>
                </tr>
            </tbody>
        </table>
        <p class="small">
            La inversi&oacute;n se calcula como comisiones + total de &uacute;ltimos cr&eacute;ditos - cartera actual.
        </p>
    </div>

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
                                <tr>
                                    <td colspan="2">Recib&iacute; de: {{ $receiptIssuer  }}</td>
                                    
                                </tr>
                                <tr>
                                    <td colspan="2">Promotor: {!! $reciboDeNombre !== '' ? $reciboDeNombre : '---' !!}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">La cantidad de: <span class="receipt-amount">{{ Formatter::currency($totalPrestamoSolicitado) }}</span></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="receipt-signature-cell">Firma</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="small uppercase text-center receipt-section">
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
