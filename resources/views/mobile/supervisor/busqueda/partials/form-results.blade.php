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

<div class="w-full space-y-6">
    <section class="rounded-3xl bg-slate-900 p-6 text-white shadow-lg">
        <div class="flex items-center gap-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </span>
            <h1 class="text-2xl font-semibold leading-tight">Ingresa tu búsqueda</h1>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white shadow-lg">
        <div class="space-y-6 p-6">
            <form method="GET" action="{{ route($searchRoute, $preservedQueryParams) }}" class="space-y-6">
                @if($showSupervisorSelector)
                    <div class="space-y-2">
                        <label for="supervisor" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Supervisor</label>
                        <select
                            id="supervisor"
                            name="supervisor"
                            class="w-full rounded-2xl border border-slate-300 bg-white py-3 px-4 text-sm font-medium text-slate-700 shadow-inner focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-400/40"
                            onchange="this.form.submit()"
                        >
                            <option value="">Seleccionar supervisor...</option>
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

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Consulta</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        placeholder="Buscar por nombre o dirección..."
                        class="w-full rounded-2xl border border-slate-300 bg-white py-3 px-4 text-sm font-medium text-slate-700 shadow-inner focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-400/40"
                    />
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-md shadow-slate-400/30 transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400/60 focus:ring-offset-1 focus:ring-offset-white"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9m-9 6h9m-9 6h9M5.25 6h.008v.008H5.25zm0 6h.008v.008H5.25zm0 6h.008v.008H5.25z" />
                        </svg>
                        Buscar
                    </button>
                    <a
                        href="{{ route($backRoute, $preservedQueryParams) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-600 shadow-inner transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-200 focus:ring-offset-1 focus:ring-offset-white"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9l6-6" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h12a6 6 0 0 1 0 12h-3" />
                        </svg>
                        Regresar
                    </a>
                </div>
            </form>

            @if($query !== '')
                <div class="space-y-4">
                    @if(!$puedeBuscar)
                        <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-600">
                            No hay promotores asociados al supervisor seleccionado.
                        </div>
                    @else
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            Se encontraron <span class="font-semibold text-slate-800">{{ $resultados->count() }}</span> coincidencias para "{{ $query }}".
                        </div>

                        @if($resultados->isEmpty())
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-6 text-center text-gray-500">
                                No se encontraron resultados.
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($resultados as $resultado)
                                    @php
                                        $detailEnabled = (bool) ($resultado['puede_detallar'] ?? false);
                                        $detalle = $resultado['detalle'] ?? null;
                                    @endphp
                                    <div x-data="{ detail: false }" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0 space-y-2">
                                                <p class="text-base font-semibold text-slate-900">{{ $resultado['nombre'] }}</p>
                                                <p class="text-xs text-slate-600">
                                                    Estatus del crédito: <span class="font-semibold text-slate-800">{{ $resultado['estatus_credito'] }}</span>
                                                </p>
                                                <p class="text-xs text-slate-600">
                                                    Supervisor: <span class="font-semibold text-slate-800">{{ $resultado['supervisor'] }}</span>
                                                </p>
                                                <p class="text-xs text-slate-600">
                                                    Aval: <span class="font-semibold text-slate-800">{{ $resultado['aval'] }}</span>
                                                </p>
                                                <p class="text-[11px] text-slate-500">
                                                    Promotor: <span class="font-semibold text-slate-700">{{ $resultado['promotor'] }}</span>
                                                </p>
                                            </div>
                                            <div class="flex flex-col items-end gap-2">
                                                <button
                                                    type="button"
                                                    @click="detail = true"
                                                    class="inline-flex items-center justify-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition {{ $detailEnabled ? 'bg-blue-600 text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400/70 focus:ring-offset-1 focus:ring-offset-white' : 'bg-slate-200 text-slate-500 cursor-not-allowed' }}"
                                                    {{ $detailEnabled ? '' : 'disabled' }}
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 20.25a8.25 8.25 0 0 1 15 0Z" />
                                                    </svg>
                                                    Detalles
                                                </button>
                                                @unless($detailEnabled)
                                                    <p class="text-[11px] font-medium text-red-500">Asignado a otro supervisor</p>
                                                @endunless
                                            </div>
                                        </div>

                                        @if($detailEnabled && $detalle)
                                            <div x-show="detail" x-cloak @click.self="detail = false" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-6">
                                                <div class="w-full max-w-md space-y-5 overflow-y-auto rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl">
                                                    <button
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1 focus:ring-offset-white"
                                                        @click="detail = false"
                                                        type="button"
                                                    >&times;</button>

                                                    <div class="space-y-2">
                                                        <h2 class="text-lg font-semibold text-slate-900">{{ $resultado['nombre'] }}</h2>
                                                        <p class="text-xs text-slate-600">Supervisor: <span class="font-semibold text-slate-800">{{ $detalle['supervisor'] ?? 'Sin supervisor' }}</span></p>
                                                        <p class="text-xs text-slate-600">Estatus del crédito: <span class="font-semibold text-slate-800">{{ $detalle['estatus_credito'] ?? 'Sin crédito' }}</span></p>
                                                    </div>

                                                    <div class="space-y-4">
                                                        <div class="space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                            <h3 class="text-sm font-semibold text-slate-900">Cliente</h3>
                                                            @php
                                                                $telefonosCliente = collect($detalle['cliente']['telefonos'] ?? [])->filter()->implode(', ');
                                                            @endphp
                                                            <p class="text-xs text-slate-600">Teléfono(s): <span class="font-semibold text-slate-800">{{ $telefonosCliente !== '' ? $telefonosCliente : 'Sin teléfono registrado' }}</span></p>
                                                            <p class="text-xs text-slate-600">Domicilio: <span class="font-semibold text-slate-800">{{ $detalle['cliente']['domicilio'] ?? 'Sin domicilio registrado' }}</span></p>

                                                            <div class="space-y-2">
                                                                <p class="text-xs font-semibold text-slate-700">INE</p>
                                                                @php $docsClienteIne = collect($detalle['cliente']['documentos']['ine'] ?? []); @endphp
                                                                @if($docsClienteIne->isEmpty())
                                                                    <p class="text-[11px] text-slate-400">Sin fotografías de INE.</p>
                                                                @else
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        @foreach($docsClienteIne as $doc)
                                                                            @if(!empty($doc['url']))
                                                                                <img src="{{ $doc['url'] }}" alt="INE del cliente" class="rounded-lg border border-white object-cover shadow" />
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="space-y-2">
                                                                <p class="text-xs font-semibold text-slate-700">Comprobante de domicilio</p>
                                                                @php $docsClienteDom = collect($detalle['cliente']['documentos']['comprobante'] ?? []); @endphp
                                                                @if($docsClienteDom->isEmpty())
                                                                    <p class="text-[11px] text-slate-400">Sin comprobantes de domicilio.</p>
                                                                @else
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        @foreach($docsClienteDom as $doc)
                                                                            @if(!empty($doc['url']))
                                                                                <img src="{{ $doc['url'] }}" alt="Comprobante del cliente" class="rounded-lg border border-white object-cover shadow" />
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                            <h3 class="text-sm font-semibold text-slate-900">Aval</h3>
                                                            <p class="text-xs text-slate-600">Nombre: <span class="font-semibold text-slate-800">{{ $detalle['aval']['nombre'] ?? 'Sin aval' }}</span></p>
                                                            @php
                                                                $telefonosAval = collect($detalle['aval']['telefonos'] ?? [])->filter()->implode(', ');
                                                            @endphp
                                                            <p class="text-xs text-slate-600">Teléfono(s): <span class="font-semibold text-slate-800">{{ $telefonosAval !== '' ? $telefonosAval : 'Sin teléfono registrado' }}</span></p>
                                                            <p class="text-xs text-slate-600">Domicilio: <span class="font-semibold text-slate-800">{{ $detalle['aval']['domicilio'] ?? 'Sin domicilio registrado' }}</span></p>

                                                            <div class="space-y-2">
                                                                <p class="text-xs font-semibold text-slate-700">INE</p>
                                                                @php $docsAvalIne = collect($detalle['aval']['documentos']['ine'] ?? []); @endphp
                                                                @if($docsAvalIne->isEmpty())
                                                                    <p class="text-[11px] text-slate-400">Sin fotografías de INE del aval.</p>
                                                                @else
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        @foreach($docsAvalIne as $doc)
                                                                            @if(!empty($doc['url']))
                                                                                <img src="{{ $doc['url'] }}" alt="INE del aval" class="rounded-lg border border-white object-cover shadow" />
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="space-y-2">
                                                                <p class="text-xs font-semibold text-slate-700">Comprobante de domicilio</p>
                                                                @php $docsAvalDom = collect($detalle['aval']['documentos']['comprobante'] ?? []); @endphp
                                                                @if($docsAvalDom->isEmpty())
                                                                    <p class="text-[11px] text-slate-400">Sin comprobantes de domicilio del aval.</p>
                                                                @else
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        @foreach($docsAvalDom as $doc)
                                                                            @if(!empty($doc['url']))
                                                                                <img src="{{ $doc['url'] }}" alt="Comprobante del aval" class="rounded-lg border border-white object-cover shadow" />
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                            <h3 class="text-sm font-semibold text-slate-900">Garantías</h3>
                                                            @php $garantias = collect($detalle['garantias'] ?? []); @endphp
                                                            @if($garantias->isEmpty())
                                                                <p class="text-[11px] text-slate-400">Sin garantías registradas.</p>
                                                            @else
                                                                <ul class="space-y-2 text-xs text-slate-600">
                                                                    @foreach($garantias as $garantia)
                                                                        <li class="flex items-start gap-2">
                                                                            <span class="text-emerald-500">•</span>
                                                                            <span>
                                                                                <span class="font-semibold text-slate-800">{{ data_get($garantia, 'tipo', 'Garantía') }}:</span>
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
    </section>
</div>
