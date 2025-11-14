@props([
    'title',
    'description' => null,
    'href' => null,
    'buttonLabel' => 'Explorar',
    'gradient' => 'from-blue-50 to-blue-100',
    'borderColor' => 'border-blue-200',
    'iconBg' => 'bg-blue-500',
    'iconColor' => 'text-white',
    'buttonColor' => 'bg-blue-600 hover:bg-blue-700',
    'disabled' => false,
    'tag' => null,
    'tagColor' => 'text-blue-600',
    'target' => null,
])

@php
    $hasCustomCta = !$slot->isEmpty();
@endphp

<div class="bg-gradient-to-br {{ $gradient }} rounded-lg p-6 border {{ $borderColor }} hover:shadow-md transition-all duration-200 hover:scale-[1.01] flex flex-col">
    <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 {{ $iconBg }} rounded-full flex items-center justify-center">
            @isset($icon)
                {{ $icon }}
            @else
                <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                </svg>
            @endisset
        </div>
        @if ($tag)
            <span class="{{ $tagColor }} text-sm font-semibold uppercase tracking-wide">{{ $tag }}</span>
        @endif
    </div>
    <div class="space-y-2 flex-1">
        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        @if ($description)
            <p class="text-sm text-gray-600">
                {{ $description }}
            </p>
        @endif
    </div>
    <div class="mt-4">
        @if ($hasCustomCta)
            {{ $slot }}
        @elseif ($disabled)
            <button disabled class="w-full bg-gray-300 text-gray-500 font-medium py-3 px-4 rounded-lg cursor-not-allowed">
                {{ $buttonLabel }}
            </button>
        @elseif ($href)
            <a
                href="{{ $href }}"
                @if ($target) target="{{ $target }}" @endif
                class="w-full {{ $buttonColor }} text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
            >
                {{ $buttonLabel }}
            </a>
        @endif
    </div>
</div>
