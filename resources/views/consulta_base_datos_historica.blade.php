<x-layouts.authenticated title="Consulta Base de Datos Histórica">
    <div class="max-w-6xl mx-auto py-8 space-y-6">

        <h1 class="text-2xl font-bold text-gray-800">
            Consulta Base de Datos Histórica
        </h1>

        {{-- Helpers inline --}}
        @php
            $formatDate = function ($value, $format = 'd/m/Y') {
                if ($value instanceof \Carbon\Carbon) {
                    return $value->format($format);
                }

                if (!is_null($value) && $value !== '') {
                    try {
                        // Forzar a interpretar como Día/Mes/Año
                        $dt = \Carbon\Carbon::createFromFormat('d/m/Y', trim((string)$value));
                        return $dt ? $dt->format($format) : (string)$value;
                    } catch (\Throwable $e) {
                        return (string)$value;
                    }
                }

                return '';
            };

            $formatMoney = function ($value, $decimals = 2) {
                if (is_numeric($value)) {
                    return '$' . number_format((float) $value, $decimals);
                }
                if (is_string($value)) {
                    $clean = str_replace(['$', ',', ' '], '', $value);
                    if (is_numeric($clean)) {
                        return '$' . number_format((float) $clean, $decimals);
                    }
                }
                return (string) $value;
            };
        @endphp


        {{-- Buscador general --}}
        <form method="GET" class="flex items-center gap-4">
            <div>
                <label class="block text-sm font-medium">Buscar</label>
                <input
                    type="text"
                    name="q"
                    value="{{ (string) ($filters['q'] ?? '') }}"
                    class="border rounded p-2"
                    placeholder="Buscar..."
                />
            </div>

            <div class="pt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                    Consultar
                </button>
            </div>
        </form>

        {{-- Buscador deudores --}}
        <form method="GET" action="{{ route('consulta.deudores') }}" class="flex items-center gap-4">
            <div>
                <label class="block text-sm font-medium">Cliente</label>
                <input type="text" name="cliente" class="border rounded p-2" placeholder="Cliente..." />
            </div>

            <div class="pt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                    Consultar Deudores
                </button>
            </div>
        </form>

        {{-- Buscador historial --}}
        <form method="GET" action="{{ route('consulta.historial') }}" class="flex items-center gap-4">
            <div>
                <label class="block text-sm font-medium">Cliente</label>
                <input
                    type="text"
                    name="cliente"
                    value="{{ (string) ($filters['cliente'] ?? '') }}"
                    class="border rounded p-2"
                    placeholder="Cliente..."
                />
            </div>

            <div class="pt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                    Consultar Historial
                </button>
            </div>
        </form>

        {{-- Resultados deudores --}}
        @isset($deudores)
            @if(count($deudores))
                <div class="overflow-x-auto border rounded">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <pre class="text-xs text-gray-500">{{ json_encode($deudores[0] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>

                                <th class="px-3 py-2 text-left font-semibold">Fecha Préstamo</th>
                                <th class="px-3 py-2 text-left font-semibold">Cliente</th>
                                <th class="px-3 py-2 text-left font-semibold">Promotora</th>
                                <th class="px-3 py-2 text-left font-semibold">Deuda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($deudores as $deudor)
                                <tr>
                                    <td class="px-3 py-2">{{ $formatDate($deudor['fecha_prestamo'] ?? null) }}</td>
                                    <td class="px-3 py-2">{{ (string) ($deudor['cliente'] ?? '') }}</td>
                                    <td class="px-3 py-2">{{ (string) ($deudor['promotora'] ?? '') }}</td>
                                    <td class="px-3 py-2">{{ $formatMoney($deudor['deuda'] ?? null) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600">No se encontraron deudores.</p>
            @endif
        @endisset

        {{-- Resultados historial --}}
        @isset($historial)
            @php
                $fechaPagoHeaders = collect($historial)
                    ->flatMap(fn ($h) => array_keys($h['pagos'] ?? []))
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();
            @endphp
            @if(count($historial))
                <div class="overflow-x-auto border rounded">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Fecha Crédito</th>
                                <th class="px-3 py-2 text-left font-semibold">Nombre</th>
                                <th class="px-3 py-2 text-left font-semibold">Préstamo</th>
                                <th class="px-3 py-2 text-left font-semibold">Abono</th>
                                <th class="px-3 py-2 text-left font-semibold">Debe</th>
                                <th class="px-3 py-2 text-left font-semibold">Observaciones</th>
                                @foreach($fechaPagoHeaders as $fecha)
                                    <th class="px-3 py-2 text-left font-semibold">
                                        {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($historial as $h)
                                @php $c = $h['cliente']; @endphp
                                <tr>
                                    <td class="px-3 py-2">{{ $formatDate($c['fecha_credito'] ?? null) }}</td>
                                    <td class="px-3 py-2">{{ (string) ($c['nombre'] ?? '') }}</td>
                                    <td class="px-3 py-2">{{ $formatMoney($c['prestamo'] ?? null) }}</td>
                                    <td class="px-3 py-2">{{ $formatMoney($c['abono'] ?? null) }}</td>
                                    <td class="px-3 py-2">{{ $formatMoney($c['debe'] ?? null) }}</td>
                                    <td class="px-3 py-2">{{ (string) ($c['observaciones'] ?? '') }}</td>
                                    @foreach($fechaPagoHeaders as $fecha)
                                        <td class="px-3 py-2">{{ $formatMoney($h['pagos'][$fecha] ?? null) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600">No se encontraron registros.</p>
            @endif
        @endisset

        {{-- Resultados multi-hoja --}}
        @if($results !== null)
            @php
                $contextHeaders = collect($results)
                    ->flatMap(fn ($r) => array_keys($r['context'] ?? []))
                    ->unique()
                    ->take($context)
                    ->all();
            @endphp

            @if(count($results))
                <div class="overflow-x-auto border rounded">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Hoja</th>
                                <th class="px-3 py-2 text-left font-semibold">Valor</th>
                                @foreach($contextHeaders as $header)
                                    <th class="px-3 py-2 text-left font-semibold">{{ (string) $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($results as $row)
                                <tr>
                                    <td class="px-3 py-2">{{ (string) $row['sheet'] }}</td>
                                    <td class="px-3 py-2">{{ (string) $row['match_value'] }}</td>
                                    @foreach($contextHeaders as $header)
                                        <td class="px-3 py-2">{{ (string) ($row['context'][$header] ?? '') }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600">No se encontraron registros.</p>
            @endif

        {{-- Tabla con datos de una sola hoja --}}
        @elseif($data && $data['rows'])
            <div class="overflow-x-auto border rounded">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            @foreach($data['headers'] as $header)
                                <th class="px-3 py-2 text-left font-semibold">
                                    {{ (string) ucfirst(str_replace('_',' ', $header)) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($data['rows'] as $row)
                            <tr>
                                @foreach($data['headers'] as $header)
                                    @php
                                        $h = strtolower($header);
                                        $val = $row[$header] ?? null;
                                        $isDate = str_contains($h, 'fecha') || in_array($h, ['fecha','date','fecha_prestamo','fecha_pago','fecha_inicio','fecha_fin']);
                                        $isMoney = str_contains($h, 'monto') || str_contains($h, 'deuda') || str_contains($h, 'importe') || str_contains($h, 'saldo') || str_contains($h, 'abono') || str_contains($h, 'pago') || str_contains($h, 'inversion');
                                    @endphp
                                    <td class="px-3 py-2">
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

            {{-- Paginación simple --}}
            <div class="flex justify-between mt-4">
                @if($offset > 0)
                    <a href="{{ request()->fullUrlWithQuery(['offset' => max(0, $offset - $limit)]) }}"
                       class="px-3 py-2 bg-gray-200 rounded">Anterior</a>
                @endif
                @if($offset + $limit < $data['total'])
                    <a href="{{ request()->fullUrlWithQuery(['offset' => $offset + $limit]) }}"
                       class="px-3 py-2 bg-gray-200 rounded">Siguiente</a>
                @endif
            </div>
        @elseif($current)
            <p class="text-gray-600">No se encontraron registros.</p>
        @endif
    </div>
</x-layouts.authenticated>
