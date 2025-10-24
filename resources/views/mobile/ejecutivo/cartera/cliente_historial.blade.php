<x-layouts.mobile.mobile-layout title="Historial de {{ $clienteNombre }}">
    <style>[x-cloak]{display:none!important}</style>

    <div x-data="historialCliente()" class="mx-auto w-full max-w-md space-y-8 px-5 py-10">
        {{-- INFO CRÉDITO --}}
        <section class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <div class="grid grid-cols-2 gap-4 text-sm text-slate-800">
                <div>
                    <p class="font-semibold">Supervisor</p>
                    <p>{{ $supervisorNombre !== '' ? $supervisorNombre : 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold">Promotor</p>
                    <p>{{ $promotorNombre !== '' ? $promotorNombre : 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold">Semanas del crédito</p>
                    <p>{{ $totalWeeks > 0 ? $totalWeeks : 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold">Semana actual</p>
                    <p>{{ $currentWeek > 0 ? 'sem '.$currentWeek : 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold">Fecha de crédito</p>
                    <p>{{ $fechaCreditoTexto ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold">Monto</p>
                    <p class="font-bold text-emerald-600">${{ number_format($montoCredito, 2, '.', ',') }}</p>
                </div>
            </div>
        </section>

        {{-- CLIENTE --}}
        <section class="space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Cliente</h2>
                <p class="text-sm font-semibold text-slate-900">{{ $clienteNombre !== '' ? $clienteNombre : 'N/A' }}</p>
            </div>

            <div class="grid grid-cols-[1fr_auto] items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-sm text-slate-800">
                    {{ $clienteDireccion ?? 'Sin dirección registrada' }}
                </p>
                <div>
                    @if($clienteDireccion)
                        <a href="https://maps.google.com/?q={{ urlencode($clienteDireccion) }}" target="_blank"
                           class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500"
                           aria-label="Ver mapa">
                            📍
                        </a>
                    @endif
                </div>
            </div>

            <div class="space-y-2">
                @forelse($clienteTelefonos as $telefono)
                    <div class="grid grid-cols-[1fr_auto] items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-sm text-slate-800">{{ $telefono }}</p>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $telefono) }}"
                           class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500"
                           aria-label="Llamar">
                            📞
                        </a>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Sin teléfonos registrados.</p>
                @endforelse
            </div>

            <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="font-semibold text-slate-700">Garantías</p>
                <ul class="space-y-2">
                    @forelse($garantiasCliente as $garantia)
                        <li class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                            <div class="text-sm text-slate-800">
                                <p>{{ $garantia['descripcion'] }}</p>
                                <p class="text-xs text-slate-500">${{ number_format($garantia['monto'], 2, '.', ',') }}</p>
                            </div>
                            @if(!empty($garantia['foto_url']))
                                <button class="text-lg text-purple-600"
                                        @click="zoomImg = @js($garantia['foto_url'])"
                                        type="button"
                                        aria-label="Ver garantía">
                                    🔍
                                </button>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Sin garantías registradas.</li>
                    @endforelse
                </ul>
                @if($documentosCliente->isNotEmpty())
                    <button class="w-full rounded-xl bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-500"
                            @click="openFotos(@js($documentosCliente), 'Documentos del Cliente')"
                            type="button">
                        Archivo Documentos 📎
                    </button>
                @endif
            </div>
        </section>

        {{-- AVAL --}}
        <section class="space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Aval</h2>
                <p class="text-sm font-semibold text-slate-900">{{ $avalNombre !== '' ? $avalNombre : 'Sin aval registrado' }}</p>
            </div>

            <div class="grid grid-cols-[1fr_auto] items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-sm text-slate-800">{{ $avalDireccion ?? 'Sin dirección registrada' }}</p>
                <div>
                    @if($avalDireccion)
                        <a href="https://maps.google.com/?q={{ urlencode($avalDireccion) }}" target="_blank"
                           class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500"
                           aria-label="Ver mapa aval">
                            📍
                        </a>
                    @endif
                </div>
            </div>

            <div class="space-y-2">
                @forelse($avalTelefonos as $telefono)
                    <div class="grid grid-cols-[1fr_auto] items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-sm text-slate-800">{{ $telefono }}</p>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $telefono) }}"
                           class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500"
                           aria-label="Llamar aval">
                            📞
                        </a>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Sin teléfonos registrados.</p>
                @endforelse
            </div>

            <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="font-semibold text-slate-700">Garantías</p>
                <ul class="space-y-2">
                    @forelse($garantiasAval as $garantia)
                        <li class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                            <div class="text-sm text-slate-800">
                                <p>{{ $garantia['descripcion'] }}</p>
                                <p class="text-xs text-slate-500">${{ number_format($garantia['monto'], 2, '.', ',') }}</p>
                            </div>
                            @if(!empty($garantia['foto_url']))
                                <button class="text-lg text-purple-600"
                                        @click="zoomImg = @js($garantia['foto_url'])"
                                        type="button"
                                        aria-label="Ver garantía aval">
                                    🔍
                                </button>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Sin garantías registradas.</li>
                    @endforelse
                </ul>
                @if($documentosAval->isNotEmpty())
                    <button class="w-full rounded-xl bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-500"
                            @click="openFotos(@js($documentosAval), 'Documentos del Aval')"
                            type="button">
                        Archivo Documentos 📎
                    </button>
                @endif
            </div>
        </section>

        {{-- TABLA SEMANAS --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
            <table class="w-full table-fixed border-collapse text-sm">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="p-2 text-left">Semana</th>
                        <th class="p-2 text-right">Monto</th>
                        <th class="p-2 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($semanas as $semana)
                        <tr>
                            <td class="p-2">sem {{ $semana['semana'] }}</td>
                            <td class="p-2 text-right">${{ number_format($semana['monto'], 2, '.', ',') }}</td>
                            <td class="p-2 text-center">{{ $semana['estado'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-3 text-center text-sm text-slate-500">Sin pagos proyectados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('mobile.supervisor.cartera') }}"
           class="block w-full rounded-2xl bg-blue-800 py-3 text-center text-sm font-semibold text-white shadow-md transition hover:bg-blue-700">
            REGRESAR
        </a>

        {{-- ===== MODAL FOTOGRAFÍAS ===== --}}
        <template x-if="showFotos">
            <div class="fixed inset-0 z-[55] flex items-end justify-center sm:items-center">
                <div class="absolute inset-0 bg-black/40" @click="closeFotos()"></div>
                <div class="relative w-full rounded-t-3xl border border-slate-200 bg-white p-5 shadow-2xl sm:mx-auto sm:max-w-md sm:rounded-3xl"
                     x-trap.noscroll="showFotos" x-transition>
                    <div class="mb-3 flex items-start justify-between">
                        <h3 class="text-base font-bold" x-text="albumTitulo"></h3>
                        <button class="rounded-xl bg-slate-100 p-2 text-sm font-semibold transition hover:bg-slate-200"
                                @click="closeFotos()"
                                type="button">
                            Cerrar
                        </button>
                    </div>
                    <div class="max-h-[70vh] space-y-3 overflow-y-auto">
                        <template x-for="(item,i) in fotosList" :key="i">
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium" x-text="item.titulo"></p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <button class="rounded-lg bg-purple-600 px-2 py-1 text-xs font-semibold text-white transition hover:bg-purple-500"
                                            @click="zoomImg=item.url"
                                            type="button">
                                        Ver
                                    </button>
                                    <a :href="item.url" target="_blank"
                                       class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                                        Abrir
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        {{-- ZOOM --}}
        <template x-if="zoomImg">
            <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80" @click="zoomImg=null">
                <img :src="zoomImg" alt="zoom" class="max-h-[90vh] max-w-[90vw] rounded-3xl shadow-2xl">
            </div>
        </template>
    </div>

    <script>
        function historialCliente(){
            return {
                showFotos:false,
                albumTitulo:'',
                fotosList:[],
                zoomImg:null,

                openFotos(lista,titulo){
                    this.fotosList = lista || [];
                    this.albumTitulo = titulo || 'Fotografías';
                    this.showFotos = true;
                },
                closeFotos(){
                    this.showFotos=false;
                    this.albumTitulo='';
                    this.fotosList=[];
                }
            }
        }
    </script>
</x-layouts.mobile.mobile-layout>
