{{-- resources/views/supervisor/clientes_prospectado.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    // ===== DEMO DATA (reemplaza con tu query real) =====
    $promotores = collect(range(1, 3))->map(function() use ($faker) {
        $clientes = collect(range(1, rand(2, 5)))->map(function() use ($faker) {
            return [
                'id'         => $faker->uuid(),
                'nombre'     => $faker->firstName(),
                'apellido_p' => $faker->lastName(),
                'apellido_m' => $faker->lastName(),
                'telefono'   => $faker->numerify('55########'),
                'direccion'  => $faker->streetAddress.', '.$faker->city,
                'curp'       => strtoupper($faker->regexify('[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}')),
                // URLs DEMO (pondrás tus rutas a Drive / storage)
                'ine_url'    => 'https://picsum.photos/seed/ine/640/400',
                'comp_url'   => 'https://picsum.photos/seed/comprobante/640/900',
                'aval'       => [
                    'nombre'      => $faker->firstName(),
                    'apellido_p'  => $faker->lastName(),
                    'apellido_m'  => $faker->lastName(),
                    'curp'        => strtoupper($faker->regexify('[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}')),
                ],
            ];
        });

        $recreditos = collect(range(1, rand(1, 3)))->map(function() use ($faker) {
            return [
                'id'         => $faker->uuid(),
                'nombre'     => $faker->firstName().' '.$faker->lastName().' (Recrédito)',
                'apellido_p' => $faker->lastName(),
                'apellido_m' => $faker->lastName(),
                'telefono'   => $faker->numerify('55########'),
                'direccion'  => $faker->streetAddress.', '.$faker->city,
                'curp'       => strtoupper($faker->regexify('[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}')),
                'ine_url'    => 'https://picsum.photos/seed/ine2/640/400',
                'comp_url'   => 'https://picsum.photos/seed/comprobante2/640/900',
                'aval'       => [
                    'nombre'      => $faker->firstName(),
                    'apellido_p'  => $faker->lastName(),
                    'apellido_m'  => $faker->lastName(),
                    'curp'        => strtoupper($faker->regexify('[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}')),
                ],
            ];
        });

        return [
            'nombre'     => $faker->firstName().' '.$faker->lastName(),
            'clientes'   => $clientes,
            'recreditos' => $recreditos,
        ];
    });
@endphp

<x-layouts.mobile.mobile-layout title="Clientes Prospectado">
  <div x-data="prospectado()" class="p-4 w-full max-w-md mx-auto space-y-6">

    {{-- LISTA POR PROMOTOR --}}
    @foreach($promotores as $promotor)
      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        {{-- Header promotor --}}
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-7 h-7 text-[12px] font-bold rounded-full bg-gray-200 text-gray-700">{{ $loop->iteration }}</span>
          <div>
            <p class="text-[15px] font-semibold text-gray-900">{{ $promotor['nombre'] }}</p>
            <p class="text-[12px] text-gray-500">Prospectos</p>
          </div>
        </div>

        {{-- Clientes Nuevos --}}
        <div class="px-3 py-2 space-y-4">
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
                      <p class="truncate text-[14px] font-medium text-gray-800">{{ $c['nombre'] }}</p>
                    </div>
                    <div>
                      <button
                        type="button"
                        class="w-full px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm ring-1 ring-emerald-900/20 transition"
                        @click="openModal({
                          id: '{{ $c['id'] }}',
                          nombre: @js($c['nombre']),
                          apellido_p: '{{ $c['apellido_p'] }}',
                          apellido_m: '{{ $c['apellido_m'] }}',
                          curp: '{{ $c['curp'] }}',
                          ine_url: '{{ $c['ine_url'] }}',
                          comp_url: '{{ $c['comp_url'] }}',
                          aval_nombre: @js($c['aval']['nombre']),
                          aval_apellido_p: '{{ $c['aval']['apellido_p'] }}',
                          aval_apellido_m: '{{ $c['aval']['apellido_m'] }}',
                          aval_curp: '{{ $c['aval']['curp'] }}'
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

          {{-- Recréditos --}}
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
                      <p class="truncate text-[14px] font-medium text-gray-800">{{ $r['nombre'] }}</p>
                    </div>
                    <div>
                      <button
                        type="button"
                        class="w-full px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm ring-1 ring-emerald-900/20 transition"
                        @click="openModal({
                          id: '{{ $r['id'] }}',
                          nombre: @js($r['nombre']),
                          apellido_p: '{{ $r['apellido_p'] }}',
                          apellido_m: '{{ $r['apellido_m'] }}',
                          curp: '{{ $r['curp'] }}',
                          ine_url: '{{ $r['ine_url'] }}',
                          comp_url: '{{ $r['comp_url'] }}',
                          aval_nombre: @js($r['aval']['nombre']),
                          aval_apellido_p: '{{ $r['aval']['apellido_p'] }}',
                          aval_apellido_m: '{{ $r['aval']['apellido_m'] }}',
                          aval_curp: '{{ $r['aval']['curp'] }}'
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

    {{-- BOTONES INFERIORES --}}
    <div class="grid grid-cols-3 gap-3">
      {{-- <a href="#" --}}
      <a href="{{ route("mobile.index") }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-semibold shadow-sm transition">Regresar</a>
      {{-- <a href="#" --}}
      <a href="{{ url()->current() }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm transition">Actualizar</a>
      {{-- <a href="#" --}}
      <a href="{{ route("mobile.$role.reporte") }}"
         class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow-sm transition">Reporte</a>
    </div>

    {{-- ===== MODAL: “Cliente nuevo” al dar CHECK ===== --}}
    <template x-if="showModal">
      <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        {{-- overlay --}}
        <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

        {{-- sheet/modal --}}
        <div
          class="relative w-full sm:max-w-md bg-white text-gray-900 border border-gray-200 shadow-2xl
                 sm:rounded-2xl rounded-t-2xl p-5 sm:mx-auto"
          x-trap.noscroll="showModal" x-transition>
          
          {{-- Título --}}
          <h2 class="text-xl sm:text-2xl font-extrabold text-center">Cliente nuevo</h2>
          <div class="mt-2 text-center space-y-0.5">
            <p class="text-sm font-semibold" x-text="selected.nombre || '—'"></p>
            <p class="text-xs text-gray-600" x-text="selected.curp || '—'"></p>
          </div>

          {{-- Formulario documentos y datos --}}
          <form class="mt-4 space-y-4">
            <div>
              <h3 class="text-sm font-semibold mb-2 text-center">Datos del Cliente</h3>
              <div class="grid grid-cols-1 gap-2">
                <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-sm" :value="selected.nombre" readonly>
                <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-sm" :value="selected.apellido_p" readonly>
                <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-sm" :value="selected.apellido_m" readonly>
                <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-sm" :value="selected.curp" readonly>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-semibold mb-2 text-center">Datos del Aval</h3>
              <div class="grid grid-cols-1 gap-2">
                <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-sm" :value="selected.aval_nombre" readonly>
                <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-sm" :value="selected.aval_apellido_p" readonly>
                <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-sm" :value="selected.aval_apellido_m" readonly>
                <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-sm" :value="selected.aval_curp" readonly>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-semibold mb-2 text-center">Documentos faltantes</h3>
              <div class="grid grid-cols-1 gap-2 text-sm">
                <label class="font-medium">INE</label>
                <input type="file" class="w-full text-xs" />
                <label class="font-medium">Comprobante de domicilio</label>
                <input type="file" class="w-full text-xs" />
              </div>
            </div>

            <div>
              <h3 class="text-sm font-semibold mb-2 text-center">Datos adicionales</h3>
              <div class="grid grid-cols-1 gap-2">
                <input type="date" class="w-full px-3 py-2 border rounded-lg text-sm" x-model="selected.fecha_nacimiento">
                <input type="number" step="0.01" placeholder="Monto máximo" class="w-full px-3 py-2 border rounded-lg text-sm" x-model="selected.monto_maximo">
                <input type="text" placeholder="Estatus" class="w-full px-3 py-2 border rounded-lg text-sm" x-model="selected.estatus">
                <label class="flex items-center text-xs"><input type="checkbox" class="mr-2" x-model="selected.tiene_credito_activo">Tiene crédito activo</label>
                <label class="flex items-center text-xs"><input type="checkbox" class="mr-2" x-model="selected.activo">Activo</label>
              </div>
            </div>
          </form>
          {{-- Botones acciones --}}
          <div class="mt-6 grid grid-cols-2 gap-3">
            <button
              class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold shadow-sm transition"
              @click="rechazar()">
              Rechazar
            </button>
            <button
              class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm transition"
              @click="aceptar()">
              Aceptar
            </button>
          </div>

          {{-- Cerrar esquina --}}
          <button class="absolute top-3 right-3 p-2 rounded-lg bg-gray-100 hover:bg-gray-200" @click="closeModal()">✕</button>
        </div>
      </div>
    </template>

  </div>

  {{-- === Alpine.js === --}}
  <script>
    function prospectado() {
      return {
        showModal: false,
        selected: { id:null, nombre:'', apellido_p:'', apellido_m:'', curp:'', ine_url:'', comp_url:'', aval_nombre:'', aval_apellido_p:'', aval_apellido_m:'', aval_curp:'', fecha_nacimiento:'', tiene_credito_activo:false, estatus:'', monto_maximo:'', activo:false },

        openModal(data) {
          this.selected = data;
          this.showModal = true;
        },
        closeModal() {
          this.showModal = false;
          this.selected  = { id:null, nombre:'', apellido_p:'', apellido_m:'', curp:'', ine_url:'', comp_url:'', aval_nombre:'', aval_apellido_p:'', aval_apellido_m:'', aval_curp:'', fecha_nacimiento:'', tiene_credito_activo:false, estatus:'', monto_maximo:'', activo:false };
        },

        // Acciones
        aceptar() {
          // Aquí POST a tu endpoint para aceptar al cliente
          console.log('ACEPTAR', this.selected);
          this.closeModal();
        },
        rechazar() {
          // Aquí POST a tu endpoint para rechazar al cliente
          console.log('RECHAZAR', this.selected);
          this.closeModal();
        },
      }
    }
  </script>
</x-layouts.mobile.mobile-layout>
