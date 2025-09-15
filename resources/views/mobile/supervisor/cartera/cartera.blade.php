<x-layouts.mobile.mobile-layout title="Cartera Supervisor">
  <div class="mx-auto w-[22rem] sm:w-[26rem] p-4 sm:p-6 space-y-6">
    <section class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-900/5 overflow-hidden">
      <div class="p-5">
        <h2 class="text-base font-bold text-gray-900 mb-3">Promotores</h2>
        <div class="space-y-3">
          @forelse($promotores as $p)
            <a href="{{ route('mobile.supervisor.cartera_promotor', $p->id) }}" class="block rounded-xl border border-gray-100 p-3 shadow-md hover:shadow transition">
              <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-900">{{ $p->nombre }} {{ $p->apellido_p }} {{ $p->apellido_m }}</span>
              </div>
            </a>
          @empty
            <p class="text-sm text-gray-500">No hay promotores registrados.</p>
          @endforelse
        </div>
      </div>
    </section>

    <section class="grid grid-cols-3 gap-3">
      <a href="{{ url()->previous() }}" class="flex items-center justify-center rounded-xl border border-gray-300 text-white text-sm font-semibold px-3 py-2 bg-blue-600 hover:bg-blue-700 shadow-sm">
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
