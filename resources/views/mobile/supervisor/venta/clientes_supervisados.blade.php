{{-- resources/views/supervisor/clientes_supervision.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    function money($v){ return '$'.number_format($v, 2, '.', ','); }

    // DEMO DATA (reemplázalo con tu query real)
    $promotores = collect(range(1, 2))->map(function() use ($faker) {
        $clientes = collect(range(1, 3))->map(function() use ($faker) {
            return [
                'id'            => $faker->uuid(),
                'nombre'        => $faker->name(),
                'telefono'      => $faker->numerify('55########'),
                'direccion'     => $faker->streetAddress.', '.$faker->city,
                'monto_credito' => $faker->numberBetween(3000, 50000),
                'curp'          => strtoupper($faker->regexify('[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}')),
                'ine_url'       => 'https://picsum.photos/seed/ine/640/400',
                'comp_url'      => 'https://picsum.photos/seed/comprobante/640/900',
            ];
        });

        $recreditos = collect(range(1, 2))->map(function() use ($faker) {
            return [
                'id'            => $faker->uuid(),
                'nombre'        => $faker->name().' (Recrédito)',
                'telefono'      => $faker->numerify('55########'),
                'direccion'     => $faker->streetAddress.', '.$faker->city,
                'monto_credito' => $faker->numberBetween(3000, 50000),
                'curp'          => strtoupper($faker->regexify('[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}')),
                'ine_url'       => 'https://picsum.photos/seed/ine2/640/400',
                'comp_url'      => 'https://picsum.photos/seed/comprobante2/640/900',
            ];
        });

        return [
            'nombre'     => $faker->firstName().' '.$faker->lastName(),
            'clientes'   => $clientes,
            'recreditos' => $recreditos,
        ];
    });
@endphp

<x-layouts.mobile.mobile-layout title="Clientes Supervisión">
  <div x-data="clientesSupervision()" class="p-4 w-full max-w-md mx-auto space-y-6">

    {{-- LISTADO DE PROMOTORES --}}
    @foreach($promotores as $promotor)
      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        {{-- Header promotor --}}
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-7 h-7 text-[12px] font-bold rounded-full bg-gray-200 text-gray-700">{{ $loop->iteration }}</span>
          <div>
            <p class="text-[15px] font-semibold text-gray-900">{{ $promotor['nombre'] }}</p>
            <p class="text-[12px] text-gray-500">Clientes supervisados</p>
          </div>
        </div>

        <div class="px-3 py-2 space-y-4">
          {{-- CLIENTES NUEVOS --}}
          <div class="bg-gray-50 rounded-lg border border-gray-200">
            <div class="px-3 py-2 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-[13px] font-bold text-gray-700">Clientes Nuevos</h3>
              <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-200 text-gray-700">{{ $promotor['clientes']->count() }}</span>
            </div>
            <div>
              @foreach($promotor['clientes'] as $c)
                <div class="py-2 px-3">
                  <div class="grid grid-cols-[70%_30%] items-center gap-2">
                    <div class="min-w-0">
                      <p class="truncate text-[14px] font-semibold text-gray-900">{{ $c['nombre'] }}</p>
                      <div class="flex items-center gap-2 text-[12px] text-gray-600">
                        <span>📞 {{ $c['telefono'] }}</span>
                        <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                          {{ money($c['monto_credito']) }}
                        </span>
                      </div>
                    </div>
                    <div>
                      <button
                        type="button"
                        class="w-full px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm"
                        @click="openModal({
                          id: '{{ $c['id'] }}',
                          nombre: @js($c['nombre']),
                          curp: '{{ $c['curp'] }}',
                          telefono: '{{ $c['telefono'] }}',
                          direccion: @js($c['direccion']),
                          monto_credito: '{{ money($c['monto_credito']) }}',
                          promotor: @js($promotor['nombre']),
                          fotos: [
                            { titulo: 'INE', url: '{{ $c['ine_url'] }}' },
                            { titulo: 'Comprobante Domicilio', url: '{{ $c['comp_url'] }}' }
                          ]
                        })">
                        CHECK
                      </button>
                    </div>
                  </div>
                </div>
                @if(!$loop->last)<div class="h-px bg-gray-100"></div>@endif
              @endforeach
            </div>
          </div>

          {{-- RECREDITOS --}}
          <div class="bg-gray-50 rounded-lg border border-gray-200">
            <div class="px-3 py-2 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-[13px] font-bold text-gray-700">Recréditos</h3>
              <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-200 text-gray-700">{{ $promotor['recreditos']->count() }}</span>
            </div>
            <div>
              @foreach($promotor['recreditos'] as $r)
                <div class="py-2 px-3">
                  <div class="grid grid-cols-[70%_30%] items-center gap-2">
                    <div class="min-w-0">
                      <p class="truncate text-[14px] font-semibold text-gray-900">{{ $r['nombre'] }}</p>
                      <div class="flex items-center gap-2 text-[12px] text-gray-600">
                        <span>📞 {{ $r['telefono'] }}</span>
                        <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                          {{ money($r['monto_credito']) }}
                        </span>
                      </div>
                    </div>
                    <div>
                      <button
                        type="button"
                        class="w-full px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm"
                        @click="openModal({
                          id: '{{ $r['id'] }}',
                          nombre: @js($r['nombre']),
                          curp: '{{ $r['curp'] }}',
                          telefono: '{{ $r['telefono'] }}',
                          direccion: @js($r['direccion']),
                          monto_credito: '{{ money($r['monto_credito']) }}',
                          promotor: @js($promotor['nombre']),
                          fotos: [
                            { titulo: 'INE', url: '{{ $r['ine_url'] }}' },
                            { titulo: 'Comprobante Domicilio', url: '{{ $r['comp_url'] }}' }
                          ]
                        })">
                        CHECK
                      </button>
                    </div>
                  </div>
                </div>
                @if(!$loop->last)<div class="h-px bg-gray-100"></div>@endif
              @endforeach
            </div>
          </div>
        </div>
      </div>
    @endforeach

    {{-- ===== MODAL SUPERVISIÓN ===== --}}
    <template x-if="showModal">
      <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

        <div
          class="relative w-full sm:max-w-md bg-white text-gray-900 border border-gray-200 shadow-2xl
                 sm:rounded-2xl rounded-t-2xl p-5 sm:mx-auto"
          x-trap.noscroll="showModal" x-transition>

          <h2 class="text-xl sm:text-2xl font-extrabold text-center">Supervisión</h2>

          <div class="mt-4 rounded-xl border p-3 space-y-3">
            <p class="text-sm font-semibold" x-text="selected.nombre"></p>
            <p class="text-xs text-gray-600" x-text="selected.curp"></p>
            <p class="text-xs text-gray-600" x-text="selected.telefono"></p>
            <p class="text-xs text-gray-600" x-text="selected.direccion"></p>
            <p class="text-sm font-bold text-emerald-700" x-text="selected.monto_credito"></p>
          </div>

          {{--  Documentos y Fotografías --}}
          <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-bold text-gray-900">Fotografías</h3>
              <button
                class="text-xs px-2 py-1 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold"
                @click="openFotos()">
                📷 Fotografías
              </button>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <template x-for="(foto, idx) in selected.fotos" :key="idx">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-2">
                  <p class="text-center text-[11px] text-gray-600 mb-1" x-text="foto.titulo"></p>
                  <img :src="foto.url"
                       class="mx-auto object-contain cursor-pointer max-h-[200px] max-w-full rounded"
                       @click="zoomImg = foto.url">
                </div>
              </template>
            </div>
          </div>

          {{-- 📍 Localización + Confirmar --}}
          <div class="mt-5 grid grid-cols-2 gap-3">
            <a :href="`https://maps.google.com/?q=${encodeURIComponent(selected.direccion)}`" target="_blank"
               class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">
              📍 Localización
            </a>
            <button class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold"
                    @click="confirmarSupervision()">Confirmar</button>
          </div>

          <button class="absolute top-3 right-3 p-2 rounded-lg bg-gray-100 hover:bg-gray-200" @click="closeModal()">✕</button>
        </div>
      </div>
    </template>

    {{-- ===== MODAL FOTOGRAFÍAS ===== --}}
    <template x-if="showFotos">
      <div class="fixed inset-0 z-[55] flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="closeFotos()"></div>

        <div class="relative w-full sm:max-w-md bg-white text-gray-900 border border-gray-200 shadow-2xl
                    sm:rounded-2xl rounded-t-2xl p-5 sm:mx-auto"
             x-trap.noscroll="showFotos" x-transition>
          <div class="flex items-start justify-between">
            <h3 class="text-base font-bold">Fotografías</h3>
            <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200" @click="closeFotos()">✕</button>
          </div>
          <div class="mt-3 space-y-3 max-h-[70vh] overflow-y-auto">
            <template x-for="(item, i) in fotosList" :key="i">
              <div class="flex items-center justify-between gap-3 rounded-lg border p-2">
                <div class="min-w-0">
                  <p class="text-sm font-medium truncate" x-text="item.titulo"></p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <button class="px-2 py-1 text-xs rounded-md bg-purple-600 hover:bg-purple-700 text-white"
                          @click="zoomImg = item.url">📷 Ver</button>
                  <a :href="item.url" target="_blank"
                     class="px-2 py-1 text-xs rounded-md bg-gray-100 hover:bg-gray-200 text-gray-800">Abrir</a>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </template>

    {{-- Zoom Imagen --}}
    <template x-if="zoomImg">
      <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80" @click="zoomImg = null">
        <img :src="zoomImg" alt="zoom" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl">
      </div>
    </template>

  </div>

  <script>
    function clientesSupervision() {
      return {
        showModal: false,
        showFotos: false,
        zoomImg: null,
        selected: { id:null, nombre:'', curp:'', telefono:'', direccion:'', monto_credito:'', promotor:'', fotos: [] },
        fotosList: [],

        openModal(data) {
          this.selected = { ...this.selected, ...data };
          this.fotosList = data.fotos || [];
          this.showModal = true;
        },
        closeModal() {
          this.showModal = false;
          this.selected = { id:null, nombre:'', curp:'', telefono:'', direccion:'', monto_credito:'', promotor:'', fotos: [] };
          this.fotosList = [];
        },
        openFotos() { this.showFotos = true; },
        closeFotos() { this.showFotos = false; },
        confirmarSupervision() {
          console.log('CONFIRMAR SUPERVISIÓN', this.selected);
          this.closeModal();
        }
      }
    }
  </script>
</x-layouts.mobile.mobile-layout>
