# CLAUDE.md — KARSA (Kawasan Analisis Risiko dan Siaga)

> File ini adalah konteks utama proyek. Baca seluruhnya sebelum menulis kode.
> Semua jawaban, komentar kode, pesan commit, dan teks antarmuka memakai **Bahasa Indonesia**.

---

## 1. Ringkasan Proyek

**KARSA** adalah Progressive Web App (PWA) berbasis Laravel untuk **analisis risiko dan peringatan dini kebakaran hutan dan lahan (karhutla)** yang berbasis partisipasi masyarakat. Aplikasi ini dibuat oleh tim siswa SMA Negeri 1 Ciruas (Kab. Serang, Banten) sebagai purwarupa karya tulis ilmiah berjudul *"Ketika Data Berbicara Sebelum Api Berkobar: KARSA sebagai Model Analisis Risiko dan Peringatan Dini"*.

Tagline: *"Satu aplikasi untuk memantau, menganalisis, dan melindungi hutan kita."*

### Masalah yang dijawab (dari kerangka pemikiran KTI)
1. Menggabungkan **data hotspot + data lingkungan (cuaca)** menjadi **tingkat risiko karhutla** yang mudah dipahami masyarakat.
2. Menyediakan **panduan langkah pencegahan 72 jam** sesuai tingkat risiko.
3. Menyediakan **pelaporan warga** yang cepat, teratur, berbasis lokasi, dan **terverifikasi** (anti-hoaks/penyalahgunaan).
4. Menjadi **sistem pendukung SiPongi+** yang fokus pada edukasi, pencegahan, dan partisipasi masyarakat (bukan pengganti).

### Prinsip desain sistem (dari bagian Urgensi KTI) — WAJIB dipenuhi
| # | Kelemahan yang harus diatasi | Implementasi di KARSA |
|---|---|---|
| 1 | Fragmentasi & akurasi data | Fusi data multi-sumber (SiPongi+, NASA FIRMS, cuaca) + **indikator tingkat keyakinan data** |
| 2 | Skoring tanpa dasar ilmiah | Pembobotan **AHP** (matriks perbandingan berpasangan + Consistency Ratio) yang bisa diatur admin, simpan riwayat skor untuk validasi historis |
| 3 | Manipulasi data partisipatif | **Verifikasi berlapis**: geotag, pencocokan silang hotspot, laporan tetangga, nilai kepercayaan pelapor |
| 4 | Konektivitas terbatas | **Offline-first**: service worker, cache data terakhir, antrean laporan offline |
| 5 | Respons BPBD/Manggala Agni lambat | **Notifikasi otomatis** ke kontak instansi saat risiko naik / laporan terverifikasi |

---

## 2. Tech Stack (jangan diganti tanpa izin)

- **Laravel 12** (PHP 8.2+), MySQL/MariaDB
- **Blade + Livewire 3 + Alpine.js + Tailwind CSS 4** (via Vite)
- **Tidak memakai** paket admin siap pakai (Filament, Nova, Breeze UI, Jetstream). Halaman publik dan panel admin dibuat sendiri.
- Auth: **Laravel Fortify** (headless, backend saja) + view Blade buatan sendiri; **Laravel Socialite** untuk "Daftar dengan Google"
- Peta: **Leaflet** + `leaflet.markercluster` + tile CartoDB Voyager/Positron (atribusi wajib tampil)
- Grafik: **Chart.js**
- Push: `laravel-notification-channels/webpush` (VAPID)
- Gambar: `intervention/image` (resize, strip EXIF saat publikasi)
- EXIF: fungsi `exif_read_data` PHP (baca GPS & waktu sebelum di-strip)
- Offline: service worker tulis sendiri (`public/sw.js`) + IndexedDB (`idb-keyval`)
- Zona waktu `Asia/Jakarta`, locale `id`, format tanggal "12 Sep 2026"

### Lingkungan deploy
- **Shared hosting cPanel** + MySQL. Tidak ada supervisor/queue worker permanen.
- Cron cPanel tiap menit: `php /home/USER/karsa/artisan schedule:run >> /dev/null 2>&1`
- Queue driver `database`; scheduler menjalankan `queue:work --stop-when-empty` tiap menit.
- Aset di-build lokal (`npm run build`), folder `public/build` ikut di-upload.
- HTTPS wajib (PWA, geolocation, kamera, dan push butuh HTTPS).

---

## 3. Aturan Kerja untuk Claude

