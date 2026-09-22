<div x-data="{
        slide: 0,
        total: 3,
        touchX: null,
        onTouchStart(e) { this.touchX = e.touches[0].clientX },
        onTouchEnd(e) {
            if (this.touchX === null) return;
            const delta = e.changedTouches[0].clientX - this.touchX;
            if (delta < -40 && this.slide < this.total - 1) this.slide++;
            if (delta > 40 && this.slide > 0) this.slide--;
            this.touchX = null;
        },
        finish() { localStorage.setItem('karsa_onboarded', '1'); window.location.href = '{{ route('menu') }}' }
     }"
     class="flex min-h-screen flex-col bg-cream-50">

    <div class="flex justify-end px-5 pt-5">
        <button type="button" @click="finish()" class="text-sm font-medium text-ink-500">Lewati</button>
    </div>

    <div class="flex-1 overflow-hidden" @touchstart="onTouchStart" @touchend="onTouchEnd">
        {{-- Slide 1: Pantau risiko --}}
        <div x-show="slide === 0" x-transition class="flex h-full flex-col items-center justify-center px-8 text-center">
            <svg viewBox="0 0 200 160" class="h-40 w-52" aria-hidden="true">
                <rect x="10" y="20" width="180" height="120" rx="16" fill="#DDEBD3" />
                <path d="M30 110 L70 70 L100 95 L140 50 L170 90" stroke="#2A6E43" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                <circle cx="70" cy="70" r="6" fill="#F28C38" />
                <circle cx="140" cy="50" r="6" fill="#D63B2F" />
                <path d="M100 30 L100 20 M107 33 L114 26 M93 33 L86 26" stroke="#1D4B2E" stroke-width="3" stroke-linecap="round" />
            </svg>
            <h2 class="mt-8 font-display text-xl font-bold text-forest-950">Pantau risiko karhutla</h2>
            <p class="mt-2 text-sm text-ink-500">Lihat tingkat risiko kebakaran hutan dan lahan di wilayah Anda secara langsung, kapan saja.</p>
        </div>

        {{-- Slide 2: Dapat peringatan --}}
        <div x-show="slide === 1" x-transition class="flex h-full flex-col items-center justify-center px-8 text-center">
            <svg viewBox="0 0 200 160" class="h-40 w-52" aria-hidden="true">
                <circle cx="100" cy="60" r="40" fill="#DDEBD3" />
                <path d="M100 40a20 20 0 0 0-20 20c0 14-6 18-6 18h52s-6-4-6-18a20 20 0 0 0-20-20Z" fill="#1D4B2E" />
                <circle cx="100" cy="86" r="4" fill="#1D4B2E" />
                <rect x="60" y="115" width="35" height="22" rx="11" fill="#F5D35C" />
                <text x="77" y="130" font-size="11" text-anchor="middle" fill="#4A3B00" font-family="sans-serif">Sedang</text>
                <path d="M100 126 L112 126" stroke="#5B7263" stroke-width="3" stroke-linecap="round" />
                <rect x="115" y="115" width="35" height="22" rx="11" fill="#F28C38" />
                <text x="132" y="130" font-size="11" text-anchor="middle" fill="#fff" font-family="sans-serif">Tinggi</text>
            </svg>
            <h2 class="mt-8 font-display text-xl font-bold text-forest-950">Dapat peringatan dini</h2>
            <p class="mt-2 text-sm text-ink-500">Terima notifikasi begitu tingkat risiko di wilayah pantauan Anda naik.</p>
        </div>

        {{-- Slide 3: Lapor & jaga hutan --}}
        <div x-show="slide === 2" x-transition class="flex h-full flex-col items-center justify-center px-8 text-center">
            <svg viewBox="0 0 200 160" class="h-40 w-52" aria-hidden="true">
                <rect x="55" y="35" width="90" height="65" rx="12" fill="#1D4B2E" />
                <rect x="70" y="20" width="60" height="20" rx="8" fill="#1D4B2E" />
                <circle cx="100" cy="67" r="18" fill="#DDEBD3" />
                <circle cx="100" cy="67" r="9" fill="#2A6E43" />
                <path d="M50 130 L80 110 L105 125 L135 100 L155 118" stroke="#4E9A5F" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <h2 class="mt-8 font-display text-xl font-bold text-forest-950">Lapor dan jaga hutan</h2>
            <p class="mt-2 text-sm text-ink-500">Kirim laporan titik asap atau api secepatnya, bantu petugas merespons lebih cepat.</p>
        </div>
    </div>

    <div class="flex items-center justify-center gap-2 py-4">
        <template x-for="i in total" :key="i">
            <span class="h-2 rounded-full bg-leaf-100 transition-all"
                  :class="(i - 1) === slide ? 'w-6 bg-forest-800' : 'w-2'"></span>
        </template>
    </div>

    <div class="space-y-3 px-6 pb-8">
        <button type="button" x-show="slide < total - 1" @click="slide++" class="karsa-btn-primary">Lanjut</button>
        <button type="button" x-show="slide === total - 1" x-cloak @click="finish()" class="karsa-btn-primary">Mulai</button>
        <p class="text-center text-sm text-ink-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-forest-800">Masuk</a>
        </p>
    </div>
</div>
