<x-layouts.authenticated>
  <x-slot name="head">
    @livewireStyles
    <title>…</title>
  </x-slot>

  <main>
    <!-- <livewire:solicitud-credito-wizard/> -->
     @livewire(solicitud-credito-wizard)
  </main>

  @livewireScripts
</x-layouts.authenticated>
