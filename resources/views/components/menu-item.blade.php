@props(['href', 'icon' => null, 'title', 'subtitle' => null])

<a href="{{ $href }}" {{ $attributes->class(['flex items-center gap-4 rounded-2xl bg-white px-4 py-3.5 border border-leaf-100 transition hover:border-pine-400/60']) }}>
    @if($icon)
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full" style="background: linear-gradient(135deg, var(--color-leaf-100), var(--color-risk-mid) 160%);">
            <x-icon :name="$icon" class="h-5 w-5 text-forest-800" />
        </span>
    @endif

    <span class="flex-1 min-w-0">
        <span class="block font-semibold text-forest-950">{{ $title }}</span>
        @if($subtitle)
            <span class="block text-sm text-ink-500">{{ $subtitle }}</span>
        @endif
    </span>

    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 shrink-0 text-ink-500">
        <path d="M9 18l6-6-6-6" />
    </svg>
</a>
