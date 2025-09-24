<x-layouts.mobile.mobile-layout title="Búsqueda">
    <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md space-y-6">
        <h1 class="text-xl font-bold text-gray-900 text-center">Ingresa tu búsqueda</h1>

        <form method="GET" action="{{ route('mobile.supervisor.busqueda', array_merge($supervisorContextQuery ?? [], [])) }}" class="space-y-4">
            <input
                type="text"
                name="q"
                value="{{ $query }}"
                placeholder="Buscar por nombre o dirección..."
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
            <div class="flex justify-between gap-4">
                <button
                    type="submit"
                    class="flex-1 py-2 bg-blue-800 text-white font-semibold rounded-lg hover:bg-blue-900 shadow-sm"
                >Buscar</button>
                <a
                    href="{{ route('mobile.supervisor.index', array_merge($supervisorContextQuery ?? [], [])) }}"
                    class="flex-1 py-2 text-center bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400"
                >Regresar</a>
            </div>
        </form>

        @if($query !== '')
            <div class="space-y-4">
                @if(!$resultados || $totalResultados === 0)
                    <p class="text-center text-gray-500">No se encontraron resultados.</p>
                @else
                    <p class="text-sm text-gray-500">
                        Se encontraron <span class="font-semibold text-gray-700">{{ $totalResultados }}</span> coincidencias para "{{ $query }}".
                    </p>

                    <div class="space-y-3">
                        @foreach($resultados as $resultado)
                            @php $detailEnabled = (bool) ($resultado['puede_detallar'] ?? false); @endphp
                            <div x-data="{ detail: false }" class="border border-gray-200 rounded-xl shadow-sm">
                                <div class="flex items-start justify-between gap-3 p-3">
                                    <div class="min-w-0 space-y-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $resultado['nombre'] }}</p>
                                        <p class="text-xs text-gray-600">
                                            Estatus del crédito: <span class="font-semibold text-gray-800">{{ $resultado['estatus_credito'] }}</span>
                                        </p>
                                        <p class="text-[11px] text-gray-500">
                                            Promotor: <span class="font-semibold text-gray-700">{{ $resultado['promotor'] }}</span>
                                        </p>

                                        @if($detailEnabled)
                                            <p class="text-xs text-gray-600">
                                                Supervisor: <span class="font-semibold text-gray-800">{{ $resultado['supervisor'] }}</span>
                                            </p>
                                            @if(!empty($resultado['aval'] ?? null))
                                                <p class="text-xs text-gray-600">
                                                    Aval: <span class="font-semibold text-gray-800">{{ $resultado['aval'] }}</span>
                                                </p>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <button
                                            type="button"
                                            @click="detail = true"
                                            class="px-3 py-1 text-sm font-semibold rounded-lg shadow-sm transition
                                                {{ $detailEnabled ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-200 text-gray-500 cursor-not-allowed' }}"
                                            {{ $detailEnabled ? '' : 'disabled' }}
                                        >Detalles</button>
                                        @unless($detailEnabled)
                                            <p class="text-[11px] font-medium text-red-500">Asignado a otro supervisor</p>
                                        @endunless
                                    </div>
                                </div>

                                @if($detailEnabled && !empty($resultado['detalle']))
                                    <div x-show="detail" x-cloak @click.self="detail = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                                        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl p-5 space-y-4">
                                            <button
                                                class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
                                                @click="detail = false"
                                                type="button"
                                            >✕</button>

                                            <div class="space-y-1">
                                                <h2 class="text-lg font-bold text-gray-900">{{ $resultado['nombre'] }}</h2>
                                                <p class="text-xs text-gray-600">Supervisor: <span class="font-semibold text-gray-800">{{ $resultado['detalle']['supervisor'] }}</span></p>
                                                <p class="text-xs text-gray-600">Estatus del crédito: <span class="font-semibold text-gray-800">{{ $resultado['detalle']['estatus_credito'] }}</span></p>
                                            </div>

                                            <div class="space-y-3">
                                                <div class="space-y-1">
                                                    <h3 class="text-sm font-semibold text-gray-900">Cliente</h3>
                                                    @php
                                                        $telefonosCliente = collect($resultado['detalle']['cliente']['telefonos'] ?? [])->filter()->implode(', ');
                                                    @endphp
                                                    <p class="text-xs text-gray-600">Teléfono(s): <span class="font-semibold text-gray-800">{{ $telefonosCliente !== '' ? $telefonosCliente : 'Sin teléfono registrado' }}</span></p>
                                                    <p class="text-xs text-gray-600">Domicilio: <span class="font-semibold text-gray-800">{{ $resultado['detalle']['cliente']['domicilio'] ?? 'Sin domicilio registrado' }}</span></p>

                                                    <div class="space-y-1">
                                                        <p class="text-xs font-semibold text-gray-700">INE</p>
                                                        @php $docsClienteIne = collect($resultado['detalle']['cliente']['documentos']['ine'] ?? []); @endphp
                                                        @if($docsClienteIne->isEmpty())
                                                            <p class="text-[11px] text-gray-400">Sin fotografías de INE.</p>
                                                        @else
                                                            <div class="grid grid-cols-2 gap-2">
                                                                @foreach($docsClienteIne as $doc)
                                                                    @if(!empty($doc['url']))
                                                                        <img src="{{ $doc['url'] }}" alt="INE del cliente" class="rounded-lg object-cover" />
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="space-y-1">
                                                        <p class="text-xs font-semibold text-gray-700">Comprobante de domicilio</p>
                                                        @php $docsClienteDom = collect($resultado['detalle']['cliente']['documentos']['comprobante'] ?? []); @endphp
                                                        @if($docsClienteDom->isEmpty())
                                                            <p class="text-[11px] text-gray-400">Sin comprobantes de domicilio.</p>
                                                        @else
                                                            <div class="grid grid-cols-2 gap-2">
                                                                @foreach($docsClienteDom as $doc)
                                                                    @if(!empty($doc['url']))
                                                                        <img src="{{ $doc['url'] }}" alt="Comprobante del cliente" class="rounded-lg object-cover" />
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="space-y-1">
                                                    <h3 class="text-sm font-semibold text-gray-900">Aval</h3>
                                                    <p class="text-xs text-gray-600">Nombre: <span class="font-semibold text-gray-800">{{ $resultado['detalle']['aval']['nombre'] }}</span></p>
                                                    @php
                                                        $telefonosAval = collect($resultado['detalle']['aval']['telefonos'] ?? [])->filter()->implode(', ');
                                                    @endphp
                                                    <p class="text-xs text-gray-600">Teléfono(s): <span class="font-semibold text-gray-800">{{ $telefonosAval !== '' ? $telefonosAval : 'Sin teléfono registrado' }}</span></p>
                                                    <p class="text-xs text-gray-600">Domicilio: <span class="font-semibold text-gray-800">{{ $resultado['detalle']['aval']['domicilio'] ?? 'Sin domicilio registrado' }}</span></p>

                                                    <div class="space-y-1">
                                                        <p class="text-xs font-semibold text-gray-700">INE</p>
                                                        @php $docsAvalIne = collect($resultado['detalle']['aval']['documentos']['ine'] ?? []); @endphp
                                                        @if($docsAvalIne->isEmpty())
                                                            <p class="text-[11px] text-gray-400">Sin fotografías de INE del aval.</p>
                                                        @else
                                                            <div class="grid grid-cols-2 gap-2">
                                                                @foreach($docsAvalIne as $doc)
                                                                    @if(!empty($doc['url']))
                                                                        <img src="{{ $doc['url'] }}" alt="INE del aval" class="rounded-lg object-cover" />
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="space-y-1">
                                                        <p class="text-xs font-semibold text-gray-700">Comprobante de domicilio</p>
                                                        @php $docsAvalDom = collect($resultado['detalle']['aval']['documentos']['comprobante'] ?? []); @endphp
                                                        @if($docsAvalDom->isEmpty())
                                                            <p class="text-[11px] text-gray-400">Sin comprobantes de domicilio del aval.</p>
                                                        @else
                                                            <div class="grid grid-cols-2 gap-2">
                                                                @foreach($docsAvalDom as $doc)
                                                                    @if(!empty($doc['url']))
                                                                        <img src="{{ $doc['url'] }}" alt="Comprobante del aval" class="rounded-lg object-cover" />
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($resultados->hasPages())
                        <div class="pt-2">
                            {{ $resultados->links('pagination::tailwind') }}
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>
</x-layouts.mobile.mobile-layout>
