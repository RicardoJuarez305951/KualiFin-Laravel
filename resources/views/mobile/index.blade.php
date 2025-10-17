{{-- resources/views/promotor/promotor_index.blade.php --}}
<x-layouts.mobile.mobile-layout title="Panel Mobile">
    <div class="bg-gray-200 rounded-2xl shadow-lg p-6 w-full max-w-md space-y-6">
      
      {{-- Saludo dinámico --}}
      <div class="text-center space-y-1">
        <h1 class="text-2xl font-bold text-gray-900 uppercase">¡Hola {{ auth()->user()->name }}!</h1>
        <p class="text-gray-600 text-sm">{{ $mensajeDelDia }}</p>
      </div>

      {{-- Botones principales --}}
      <div class="space-y-4">
        @unlessrole('ejecutivo|administrativo|superadmin')
        {{-- Mi Objetivo --}}
          <a href="{{ route("mobile.$role.objetivo") }}"
           class="flex items-center justify-center gap-2 w-full py-3 rounded-lg bg-blue-800 text-white font-semibold hover:bg-blue-900 shadow-sm hover:shadow transition ring-1 ring-blue-900/10">
          {{-- Icono: Target --}}
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
               viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 12c1.657 0 3-1.343 3-3S13.657 6 12 6s-3 1.343-3 3 1.343 3 3 3zm0 0v8m0-8H4m8 0h8" />
          </svg>
          <span>Mi Objetivo</span>
        </a>
        @endunlessrole

        {{-- Mi Cartera --}}
          <a href="{{ route("mobile.$role.cartera") }}"
           class="flex items-center justify-center gap-2 w-full py-3 rounded-lg bg-blue-800 text-white font-semibold hover:bg-blue-900 shadow-sm hover:shadow transition ring-1 ring-blue-900/10">
          {{-- Icono: Wallet --}}
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
               viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21 12.75V7.5a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 7.5v9a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 16.5v-4.5z" />
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.5 12a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
          </svg>
          <span>Mi Cartera</span>
        </a>

        {{-- Mi Venta --}}
          <a href="{{ route("mobile.$role.venta") }}"
           class="flex items-center justify-center gap-2 w-full py-3 rounded-lg bg-blue-800 text-white font-semibold hover:bg-blue-900 shadow-sm hover:shadow transition ring-1 ring-blue-900/10">
          {{-- Icono: Shopping cart --}}
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
               viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 3h1.5l1.72 12.094a1.5 1.5 0 001.49 1.406h10.06a1.5 1.5 0 001.49-1.406L21.75 6.75H5.25" />
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm9 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
          </svg>
          <span>Mi Venta</span>
        </a>

        @role('supervisor|ejecutivo|administrativo|superadmin')
          {{-- Busquedas --}}
          <a href="{{ route("mobile.supervisor.busqueda") }}"
            class="flex items-center justify-center gap-2 w-full py-3 rounded-lg bg-blue-800 text-white font-semibold hover:bg-blue-900 shadow-sm hover:shadow transition ring-1 ring-blue-900/10">
            {{-- Icono Lupa --}}
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" />
            </svg>
            <span>Busquedas</span>
          </a>
          @role('supervisor|administrativo|superadmin')
          {{-- Apertura --}}
          <a href="{{ route("mobile.supervisor.apertura") }}"
           class="flex items-center justify-center gap-2 w-full py-3 rounded-lg bg-blue-800 text-white font-semibold hover:bg-blue-900 shadow-sm hover:shadow transition ring-1 ring-blue-900/10">
          {{-- Icono: Candado abierto --}}
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 11V7a3 3 0 00-6 0v4m-3 0h12v9H6v-9z" />
            </svg>
          <span>Apertura</span>
          </a>
          @endrole
        @endrole

        @role('ejecutivo|administrativo|superadmin')
        {{-- Informes --}}
        <a href="{{ route("mobile.$role.informes") }}"
           class="flex items-center justify-center gap-2 w-full py-3 rounded-lg bg-blue-800 text-white font-semibold hover:bg-blue-900 shadow-sm hover:shadow transition ring-1 ring-blue-900/10">
          {{-- Icono: Document / Report --}}
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
              viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
            d="M19.5 8.25V19.5a2.25 2.25 0 01-2.25 2.25h-10.5A2.25 2.25 0 014.5 19.5V4.5A2.25 2.25 0 016.75 2.25h6.75L19.5 8.25z" />
            <path stroke-linecap="round" stroke-linejoin="round"
            d="M9 12h6m-6 3.75h6" />
          </svg>
          <span>Informes</span>
        </a>
        @endrole

        <div class="space-y-4">
            <a href="{{ route('mobile.misdesembolsos') }}"
               class="flex items-center justify-center gap-2 w-full py-3 rounded-lg bg-blue-800 text-white font-semibold hover:bg-blue-900 shadow-sm hover:shadow transition ring-1 ring-blue-900/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25v13.5A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75V5.25z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8.25 9h7.5m-7.5 3.75h7.5m-7.5 3.75h4.5" />
                </svg>
                <span>Mis Desembolsos</span>
            </a>
        </div>

      </div>

      {{-- Divider --}}
      <div class="border-t border-gray-200 pt-4">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit"
                  class="w-full text-center text-gray-700 hover:text-gray-900 font-medium text-sm transition">
            Cerrar sesión
          </button>
        </form>
      </div>
    </div>
  </div>
</x-layouts.mobile.mobile-layout>

