@php
    $role = $role ?? 'supervisor';
    $supervisores = collect($supervisores ?? []);
    $supervisorContextQuery = $supervisorContextQuery ?? request()->attributes->get('supervisor_context_query', []);
    $selectedSupervisor = data_get($supervisorContextQuery, 'supervisor');
    $query = $query ?? '';
    $resultados = collect($resultados ?? []);
    $puedeBuscar = (bool) ($puedeBuscar ?? false);

    $isExecutiveContext = in_array($role, ['ejecutivo', 'administrativo', 'superadmin'], true);
    $searchRoute = $isExecutiveContext ? 'mobile.ejecutivo.busqueda' : 'mobile.supervisor.busqueda';
    $backRoute = $isExecutiveContext ? 'mobile.ejecutivo.index' : 'mobile.supervisor.index';
    $showSupervisorSelector = in_array($role, ['administrativo', 'superadmin'], true) && $supervisores->isNotEmpty();
    $preservedQueryParams = collect($supervisorContextQuery)->when(!$showSupervisorSelector, fn ($params) => $params->except('supervisor'))->all();
@endphp

<div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md space-y-6">
    <h1 class="text-xl font-bold text-gray-900 text-center">Ingresa tu búsqueda</h1>

    <form method="GET" action="{{ route($searchRoute, $preservedQueryParams) }}" class="space-y-4">
        @if($showSupervisorSelector)
            <div class="space-y-1">
                <label for="supervisor" class="block text-sm font-semibold text-gray-700">Supervisor</label>
                <select
                    id="supervisor"
                    name="supervisor"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                    onchange="this.form.submit()"
                >
                    <option value="">Seleccionar supervisor…</option>
                    @foreach($supervisores as $supervisorOption)
                        @php
                            $optionId = data_get($supervisorOption, 'id');
                        @endphp
                        <option value="{{ $optionId }}" @selected((string) $optionId === (string) $selectedSupervisor)>
                            {{ data_get($supervisorOption, 'nombre', 'Sin nombre') }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

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
                href="{{ route($backRoute, $preservedQueryParams) }}"
                class="flex-1 py-2 text-center bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400"
            >Regresar</a>
        </div>
    </form>

    @if($query !== '')
        <div class="space-y-4">
            @if(!$puedeBuscar)
                <p class="text-sm text-gray-500">No hay promotores asociados al supervisor seleccionado.</p>
            @else
                <p class="text-sm text-gray-500">
                    Se encontraron <span class="font-semibold text-gray-700">{{ $resultados->count() }}</span> coincidencias para "{{ $query }}".
                </p>

                @if($resultados->isEmpty())
                    <p class="text-center text-gray-500">No se encontraron resultados.</p>
                @else
                    <div class="space-y-3">
                        @foreach($resultados as $resultado)
                            @php
                                $detailEnabled = (bool) ($resultado['puede_detallar'] ?? false);
                                $detalle = $resultado['detalle'] ?? null;
                            @endphp
                            <div x-data="{ detail: false }" class="border border-gray-200 rounded-xl shadow-sm">
                                <div class="flex items-start justify-between gap-3 p-3">
                                    <div class="min-w-0 space-y-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $resultado['nombre'] }}</p>
                                        <p class="text-xs text-gray-600">
                                            Estatus del crédito: <span class="font-semibold text-gray-800">{{ $resultado['estatus_credito'] }}</span>
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            Supervisor: <span class="font-semibold text-gray-800">{{ $resultado['supervisor'] }}</span>
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            Aval: <span class="font-semibold text-gray-800">{{ $resultado['aval'] }}</span>
                                        </p>
                                        <p class="text-[11px] text-gray-500">
                                            Promotor: <span class="font-semibold text-gray-700">{{ $resultado['promotor'] }}</span>
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <button
                                            type="button"
                                            @click="detail = true"
                                            class="px-3 py-1 text-sm font-semibold rounded-lg shadow-sm transition {{ $detailEnabled ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-200 text-gray-500 cursor-not-allowed' }}"
                                            {{ $detailEnabled ? '' : 'disabled' }}
                                        >Detalles</button>
                                        @unless($detailEnabled)
                                            <p class="text-[11px] font-medium text-red-500">Asignado a otro supervisor</p>
                                        @endunless
                                    </div>
                                </div>

                                @if($detailEnabled && $detalle)
                                    <div x-show="detail" x-cloak @click.self="detail = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                                        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl p-5 space-y-4">
                                            <button
                                                class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
                                                @click="detail = false"
                                                type="button"
                                            >✕</button>

                                            <div class="space-y-1">
                                                <h2 class="text-lg font-bold text-gray-900">{{ $resultado['nombre'] }}</h2>
                                                <p class="text-xs text-gray-600">Supervisor: <span class="font-semibold text-gray-800">{{ $detalle['supervisor'] ?? 'Sin supervisor' }}</span></p>
                                                <p class="text-xs text-gray-600">Estatus del crédito: <span class="font-semibold text-gray-800">{{ $detalle['estatus_credito'] ?? 'Sin crédito' }}</span></p>
                                            </div>

                                            <div class="space-y-3">
                                                <div class="space-y-1">
                                                    <h3 class="text-sm font-semibold text-gray-900">Cliente</h3>
                                                    @php
                                                        $telefonosCliente = collect($detalle['cliente']['telefonos'] ?? [])->filter()->implode(', ');
                                                    @endphp
                                                    <p class="text-xs text-gray-600">Teléfono(s): <span class="font-semibold text-gray-800">{{ $telefonosCliente !== '' ? $telefonosCliente : 'Sin teléfono registrado' }}</span></p>
                                                    <p class="text-xs text-gray-600">Domicilio: <span class="font-semibold text-gray-800">{{ $detalle['cliente']['domicilio'] ?? 'Sin domicilio registrado' }}</span></p>

                                                    <div class="space-y-1">
                                                        <p class="text-xs font-semibold text-gray-700">INE</p>
                                                        @php $docsClienteIne = collect($detalle['cliente']['documentos']['ine'] ?? []); @endphp
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
                                                        @php $docsClienteDom = collect($detalle['cliente']['documentos']['comprobante'] ?? []); @endphp
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
                                                    <p class="text-xs text-gray-600">Nombre: <span class="font-semibold text-gray-800">{{ $detalle['aval']['nombre'] ?? 'Sin aval' }}</span></p>
                                                    @php
                                                        $telefonosAval = collect($detalle['aval']['telefonos'] ?? [])->filter()->implode(', ');
                                                    @endphp
                                                    <p class="text-xs text-gray-600">Teléfono(s): <span class="font-semibold text-gray-800">{{ $telefonosAval !== '' ? $telefonosAval : 'Sin teléfono registrado' }}</span></p>
                                                    <p class="text-xs text-gray-600">Domicilio: <span class="font-semibold text-gray-800">{{ $detalle['aval']['domicilio'] ?? 'Sin domicilio registrado' }}</span></p>

                                                    <div class="space-y-1">
                                                        <p class="text-xs font-semibold text-gray-700">INE</p>
                                                        @php $docsAvalIne = collect($detalle['aval']['documentos']['ine'] ?? []); @endphp
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
                                                        @php $docsAvalDom = collect($detalle['aval']['documentos']['comprobante'] ?? []); @endphp
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
                                            <div class="space-y-1">
                                                <h3 class="text-sm font-semibold text-gray-900">Garantías</h3>
                                                @php $garantias = collect($detalle['garantias'] ?? []); @endphp
                                                @if($garantias->isEmpty())
                                                    <p class="text-[11px] text-gray-400">Sin garantías registradas.</p>
                                                @else
                                                    <ul class="space-y-1 text-xs text-gray-600">
                                                        @foreach($garantias as $garantia)
                                                            <li class="flex items-start gap-2">
                                                                <span class="text-gray-400">•</span>
                                                                <span>
                                                                    <span class="font-semibold text-gray-800">{{ data_get($garantia, 'tipo', 'Garantía') }}:</span>
                                                                    {{ data_get($garantia, 'descripcion', data_get($garantia, 'marca')) }}
                                                                </span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>
