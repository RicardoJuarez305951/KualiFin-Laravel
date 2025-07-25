{{-- resources/views/promotora/promotora_index.blade.php --}}
<x-layouts.promotora_mobile.mobile-layout title="Panel Promotora">
  <div class="bg-gray-100 min-h-screen p-4 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow p-6 w-full max-w-md">
      {{-- Saludo dinámico --}}
      <h1 class="text-2xl font-bold text-center text-black uppercase mb-2">
        ¡Hola {{ auth()->user()->name }}!
      </h1>
      <p class="text-center text-gray-700 mb-6">
        Cada día es una nueva oportunidad para crecer...
      </p>

      {{-- Botones principales --}}
      <div class="space-y-4">
        

        <x-primary-button
          as="a"
          href="{{ route('promotora.objetivo') }}"
          class="w-full flex items-center justify-center space-x-2 
                 bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg"
        >
          <svg xmlns="http://www.w3.org/2000/svg"
               class="w-5 h-5 text-white"
               fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4-.895 4-2-1.79-2-4-2zm0-4a8 8 0 100 16 8 8 0 000-16z"/>
          </svg>
          <span>Mi Objetivo</span>
        </x-primary-button>

        <x-primary-button
          as="a"
          href="{{ route('promotora.cartera') }}"
          class="w-full flex items-center justify-center space-x-2 
                 bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg"
        >
          <svg xmlns="http://www.w3.org/2000/svg"
               class="w-5 h-5 text-white"
               fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 8h14M5 12h14M5 16h14M4 6h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/>
          </svg>
          <span>Mi Cartera</span>
        </x-primary-button>

        <x-primary-button
          as="a"
          href="{{ route('promotora.venta') }}"
          class="w-full flex items-center justify-center space-x-2 
                 bg-blue-800 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg"
        >
          <svg xmlns="http://www.w3.org/2000/svg"
               class="w-5 h-5 text-white"
               fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h2l.344 2M7 13h10l4-8H5.344M7 13l-1.657 5.486A1 1 0 016.344 20h11.312a1 1 0 00.999-.766L21 13M7 13H5.344M17 21a2 2 0 100-4 2 2 0 000 4zm-8 0a2 2 0 100-4 2 2 0 000 4z"/>
          </svg>
          <span>Mi Venta</span>
        </x-primary-button>
        
      </div>

      {{-- Salir --}}
      <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button
          type="submit"
          class="w-full text-center text-black hover:text-gray-800 font-medium"
        >
          Salir
        </button>
      </form>
    </div>
  </div>
</x-layouts.promotora_mobile.mobile-layout>
