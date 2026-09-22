<div
    x-data="laporForm()"
    x-init="init()"
    @request-geolocation.window="getLocation()"
>
    <x-forest-header title="Pelaporan Karhutla" :back="true" />

    <div class="px-5 py-6">
        <template x-if="!online">
            <div class="mb-4 rounded-2xl bg-risk-mid/20 p-3 text-center text-xs font-medium text-[#4A3B00]">
                Anda sedang offline. Laporan akan disimpan di perangkat dan terkirim otomatis saat online kembali.
            </div>
        </template>

        @if ($submitted)
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <x-icon name="check" class="h-14 w-14 rounded-full bg-forest-800 p-3 text-white" />
                <h2 class="mt-4 font-display text-lg font-bold text-forest-950">Laporan terkirim</h2>
                <p class="mt-1 text-sm text-ink-500">Terima kasih atas partisipasi Anda menjaga hutan kita.</p>
                <a href="{{ route('lapor.saya') }}" class="karsa-btn-primary mt-6 max-w-[220px]">Lihat laporan saya</a>
            </div>
        @else
            <div x-show="queuedOffline" x-cloak class="flex flex-col items-center justify-center py-16 text-center">
                <x-icon name="upload" class="h-14 w-14 rounded-full bg-risk-mid/40 p-3 text-[#4A3B00]" />
                <h2 class="mt-4 font-display text-lg font-bold text-forest-950">Laporan tersimpan</h2>
                <p class="mt-1 text-sm text-ink-500">Akan terkirim otomatis saat perangkat Anda online.</p>
                <a href="{{ route('menu') }}" class="karsa-btn-primary mt-6 max-w-[220px]">Kembali ke menu</a>
            </div>

            <form x-show="!queuedOffline" @submit="if (!online) { $event.preventDefault(); queuedOffline = true; }" wire:submit="submit" class="space-y-5" enctype="multipart/form-data">
                {{-- Honeypot: tersembunyi dari manusia, bot pengisi form otomatis akan mengisinya --}}
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off"
                       class="absolute -left-[9999px] h-0 w-0 opacity-0" aria-hidden="true">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-forest-950">Lokasi kejadian</label>
                    <div class="relative h-48 overflow-hidden rounded-2xl border border-leaf-100" wire:ignore>
                        <div x-ref="miniMap" class="h-full w-full"></div>
                    </div>
                    <button type="button" @click="getLocation()" class="mt-2 flex items-center gap-1.5 text-sm font-semibold text-forest-800">
                        <x-icon name="location" class="h-4 w-4" /> Gunakan lokasi saya
                    </button>
                    @error('lat') <p class="mt-1 text-xs text-risk-extreme">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-forest-950">Foto/video</label>
                    <label class="flex h-32 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-leaf-100 text-ink-500">
                        <x-icon name="camera" class="h-6 w-6" />
                        <span class="mt-1 text-xs">Ambil foto/video atau pilih dari galeri</span>
                        <input type="file" x-ref="fileInput" wire:model="media" accept="image/*,video/*" capture="environment" multiple class="hidden">
                    </label>
                    <div wire:loading wire:target="media" class="mt-1 text-xs text-ink-500">Mengunggah…</div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($media as $file)
                            <img src="{{ $file->temporaryUrl() }}" class="h-16 w-16 rounded-xl object-cover" alt="Pratinjau">
                        @endforeach
                    </div>
                    @error('media') <p class="mt-1 text-xs text-risk-extreme">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-forest-950">Jenis kejadian</label>
                    <select wire:model="type" class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm">
                        <option value="">Pilih jenis kejadian</option>
                        <option value="asap">Asap</option>
                        <option value="api_kecil">Api kecil</option>
                        <option value="api_besar">Api besar</option>
                        <option value="pembakaran_lahan">Pembakaran lahan</option>
                    </select>
                    @error('type') <p class="mt-1 text-xs text-risk-extreme">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-forest-950">Deskripsi</label>
                    <textarea wire:model="description" rows="4" placeholder="Jelaskan kondisi yang Anda lihat..."
                              class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm"></textarea>
                    @error('description') <p class="mt-1 text-xs text-risk-extreme">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="karsa-btn-primary" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Kirim laporan</span>
                    <span wire:loading wire:target="submit">Mengirim…</span>
                </button>
            </form>
        @endif
    </div>
</div>

<script>
function laporForm() {
    return {
        online: navigator.onLine,
        queuedOffline: false,
        map: null,
        marker: null,

        init() {
            window.addEventListener('online', () => this.online = true);
            window.addEventListener('offline', () => this.online = false);

            this.map = L.map(this.$refs.miniMap).setView([-6.12, 106.15], 11);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap &copy; CARTO',
            }).addTo(this.map);

            this.marker = L.marker(this.map.getCenter(), { draggable: true }).addTo(this.map);
            this.marker.on('dragend', () => {
                const pos = this.marker.getLatLng();
                this.$wire.setLocation(pos.lat, pos.lng, null);
            });

            this.getLocation();
        },

        getLocation() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(pos => {
                const { latitude, longitude, accuracy } = pos.coords;
                this.map.setView([latitude, longitude], 15);
                this.marker.setLatLng([latitude, longitude]);
                this.$wire.setLocation(latitude, longitude, accuracy);
            }, () => {}, { enableHighAccuracy: true, timeout: 8000 });
        },
    };
}
</script>
