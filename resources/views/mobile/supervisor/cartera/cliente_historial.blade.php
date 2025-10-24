<x-layouts.mobile.mobile-layout title="Historial de {{ $clienteNombre }}">
  <style>[x-cloak]{display:none!important}</style>

  <div x-data="historialCliente()" class="w-[22rem] sm:w-[26rem] mx-auto space-y-6 px-5 py-8">

    {{-- INFO DEL CREDITO --}}
    <section class="space-y-4 rounded-3xl border border-slate-200 bg-white px-5 py-4 shadow">
      <h2 class="text-lg font-bold text-gray-900">INFO DEL CR&Eacute;DITO</h2>
      <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
        <div class="space-y-1">
          <p class="font-semibold">Supervisor</p>
          <div class="border-b border-gray-300 h-6 leading-6">{{ $supervisorNombre !== '' ? $supervisorNombre : 'N/A' }}</div>
        </div>
        <div class="space-y-1">
          <p class="font-semibold">Promotor</p>
          <div class="border-b border-gray-300 h-6 leading-6">{{ $promotorNombre !== '' ? $promotorNombre : 'N/A' }}</div>
        </div>
        <div class="space-y-1">
          <p class="font-semibold">Semanas del cr&eacute;dito</p>
          <div class="border-b border-gray-300 h-6 leading-6">{{ $totalWeeks > 0 ? $totalWeeks : 'N/A' }}</div>
        </div>
        <div class="space-y-1">
          <p class="font-semibold">Semana actual</p>
          <div class="border-b border-gray-300 h-6 leading-6">{{ $currentWeek > 0 ? 'sem '.$currentWeek : 'N/A' }}</div>
        </div>
        <div class="space-y-1">
          <p class="font-semibold">Fecha de cr&eacute;dito</p>
          <div class="border-b border-gray-300 h-6 leading-6">{{ $fechaCreditoTexto ?? 'N/A' }}</div>
        </div>
        <div class="space-y-1">
          <p class="font-semibold">Monto</p>
          <div class="border-b border-gray-300 h-6 leading-6 font-bold text-green-600">
            ${{ number_format($montoCredito, 2, '.', ',') }}
          </div>
        </div>
      </div>
    </section>

    {{-- CLIENTE --}}
    <section class="space-y-4 rounded-3xl border border-slate-200 bg-white px-5 py-4 shadow">
      <div>
        <h2 class="text-lg font-bold text-gray-900">Cliente</h2>
        <p class="mt-1 text-sm font-semibold">{{ $clienteNombre !== '' ? $clienteNombre : 'N/A' }}</p>
      </div>

      {{-- Direcci&oacute;n --}}
      <div class="grid grid-cols-[1fr_auto] gap-2 items-center text-sm text-gray-800">
        <div>{{ $clienteDireccion ?? 'Sin direcci&oacute;n registrada' }}</div>
        <div>
          @if($clienteDireccion)
            <a href="https://maps.google.com/?q={{ urlencode($clienteDireccion) }}" target="_blank"
               class="flex justify-center items-center px-3 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">
              📍
            </a>
          @endif
        </div>
      </div>

      {{-- Tel&eacute;fono --}}
      <div class="space-y-2">
        @forelse($clienteTelefonos as $telefono)
          <div class="grid grid-cols-[1fr_auto] gap-2 items-center text-sm">
            <div class="text-gray-800">{{ $telefono }}</div>
            <div>
              <a href="tel:{{ preg_replace('/[^0-9+]/', '', $telefono) }}"
                 class="flex justify-center items-center px-3 py-2 text-sm font-semibold bg-green-600 text-white rounded-lg shadow hover:bg-green-700">
                📞
              </a>
            </div>
          </div>
        @empty
          <p class="text-sm text-gray-500">Sin tel&eacute;fonos registrados.</p>
        @endforelse
      </div>

      {{-- Garant&iacute;as --}}
      <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 shadow-inner space-y-2">
        <p class="font-semibold text-gray-700">Garant&iacute;as</p>
        <ul class="space-y-2">
          @forelse($garantiasCliente as $garantia)
            <li class="flex justify-between items-center bg-white px-3 py-2 rounded-lg shadow-sm border border-slate-200">
              <div class="text-sm text-gray-800">
                <p>{{ $garantia['descripcion'] }}</p>
                <p class="text-xs text-gray-500">${{ number_format($garantia['monto'], 2, '.', ',') }}</p>
              </div>
              @if(!empty($garantia['foto_url']))
                <button class="text-sm font-semibold text-purple-600 underline"
                        @click="zoomImg = @js($garantia['foto_url'])">
                  Ver
                </button>
              @endif
            </li>
          @empty
            <li class="text-sm text-gray-500">Sin garant&iacute;as registradas.</li>
          @endforelse
        </ul>
        @if($documentosCliente->isNotEmpty())
          <button class="mt-3 w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 rounded-lg shadow"
                  @click="openFotos(@js($documentosCliente), 'Documentos del Cliente')">
            Archivo Documentos
          </button>
        @endif
      </div>
    </section>

    {{-- AVAL --}}
    <section class="space-y-4 rounded-3xl border border-slate-200 bg-white px-5 py-4 shadow">
      <div>
        <h2 class="text-lg font-bold text-gray-900">Aval</h2>
        <p class="mt-1 text-sm font-semibold">{{ $avalNombre !== '' ? $avalNombre : 'Sin aval registrado' }}</p>
      </div>

      {{-- Direcci&oacute;n --}}
      <div class="grid grid-cols-[1fr_auto] gap-2 items-center text-sm text-gray-800">
        <div>{{ $avalDireccion ?? 'Sin direcci&oacute;n registrada' }}</div>
        <div>
          @if($avalDireccion)
            <a href="https://maps.google.com/?q={{ urlencode($avalDireccion) }}" target="_blank"
               class="flex justify-center items-center px-3 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">
              📍
            </a>
          @endif
        </div>
      </div>

      {{-- Tel&eacute;fono --}}
      <div class="space-y-2">
        @forelse($avalTelefonos as $telefono)
          <div class="grid grid-cols-[1fr_auto] gap-2 items-center text-sm">
            <div class="text-gray-800">{{ $telefono }}</div>
            <div>
              <a href="tel:{{ preg_replace('/[^0-9+]/', '', $telefono) }}"
                 class="flex justify-center items-center px-3 py-2 text-sm font-semibold bg-green-600 text-white rounded-lg shadow hover:bg-green-700">
                📞
              </a>
            </div>
          </div>
        @empty
          <p class="text-sm text-gray-500">Sin tel&eacute;fonos registrados.</p>
        @endforelse
      </div>

      {{-- Garant&iacute;as --}}
      <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 shadow-inner space-y-2">
        <p class="font-semibold text-gray-700">Garant&iacute;as</p>
        <ul class="space-y-2">
          @forelse($garantiasAval as $garantia)
            <li class="flex justify-between items-center bg-white px-3 py-2 rounded-lg shadow-sm border border-slate-200">
              <div class="text-sm text-gray-800">
                <p>{{ $garantia['descripcion'] }}</p>
                <p class="text-xs text-gray-500">${{ number_format($garantia['monto'], 2, '.', ',') }}</p>
              </div>
              @if(!empty($garantia['foto_url']))
                <button class="text-sm font-semibold text-purple-600 underline"
                        @click="zoomImg = @js($garantia['foto_url'])">
                  Ver
                </button>
              @endif
            </li>
          @empty
            <li class="text-sm text-gray-500">Sin garant&iacute;as registradas.</li>
          @endforelse
        </ul>
        @if($documentosAval->isNotEmpty())
          <button class="mt-3 w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 rounded-lg shadow"
                  @click="openFotos(@js($documentosAval), 'Documentos del Aval')">
            Archivo Documentos
          </button>
        @endif
      </div>
    </section>

    {{-- TABLA SEMANAS --}}
    <section class="rounded-3xl border border-slate-200 bg-white px-5 py-4 shadow">
      <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-inner">
        <table class="w-full text-sm table-auto border-collapse">
          <thead class="bg-gray-100 text-gray-700">
            <tr>
              <th class="p-2 text-left">Semana</th>
              <th class="p-2 text-right">Monto</th>
              <th class="p-2 text-center">Estado</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            @forelse($semanas as $semana)
              <tr>
                <td class="p-2">sem {{ $semana['semana'] }}</td>
                <td class="p-2 text-right">${{ number_format($semana['monto'], 2, '.', ',') }}</td>
                <td class="p-2 text-center">{{ $semana['estado'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="p-3 text-center text-sm text-gray-500">Sin pagos proyectados.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    {{-- BOTON REGRESAR --}}
    <a href="{{ route('mobile.supervisor.cartera', array_merge($supervisorContextQuery ?? [], [])) }}"
       class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl text-center shadow-md transition">
      REGRESAR
    </a>

    {{-- ===== MODAL FOTOGRAFIAS ===== --}}
    <template x-if="showFotos">
      <div class="fixed inset-0 z-[55] flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="closeFotos()"></div>
        <div class="relative w-full sm:max-w-md bg-white text-gray-900 border border-gray-200 shadow-2xl sm:rounded-2xl rounded-t-2xl p-5 sm:mx-auto"
             x-trap.noscroll="showFotos" x-transition>
          <div class="flex items-start justify-between mb-3">
            <h3 class="text-base font-bold" x-text="albumTitulo"></h3>
            <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-semibold" @click="closeFotos()">Cerrar</button>
          </div>
          <div class="space-y-3 max-h-[70vh] overflow-y-auto">
            <template x-for="(item,i) in fotosList" :key="i">
              <div class="flex items-center justify-between gap-3 rounded-lg border p-2">
                <div class="min-w-0">
                  <p class="text-sm font-medium truncate" x-text="item.titulo"></p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <button class="px-2 py-1 text-xs rounded-md bg-purple-600 hover:bg-purple-700 text-white"
                          @click="zoomImg=item.url">
                    Ver
                  </button>
                  <a :href="item.url" target="_blank"
                     class="px-2 py-1 text-xs rounded-md bg-gray-100 hover:bg-gray-200 text-gray-800">
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
        <img :src="zoomImg" alt="zoom" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl">
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
          this.fotosList = lista || []
          this.albumTitulo = titulo || 'Fotograf&iacute;as'
          this.showFotos = true
        },
        closeFotos(){
          this.showFotos=false
          this.albumTitulo=''
          this.fotosList=[]
        }
      }
    }
  </script>
</x-layouts.mobile.mobile-layout>


