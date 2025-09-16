@props([
  'tag' => 'div',
])

<{{ $tag }}
  {{ $attributes->merge([
    'class' => 'overflow-y-auto min-h-0', // min-h-0 es CLAVE dentro de flex
  ]) }}
  x-init="$el.style.webkitOverflowScrolling='touch'"  {{-- inercia iOS --}}
>
  {{ $slot }}
</{{ $tag }}>
