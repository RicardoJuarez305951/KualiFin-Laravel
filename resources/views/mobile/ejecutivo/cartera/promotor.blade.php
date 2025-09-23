<x-layouts.mobile.mobile-layout title="Cartera Promotor">
  <div class="mx-auto w-[22rem] sm:w-[26rem] p-4 sm:p-6 space-y-6">
    <section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
      <div class="p-5">
        <h2 class="text-base font-bold text-gray-900 mb-3">{{ $promotor->nombre }} {{ $promotor->apellido_p }} {{ $promotor->apellido_m }}</h2>
        @php $diasPago = trim((string) ($promotor->dias_de_pago ?? '')); @endphp
        @if($diasPago !== '')
          <p class="text-xs text-gray-500 mb-4">Días de pago: {{ $diasPago }}</p>
        @endif
        <div class="space-y-3">
          @forelse($clientes as $c)
            <a href="{{ route('mobile.supervisor.cliente_historial', $c->id) }}" class="block rounded-xl border border-gray-100 p-3 shadow-md hover:shadow transition">
              <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-900">{{ $c->nombre }} {{ $c->apellido_p }} {{ $c->apellido_m }}</span>
                @if($c->credito)
                  <span class="text-xs text-gray-500">Crédito #{{ $c->credito->id }}</span>
                @endif
              </div>
            </a>
          @empty
            <p class="text-sm text-gray-500">No hay clientes registrados.</p>
          @endforelse
        </div>
      </div>
    </section>

    <section class="grid grid-cols-3 gap-3">
      <a href="{{ route('mobile.supervisor.cartera') }}" class="flex items-center justify-center rounded-xl border border-gray-300 text-white text-sm font-semibold px-3 py-2 bg-blue-600 hover:bg-blue-700 shadow-sm">
        Regresar
      </a>

      <a href="{{ url()->current() }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 text-white text-sm font-semibold px-3 py-2 hover:bg-blue-700 shadow">
        Actualizar
      </a>

      <a href="{{ route('mobile.supervisor.reporte') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 text-white text-sm font-semibold px-3 py-2 hover:bg-indigo-700 shadow">
        Reporte
      </a>
    </section>
  </div>
</x-layouts.mobile.mobile-layout>
