@php
    use Illuminate\Support\Arr;

    $contexto = $contexto ?? [];
    $listas = $listas ?? [];
    $totales = $totales ?? [];
    $cierres = $cierres ?? [];
    $cobranza = $cobranza ?? [];
    $base = $base ?? [];

    $promotorNombre = Arr::get($contexto, 'promotor.nombre', Arr::get($base, 'promotorNombre', ''));
    $supervisorNombre = Arr::get($contexto, 'supervisor.nombre', Arr::get($base, 'supervisorNombre', ''));
    $ejecutivoNombre = Arr::get($contexto, 'ejecutivo.nombre', Arr::get($base, 'ejecutivoNombre', ''));
    $fechaReporte = Arr::get($contexto, 'fecha_reporte');
    $semanaVenta = Arr::get($contexto, 'semana_venta');
    $rango = Arr::get($contexto, 'rango', []);

    $fallo = Arr::get($listas, 'fallo', []);
    $prestamos = Arr::get($listas, 'prestamos', []);
    $desembolsos = Arr::get($listas, 'desembolsos', []);
    $recreditos = Arr::get($listas, 'recreditos', []);
    $adelantos = Arr::get($listas, 'adelantos', []);
    $recuperacion = Arr::get($listas, 'recuperacion', []);
    $cobranzaDias = Arr::get($cobranza, 'dias', []);

    $formatCurrency = static function ($value): string {
        $number = is_numeric($value) ? (float) $value : 0.0;

        return '$' . number_format($number, 2, '.', ',');
    };

    $formatDate = static function ($value): string {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('d/m/Y');
        }

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return '---';
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de desembolso</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10px;
            margin: 18px;
            color: #111;
        }
        h1 {
            font-size: 16px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        h2 {
            font-size: 13px;
            margin: 18px 0 6px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 0.6px solid #333;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f0f0f0;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #555; }
        .small { font-size: 9px; }
        .section { margin-top: 18px; }
        .grid {
            display: table;
            width: 100%;
            border: 0.6px solid #333;
            border-collapse: collapse;
        }
        .grid-row {
            display: table-row;
        }
        .grid-cell {
            display: table-cell;
            padding: 6px 8px;
            border-right: 0.6px solid #333;
            border-bottom: 0.6px solid #333;
        }
        .grid-cell:last-child {
            border-right: none;
        }
        .grid-row:last-child .grid-cell {
            border-bottom: none;
        }
        .summary-table td {
            font-size: 10px;
        }
        .summary-table td.label {
            width: 70%;
        }
        .summary-table td.value {
            width: 30%;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Reporte de desembolso</h1>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell">
                    <strong>Promotora</strong><br>
                    {{ $promotorNombre !== '' ? $promotorNombre : '---' }}
                </div>
                <div class="grid-cell">
                    <strong>Supervisor</strong><br>
                    {{ $supervisorNombre !== '' ? $supervisorNombre : '---' }}
                </div>
                <div class="grid-cell">
                    <strong>Ejecutivo</strong><br>
                    {{ $ejecutivoNombre !== '' ? $ejecutivoNombre : '---' }}
                </div>
                <div class="grid-cell">
                    <strong>Fecha de reporte</strong><br>
                    {{ $formatDate($fechaReporte) }}
                </div>
            </div>
            <div class="grid-row">
                <div class="grid-cell">
                    <strong>Semana de venta</strong><br>
                    {{ $semanaVenta ?: '---' }}
                </div>
                <div class="grid-cell">
                    <strong>Rango del reporte</strong><br>
                    {{ $formatDate(Arr::get($rango, 'inicio')) }} al {{ $formatDate(Arr::get($rango, 'fin')) }}
                </div>
                <div class="grid-cell">
                    <strong>Cartera real</strong><br>
                    {{ $formatCurrency(Arr::get($totales, 'cartera_real', 0)) }}
                </div>
                <div class="grid-cell">
                    <strong>Total final</strong><br>
                    {{ $formatCurrency(Arr::get($totales, 'total_final', 0)) }}
                </div>
            </div>
        </div>
    </header>

    <section class="section">
        <h2>Fallo</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 18%">Fecha</th>
                    <th>Cliente</th>
                    <th style="width: 20%" class="text-right">Monto pendiente</th>
                </tr>
            </thead>
            <tbody>
                @forelse(Arr::get($fallo, 'items', []) as $item)
                    <tr>
                        <td>{{ $formatDate(Arr::get($item, 'fecha_texto')) }}</td>
                        <td>{{ Arr::get($item, 'cliente', 'Sin cliente') }}</td>
                        <td class="text-right">{{ $formatCurrency(Arr::get($item, 'monto', 0)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center muted">No hay fallas registradas en el periodo.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2" class="text-right"><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ $formatCurrency(Arr::get($fallo, 'total', Arr::get($totales, 'fallo', 0))) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Préstamos aprobados</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 18%">Fecha</th>
                    <th>Cliente</th>
                    <th style="width: 18%" class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prestamos as $item)
                    <tr>
                        <td>{{ $formatDate(Arr::get($item, 'fecha_texto')) }}</td>
                        <td>{{ Arr::get($item, 'cliente', 'Sin cliente') }}</td>
                        <td class="text-right">{{ $formatCurrency(Arr::get($item, 'monto', 0)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center muted">Sin préstamos registrados.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2" class="text-right"><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ $formatCurrency(Arr::get($totales, 'prestamos', 0)) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Desembolsos</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 18%">Fecha</th>
                    <th>Cliente</th>
                    <th style="width: 18%" class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($desembolsos as $item)
                    <tr>
                        <td>{{ $formatDate(Arr::get($item, 'fecha_texto')) }}</td>
                        <td>{{ Arr::get($item, 'cliente', 'Sin cliente') }}</td>
                        <td class="text-right">{{ $formatCurrency(Arr::get($item, 'monto', 0)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center muted">Sin desembolsos registrados.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2" class="text-right"><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ $formatCurrency(Arr::get($totales, 'desembolso', 0)) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Recréditos</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 18%">Fecha</th>
                    <th>Cliente</th>
                    <th style="width: 18%" class="text-right">Monto nuevo</th>
                    <th style="width: 18%" class="text-right">Monto anterior</th>
                    <th style="width: 18%" class="text-right">Saldo posterior</th>
                </tr>
            </thead>
            <tbody>
                @forelse(Arr::get($recreditos, 'items', []) as $item)
                    <tr>
                        <td>{{ $formatDate(Arr::get($item, 'fecha_texto')) }}</td>
                        <td>{{ Arr::get($item, 'cliente', 'Sin cliente') }}</td>
                        <td class="text-right">{{ $formatCurrency(Arr::get($item, 'monto_nuevo', 0)) }}</td>
                        <td class="text-right">{{ $formatCurrency(Arr::get($item, 'monto_anterior', 0)) }}</td>
                        <td class="text-right">{{ $formatCurrency(Arr::get($item, 'saldo_post', 0)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center muted">No se registraron recréditos en el periodo.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2" class="text-right"><strong>Total nuevo</strong></td>
                    <td class="text-right"><strong>{{ $formatCurrency(Arr::get($recreditos, 'total_nuevo', 0)) }}</strong></td>
                    <td class="text-right"><strong>{{ $formatCurrency(Arr::get($recreditos, 'total_anterior', 0)) }}</strong></td>
                    <td class="text-right"><strong>{{ $formatCurrency(Arr::get($recreditos, 'total', Arr::get($totales, 'recreditos', 0))) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Adelantos y recuperación</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 18%">Fecha</th>
                    <th>Cliente</th>
                    <th style="width: 18%" class="text-right">Adelanto</th>
                    <th style="width: 18%" class="text-right">Recuperación</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rows = max(count(Arr::get($adelantos, 'items', [])), count(Arr::get($recuperacion, 'items', [])));
                    $adelantosItems = Arr::get($adelantos, 'items', []);
                    $recuperacionItems = Arr::get($recuperacion, 'items', []);
                @endphp
                @for($index = 0; $index < $rows; $index++)
                    @php
                        $adelanto = $adelantosItems[$index] ?? null;
                        $recupera = $recuperacionItems[$index] ?? null;
                    @endphp
                    <tr>
                        <td>{{ $formatDate(Arr::get($adelanto, 'fecha_texto') ?? Arr::get($recupera, 'fecha_texto')) }}</td>
                        <td>
                            {{ Arr::get($adelanto, 'cliente') ?? Arr::get($recupera, 'cliente', 'Sin cliente') }}
                        </td>
                        <td class="text-right">{{ $adelanto ? $formatCurrency(Arr::get($adelanto, 'monto', 0)) : '---' }}</td>
                        <td class="text-right">{{ $recupera ? $formatCurrency(Arr::get($recupera, 'monto', 0)) : '---' }}</td>
                    </tr>
                @endfor
                @if($rows === 0)
                    <tr>
                        <td colspan="4" class="text-center muted">Sin movimientos de adelantos o recuperación.</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="2" class="text-right"><strong>Totales</strong></td>
                    <td class="text-right"><strong>{{ $formatCurrency(Arr::get($adelantos, 'total', Arr::get($totales, 'adelantos', 0))) }}</strong></td>
                    <td class="text-right"><strong>{{ $formatCurrency(Arr::get($recuperacion, 'total', Arr::get($totales, 'recuperacion', 0))) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Cobranza semanal</h2>
        <table>
            <thead>
                <tr>
                    <th>Día</th>
                    <th style="width: 18%">Fecha</th>
                    <th style="width: 20%" class="text-right">Total cobrado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cobranzaDias as $dia)
                    <tr>
                        <td>{{ Arr::get($dia, 'dia', '---') }}</td>
                        <td>{{ $formatDate(Arr::get($dia, 'fecha_texto')) }}</td>
                        <td class="text-right">{{ $formatCurrency(Arr::get($dia, 'total', 0)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center muted">Sin información de cobranza.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2" class="text-right"><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ $formatCurrency(Arr::get($totales, 'cobranza', 0)) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Resumen de totales</h2>
        <table class="summary-table">
            <tbody>
                <tr>
                    <td class="label">Fondo de ahorro</td>
                    <td class="value">{{ $formatCurrency(Arr::get($totales, 'fondo_ahorro', Arr::get($cierres, 'fondo_ahorro', 0))) }}</td>
                </tr>
                <tr>
                    <td class="label">Total izquierdo</td>
                    <td class="value">{{ $formatCurrency(Arr::get($totales, 'total_izquierdo', 0)) }}</td>
                </tr>
                <tr>
                    <td class="label">Comisión promotora</td>
                    <td class="value">{{ $formatCurrency(Arr::get($cierres, 'comisiones_prom', 0)) }}</td>
                </tr>
                <tr>
                    <td class="label">Comisión supervisor</td>
                    <td class="value">{{ $formatCurrency(Arr::get($cierres, 'comisiones_superv', 0)) }}</td>
                </tr>
                <tr>
                    <td class="label">Otros ingresos</td>
                    <td class="value">{{ $formatCurrency(Arr::get($cierres, 'otros', 0)) }}</td>
                </tr>
                <tr>
                    <td class="label">Inversión</td>
                    <td class="value">{{ $formatCurrency(Arr::get($cierres, 'inversion', 0)) }}</td>
                </tr>
            </tbody>
        </table>
        <p class="small muted">
            El total final se compone de desembolsos, recréditos y fondos menos comisiones, más otros ingresos e inversión
            registrada en el periodo.
        </p>
    </section>
</body>
</html>
