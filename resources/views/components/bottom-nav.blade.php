@php
    $items = [
        ['href' => route('menu'), 'icon' => 'home', 'label' => 'Beranda', 'active' => request()->routeIs('menu')],
        ['href' => route('peta'), 'icon' => 'map', 'label' => 'Peta', 'active' => request()->routeIs('peta')],
        ['href' => route('notifikasi'), 'icon' => 'bell', 'label' => 'Notifikasi', 'active' => request()->routeIs('notifikasi')],
        ['href' => route('profil'), 'icon' => 'user', 'label' => 'Profil', 'active' => request()->routeIs('profil')],
    ];
@endphp

<nav class="fixed inset-x-0 bottom-0 z-30 mx-auto flex max-w-[480px] items-stretch border-t border-leaf-100 bg-white/95 backdrop-blur-sm"
     style="padding-bottom: env(safe-area-inset-bottom, 0);">
    @foreach($items as $item)
        <a href="{{ $item['href'] }}"
           class="flex flex-1 flex-col items-center gap-1 py-2.5 text-xs font-medium {{ $item['active'] ? 'text-forest-800' : 'text-ink-500' }}"
           aria-current="{{ $item['active'] ? 'page' : 'false' }}">
            <x-icon :name="$item['icon']" class="h-5 w-5" />
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
