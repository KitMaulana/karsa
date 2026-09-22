<x-layouts.public title="Tentang KARSA">
    <x-forest-header title="Tentang KARSA" :back="true" />

    <div class="space-y-5 px-5 py-6">
        <x-card>
            <p class="text-sm leading-relaxed text-ink-500">
                <strong class="text-forest-950">KARSA (Kawasan Analisis Risiko dan Siaga)</strong> adalah purwarupa
                aplikasi analisis risiko dan peringatan dini kebakaran hutan dan lahan (karhutla) berbasis
                partisipasi masyarakat, dikembangkan oleh tim siswa SMA Negeri 1 Ciruas, Kabupaten Serang, Banten,
                sebagai karya tulis ilmiah <em>"Ketika Data Berbicara Sebelum Api Berkobar: KARSA sebagai Model
                Analisis Risiko dan Peringatan Dini"</em>.
            </p>
        </x-card>

        <x-card>
            <h2 class="font-display font-bold text-forest-950">Metodologi skoring</h2>
            <p class="mt-2 text-sm leading-relaxed text-ink-500">
                Skor risiko dihitung dari tiga faktor utama: kepadatan hotspot, kondisi cuaca, dan kerentanan
                wilayah, digabungkan memakai pembobotan <strong>Analytic Hierarchy Process (AHP)</strong>.
            </p>

            @if($model)
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><span>Bobot hotspot</span><strong>{{ number_format($model->weights['hotspot'] * 100, 1) }}%</strong></div>
                    <div class="flex justify-between"><span>Bobot cuaca</span><strong>{{ number_format($model->weights['cuaca'] * 100, 1) }}%</strong></div>
                    <div class="flex justify-between"><span>Bobot kerentanan</span><strong>{{ number_format($model->weights['kerentanan'] * 100, 1) }}%</strong></div>
                    <div class="flex justify-between border-t border-leaf-100 pt-2"><span>Consistency Ratio (CR)</span><strong class="{{ $model->cr <= 0.1 ? 'text-forest-800' : 'text-risk-extreme' }}">{{ number_format($model->cr, 4) }}</strong></div>
                </div>
                <p class="mt-2 text-xs text-ink-500">CR ≤ 0,10 dianggap konsisten secara matematis (metode Saaty).</p>
            @endif
        </x-card>

        <x-card>
            <h2 class="font-display font-bold text-forest-950">Atribusi sumber data</h2>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-ink-500">
                <li>Hotspot: SiPongi+ (KLHK/Kemenhut) dan NASA FIRMS</li>
                <li>Cuaca: Open-Meteo dan BMKG</li>
                <li>Laporan warga terverifikasi oleh relawan/petugas setempat</li>
            </ul>
        </x-card>

        <x-card>
            <p class="text-xs leading-relaxed text-ink-500">
                <strong>Disclaimer:</strong> KARSA adalah alat bantu pencegahan. Hotspot adalah titik panas,
                belum tentu titik api. Untuk keadaan darurat hubungi 112 / BPBD setempat.
            </p>
        </x-card>
    </div>
</x-layouts.public>
