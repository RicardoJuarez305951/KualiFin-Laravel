{{-- resources/views/promotora/promotora_index.blade.php --}}
<x-layouts.promotora_mobile.mobile-layout title="Panel Promotora">
  <div class="bg-gray-100 min-h-screen p-4 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md space-y-6">
      
      {{-- Saludo dinámico --}}
      <div class="text-center space-y-1">
        <h1 class="text-2xl font-bold text-gray-900 uppercase">¡Hola {{ auth()->user()->name }}!</h1>
        <p class="text-gray-600 text-sm">Cada día es una nueva oportunidad para crecer...</p>
      </div>

      {{-- Botones principales --}}
      <div class="space-y-4">
        {{-- Mi Objetivo --}}
        <a href="{{ route('promotora.objetivo') }}"
           class="flex items-center justify-center gap-2 w-full py-3 rounded-lg bg-blue-800 text-white font-semibold hover:bg-blue-900 shadow-sm hover:shadow transition ring-1 ring-blue-900/10">
          {{-- Icono: Target --}}
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
               viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 12c1.657 0 3-1.343 3-3S13.657 6 12 6s-3 1.343-3 3 1.343 3 3 3zm0 0v8m0-8H4m8 0h8" />
          </svg>
          <span>Mi Objetivo</span>
        </a>

        {{-- Mi Cartera --}}
        <a href="{{ route('promotora.cartera') }}"
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
        <a href="{{ route('promotora.venta') }}"
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
</x-layouts.promotora_mobile.mobile-layout>