1. **Kerjakan per tahap** sesuai `PROMPTS.md`. Di akhir tiap tahap: ringkas apa yang dibuat, file yang berubah, perintah yang harus saya jalankan, dan cara mengetesnya.
2. Sebelum mengubah skema DB yang sudah ada, **buat migration baru**, jangan edit migration lama yang sudah dijalankan.
3. Jangan pernah menaruh kunci API di kode. Semua lewat `.env` → `config/karsa.php`.
4. Semua pemanggilan API eksternal lewat **Service class** dengan timeout (10 detik), retry (2x), cache, dan log kegagalan ke tabel `data_sync_logs`. Kegagalan sumber data **tidak boleh** membuat halaman error — tampilkan data terakhir + label "Data terakhir diperbarui …".
5. Tulis **Feature test** (Pest) untuk: skoring risiko, kalkulasi AHP, verifikasi laporan, dan parser tiap sumber hotspot.
6. Gunakan Form Request untuk validasi, Policy untuk otorisasi, Enum PHP untuk status/level.
7. Ikuti desain di `docs/design/` (screenshot dari `karsa-desain-semua-layar-final.pdf`). Jika ada layar yang belum ada desainnya, buat konsisten dengan gaya yang sama.
8. Teks antarmuka: kalimat biasa (sentence case), kata kerja aktif, jelas. Tombol menyebut aksinya ("Kirim laporan", bukan "Submit").
9. Jangan menjalankan perintah destruktif (`migrate:fresh`, hapus file) tanpa bertanya.
10. Jika ada keputusan teknis yang belum diatur di file ini, **tanya dulu** dengan satu pertanyaan singkat berikut rekomendasimu.

---

## 4. Design System (diambil dari desain PDF)

### Warna (definisikan sebagai CSS variable + token Tailwind `@theme`)
| Token | Hex | Pemakaian |
|---|---|---|
| `forest-950` | `#0F3A22` | Bagian atas gradasi header/splash |
| `forest-800` | `#1D4B2E` | Tombol utama, teks judul gelap |
| `forest-600` | `#2A6E43` | Bagian bawah gradasi, ikon aktif |
| `pine-400` | `#4E9A5F` | Pohon ilustrasi terang |
| `leaf-100` | `#DDEBD3` | Lingkaran ikon, border kartu |
| `cream-50` | `#F4F7EE` | Latar belakang halaman |
| `ink-500` | `#5B7263` | Teks sekunder |
| `risk-low` | `#7BC96F` | Rendah |
| `risk-mid` | `#F5D35C` | Sedang (teks label `#4A3B00`) |
| `risk-high` | `#F28C38` | Tinggi |
| `risk-extreme` | `#D63B2F` | Sangat tinggi |
| `water` | `#B9D9EC` | Sungai di peta ilustrasi |

### Tipografi
- Judul/heading: **Bricolage Grotesque** (700–800), wordmark "KARSA" huruf lebar dan tebal dengan letter-spacing lebar.
- Isi: **Plus Jakarta Sans** (400–600).
- Angka skor besar (mis. `62/100`): Bricolage Grotesque 800, "/100" lebih kecil dan warna `ink-500`.

### Komponen khas
- **Header hutan**: gradasi `forest-950 → forest-600`, deretan siluet pohon pinus (SVG, 3 tingkat warna) dengan tepi bawah bergelombang ke `cream-50`. Tombol kembali bulat semi-transparan di kiri, judul putih tebal, lingkaran "bulan" samar di kanan atas. Jadikan **komponen Blade `<x-forest-header title="…" :back="true" />`** dan SVG pohon sebagai satu file `resources/svg/forest-strip.svg`.
- **Kartu**: putih, radius 24px, border 1px `leaf-100`, tanpa bayangan tebal.
- **Tombol utama**: `forest-800`, teks putih, radius 16px, tinggi 56px, lebar penuh, menempel di bawah layar pada halaman form.
- **Pill level risiko**: `<x-risk-pill level="sedang" />` memakai warna risk-*.
- **Gauge setengah lingkaran** (Detail Wilayah) dan **sparkline 7 hari** (Peta Risiko).
- **Item menu** (layar Menu): kapsul putih, ikon dalam lingkaran hijau-kuning muda, garis pemisah tipis, chevron kanan.
- **Bottom navigation** (4 tab): Beranda, Peta, Notifikasi, Profil.
- **Indikator halaman onboarding**: titik kecil + satu titik aktif memanjang.
- Tampilan mobile-first (lebar desain 390px). Di layar ≥768px, konten publik ditampilkan dalam kolom tengah maks 480px; panel admin memakai layout desktop penuh.
- Hormati `prefers-reduced-motion`, fokus keyboard terlihat, kontras teks memenuhi WCAG AA (teks pada pill kuning memakai warna gelap).

---

## 5. Peran Pengguna

| Peran | Akses |
|---|---|
| **Tamu** (tanpa login) | Splash, onboarding, peta risiko, detail wilayah, aksi pencegahan, peringatan, KARSA News, program aksi |
| **Warga** (terdaftar) | + kirim laporan, riwayat laporan sendiri, notifikasi push, atur wilayah pantauan, ikut program aksi |
| **Verifikator** (relawan/Wapala/petugas) | + antrean verifikasi laporan di wilayah yang ditugaskan |
| **Admin** | Semua menu admin |
| **Superadmin** | + kelola admin, pengaturan sumber data & kunci API |

