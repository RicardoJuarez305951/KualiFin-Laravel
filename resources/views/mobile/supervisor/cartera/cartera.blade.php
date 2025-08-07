{{-- resources/views/mobile/cartera.blade.php --}}
<x-layouts.mobile.mobile-layout title="Tu Cartera">
    <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-md space-y-6">
    <h2 class="text-center text-2xl font-bold text-gray-800">Tu Cartera</h2>

    <div class="space-y-4">
      {{-- Cartera vigente --}}
      <a href="{{ route("mobile.$role.vigente") }}"
         class="block text-center py-4 bg-blue-800 rounded-2xl shadow-md text-lg font-semibold text-white hover:bg-blue-900 transition">
        CARTERA VIGENTE
      </a>

      {{-- Cartera vencida --}}
      <a href="{{ route("mobile.$role.vencida") }}"
         class="block text-center py-4 bg-red-600 rounded-2xl shadow-md text-lg font-semibold text-white hover:bg-red-700 transition">
        CARTERA VENCIDA
      </a>

      {{-- Cartera inactiva --}}
      <a href="{{ route("mobile.$role.inactiva") }}"
         class="block text-center py-4 bg-gray-600 rounded-2xl shadow-md text-lg font-semibold text-white hover:bg-gray-700 transition">
        CARTERA INACTIVA
      </a>
    </div>

    <a href="{{ route("mobile.$role.index") }}"
       class="block mt-6 text-center text-blue-800 hover:text-blue-900 font-medium py-3">
      ← Regresar
    </a>
  </div>
</x-layouts.mobile.mobile-layout>
