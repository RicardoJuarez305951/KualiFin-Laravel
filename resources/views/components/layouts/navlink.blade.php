@props(['route', 'icon', 'text'])

@php
    $isActive = request()->routeIs($route);
@endphp

<div class="relative">
    @if($isActive)
        <span class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold bg-blue-600 text-white border-l-4 border-blue-800" aria-current="page">
            <span class="flex items-center justify-center w-8 h-8 text-lg bg-white/20 rounded-lg">{{ $icon }}</span>
            <span class="whitespace-nowrap font-semibold">{{ $text }}</span>
        </span>
    @else
        <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition-colors duration-150">
            <span class="flex items-center justify-center w-8 h-8 text-lg bg-slate-100 rounded-lg">{{ $icon }}</span>
            <span class="whitespace-nowrap font-medium">{{ $text }}</span>
        </a>
    @endif
</div>
