@php
    $columns = $section['columns'] ?? [];
    $records = $section['records'] ?? [];
@endphp

<section class="rounded-xl border border-gray-200 bg-white shadow-sm scroll-mt-24" id="vista-{{ $section['numero'] }}">
    <header class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $section['numero'] }}. {{ $section['title'] }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $section['description'] }}</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
            {{ count($records) }} registros
        </span>
    </header>

    @if (empty($records))
        <p class="px-6 py-5 text-sm text-gray-500">Sin registros pendientes.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach ($columns as $column)
                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-xs text-gray-500">
                                {{ $column['label'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($records as $record)
                        <tr class="hover:bg-gray-50">
                            @foreach ($columns as $column)
                                @php
                                    $key = $column['key'];
                                    $value = $record[$key] ?? 'N/A';
                                @endphp
                                <td class="px-6 py-4 align-top">
                                    @if ($key === 'riesgo')
                                        <span @class([
                                            'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                            'bg-emerald-100 text-emerald-700' => $value === 'Bajo',
                                            'bg-amber-100 text-amber-700' => $value === 'Medio',
                                            'bg-rose-100 text-rose-700' => $value === 'Alto',
                                        ])>
                                            {{ $value }}
                                        </span>
                                    @elseif ($key === 'tiene_credito_activo')
                                        <span @class([
                                            'rounded-md px-2 py-1 text-xs font-semibold uppercase',
                                            'bg-emerald-100 text-emerald-700' => $value === 'Si',
                                            'bg-gray-200 text-gray-700' => $value !== 'Si',
                                        ])>
                                            {{ $value }}
                                        </span>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