Gunakan kolom `role` (Enum) di tabel `users` + Gate/Policy. Tidak perlu paket permission.

---

## 6. Halaman Publik (PWA) — peta ke desain PDF

| Route | Layar desain | Isi |
|---|---|---|
| `/` | Splash | Logo perisai-pohon, wordmark KARSA, "Kawasan Analisis Risiko dan Siaga", tagline, teks status "Menyiapkan data risiko". Tampil ±1,5 detik lalu ke onboarding (kunjungan pertama) atau ke Menu. Simpan status di `localStorage` `karsa_onboarded`. |
| `/mulai` | Onboarding 1–3 | Swipe 3 slide (Pantau risiko / Dapat peringatan / Lapor dan jaga hutan), tombol "Lewati", "Lanjut", "Mulai", tautan "Masuk". |
| `/daftar`, `/masuk` | Daftar Pengguna | Nama lengkap, email **atau** nomor HP, kata sandi (min 8, ikon mata), pilih kabupaten/kota, centang Syarat & Kebijakan Privasi, "Daftar dengan Google". Halaman masuk mengikuti gaya yang sama. |
| `/menu` | Menu | 6 item: Peta Risiko Karhutla, Aksi Pencegahan, Pelaporan, Peringatan & Status, KARSA News, Penggalangan Dana. Tambahkan kartu ringkas di atas daftar: risiko wilayah pengguna hari ini. |
| `/peta` | Peta Risiko Karhutla | Peta Leaflet: poligon kecamatan diwarnai level risiko + titik hotspot (warna berdasarkan confidence) + laporan terverifikasi. Legenda 4 level, tombol zoom, filter lapisan (Risiko / Hotspot / Laporan), toggle 24 jam / 7 hari. Kartu bawah: nama wilayah, pill level, skor/100, sparkline 7 hari, tombol "Lihat detail wilayah". Tombol sekunder "Buka peta SiPongi+" (lihat §8.4). |
| `/wilayah/{kecamatan:slug}` | Detail Wilayah | Gauge skor, pill level, 3 kartu (Suhu, Kelembapan, Hotspot), kartu Faktor Risiko (Hotspot, Kondisi cuaca, Kerentanan wilayah — masing-masing bar + pill), **indikator keyakinan data** (Tinggi/Sedang/Rendah + sumber yang aktif + waktu pembaruan), estimasi emisi CO₂ indikatif, tombol "Lihat rekomendasi". |
| `/aksi` | Aksi Pencegahan | Banner level risiko, kalimat anjuran, "Rekomendasi — Rencana aksi 72 jam ke depan" (daftar kartu berikon, bisa dicentang warga sebagai checklist pribadi, tersimpan lokal), tombol "Panduan lengkap" → `/aksi/panduan`. Filter audiens: Warga umum / Petani & pekebun / Sekolah. |
| `/lapor` | Pelaporan Karhutla | Lokasi (auto GPS + geser pin), Foto/Video (kamera langsung), Deskripsi, jenis kejadian (asap / api kecil / api besar / pembakaran lahan), "Gunakan lokasi saya", "Kirim laporan". Wajib login. Bila offline → masuk antrean, tampil "Laporan tersimpan, akan terkirim saat online". |
| `/lapor/saya` | (baru) | Riwayat laporan + status verifikasi + nilai kepercayaan. |
| `/peringatan` | Peringatan & Status | Kartu peringatan aktif (wilayah, level lama → level baru, kalimat perubahan), "Penyebab utama" (dihitung otomatis dari faktor yang naik), anjuran, tombol "Lihat peta risiko". Di bawahnya riwayat peringatan. |
| `/news`, `/news/{slug}` | KARSA News | Tab Semua / Berita / Edukasi, kartu dengan thumbnail ilustrasi, tanggal. Halaman detail artikel. |
| `/program`, `/program/{slug}` | Penggalangan Dana (belum ada desain) | Lihat §12. |
| `/notifikasi` | (baru) | Daftar notifikasi in-app, tombol aktifkan push. |
| `/profil` | (baru) | Data diri, wilayah pantauan (maks 3 kecamatan), preferensi notifikasi, nilai kepercayaan, keluar. |
| `/tentang`, `/privasi`, `/syarat` | (baru) | Tentang KARSA & tim, metodologi skoring (transparansi), atribusi sumber data. |
| `/offline` | (baru) | Halaman cadangan offline. |

Bottom navigation muncul di halaman utama (Menu/Beranda, Peta, Notifikasi, Profil), tidak muncul di halaman form.

---

## 7. Panel Admin (`/admin`, layout desktop, sidebar)

