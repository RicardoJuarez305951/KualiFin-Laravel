@props(['as' => 'button', 'href' => null])

@if($as === 'a')
  <a href="{{ $href }}"
     {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-4 py-2 bg-blue-600
                             border border-transparent rounded-md font-semibold text-xs text-white
                             uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900
                             focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25
                             transition']) }}>
    {{ $slot }}
  </a>
@else
  <button {{ $attributes->merge(['type' => 'submit', 'class' => '…mis mismas clases…']) }}>
    {{ $slot }}
  </button>
@endif
