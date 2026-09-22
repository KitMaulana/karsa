@php
    $navItems = [
        ['route' => 'admin.dashboard', 'icon' => 'gauge', 'label' => 'Dasbor'],
        ['route' => 'admin.hotspots.index', 'icon' => 'flame', 'label' => 'Hotspot'],
        ['route' => 'admin.regions.index', 'icon' => 'map', 'label' => 'Wilayah'],
        ['route' => 'admin.risk-model.index', 'icon' => 'settings', 'label' => 'Model Risiko (AHP)'],
        ['route' => 'admin.validation.index', 'icon' => 'check', 'label' => 'Validasi Historis'],
        ['route' => 'admin.reports.index', 'icon' => 'camera', 'label' => 'Laporan Warga'],
        ['route' => 'admin.alerts.index', 'icon' => 'megaphone', 'label' => 'Peringatan'],
        ['route' => 'admin.recommendations.index', 'icon' => 'leaf', 'label' => 'Rekomendasi Aksi'],
        ['route' => 'admin.posts.index', 'icon' => 'newspaper', 'label' => 'KARSA News'],
        ['route' => 'admin.programs.index', 'icon' => 'gift', 'label' => 'Program & Donasi'],
        ['route' => 'admin.users.index', 'icon' => 'users', 'label' => 'Pengguna'],
        ['route' => 'admin.authority-contacts.index', 'icon' => 'phone', 'label' => 'Kontak Instansi'],
        ['route' => 'admin.settings.index', 'icon' => 'settings', 'label' => 'Pengaturan'],
        ['route' => 'admin.activity-logs.index', 'icon' => 'book-open', 'label' => 'Log Aktivitas'],
    ];
@endphp

<div class="flex h-16 items-center gap-2 border-b border-leaf-100 px-5">
    <span class="karsa-wordmark text-forest-800">KARSA</span>
    <span class="text-xs text-ink-500">Admin</span>
</div>

<nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
    @foreach($navItems as $item)
        @continue(! \Illuminate\Support\Facades\Route::has($item['route']))
        <a href="{{ route($item['route']) }}"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs($item['route'].'*') ? 'bg-leaf-100 text-forest-800' : 'text-ink-500 hover:bg-leaf-100/60' }}">
            <x-icon :name="$item['icon']" class="h-4.5 w-4.5" />
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