1. **Dasbor** — kartu ringkas (hotspot 24 jam di wilayah pantauan, kecamatan per level, laporan menunggu verifikasi, peringatan aktif, pengguna), peta gabungan, grafik tren skor & hotspot 30 hari, status tiap sumber data (hijau/kuning/merah + waktu sinkron terakhir).
2. **Hotspot** — tabel hotspot (filter tanggal, sumber, satelit, confidence, kecamatan), tombol "Sinkronkan sekarang", log sinkronisasi, ekspor CSV/GeoJSON.
3. **Wilayah** — kabupaten/kota & kecamatan: impor GeoJSON batas wilayah, titik pusat, **nilai kerentanan** (sub-faktor: tutupan lahan/vegetasi, lahan gambut, riwayat kebakaran, jarak ke permukiman, akses pemadam), aktif/nonaktif pemantauan.
4. **Model Risiko (AHP)** — matriks perbandingan berpasangan (skala Saaty 1–9) untuk 3 faktor utama dan sub-faktor cuaca; hitung bobot (eigenvector), λmax, CI, **CR** (tolak simpan bila CR > 0,10); ambang level; parameter normalisasi; **simulator** (isi nilai → lihat skor); riwayat versi model (skor lama tetap menunjuk versi modelnya).
5. **Validasi Historis** — bandingkan skor harian dengan kejadian nyata (hotspot high-confidence / laporan terverifikasi) → tabel kontingensi, akurasi, recall, false alarm rate per periode. Ini bahan bab hasil KTI.
6. **Laporan Warga** — antrean verifikasi (urut nilai kepercayaan & waktu), detail laporan (foto, peta, jarak ke hotspot terdekat, laporan sekitar, riwayat pelapor, bendera kecurigaan), aksi: Verifikasi / Tolak (dengan alasan) / Teruskan ke instansi / Tandai selesai.
7. **Peringatan** — log peringatan otomatis, buat peringatan manual/siaran, statistik notifikasi terkirim.
8. **Rekomendasi Aksi** — CRUD rekomendasi per level risiko × audiens (ikon, judul, penjelasan, urutan), panduan lengkap (rich text).
9. **KARSA News** — CRUD artikel (kategori Berita/Edukasi, thumbnail, isi rich text via Trix, status draf/terbit, jadwal terbit).
10. **Program Aksi & Donasi** — lihat §12.
11. **Pengguna** — daftar, ubah peran, blokir, atur wilayah tugas verifikator, reset nilai kepercayaan.
12. **Kontak Instansi** — BPBD, Manggala Agni, Damkar, Polsek, kepala desa: nama, instansi, wilayah, email, nomor WA, level minimal yang memicu notifikasi.
13. **Pengaturan** (superadmin) — sumber data hotspot (aktif/prioritas, endpoint, kunci), interval sinkron, radius penyangga, parameter estimasi CO₂, VAPID, teks disclaimer.
14. **Log Aktivitas** — audit semua aksi admin/verifikator.

---

## 8. Integrasi Data

Semua sumber memakai pola **adapter**:

```
app/Services/Hotspot/
  HotspotProvider.php          // interface: fetch(BBox $bbox, CarbonPeriod $period): Collection<HotspotDTO>
  SipongiProvider.php
  FirmsProvider.php
  HotspotAggregator.php        // gabung, dedup, hitung keyakinan
app/Services/Weather/
  WeatherProvider.php
  OpenMeteoProvider.php
  BmkgProvider.php
```

`HotspotDTO`: `source`, `satellite`, `lat`, `lng`, `confidence` (low/medium/high + angka asli bila ada), `frp`, `detected_at` (UTC → tampil WIB), `external_id`, `raw` (json).

### 8.1 SiPongi+ (sumber utama, KLHK/Kemenhut)
- Laman peta publik: `https://sipongi.gakkum.kehutanan.go.id/peta`. Laman ini aplikasi sisi-klien (SPA) — **tidak ada API publik resmi yang terdokumentasi**. Data dimuat oleh JavaScript dari endpoint internal.
- Langkah menemukan endpoint (lakukan manual oleh developer): buka laman peta → DevTools → tab Network → filter `Fetch/XHR` → muat ulang → catat URL yang mengembalikan data hotspot (JSON/GeoJSON), parameter (rentang waktu, satelit, confidence), dan header yang dibutuhkan.
- Ada proyek komunitas yang memakai endpoint `https://opsroom.sipongidata.my.id/api/opsroom/indoHotspot` (parameter `late`, satelit, confidence). **Status resminya belum pasti** — verifikasi dulu lewat DevTools bahwa laman SiPongi+ memang memanggil endpoint ini sebelum dipakai.
- Simpan endpoint di `.env` (`SIPONGI_HOTSPOT_URL`, `SIPONGI_EXTRA_PARAMS`) — **jangan hardcode**, karena endpoint internal bisa berubah sewaktu-waktu.
- `SipongiProvider` harus toleran perubahan format: parser memetakan beberapa kemungkinan nama field (`lat/latitude/y`, `lng/lon/longitude/x`, `conf/confidence/kepercayaan`, dll.), simpan `raw`, dan bila parsing gagal → catat log + pakai sumber cadangan.
- Etika: panggil **maksimal tiap 60 menit**, satu permintaan per siklus untuk bbox wilayah pantauan, kirim `User-Agent: KARSA/1.0 (SMAN 1 Ciruas; kontak@…)`, tampilkan atribusi "Sumber: SiPongi+ KLHK/Kemenhut".

