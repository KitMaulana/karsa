<div
    x-data="petaRisiko({
        districts: @js($districts),
        apiHotspots: '{{ url('/api/v1/hotspots') }}',
    })"
>
    <x-forest-header title="Peta Risiko Karhutla" :back="true" />

    <div class="relative -mt-4 px-5">
        <div class="karsa-card overflow-hidden">
            <div wire:ignore class="relative h-[360px] w-full" style="height: 360px; min-height: 360px;">
                <div x-ref="map" class="h-full w-full" style="height: 360px; min-height: 360px; z-index: 1;"></div>

                {{-- Toggle 24 jam / 7 hari --}}
                <div class="absolute left-3 top-3 z-[1000] flex rounded-full bg-white p-1 text-xs font-semibold shadow">
                    <button type="button" @click="setHours(24)" :class="hours === 24 ? 'bg-forest-800 text-white' : 'text-ink-500'" class="rounded-full px-3 py-1.5 transition">24 jam</button>
                    <button type="button" @click="setHours(168)" :class="hours === 168 ? 'bg-forest-800 text-white' : 'text-ink-500'" class="rounded-full px-3 py-1.5 transition">7 hari</button>
                </div>

                {{-- Filter lapisan --}}
                <div class="absolute right-3 top-3 z-[1000] space-y-1.5 rounded-2xl bg-white p-2.5 text-xs shadow">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-forest-800 pb-0.5 border-b border-leaf-100">Lapisan Peta</div>
                    <label class="flex items-center gap-1.5 cursor-pointer font-medium text-forest-950">
                        <input type="checkbox" x-model="layers.risk" @change="applyLayers()" class="rounded text-forest-800">
                        <span>📍 Risiko Kecamatan</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer font-medium text-forest-950">
                        <input type="checkbox" x-model="layers.hotspot" @change="applyLayers()" class="rounded text-forest-800">
                        <span>🔥 Hotspot Satelit</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer font-medium text-forest-950">
                        <input type="checkbox" x-model="layers.reports" @change="applyLayers()" class="rounded text-forest-800">
                        <span>📢 Laporan Warga</span>
                    </label>
                </div>

                {{-- Legenda --}}
                <div class="absolute bottom-3 left-3 z-[1000] rounded-2xl bg-white/95 px-3 py-2 text-[11px] shadow">
                    <div class="font-bold text-[10px] text-forest-950 border-b border-leaf-100 pb-1 mb-1">Status Risiko Wilayah</div>
                    <div class="flex items-center gap-1.5 font-medium"><span class="h-2.5 w-2.5 rounded-full" style="background:#7BC96F"></span>Rendah (Aman)</div>
                    <div class="flex items-center gap-1.5 font-medium"><span class="h-2.5 w-2.5 rounded-full" style="background:#F5D35C"></span>Sedang (Waspada)</div>
                    <div class="flex items-center gap-1.5 font-medium"><span class="h-2.5 w-2.5 rounded-full" style="background:#F28C38"></span>Tinggi (Siaga)</div>
                    <div class="flex items-center gap-1.5 font-medium"><span class="h-2.5 w-2.5 rounded-full" style="background:#D63B2F"></span>Sangat tinggi (Awas)</div>
                    <div class="mt-1 pt-1 border-t border-leaf-100 text-[10px] text-ink-500 font-medium">🔥 Hotspot NASA/SiPongi</div>
                </div>
            </div>
        </div>

        <p class="mt-2 text-center text-[11px] text-ink-500">
            Sumber: SiPongi+ KLHK/Kemenhut, NASA FIRMS · Hotspot ≠ titik api. Untuk darurat hubungi 112 / BPBD setempat.
        </p>
    </div>

    {{-- Kartu wilayah terpilih --}}
    <div class="space-y-3 px-5 py-5" x-show="selected" x-cloak>
        <template x-if="selected">
            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-forest-800">Wilayah Terpilih</span>
                        <p class="font-display font-bold text-forest-950" x-text="selected?.name"></p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :style="`background:${selected?.color};color:${selected?.level === 'sedang' ? '#4A3B00' : '#fff'}`" x-text="selected?.levelLabel"></span>
                </div>
                <div class="mt-2 flex items-end gap-1">
                    <span class="font-display text-2xl font-extrabold text-forest-950" x-text="selected?.score ?? '–'"></span>
                    <span class="mb-0.5 text-xs text-ink-500">/100 (Indeks Risiko)</span>
                </div>
                <canvas x-ref="sparkline" height="60" class="mt-2"></canvas>
                <a :href="selected?.detailUrl" class="karsa-btn-primary mt-3">Lihat detail wilayah</a>
            </x-card>
        </template>
    </div>

    <div class="px-5 pb-6">
        <a href="{{ route('peta.sipongi') }}"
           class="flex h-12 w-full items-center justify-center rounded-2xl border border-leaf-100 text-sm font-semibold text-forest-950">
            Buka peta SiPongi+
        </a>
    </div>

    <button type="button" @click="locateMe()" class="fixed bottom-24 right-5 z-30 flex h-12 w-12 items-center justify-center rounded-full bg-forest-800 text-white shadow-lg" aria-label="Lokasi saya">
        <x-icon name="location" class="h-5 w-5" />
    </button>

    <script>
    function petaRisiko(config) {
        return {
            hours: 24,
            layers: { risk: true, hotspot: true, reports: true },
            selected: null,
            map: null,
            hotspotLayer: null,
            riskLayer: null,
            districts: config.districts,

            init() {
                if (this.map) return;
                if (typeof L === 'undefined' || !L.map) {
                    setTimeout(() => this.init(), 100);
                    return;
                }

                try {
                    if (this.$refs.map && this.$refs.map._leaflet_id) {
                        this.$refs.map._leaflet_id = null;
                    }

                    this.map = L.map(this.$refs.map, { zoomControl: false }).setView([-6.12, 106.15], 10);
                    L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19,
                    }).addTo(this.map);

                    this.riskLayer = L.layerGroup().addTo(this.map);
                    (this.districts || []).forEach(d => {
                        if (!d.lat || !d.lng) return;
                        const marker = L.circleMarker([d.lat, d.lng], {
                            radius: 10,
                            color: '#fff',
                            weight: 2,
                            fillColor: d.color || '#7BC96F',
                            fillOpacity: 0.9,
                        }).on('click', () => {
                            this.selectDistrict(d);
                            marker.openPopup();
                        });

                        const levelName = (d.level || 'rendah').toUpperCase();
                        const popupContent = '<strong style="font-size: 13px; color: #0F3A22;">📍 ' + d.name + '</strong><br>' +
                            '<span style="font-size: 11px; color: #5B7263;">Titik Pantauan Kecamatan</span><br>' +
                            '<span style="font-size: 11px; font-weight: 700; color: #2A6E43;">Risiko: ' + levelName + '</span>';
                        marker.bindPopup(popupContent);

                        marker.bindTooltip('📍 ' + d.name, { direction: 'top' });
                        marker.addTo(this.riskLayer);
                    });

                    this.hotspotLayer = (typeof L.markerClusterGroup === 'function') 
                        ? L.markerClusterGroup() 
                        : L.layerGroup();
                    this.map.addLayer(this.hotspotLayer);
                    this.loadHotspots();

                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 100);
                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 400);
                } catch (e) {
                    console.error('Error initializing Leaflet map:', e);
                }
            },

            setHours(h) { this.hours = h; this.loadHotspots(); },

            applyLayers() {
                if (!this.map) return;
                this.layers.risk ? this.map.addLayer(this.riskLayer) : this.map.removeLayer(this.riskLayer);
                this.layers.hotspot ? this.map.addLayer(this.hotspotLayer) : this.map.removeLayer(this.hotspotLayer);
            },

            loadHotspots() {
                fetch(`${config.apiHotspots}?hours=${this.hours}`)
                    .then(r => r.json())
                    .then(geojson => {
                        if (!this.hotspotLayer) return;
                        this.hotspotLayer.clearLayers();
                        (geojson.features || []).forEach(f => {
                            const [lng, lat] = f.geometry.coordinates;
                            const marker = L.circleMarker([lat, lng], {
                                radius: 5, color: f.properties.confidence_color, fillColor: f.properties.confidence_color, fillOpacity: 0.8, weight: 1,
                            });
                            marker.bindPopup(`🔥 Titik Panas: ${f.properties.confidence}${f.properties.corroborated ? ' · terkonfirmasi 2 sumber' : ''}`);
                            this.hotspotLayer.addLayer(marker);
                        });
                    })
                    .catch(() => {});
            },

            selectDistrict(d) {
                fetch(`/api/v1/risk/${d.slug}`).then(r => r.json()).then(data => {
                    this.selected = {
                        name: d.name,
                        score: data.score ?? '–',
                        level: data.level,
                        levelLabel: data.level_label ?? 'Belum ada data',
                        color: d.color,
                        detailUrl: `/wilayah/${d.slug}`,
                    };
                    this.$nextTick(() => this.drawSparkline(data.trend_7d || []));
                });
            },

            drawSparkline(values) {
                if (!this.$refs.sparkline) return;
                if (this._chart) this._chart.destroy();
                this._chart = new Chart(this.$refs.sparkline, {
                    type: 'line',
                    data: {
                        labels: values.map((_, i) => i + 1),
                        datasets: [{ data: values, borderColor: '#2A6E43', backgroundColor: 'rgba(42,110,67,0.1)', fill: true, tension: 0.3, pointRadius: 0 }],
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: { x: { display: false }, y: { display: false } },
                        responsive: true,
                    },
                });
            },

            locateMe() {
                if (!navigator.geolocation || !this.map) return;
                navigator.geolocation.getCurrentPosition(pos => {
                    this.map.setView([pos.coords.latitude, pos.coords.longitude], 13);
                });
            },
        };
    }
    </script>
</div>

