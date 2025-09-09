<x-layouts.authenticated title="Consulta Base de Datos Histórica">
    <div class="max-w-6xl mx-auto py-8 space-y-6">

        <h1 class="text-2xl font-bold text-gray-800">
            Consulta Base de Datos Histórica
        </h1>

        <form method="GET" class="flex items-center gap-4">
            <div>
                <label class="block text-sm font-medium">Buscar</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                    class="border rounded p-2" placeholder="Buscar..." />
            </div>

            <div class="pt-6">
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                    Consultar
                </button>
            </div>
        </form>

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
                                {{-- Cabeceras contextuales generadas dinámicamente --}}
                                @foreach($contextHeaders as $header)
                                    <th class="px-3 py-2 text-left font-semibold">
                                        {{ $header }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($results as $row)
                                <tr>
                                    <td class="px-3 py-2">{{ $row['sheet'] }}</td>
                                    <td class="px-3 py-2">{{ $row['match_value'] }}</td>
                                    @foreach($contextHeaders as $header)
                                        <td class="px-3 py-2">{{ $row['context'][$header] ?? '' }}</td>
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
                                    {{ ucfirst(str_replace('_',' ', $header)) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($data['rows'] as $row)
                            <tr>
                                @foreach($data['headers'] as $header)
                                    <td class="px-3 py-2">
                                        {{ $row[$header] ?? '' }}
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