### 8.2 NASA FIRMS (sumber cadangan resmi, API terdokumentasi)
- Butuh `MAP_KEY` gratis dari situs FIRMS (`FIRMS_MAP_KEY` di `.env`).
- Endpoint area: `https://firms.modaps.eosdis.nasa.gov/api/area/csv/{MAP_KEY}/{SOURCE}/{west,south,east,north}/{DAY_RANGE}`
  - `SOURCE`: `VIIRS_SNPP_NRT`, `VIIRS_NOAA20_NRT`, `VIIRS_NOAA21_NRT`, `MODIS_NRT`
  - `DAY_RANGE`: 1–5 (pakai 1 untuk sinkron rutin)
- Confidence VIIRS berupa `l/n/h`; MODIS berupa angka 0–100 (≤30 low, 31–79 medium, ≥80 high).
- Atribusi: "NASA FIRMS".

### 8.3 Fusi & deduplikasi
- Dua titik dari sumber berbeda dianggap sama bila jarak ≤ 1 km **dan** selisih waktu ≤ 3 jam → simpan satu hotspot dengan `sources` = daftar sumber (`corroborated = true`).
- **Indikator keyakinan data** per kecamatan (0–100 → Tinggi/Sedang/Rendah):
  - kesegaran data hotspot (≤ 3 jam = penuh), jumlah sumber aktif, proporsi hotspot high-confidence/terkonfirmasi, ketersediaan data cuaca, kelengkapan data kerentanan.
  - Tampilkan di Detail Wilayah dan panel admin.

### 8.4 Embed SiPongi+ (pelengkap, bukan sumber data)
- Tombol "Buka peta SiPongi+" membuka laman resmi di tab baru.
- Opsi iframe `/peta/sipongi`: **cek dulu** header `X-Frame-Options`/`Content-Security-Policy frame-ancestors` dari laman SiPongi+. Jika pemuatan dalam iframe diblokir, jangan dipaksa — cukup tampilkan tautan. Buat pengaturan admin `sipongi_embed_enabled`.

### 8.5 Cuaca
- **Open-Meteo** (utama, tanpa kunci): `https://api.open-meteo.com/v1/forecast?latitude={lat}&longitude={lng}&current=temperature_2m,relative_humidity_2m,wind_speed_10m,precipitation&daily=temperature_2m_max,relative_humidity_2m_min,precipitation_sum,wind_speed_10m_max&past_days=30&forecast_days=3&timezone=Asia%2FJakarta` — diambil per titik pusat kecamatan tiap 60 menit. Hitung **hari tanpa hujan berturut-turut** (precipitation_sum < 1 mm) dari `past_days`.
- **BMKG** (pelengkap, prakiraan resmi): `https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={kode_desa}` — butuh kode wilayah administrasi tingkat IV (Kepmendagri) untuk satu desa perwakilan per kecamatan (kolom `bmkg_adm4` di tabel kecamatan). Batasi laju permintaan dan tampilkan atribusi "BMKG".
- Prakiraan 3 hari dipakai untuk **proyeksi risiko 72 jam** di halaman Aksi Pencegahan.

---

## 9. Model Skoring Risiko (AHP)

Skor akhir per kecamatan per waktu, 0–100:

```
R = w_h · S_hotspot + w_c · S_cuaca + w_k · S_kerentanan
```

### 9.1 Sub-skor (semua dinormalisasi 0–100, parameter bisa diubah admin)
- **S_hotspot**: jumlah hotspot berbobot dalam poligon kecamatan + buffer (default 5 km) selama 24 jam terakhir. Bobot confidence: high 1,0; medium 0,6; low 0,3; bonus ×1,2 bila terkonfirmasi 2 sumber. Normalisasi linier, 0 → 0, ≥ `hotspot_max` (default 10) → 100. Tambah laporan warga **terverifikasi** sebagai hotspot bernilai 1,0.
- **S_cuaca** = rata-rata berbobot AHP (sub-matriks) dari:
  - suhu maks: 25 °C → 0, 38 °C → 100
  - kelembapan min: 85 % → 0, 35 % → 100 (terbalik)
  - kecepatan angin maks: 0 → 0, 40 km/jam → 100
  - hari tanpa hujan: 0 → 0, 21 hari → 100
- **S_kerentanan** = nilai statis dari data wilayah (diisi admin, 0–100).

