{{-- resources/views/supervisor/historial_cliente.blade.php --}}
@php
    use Faker\Factory as Faker;
    $faker = Faker::create('es_MX');

    $clientName = $faker->name();
    $avalName   = $faker->name();

    $supervisor = auth()->check() ? auth()->user()->name : $faker->name();
    $promotor   = $faker->name();

    $totalLoan   = $faker->randomFloat(2, 20000, 100000);
    $creditDate  = now()->subWeeks(rand(1, 20))->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $totalWeeks  = 17;
    $currentWeek = rand(1, $totalWeeks);

    $weeklyAmount = $totalLoan / $totalWeeks;

    $cliente = [
        'direccion' => $faker->streetAddress.', '.$faker->city,
        'telefono'  => $faker->numerify('55########'),
        'garantias' => ['Teléfono','Pantalla','Consola de videojuegos'],
    ];
    $aval = [
        'direccion' => $faker->streetAddress.', '.$faker->city,
        'telefono'  => $faker->numerify('55########'),
        'garantias' => ['Laptop','Motocicleta'],
    ];

    $clienteFotos = [
        ['titulo' => 'INE Frente',             'url' => 'https://picsum.photos/seed/inef/800/600'],
        ['titulo' => 'INE Reverso',            'url' => 'https://picsum.photos/seed/iner/800/600'],
        ['titulo' => 'Comprobante Domicilio',  'url' => 'https://picsum.photos/seed/compdom/900/1200'],
        ['titulo' => 'Firma Pagaré',           'url' => 'https://picsum.photos/seed/firma/900/600'],
    ];
    $avalFotos = [
        ['titulo' => 'INE Aval',               'url' => 'https://picsum.photos/seed/ineaval/800/600'],
        ['titulo' => 'Comprobante Aval',       'url' => 'https://picsum.photos/seed/compaval/900/1200'],
    ];

    function money($v){ return '$'.number_format($v, 2, '.', ','); }
@endphp

