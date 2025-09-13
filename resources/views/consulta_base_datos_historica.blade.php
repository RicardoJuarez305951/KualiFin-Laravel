{{-- resources/views/consulta_base_datos_historica.blade.php --}}
<x-layouts.authenticated title="Consulta Base de Datos Histórica">
    <div class="mx-auto max-w-7xl py-8 space-y-6">

        {{-- ======= ESTILOS LIGHT & COOL ======= --}}
        <style>
            :root{
                --kf-bg: #f7fafc;          /* base muy clara */
                --kf-card: #ffffff;        /* tarjetas blancas */
                --kf-border: #e5e7eb;      /* gris claro */
                --kf-text: #0f172a;        /* slate-900 */
                --kf-text-muted: #475569;  /* slate-600 */
                --kf-accent: #2563eb;      /* blue-600 */
                --kf-accent-50:#eff6ff;    /* blue-50 */
                --kf-accent-100:#dbeafe;   /* blue-100 */
                --kf-emerald:#059669;      /* emerald-600 */
                --kf-fuchsia:#a21caf;      /* fuchsia-700 */
                --kf-surface:#f8fafc;      /* slate-50 */
            }
            body{ background: var(--kf-bg); }

            /* Card simple, ligera */
            .kf-card{
                background: var(--kf-card);
                border: 1px solid var(--kf-border);
                border-radius: 14px;
                box-shadow: 0 4px 16px rgba(15,23,42,.06);
            }
            .kf-inner{ position: relative; }

            /* Inputs light */
            .kf-input{
                width: 100%;
                border-radius: 12px;
                border: 1px solid var(--kf-border);
                background: #fff;
                padding: .625rem .75rem;
                font-size: .95rem;
                color: var(--kf-text);
                transition: box-shadow .15s, border-color .15s;
            }
            .kf-input::placeholder{ color: #94a3b8; }
            .kf-input:focus{
                outline: none;
                border-color: var(--kf-accent-100);
                box-shadow: 0 0 0 4px var(--kf-accent-50);
            }

            /* Botones */
            .kf-btn{
                display:inline-flex; align-items:center; gap:.5rem;
                padding:.55rem .9rem; border-radius:12px;
                font-weight: 600; font-size:.9rem;
                border:1px solid transparent; transition: all .2s ease;
            }
            .kf-btn.primary{
                color:#fff; background: var(--kf-accent);
                box-shadow: 0 6px 14px rgba(37,99,235,.18);
            }
            .kf-btn.primary:hover{ filter: brightness(1.05); transform: translateY(-1px); }
            .kf-btn.ghost{
                color: var(--kf-text); background: #fff; border-color: var(--kf-border);
            }
            .kf-btn.ghost:hover{ background: var(--kf-surface); }

            .kf-btn.emerald{ background: var(--kf-emerald); color:#fff; }
            .kf-btn.fuchsia{ background: var(--kf-fuchsia); color:#fff; }

            /* Chips suaves */
            .chip{
                display:inline-flex; align-items:center; gap:.35rem;
                padding:.25rem .6rem; border-radius:999px; font-weight:700; font-size:.72rem;
                background: var(--kf-accent-50); color:#1d4ed8; border:1px solid var(--kf-accent-100);
            }
            .chip.gray{
                background:#f1f5f9; color:#334155; border-color:#e2e8f0;
            }
            .chip.purple{
                background:#faf5ff; color:#7c3aed; border-color:#ede9fe;
            }

            /* Tablas claras */
            .kf-table{ width:100%; border-collapse: separate; border-spacing:0; }
            .kf-table thead th{
                position: sticky; top: 0; z-index: 5;
                background: #fff;
                border-bottom: 1px solid var(--kf-border);
                font-size:.72rem; letter-spacing:.04em;
                color:#334155; text-transform: uppercase;
            }
            .kf-th{ padding:.75rem .75rem; text-align:left; font-weight:800; }
            .kf-td{ padding:.6rem .75rem; color: var(--kf-text); border-bottom:1px solid var(--kf-border); }
            .kf-row:nth-child(odd){ background: #fcfdff; } /* zebra ligerita */
            .kf-row:hover{ background:#f5faff; }         /* hover frío */

            .money{ font-variant-numeric: tabular-nums; text-align: right; }
            .mono{ font-variant-numeric: tabular-nums; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; color:#0f172a; }

            /* Scrollbar limpio */
            .kf-scroll{ overflow-x: auto; }
            .kf-scroll::-webkit-scrollbar{ height:10px; width:10px; }
            .kf-scroll::-webkit-scrollbar-thumb{ background:#cbd5e1; border-radius:999px; }
            .kf-scroll::-webkit-scrollbar-track{ background:#eef2f7; }

            .section-head{
                padding: 1rem 1.25rem;
                border-bottom:1px solid var(--kf-border);
                display:flex; align-items:center; justify-content:space-between;
            }

            .pager{
                display:flex; align-items:center; justify-content:space-between;
                padding: .75rem 1rem; border-top:1px solid var(--kf-border);
                background:#fff;
            }
        </style>

        {{-- ======= TÍTULO ======= --}}
        <div class="kf-card p-6">
            <div class="kf-inner">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-black tracking-tight" style="color:var(--kf-text)">
                            Consulta Base de Datos Histórica
                        </h1>
                        <p class="text-sm" style="color:var(--kf-text-muted)">Búsquedas ligeras, lectura clara y fría para financiera.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="chip">Histórica</span>
                        <span class="chip gray">Solo lectura</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======= HELPERS PHP ======= --}}
        @php
            $formatDate = function ($value, $format = 'd/m/Y') {
                if ($value instanceof \Carbon\Carbon) return $value->format($format);
                if (!is_null($value) && $value !== '') {
                    try { $dt = \Carbon\Carbon::createFromFormat('d/m/Y', trim((string)$value)); return $dt ? $dt->format($format) : (string)$value; }
                    catch (\Throwable $e) { return (string)$value; }
                }
                return '';
            };
            $formatMoney = function ($value, $decimals = 2) {
                if (is_numeric($value)) return '$' . number_format((float) $value, $decimals);
                if (is_string($value)) { $clean = str_replace(['$', ',', ' '], '', $value); if (is_numeric($clean)) return '$' . number_format((float) $clean, $decimals); }
                return (string) $value;
            };
        @endphp

        {{-- ======= TOOLBAR GLOBAL ======= --}}
        {{-- <div class="kf-card p-5">
            <div class="kf-inner">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1 sm:col-span-2">
                        <label class="text-xs font-semibold" style="color:var(--kf-text-muted)">Buscar</label>
                        <input type="text" name="q" value="{{ (string) ($filters['q'] ?? '') }}"
                               placeholder="Nombre, hoja, observación, etc."
                               class="kf-input">
                    </div>
                    <div class="flex items-end gap-3">
                        <button type="submit" class="kf-btn primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 -ml-1" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4a6 6 0 104.472 10.028l4.25 4.25a1 1 0 101.415-1.415l-4.25-4.25A6 6 0 0010 4z"/></svg>
                            Consultar
                        </button>
                        <a href="{{ route('consulta.historica') ?? url()->current() }}" class="kf-btn ghost">Limpiar</a>
                    </div>
                </form>
            </div>
        </div> --}}

        {{-- ======= BUSCADORES ESPECÍFICOS ======= --}}
        <div class="grid md:grid-cols-2 gap-6">
            {{-- Deudores --}}
            <div class="kf-card p-5">
                <div class="kf-inner">
                    <form method="GET" action="{{ route('consulta.deudores') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-xs font-semibold" style="color:var(--kf-text-muted)">Cliente</label>
                            <input type="text" name="cliente" placeholder="Cliente..." class="kf-input">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="kf-btn emerald">Consultar Deudores</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Historial --}}
            <div class="kf-card p-5">
                <div class="kf-inner">
                    <form method="GET" action="{{ route('consulta.historial') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-xs font-semibold" style="color:var(--kf-text-muted)">Cliente</label>
                            <input type="text" name="cliente" value="{{ (string) ($filters['cliente'] ?? '') }}"
                                   placeholder="Cliente..." class="kf-input">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="kf-btn fuchsia">Consultar Historial</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ==================== RESULTADOS: DEUDORES ==================== --}}
        @isset($deudores)
            <div class="kf-card">
                <div class="section-head">
                    <h2 class="text-lg font-bold" style="color:var(--kf-text)">Deudores</h2>
                    <span class="chip gray">{{ count($deudores) }} resultado(s)</span>
                </div>

                @if(count($deudores))
                    <div class="kf-inner p-0">
                        <div class="kf-scroll">
                            <table class="kf-table text-sm">
                                <thead>
                                    <tr>
                                        <th class="kf-th">
                                            <span class="sr-only">
                                                <pre class="hidden md:block text-[10px] text-slate-500 px-3 py-2">{{ json_encode($deudores[0] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                            </span>
                                        </th>
                                        <th class="kf-th">Fecha Préstamo</th>
                                        <th class="kf-th">Cliente</th>
                                        <th class="kf-th">Promotora</th>
                                        <th class="kf-th">Alerta</th>
                                        <th class="kf-th" style="text-align:right">Deuda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deudores as $i => $deudor)
                                        <tr class="kf-row">
                                            <td class="kf-td mono">{{ $formatDate($deudor['fecha_prestamo'] ?? null) }}</td>
                                            <td class="kf-td font-semibold">{{ (string) ($deudor['cliente'] ?? '') }}</td>
                                            <td class="kf-td">{{ (string) ($deudor['promotora'] ?? '') }}</td>
                                            <td class="kf-td">{{ (string) ($deudor['alerta'] ?? '') }}</td>
                                            <td class="kf-td money">{{ $formatMoney($deudor['deuda'] ?? null) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="kf-inner p-4">
                        <p class="text-sm" style="color:var(--kf-text-muted)">No se encontraron deudores.</p>
                    </div>
                @endif
            </div>
        @endisset

        {{-- ==================== RESULTADOS: HISTORIAL ==================== --}}
        @isset($historial)
            @php
                $fechaPagoHeaders = collect($historial)->flatMap(fn ($h) => array_keys($h['pagos'] ?? []))->unique()->sort()->values()->all();
            @endphp

            <div class="kf-card">
                <div class="section-head">
                    <h2 class="text-lg font-bold" style="color:var(--kf-text)">Historial de Pagos</h2>
                    <div class="flex items-center gap-2">
                        <span class="chip gray">{{ count($historial) }} crédito(s)</span>
                        <span class="chip purple">{{ count($fechaPagoHeaders) }} fecha(s)</span>
                    </div>
                </div>

                @if(count($historial))
                    <div class="kf-inner p-3">
                        <details class="mb-2">
                            <summary class="text-xs cursor-pointer" style="color:var(--kf-text-muted)">Ver muestra JSON</summary>
                            <pre class="text-[11px] text-slate-700 p-2 bg-slate-50 rounded-lg border border-slate-200 overflow-x-auto">{{ json_encode(['count' => count($historial), 'sample' => $historial[0] ?? null], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>

                        <div class="kf-scroll">
                            <table class="kf-table text-sm">
                                <thead>
                                    <tr>
                                        <th class="kf-th">Fecha Crédito</th>
                                        <th class="kf-th">Nombre</th>
                                        <th class="kf-th" style="text-align:right">Préstamo</th>
                                        <th class="kf-th" style="text-align:right">Abono</th>
                                        <th class="kf-th" style="text-align:right">Debe</th>
                                        <th class="kf-th">Obs.</th>
                                        @foreach($fechaPagoHeaders as $fecha)
                                            <th class="kf-th" style="text-align:right">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($historial as $h)
                                        @php $c = $h['cliente']; @endphp
                                        <tr class="kf-row">
                                            <td class="kf-td mono">{{ $formatDate($c['fecha_credito'] ?? null) }}</td>
                                            <td class="kf-td font-semibold">{{ (string) ($c['nombre'] ?? '') }}</td>
                                            <td class="kf-td money">{{ $formatMoney($c['prestamo'] ?? null) }}</td>
                                            <td class="kf-td money">{{ $formatMoney($c['abono'] ?? null) }}</td>
                                            <td class="kf-td money">{{ $formatMoney($c['debe'] ?? null) }}</td>
                                            <td class="kf-td max-w-[16rem] truncate" title="{{ (string) ($c['observaciones'] ?? '') }}">
                                                {{ (string) ($c['observaciones'] ?? '') }}
                                            </td>
                                            @foreach($fechaPagoHeaders as $fecha)
                                                <td class="kf-td money">{{ $formatMoney($h['pagos'][$fecha] ?? null) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="kf-inner p-4">
                        <p class="text-sm" style="color:var(--kf-text-muted)">No se encontraron registros.</p>
                    </div>
                @endif
            </div>
        @endisset

        {{-- ==================== RESULTADOS: MULTI-HOJA ==================== --}}
        @if($results !== null)
            @php
                $contextHeaders = collect($results)->flatMap(fn ($r) => array_keys($r['context'] ?? []))->unique()->take($context)->all();
            @endphp

            <div class="kf-card">
                <div class="section-head">
                    <h2 class="text-lg font-bold" style="color:var(--kf-text)">Resultados Multi-Hoja</h2>
                    <span class="chip gray">{{ count($results) }} fila(s)</span>
                </div>

                @if(count($results))
                    <div class="kf-inner p-0">
                        <div class="kf-scroll">
                            <table class="kf-table text-sm">
                                <thead>
                                    <tr>
                                        <th class="kf-th">Hoja</th>
                                        <th class="kf-th">Valor</th>
                                        @foreach($contextHeaders as $header)
                                            <th class="kf-th">{{ (string) $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results as $row)
                                        <tr class="kf-row">
                                            <td class="kf-td font-semibold">{{ (string) $row['sheet'] }}</td>
                                            <td class="kf-td">{{ (string) $row['match_value'] }}</td>
                                            @foreach($contextHeaders as $header)
                                                <td class="kf-td">{{ (string) ($row['context'][$header] ?? '') }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="kf-inner p-4">
                        <p class="text-sm" style="color:var(--kf-text-muted)">No se encontraron registros.</p>
                    </div>
                @endif
            </div>

        {{-- ==================== UNA SOLA HOJA ==================== --}}
        @elseif($data && $data['rows'])
            <div class="kf-card">
                <div class="section-head">
                    <h2 class="text-lg font-bold" style="color:var(--kf-text)">Hoja: {{ (string) ($current ?? '—') }}</h2>
                    <div class="flex items-center gap-2">
                        <span class="chip gray">{{ $data['total'] }} registro(s)</span>
                        <span class="chip">Límite {{ $limit }}</span>
                    </div>
                </div>

                <div class="kf-inner p-0">
                    <div class="kf-scroll">
                        <table class="kf-table text-sm">
                            <thead>
                                <tr>
                                    @foreach($data['headers'] as $header)
                                        <th class="kf-th">{{ (string) ucfirst(str_replace('_',' ', $header)) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['rows'] as $row)
                                    <tr class="kf-row">
                                        @foreach($data['headers'] as $header)
                                            @php
                                                $h = strtolower($header);
                                                $val = $row[$header] ?? null;
                                                $isDate = str_contains($h, 'fecha') || in_array($h, ['fecha','date','fecha_prestamo','fecha_pago','fecha_inicio','fecha_fin']);
                                                $isMoney = str_contains($h, 'monto') || str_contains($h, 'deuda') || str_contains($h, 'importe') || str_contains($h, 'saldo') || str_contains($h, 'abono') || str_contains($h, 'pago') || str_contains($h, 'inversion');
                                            @endphp
                                            <td class="kf-td {{ $isMoney ? 'money' : '' }} {{ $isDate ? 'mono' : '' }}">
                                                @if($isDate)
                                                    {{ $formatDate($val) }}
                                                @elseif($isMoney)
                                                    {{ $formatMoney($val) }}
                                                @else
                                                    {{ is_scalar($val) ? (string) $val : '' }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    <div class="pager">
                        <div class="text-xs" style="color:var(--kf-text-muted)">
                            Mostrando <span class="font-semibold" style="color:var(--kf-text)">{{ $offset + 1 }}</span> –
                            <span class="font-semibold" style="color:var(--kf-text)">{{ min($offset + $limit, $data['total']) }}</span>
                            de <span class="font-semibold" style="color:var(--kf-text)">{{ $data['total'] }}</span>
                        </div>
                        <div class="flex gap-2">
                            @if($offset > 0)
                                <a href="{{ request()->fullUrlWithQuery(['offset' => max(0, $offset - $limit)]) }}" class="kf-btn ghost">Anterior</a>
                            @endif
                            @if($offset + $limit < $data['total'])
                                <a href="{{ request()->fullUrlWithQuery(['offset' => $offset + $limit]) }}" class="kf-btn primary">Siguiente</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        @elseif($current)
            <div class="kf-card p-5">
                <div class="kf-inner">
                    <p class="text-sm" style="color:var(--kf-text-muted)">No se encontraron registros.</p>
                </div>
            </div>
        @endif
    </div>
</x-layouts.authenticated>