### 9.2 AHP
- `app/Services/Risk/AhpCalculator.php`: input matriks n×n (reciprocal), output bobot (metode eigenvector, iterasi power method hingga konvergen, atau rata-rata geometrik baris sebagai pembanding), λmax, CI = (λmax − n)/(n − 1), CR = CI/RI. RI: n=3 → 0,58; n=4 → 0,90; n=5 → 1,12.
- Model default (bisa diubah): hotspot vs cuaca = 2, hotspot vs kerentanan = 3, cuaca vs kerentanan = 2 → bobot ≈ 0,54 / 0,30 / 0,16. Hitung ulang di kode, jangan hardcode bobot.
- Simpan setiap versi di tabel `risk_models` (matriks, bobot, CR, ambang, aktif). Tabel `risk_scores` menyimpan `risk_model_id` agar hasil bisa ditelusuri.

### 9.3 Level
| Level | Rentang default |
|---|---|
| Rendah | 0–39 |
| Sedang | 40–64 |
| Tinggi | 65–84 |
| Sangat tinggi | 85–100 |

### 9.4 Kabupaten
Skor kabupaten = rata-rata tertimbang luas dari skor kecamatan; tampilkan juga kecamatan dengan skor tertinggi.

### 9.5 Penyebab utama (halaman Peringatan)
Bandingkan sub-skor saat ini dengan 24 jam sebelumnya; tampilkan 3 faktor dengan kenaikan kontribusi terbesar, misal "Peningkatan hotspot", "Suhu meningkat", "Kelembapan menurun", "Hari tanpa hujan bertambah".

---

## 10. Estimasi Emisi CO₂ (indikatif)

Rumus IPCC 2006 (Vol. 4, Pers. 2.27): `L = A × MB × Cf × Gef × 10⁻³` (ton CO₂)
- `A` = luas terbakar (ha) — **diestimasi** dari jumlah hotspot × `luas_per_hotspot_ha` (default 1 ha, bisa diubah) atau dari luas yang diisi admin untuk kejadian terverifikasi.
- `MB` = massa bahan bakar (t/ha), `Cf` = faktor pembakaran, `Gef` = faktor emisi CO₂ (g/kg) — nilai default per tipe tutupan lahan disimpan di pengaturan; **admin wajib mencocokkan dengan tabel IPCC 2006 Vol. 4 (Tabel 2.4–2.5)** sebelum dipakai di KTI.
- Di UI selalu diberi label **"Estimasi indikatif"** + tautan ke halaman metodologi. Jangan menyajikannya sebagai angka resmi.

---

## 11. Pelaporan Warga & Verifikasi Berlapis

### 11.1 Alur
1. Warga login → ambil foto/video lewat `<input type="file" accept="image/*,video/*" capture="environment">` → lokasi dari `navigator.geolocation` (akurasi dicatat) → deskripsi & jenis kejadian → kirim.
2. Server: validasi (maks foto 5 MB, video 20 MB/30 detik), baca EXIF (GPS, waktu), simpan file asli di storage privat, buat versi publik tanpa EXIF (resize 1280px), hitung perceptual hash foto.
3. Hitung **nilai kepercayaan laporan** (0–100):
   - +25 ada hotspot (sumber mana pun) ≤ 5 km dalam 24 jam
   - +20 GPS EXIF foto ≤ 1 km dari lokasi laporan (bila EXIF ada)
   - +15 akurasi geolocation ≤ 100 m
   - +15 ada laporan lain dari pelapor berbeda ≤ 2 km dalam 6 jam
   - +10 waktu EXIF foto ≤ 2 jam sebelum kirim
   - +0–15 reputasi pelapor (laporan terverifikasi sebelumnya ↑, ditolak ↓)
   - Bendera merah (tidak menambah skor, ditampilkan ke verifikator): foto duplikat (hash sama dengan laporan lain), lokasi di luar Indonesia, laporan > 3 per jam dari akun yang sama, akun baru < 24 jam.
4. Status: `baru → ditinjau → terverifikasi | ditolak → diteruskan → selesai`.
5. Laporan terverifikasi → tampil di peta publik (identitas pelapor disembunyikan), ikut menaikkan S_hotspot, dan memicu notifikasi ke kontak instansi wilayah tersebut.
6. Nilai kepercayaan ≥ 80 **dan** ada hotspot terkonfirmasi → otomatis status `ditinjau` prioritas + notifikasi segera ke verifikator (tetap butuh konfirmasi manusia).

### 11.2 Anti-penyalahgunaan
Rate limit (3 laporan/jam/akun, 10/jam/IP), wajib login, honeypot field, pemblokiran akun oleh admin, log semua aksi verifikasi.

---

## 12. Program Aksi & Penggalangan Dana (improvisasi)

Layar "Penggalangan Dana" di menu belum punya desain; buat dengan gaya yang sama.
- **Program aksi**: kegiatan pelestarian (mis. penanaman pohon bersama Wapala SMAN 1 Ciruas, patroli, sosialisasi). Warga bisa **ikut sebagai relawan** (daftar ikut, kuota).
- **Donasi**: setiap program boleh punya target dana, info rekening/QRIS penyelenggara (unggah gambar), progres yang **diperbarui manual oleh admin** setelah dana diterima, dan **laporan penggunaan dana** (transparansi). Tidak ada payment gateway pada versi ini.
- Tambahkan pengaturan `donasi_enabled` (default mati) dan catatan di halaman: penggalangan dana untuk umum di Indonesia memerlukan izin pengumpulan uang/barang dari instansi berwenang — pada tahap purwarupa, fitur ini ditampilkan sebagai mode demonstrasi atau dibatasi untuk lingkungan sekolah.