<x-layouts.mobile.mobile-layout title="Historial de {{ $clientName }}">
  <style>[x-cloak]{display:none!important}</style>

  <div x-data="historialCliente()" class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md mx-auto space-y-6">

    {{-- INFO CRÉDITO --}}
    <div class="bg-white rounded-2xl shadow-md p-4 space-y-3">
      <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
        <div><p class="font-semibold">Supervisor</p><p>{{ $supervisor }}</p></div>
        <div><p class="font-semibold">Promotor</p><p>{{ $promotor }}</p></div>
        <div><p class="font-semibold">Semanas del crédito</p><p>{{ $totalWeeks }}</p></div>
        <div><p class="font-semibold">Semana actual</p><p>sem {{ $currentWeek }}</p></div>
        <div><p class="font-semibold">Fecha de crédito</p><p>{{ $creditDate }}</p></div>
        <div><p class="font-semibold">Monto</p><p class="font-bold text-green-600">{{ money($totalLoan) }}</p></div>
      </div>
    </div>

    {{-- CLIENTE --}}
    <div class="bg-white rounded-2xl shadow-md p-4 space-y-4">
      <h2 class="text-lg font-bold text-gray-900">👤 Cliente</h2>
      <p class="text-sm font-semibold">{{ $clientName }}</p>

      {{-- Dirección --}}
      <div class="grid grid-cols-[90%_10%] gap-2 items-center">
        <div class="text-sm text-gray-800">{{ $cliente['direccion'] }}</div>
        <div>
          <a href="https://maps.google.com/?q={{ urlencode($cliente['direccion']) }}" target="_blank"
             class="flex justify-center items-center px-3 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">📍</a>
        </div>
      </div>

      {{-- Teléfono --}}
      <div class="grid grid-cols-[90%_10%] gap-2 items-center mt-2">
        <div class="text-sm text-gray-800">{{ $cliente['telefono'] }}</div>
        <div>
          <a href="tel:{{ $cliente['telefono'] }}"
             class="flex justify-center items-center px-3 py-2 text-sm font-semibold bg-green-600 text-white rounded-lg shadow hover:bg-green-700">📞</a>
        </div>
      </div>

      {{-- Garantías --}}
      <div class="bg-gray-50 rounded-xl p-3 shadow-inner space-y-2">
        <p class="font-semibold text-gray-700">Garantías</p>
        <ul class="space-y-2">
          @foreach($cliente['garantias'] as $g)
            <li class="flex justify-between items-center bg-white px-3 py-2 rounded-lg shadow-sm border">
              <span class="text-sm text-gray-800">{{ $g }}</span>
              <button class="text-purple-600 text-lg">📷</button>
            </li>
          @endforeach
        </ul>
        <button class="mt-3 w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 rounded-lg shadow"
                @click="openFotos(@js($clienteFotos), 'Fotografías del Cliente')">
          Archivo Fotografías 📷
        </button>
      </div>
    </div>

    {{-- AVAL --}}
    <div class="bg-white rounded-2xl shadow-md p-4 space-y-4">
      <h2 class="text-lg font-bold text-gray-900">🧑‍🤝‍🧑 Aval</h2>
      <p class="text-sm font-semibold">{{ $avalName }}</p>

      {{-- Dirección --}}
      <div class="grid grid-cols-[90%_10%] gap-2 items-center">
        <div class="text-sm text-gray-800">{{ $aval['direccion'] }}</div>
        <div>
          <a href="https://maps.google.com/?q={{ urlencode($aval['direccion']) }}" target="_blank"
             class="flex justify-center items-center px-3 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">📍</a>
        </div>
      </div>

      {{-- Teléfono --}}
      <div class="grid grid-cols-[90%_10%] gap-2 items-center mt-2">
        <div class="text-sm text-gray-800">{{ $aval['telefono'] }}</div>
        <div>
          <a href="tel:{{ $aval['telefono'] }}"
             class="flex justify-center items-center px-3 py-2 text-sm font-semibold bg-green-600 text-white rounded-lg shadow hover:bg-green-700">📞</a>
        </div>
      </div>

      {{-- Garantías --}}
      <div class="bg-gray-50 rounded-xl p-3 shadow-inner space-y-2">
        <p class="font-semibold text-gray-700">Garantías</p>
        <ul class="space-y-2">
          @foreach($aval['garantias'] as $g)
            <li class="flex justify-between items-center bg-white px-3 py-2 rounded-lg shadow-sm border">
              <span class="text-sm text-gray-800">{{ $g }}</span>
              <button class="text-purple-600 text-lg">📷</button>
            </li>
          @endforeach
        </ul>
        <button class="mt-3 w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 rounded-lg shadow"
                @click="openFotos(@js($avalFotos), 'Fotografías del Aval')">
          Archivo Fotografías 📷
        </button>
      </div>
    </div>

    {{-- TABLA SEMANAS --}}
    <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-300 bg-white">
      <table class="w-full text-sm table-fixed border-collapse">
        <thead class="bg-gray-100 text-gray-700">
          <tr><th class="p-2 text-left">Semana</th><th class="p-2 text-right">Monto</th><th class="p-2 text-center">Estado</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          @for($i=1;$i<=$totalWeeks;$i++)
            @php
              if($i < $currentWeek){ $status=['Pagado','Anticipo','Atrasado'][array_rand(['Pagado','Anticipo','Atrasado'])]; }
              else{ $status='Pagar'; }
            @endphp
            <tr>
              <td class="p-2">sem {{ $i }}</td>
              <td class="p-2 text-right">{{ money($weeklyAmount) }}</td>
              <td class="p-2 text-center">{{ $status }}</td>
            </tr>
          @endfor
        </tbody>
      </table>
    </div>

    {{-- BOTÓN REGRESAR --}}
    <a href="{{ route('mobile.supervisor.cartera') }}"
       class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-xl text-center shadow-md transition">REGRESAR</a>

    {{-- ===== MODAL FOTOGRAFÍAS ===== --}}
    <template x-if="showFotos">
      <div class="fixed inset-0 z-[55] flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="closeFotos()"></div>
        <div class="relative w-full sm:max-w-md bg-white text-gray-900 border border-gray-200 shadow-2xl sm:rounded-2xl rounded-t-2xl p-5 sm:mx-auto"
             x-trap.noscroll="showFotos" x-transition>
          <div class="flex items-start justify-between mb-3">
            <h3 class="text-base font-bold" x-text="albumTitulo"></h3>
            <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200" @click="closeFotos()">✕</button>
          </div>
          <div class="space-y-3 max-h-[70vh] overflow-y-auto">
            <template x-for="(item,i) in fotosList" :key="i">
              <div class="flex items-center justify-between gap-3 rounded-lg border p-2">
                <div class="min-w-0"><p class="text-sm font-medium truncate" x-text="item.titulo"></p></div>
                <div class="flex items-center gap-2 shrink-0">
                  <button class="px-2 py-1 text-xs rounded-md bg-purple-600 hover:bg-purple-700 text-white"
                          @click="zoomImg=item.url">📷 Ver</button>
                  <a :href="item.url" target="_blank"
                     class="px-2 py-1 text-xs rounded-md bg-gray-100 hover:bg-gray-200 text-gray-800">Abrir</a>
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
          this.albumTitulo = titulo || 'Fotografías'
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
