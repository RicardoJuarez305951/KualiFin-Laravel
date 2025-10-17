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
    $validadorNombre = Arr::get($contexto, 'validador.nombre', Arr::get($base, 'validadorNombre', ''));
    $fechaReporte = Arr::get($contexto, 'fecha_reporte');
    $semanaVenta = Arr::get($contexto, 'semana_venta');
    $rango = Arr::get($contexto, 'rango', []);

    $fallo       = Arr::get($listas, 'fallo', []);
    $prestamos   = Arr::get($listas, 'prestamos', []);
    $desembolsos = Arr::get($listas, 'desembolsos', []);
    $recreditos  = Arr::get($listas, 'recreditos', []);
    $adelantos   = Arr::get($listas, 'adelantos', []);
    $recuperacion = Arr::get($listas, 'recuperacion', []);
    $cobranzaDias = Arr::get($cobranza, 'dias', []);
    $firmas = $firmas ?? [];
    $firmaSupervisor = trim((string) Arr::get($firmas, 'supervisor', ''));
    $firmaPromotor = trim((string) Arr::get($firmas, 'promotor', ''));
    $firmaValidador = trim((string) Arr::get($firmas, 'validador', ''));

    $formatCurrency = static function ($value): string {
        $number = is_numeric($value) ? (float) $value : 0.0; return '$' . number_format($number, 2, '.', ',');
    };
    $formatDate = static function ($value): string {
        if ($value instanceof \Carbon\CarbonInterface) return $value->format('d/m/Y');
        if (is_string($value) && trim($value) !== '') return $value;
        return '---';
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reporte</title>
<style>
    *{ box-sizing:border-box; }
    html,body{ margin:0; padding:0; }
    body{ font-family:"DejaVu Sans", Arial, sans-serif; font-size:9px; line-height:1.12; margin:8px; color:#111; }
    .ttl{ font-weight:700; font-size:10px; text-transform:uppercase; margin:0 0 2px 0; }
    .ttlh1{ font-weight:800; font-size:12px; text-transform:uppercase; margin:0 0 4px 0; }
    table{ width:100%; border-collapse:collapse; }
    th,td{ border:0.4px solid #000; padding:2px 3px; vertical-align:top; }
    th{ font-size:8px; font-weight:700; text-transform:uppercase; }
    .text-right{ text-align:right; }
    .text-center{ text-align:center; }
    .muted{ color:#444; }
    .grid{ display:table; width:100%; border:0.4px solid #000; border-collapse:collapse; margin:0 0 6px 0; }
    .grid-row{ display:table-row; }
    .grid-cell{ display:table-cell; padding:3px 4px; border-right:0.4px solid #000; border-bottom:0.4px solid #000; }
    .grid-cell:last-child{ border-right:none; }
    .grid-row:last-child .grid-cell{ border-bottom:none; }
    .two-col{ width:100%; table-layout:fixed; border:none; border-collapse:separate; }
    .two-col td{ width:50%; vertical-align:top; padding:0 0 6px 6px; border:none; }
    .two-col td:first-child{ padding-left:0; }
    .card{ page-break-inside:avoid; }
    .inner{ width:100%; border-collapse:collapse; margin:0; }
    .inner th,.inner td{ border:0.4px solid #000; padding:2px 3px; }
    .summary-table td.label{ width:65%; }
    .summary-table td.value{ width:35%; text-align:right; font-weight:700; }
    .signature-table{ width:100%; border-collapse:collapse; }
    .signature-table td{ border:0.4px solid #000; padding:6px 5px; text-align:center; width:33.33%; vertical-align:top; }
    .signature-wrapper{ min-height:38px; display:block; margin-bottom:6px; }
    .signature-wrapper img{ max-width:100%; max-height:38px; }
    .signature-placeholder{ width:100%; border-bottom:0.4px solid #000; height:38px; }
    .signature-name{ font-weight:700; font-size:9px; margin-bottom:2px; }
    .signature-role{ font-size:8px; text-transform:uppercase; color:#444; }
</style>
</head>
<body>
    <div class="ttlh1">Reporte de desembolso</div>
    <div class="grid">
        <div class="grid-row">
            <div class="grid-cell"><b>Promotora:</b> {{ $promotorNombre !== '' ? $promotorNombre : '---' }}</div>
            <div class="grid-cell"><b>Supervisor:</b> {{ $supervisorNombre !== '' ? $supervisorNombre : '---' }}</div>
            <div class="grid-cell"><b>Ejecutivo:</b> {{ $ejecutivoNombre !== '' ? $ejecutivoNombre : '---' }}</div>
            <div class="grid-cell"><b>Fecha:</b> {{ $formatDate($fechaReporte) }}</div>
        </div>
        <div class="grid-row">
            <div class="grid-cell"><b>Semana:</b> {{ $semanaVenta ?: '---' }}</div>
            <div class="grid-cell"><b>Rango:</b> {{ $formatDate(Arr::get($rango,'inicio')) }}–{{ $formatDate(Arr::get($rango,'fin')) }}</div>
            <div class="grid-cell"><b>Cartera real:</b> {{ $formatCurrency(Arr::get($totales,'cartera_real',0)) }}</div>
            <div class="grid-cell"><b>Total final:</b> {{ $formatCurrency(Arr::get($totales,'total_final',0)) }}</div>
        </div>
    </div>

    {{-- Fila 1: Fallo vs Préstamos (mismo alto) --}}
    @php
        $falloItems = Arr::get($fallo,'items',[]);
        $prestamosItems = is_array($prestamos) ? $prestamos : [];
        $pair1Max = max(count($falloItems), count($prestamosItems));
    @endphp
    <table class="two-col">
        <tr>
            <td>
                <div class="card">
                    <div class="ttl">Fallo</div>
                    <table class="inner">
                        <thead>
                        <tr>
                            <th style="width:18%">Fecha</th>
                            <th>Cliente</th>
                            <th style="width:20%" class="text-right">Monto pend.</th>
                        </tr>
                        </thead>
                        <tbody>
                        @for($i=0;$i<$pair1Max;$i++)
                            @php $it = $falloItems[$i] ?? null; @endphp
                            <tr>
                                <td>{{ $it ? $formatDate(Arr::get($it,'fecha_texto')) : '---' }}</td>
                                <td>{{ $it ? Arr::get($it,'cliente','Sin cliente') : 'N/A' }}</td>
                                <td class="text-right">{{ $it ? $formatCurrency(Arr::get($it,'monto',0)) : 'N/A' }}</td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="2" class="text-right"><b>Total</b></td>
                            <td class="text-right"><b>{{ $formatCurrency(Arr::get($fallo,'total',Arr::get($totales,'fallo',0))) }}</b></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="ttl">Préstamos aprobados</div>
                    <table class="inner">
                        <thead>
                        <tr>
                            <th style="width:18%">Fecha</th>
                            <th>Cliente</th>
                            <th style="width:18%" class="text-right">Monto</th>
                        </tr>
                        </thead>
                        <tbody>
                        @for($i=0;$i<$pair1Max;$i++)
                            @php $it = $prestamosItems[$i] ?? null; @endphp
                            <tr>
                                <td>{{ $it ? $formatDate(Arr::get($it,'fecha_texto')) : '---' }}</td>
                                <td>{{ $it ? Arr::get($it,'cliente','Sin cliente') : 'N/A' }}</td>
                                <td class="text-right">{{ $it ? $formatCurrency(Arr::get($it,'monto',0)) : 'N/A' }}</td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="2" class="text-right"><b>Total</b></td>
                            <td class="text-right"><b>{{ $formatCurrency(Arr::get($totales,'prestamos',0)) }}</b></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Fila 2: Desembolsos vs Recréditos (mismo alto) --}}
    @php
        $desItems = is_array($desembolsos) ? $desembolsos : [];
        $recItems = Arr::get($recreditos,'items',[]);
        $pair2Max = max(count($desItems), count($recItems));
    @endphp
    <table class="two-col">
        <tr>
            <td>
                <div class="card">
                    <div class="ttl">Desembolsos</div>
                    <table class="inner">
                        <thead>
                        <tr>
                            <th style="width:18%">Fecha</th>
                            <th>Cliente</th>
                            <th style="width:18%" class="text-right">Monto</th>
                        </tr>
                        </thead>
                        <tbody>
                        @for($i=0;$i<$pair2Max;$i++)
                            @php $it = $desItems[$i] ?? null; @endphp
                            <tr>
                                <td>{{ $it ? $formatDate(Arr::get($it,'fecha_texto')) : '---' }}</td>
                                <td>{{ $it ? Arr::get($it,'cliente','Sin cliente') : 'N/A' }}</td>
                                <td class="text-right">{{ $it ? $formatCurrency(Arr::get($it,'monto',0)) : 'N/A' }}</td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="2" class="text-right"><b>Total</b></td>
                            <td class="text-right"><b>{{ $formatCurrency(Arr::get($totales,'desembolso',0)) }}</b></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="ttl">Recréditos</div>
                    <table class="inner">
                        <thead>
                        <tr>
                            <th style="width:18%">Fecha</th>
                            <th>Cliente</th>
                            <th style="width:18%" class="text-right">Nuevo</th>
                            <th style="width:18%" class="text-right">Anterior</th>
                            <th style="width:18%" class="text-right">Saldo post.</th>
                        </tr>
                        </thead>
                        <tbody>
                        @for($i=0;$i<$pair2Max;$i++)
                            @php $it = $recItems[$i] ?? null; @endphp
                            <tr>
                                <td>{{ $it ? $formatDate(Arr::get($it,'fecha_texto')) : '---' }}</td>
                                <td>{{ $it ? Arr::get($it,'cliente','Sin cliente') : 'N/A' }}</td>
                                <td class="text-right">{{ $it ? $formatCurrency(Arr::get($it,'monto_nuevo',0)) : 'N/A' }}</td>
                                <td class="text-right">{{ $it ? $formatCurrency(Arr::get($it,'monto_anterior',0)) : 'N/A' }}</td>
                                <td class="text-right">{{ $it ? $formatCurrency(Arr::get($it,'saldo_post',0)) : 'N/A' }}</td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="2" class="text-right"><b>Total nuevo</b></td>
                            <td class="text-right"><b>{{ $formatCurrency(Arr::get($recreditos,'total_nuevo',0)) }}</b></td>
                            <td class="text-right"><b>{{ $formatCurrency(Arr::get($recreditos,'total_anterior',0)) }}</b></td>
                            <td class="text-right"><b>{{ $formatCurrency(Arr::get($recreditos,'total',Arr::get($totales,'recreditos',0))) }}</b></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Fila 3: Adelantos/Recuperación (izq) vs Cobranza semanal (der) con alto sincronizado --}}
    @php
        $adelItems = Arr::get($adelantos,'items',[]);
        $recupItems = Arr::get($recuperacion,'items',[]);
        $leftRows = max(count($adelItems), count($recupItems));   // ya cruzado
        $rightRows = count($cobranzaDias);
        $pair3Max = max($leftRows, $rightRows);
    @endphp
    <table class="two-col">
        <tr>
            <td>
                <div class="card">
                    <div class="ttl">Adelantos y recuperación</div>
                    <table class="inner">
                        <thead>
                        <tr>
                            <th style="width:18%">Fecha</th>
                            <th>Cliente</th>
                            <th style="width:18%" class="text-right">Adelanto</th>
                            <th style="width:18%" class="text-right">Recuperación</th>
                        </tr>
                        </thead>
                        <tbody>
                        @for($i=0;$i<$pair3Max;$i++)
                            @php
                                $a = $adelItems[$i] ?? null;
                                $r = $recupItems[$i] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $a ? $formatDate(Arr::get($a,'fecha_texto')) : ($r ? $formatDate(Arr::get($r,'fecha_texto')) : '---') }}</td>
                                <td>{{ $a ? Arr::get($a,'cliente','Sin cliente') : ($r ? Arr::get($r,'cliente','Sin cliente') : 'N/A') }}</td>
                                <td class="text-right">{{ $a ? $formatCurrency(Arr::get($a,'monto',0)) : 'N/A' }}</td>
                                <td class="text-right">{{ $r ? $formatCurrency(Arr::get($r,'monto',0)) : 'N/A' }}</td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="2" class="text-right"><b>Totales</b></td>
                            <td class="text-right"><b>{{ $formatCurrency(Arr::get($adelantos,'total',Arr::get($totales,'adelantos',0))) }}</b></td>
                            <td class="text-right"><b>{{ $formatCurrency(Arr::get($recuperacion,'total',Arr::get($totales,'recuperacion',0))) }}</b></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="ttl">Cobranza semanal</div>
                    <table class="inner">
                        <thead>
                        <tr>
                            <th>Día</th>
                            <th style="width:18%">Fecha</th>
                            <th style="width:20%" class="text-right">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @for($i=0;$i<$pair3Max;$i++)
                            @php $dia = $cobranzaDias[$i] ?? null; @endphp
                            <tr>
                                <td>{{ $dia ? Arr::get($dia,'dia','---') : '---' }}</td>
                                <td>{{ $dia ? $formatDate(Arr::get($dia,'fecha_texto')) : '---' }}</td>
                                <td class="text-right">{{ $dia ? $formatCurrency(Arr::get($dia,'total',0)) : 'N/A' }}</td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="2" class="text-right"><b>Total</b></td>
                            <td class="text-right"><b>{{ $formatCurrency(Arr::get($totales,'cobranza',0)) }}</b></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Fila 4 (Resumen ocupa 2 columnas) --}}
    <table class="two-col">
        <tr>
            <td colspan="2">
                <div class="card">
                    <div class="ttl">Resumen de totales</div>
                    <table class="inner summary-table">
                        <tbody>
                        <tr><td class="label">Fondo de ahorro</td><td class="value">{{ $formatCurrency(Arr::get($totales,'fondo_ahorro',Arr::get($cierres,'fondo_ahorro',0))) }}</td></tr>
                        <tr><td class="label">Total izquierdo</td><td class="value">{{ $formatCurrency(Arr::get($totales,'total_izquierdo',0)) }}</td></tr>
                        <tr><td class="label">Comisión promotora</td><td class="value">{{ $formatCurrency(Arr::get($cierres,'comisiones_prom',0)) }}</td></tr>
                        <tr><td class="label">Comisión supervisor</td><td class="value">{{ $formatCurrency(Arr::get($cierres,'comisiones_superv',0)) }}</td></tr>
                        <tr><td class="label">Otros ingresos</td><td class="value">{{ $formatCurrency(Arr::get($cierres,'otros',0)) }}</td></tr>
                        <tr><td class="label">Inversión</td><td class="value">{{ $formatCurrency(Arr::get($cierres,'inversion',0)) }}</td></tr>
                        </tbody>
                    </table>
                    <div class="muted" style="margin-top:2px;">
                        <b>Nota:</b> Total final = desembolsos + recréditos + fondos − comisiones + otros + inversión.
                    </div>
                </div>
            </td>
        </tr>
    </table>

    @php
        $supervisorNombreImp = $supervisorNombre !== '' ? $supervisorNombre : '---';
        $promotorNombreImp = $promotorNombre !== '' ? $promotorNombre : '---';
        $validadorNombreImp = $validadorNombre !== '' ? $validadorNombre : ($ejecutivoNombre !== '' ? $ejecutivoNombre : '---');
    @endphp
    <div class="card">
        <div class="ttl">Firmas</div>
        <table class="signature-table">
            <tbody>
            <tr>
                <td>
                    <div class="signature-wrapper">
                        @if($firmaSupervisor !== '')
                            <img src="{{ $firmaSupervisor }}" alt="Firma supervisor">
                        @else
                            <div class="signature-placeholder"></div>
                        @endif
                    </div>
                    <div class="signature-name">{{ $supervisorNombreImp }}</div>
                    <div class="signature-role">Supervisor</div>
                </td>
                <td>
                    <div class="signature-wrapper">
                        @if($firmaPromotor !== '')
                            <img src="{{ $firmaPromotor }}" alt="Firma promotora">
                        @else
                            <div class="signature-placeholder"></div>
                        @endif
                    </div>
                    <div class="signature-name">{{ $promotorNombreImp }}</div>
                    <div class="signature-role">Promotora</div>
                </td>
                <td>
                    <div class="signature-wrapper">
                        @if($firmaValidador !== '')
                            <img src="{{ $firmaValidador }}" alt="Firma validador">
                        @else
                            <div class="signature-placeholder"></div>
                        @endif
                    </div>
                    <div class="signature-name">{{ $validadorNombreImp }}</div>
                    <div class="signature-role">Validador</div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