---

## 13. Peringatan Dini & Notifikasi

- Job `CalculateRiskScores` berjalan setelah sinkron hotspot & cuaca. Bila level kecamatan **naik** dibanding perhitungan sebelumnya (atau skor naik ≥ 15 poin dalam 24 jam) → buat `alerts` + kirim:
  - **Web Push** ke warga yang memantau kecamatan tersebut / kabupaten induknya
  - Notifikasi in-app (tabel `notifications` Laravel)
  - **Email** ke kontak instansi yang level minimalnya terpenuhi; tampilkan juga tombol "Kirim via WhatsApp" (tautan `https://wa.me/{nomor}?text=…`) di panel admin. Integrasi WhatsApp gateway (opsional, nanti) lewat interface `AuthorityNotifier`.
- Cegah spam: satu peringatan per kecamatan per level per 12 jam.
- Isi notifikasi: "Risiko karhutla di Kec. Ciruas naik dari Sedang ke Tinggi. Lihat langkah pencegahan 72 jam." + deep link `/aksi?kecamatan=ciruas`.
- iOS: push hanya bekerja bila PWA sudah dipasang ke layar utama (iOS 16.4+). Tampilkan petunjuk pemasangan di halaman Notifikasi.

---

## 14. PWA & Offline-First

- `public/manifest.webmanifest`: name "KARSA — Kawasan Analisis Risiko dan Siaga", short_name "KARSA", `theme_color` `#1D4B2E`, `background_color` `#F4F7EE`, `display: standalone`, ikon 192/512 + maskable (logo perisai-pohon), shortcuts: "Lapor karhutla", "Peta risiko".
- `public/sw.js` (versi cache di konstanta):
  - Precache: shell (CSS/JS build, font, ikon, `/offline`, `/menu`).
  - `/api/v1/*` data risiko & hotspot: **stale-while-revalidate**, simpan juga ke IndexedDB dengan cap waktu → UI menampilkan "Data terakhir: 12 Sep 2026 14.00 WIB (offline)".
  - Tile peta: cache-first, batasi 300 entri.
  - Halaman navigasi: network-first, fallback cache, lalu `/offline`.
  - **Antrean laporan offline**: form menyimpan laporan (termasuk file sebagai Blob) ke IndexedDB → kirim saat event `sync` (Background Sync) atau event `online` (cadangan untuk browser tanpa Background Sync).
- Tombol "Pasang aplikasi" (tangkap `beforeinstallprompt`) + petunjuk manual untuk iOS.
- Target Lighthouse PWA/Performance ≥ 90 pada mobile.

---

