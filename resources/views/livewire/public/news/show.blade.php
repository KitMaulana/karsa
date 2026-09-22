<div>
    <x-forest-header :title="ucfirst($post->category)" :back="true" />

    <div class="px-5 py-6">
        @if($post->thumbnail)
            <img src="{{ $post->thumbnail }}" alt="" class="mb-4 h-48 w-full rounded-2xl object-cover">
        @endif

        <h1 class="font-display text-xl font-bold text-forest-950">{{ $post->title }}</h1>
        <p class="mt-1 text-xs text-ink-500">{{ $post->published_at?->translatedFormat('d M Y') }}</p>

        <div class="karsa-article mt-4 text-sm leading-relaxed text-ink-500">
            {!! $post->body !!}
        </div>
    </div>
</div>
