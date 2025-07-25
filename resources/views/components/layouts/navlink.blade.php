@props(['route', 'icon', 'text'])

<li>
    <a 
        href="{{ route($route) }}"
        class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group {{ request()->routeIs($route) ? 'bg-gray-100' : '' }}"
        title="{{ $text }}"
    >
        <span class="text-xl min-w-[1.5rem]">{{ $icon }}</span>
        <span class="ml-3 transition-opacity duration-300" x-show="!sidebarCollapsed">{{ $text }}</span>
    </a>
</li>