## 15. API Internal (JSON, dipakai PWA) — `routes/api.php`, prefix `/api/v1`

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/regions` | kabupaten + kecamatan (ringan, tanpa geometri) |
| GET | `/regions/geojson?kabupaten=serang` | poligon + skor terkini (di-cache 10 menit) |
| GET | `/risk/{kecamatan}` | skor, level, sub-skor, keyakinan data, cuaca, tren 7 hari |
| GET | `/hotspots?bbox=&hours=24` | GeoJSON hotspot |
| GET | `/alerts/active` | peringatan aktif |
| GET | `/recommendations?level=&audience=` | rekomendasi aksi |
| POST | `/reports` | kirim laporan (auth sanctum/session, multipart) |
| GET | `/open/reports.geojson` | **Open data** laporan terverifikasi (tanpa identitas) — wujud dukungan ke SiPongi+/instansi |

Rate limit publik 60/menit/IP.

---

## 16. Skema Database (ringkas)

- `users` (+ `phone`, `role`, `trust_score`, `google_id`, `home_regency_id`, `is_blocked`)
- `regencies` (kode, nama, slug, `geometry` json, centroid)
- `districts` / kecamatan (`regency_id`, kode, nama, slug, `geometry` json, `centroid_lat/lng`, `area_ha`, `bmkg_adm4`, `vulnerability_score`, `vulnerability_factors` json, `is_monitored`)
- `user_watch_districts` (pivot, maks 3)
- `hotspots` (`external_id`, `sources` json, `satellite`, `lat`, `lng`, `confidence`, `confidence_raw`, `frp`, `detected_at`, `district_id` nullable, `corroborated`, `raw` json) — index (`detected_at`, `district_id`)
- `weather_observations` (`district_id`, `observed_at`, `temp_max`, `rh_min`, `wind_max`, `rain_mm`, `dry_days`, `source`, `is_forecast`)
- `risk_models` (`name`, `matrix` json, `sub_matrix` json, `weights` json, `cr`, `thresholds` json, `params` json, `is_active`)
- `risk_scores` (`district_id`, `risk_model_id`, `calculated_at`, `score`, `level`, `s_hotspot`, `s_weather`, `s_vulnerability`, `data_confidence`, `co2_estimate_t`, `detail` json) — index (`district_id`, `calculated_at`)
- `alerts` (`district_id`, `from_level`, `to_level`, `score`, `causes` json, `is_manual`, `message`, `sent_at`)
- `reports` (`user_id`, `district_id`, `lat`, `lng`, `accuracy_m`, `type`, `description`, `media` json, `exif` json, `phash`, `trust_score`, `flags` json, `status`, `verified_by`, `verified_at`, `rejection_reason`, `forwarded_at`)
- `report_status_logs`
- `recommendations` (`level`, `audience`, `icon`, `title`, `body`, `order`)
- `posts` (`category`, `title`, `slug`, `thumbnail`, `excerpt`, `body`, `status`, `published_at`, `author_id`)
- `programs` (`title`, `slug`, `description`, `cover`, `starts_at`, `location`, `volunteer_quota`, `donation_enabled`, `donation_target`, `donation_collected`, `payment_info` json, `usage_report`)
- `program_participants`
- `authority_contacts` (`name`, `agency`, `regency_id`, `district_id` nullable, `email`, `whatsapp`, `min_level`)
- `push_subscriptions` (dari paket webpush)
- `data_sync_logs` (`source`, `started_at`, `finished_at`, `status`, `records`, `message`)
- `settings` (key-value, di-cache)
- `activity_logs`

Seeder: Provinsi Banten (kabupaten/kota), seluruh kecamatan **Kabupaten Serang** dengan titik pusat (verifikasi nama & jumlah kecamatan dengan data BPS/Kemendagri terbaru), model AHP default, rekomendasi default per level, 4 artikel contoh sesuai desain, 1 program contoh (penanaman pohon bersama Wapala SMAN 1), akun superadmin dari `.env`.

---

## 17. Scheduler (`routes/console.php`)

| Jadwal | Tugas |
|---|---|
| tiap menit | `queue:work --stop-when-empty --max-time=50` |
| tiap 60 menit (menit ke-5) | `karsa:sync-hotspots` (SiPongi → gagal → FIRMS) |
| tiap 60 menit (menit ke-10) | `karsa:sync-weather` |
| tiap 60 menit (menit ke-15) | `karsa:calculate-risk` → deteksi kenaikan → peringatan |
| harian 00.30 | snapshot skor harian untuk validasi historis, bersihkan hotspot > 180 hari (arsipkan ringkasan) |
| mingguan | hapus media laporan ditolak > 30 hari |

Semua command juga bisa dijalankan manual dari panel admin (tombol "Jalankan sekarang").

---

## 18. Struktur Folder Tambahan

```
app/
  Enums/            RiskLevel, ReportStatus, UserRole, HotspotConfidence
  Services/Hotspot, Services/Weather, Services/Risk (AhpCalculator, RiskScorer, DataConfidence, Co2Estimator),
  Services/Reports (TrustScorer, MediaProcessor), Services/Notify (AuthorityNotifier)
  Livewire/Public/..., Livewire/Admin/...
  Console/Commands/ SyncHotspots, SyncWeather, CalculateRisk
resources/views/
  layouts/public.blade.php, layouts/admin.blade.php
  components/ forest-header, risk-pill, risk-gauge, sparkline, bottom-nav, menu-item, card
  public/..., admin/...
resources/svg/ logo-karsa.svg, forest-strip.svg, ilustrasi onboarding
public/ sw.js, manifest.webmanifest, icons/
docs/ design/*.png, KERANGKA_PEMIKIRAN_KTI.pdf, metodologi.md
```

---

## 19. Keamanan & Privasi

- Data pribadi pelapor (nama, kontak, lokasi rumah) tidak pernah tampil publik; foto publik tanpa EXIF.
- CSRF, validasi file (mime nyata, bukan ekstensi), simpan unggahan di disk privat, akses lewat route bertanda tangan.
- Kata sandi min 8 karakter, throttling login, verifikasi email untuk akun berbasis email.
- Halaman Kebijakan Privasi menjelaskan data yang dikumpulkan (lokasi, foto) dan tujuannya.
- Disclaimer di peta: "KARSA adalah alat bantu pencegahan. Hotspot adalah titik panas, belum tentu titik api. Untuk keadaan darurat hubungi 112 / BPBD setempat."

---

## 20. Definition of Done (per fitur)
- Tampilan sesuai desain di layar 390px dan tetap rapi di desktop.
- Berfungsi saat salah satu sumber data mati (diuji dengan mematikan URL di `.env`).
- Ada test untuk logika inti, `php artisan test` hijau.
- Tidak ada kunci/secret di repo; `.env.example` diperbarui.
- Teks antarmuka berbahasa Indonesia yang baik dan benar.
