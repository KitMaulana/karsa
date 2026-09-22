<div>
    <x-forest-header title="KARSA News" :back="true" />

    <div class="px-5 py-5">
        <div class="flex gap-2">
            @foreach(['semua' => 'Semua', 'berita' => 'Berita', 'edukasi' => 'Edukasi'] as $key => $label)
                <button type="button" wire:click="setTab('{{ $key }}')"
                        class="rounded-full px-4 py-2 text-sm font-medium {{ $tab === $key ? 'bg-forest-800 text-white' : 'bg-leaf-100 text-forest-950' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="mt-5 space-y-4">
            @forelse($posts as $post)
                <a href="{{ route('news.show', $post) }}" class="karsa-card block overflow-hidden">
                    <div class="flex h-28 items-center justify-center bg-leaf-100">
                        @if($post->thumbnail)
                            <img src="{{ $post->thumbnail }}" alt="" class="h-full w-full object-cover">
                        @else
                            <x-icon :name="$post->category === 'edukasi' ? 'book-open' : 'newspaper'" class="h-10 w-10 text-forest-600" />
                        @endif
                    </div>
                    <div class="p-4">
                        <span class="text-xs font-medium uppercase tracking-wide text-forest-600">{{ ucfirst($post->category) }}</span>
                        <p class="mt-1 font-display font-bold text-forest-950">{{ $post->title }}</p>
                        <p class="mt-1 text-xs text-ink-500">{{ $post->published_at?->translatedFormat('d M Y') }}</p>
                    </div>
                </a>
            @empty
                <p class="text-center text-sm text-ink-500">Belum ada artikel.</p>
            @endforelse
        </div>

        <div class="mt-5">{{ $posts->links() }}</div>
    </div>
</div>
