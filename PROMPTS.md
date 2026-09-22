# PROMPTS.md — Urutan Prompt Vibe Coding KARSA

Cara pakai:
1. Buat proyek Laravel baru, taruh `CLAUDE.md` dan `PROMPTS.md` di root proyek.
2. Ekspor tiap halaman `karsa-desain-semua-layar-final.pdf` menjadi PNG ke `docs/design/` dengan nama berurutan: `01-splash.png`, `02-menu.png`, `03-onboarding-1.png`, `04-onboarding-2.png`, `05-onboarding-3.png`, `06-daftar.png`, `07-peta-risiko.png`, `08-detail-wilayah.png`, `09-aksi-pencegahan.png`, `10-pelaporan.png`, `11-peringatan.png`, `12-news.png`. Taruh juga PDF kerangka KTI di `docs/`.
3. Jalankan prompt **satu per satu**. Jangan lanjut sebelum tahap sebelumnya dites dan jalan. Setelah tiap tahap: `git commit`.
4. Jika hasil melenceng, pakai prompt perbaikan di bagian bawah.

---

## Tahap 0 — Orientasi

```
Baca CLAUDE.md sampai habis, lalu lihat semua gambar di docs/design/ dan PDF di docs/.
Jangan menulis kode dulu. Berikan:
1) ringkasan pemahamanmu tentang KARSA dalam 10 poin,
2) daftar layar publik + layar admin yang akan dibuat,
3) hal yang menurutmu belum jelas atau berisiko (maks 5), masing-masing dengan rekomendasimu.
```

## Tahap 1 — Fondasi proyek

```
Kerjakan fondasi sesuai CLAUDE.md §2, §4, §18:
- Pasang dan konfigurasi Livewire 3, Tailwind 4 (Vite), Alpine, Fortify (headless), Socialite, intervention/image, Pest.
- Buat config/karsa.php + lengkapi .env.example (SIPONGI_HOTSPOT_URL, SIPONGI_EXTRA_PARAMS, FIRMS_MAP_KEY, VAPID_*, GOOGLE_*, SUPERADMIN_EMAIL/PASSWORD, KARSA_CONTACT_EMAIL).
- Timezone Asia/Jakarta, locale id, Carbon berbahasa Indonesia.
- Design token warna & font (Bricolage Grotesque + Plus Jakarta Sans, self-host via @fontsource) di Tailwind @theme.
- Layout public (mobile-first, kolom tengah maks 480px di desktop) dan layout admin (sidebar).
- Komponen Blade: forest-header (SVG pohon 3 lapis + tepi bergelombang, persis seperti desain), risk-pill, card, menu-item, bottom-nav, primary-button.
- Enum: RiskLevel, ReportStatus, UserRole, HotspotConfidence.
Buat halaman /_komponen untuk pratinjau semua komponen (hanya aktif di APP_ENV=local).
```

## Tahap 2 — Database & seeder

```
Buat semua migration, model, relasi, dan factory sesuai CLAUDE.md §16.
Seeder: kabupaten/kota Banten, seluruh kecamatan Kabupaten Serang dengan titik pusat (tulis sumber datanya di komentar dan tandai bagian yang perlu saya verifikasi), model AHP default, rekomendasi default per level × audiens, 4 artikel contoh sesuai desain KARSA News, 1 program contoh, akun superadmin dari .env.
Tambahkan command `karsa:import-geojson {file} {--level=district}` untuk mengimpor batas wilayah dari file GeoJSON (cocokkan berdasarkan kode atau nama).
Jelaskan di mana saya bisa mendapatkan GeoJSON batas kecamatan Kabupaten Serang.
```

## Tahap 3 — Splash, onboarding, auth

```
Buat halaman / (splash), /mulai (onboarding 3 slide dengan swipe + indikator), /daftar, /masuk, lupa kata sandi — persis seperti docs/design/01, 03, 04, 05, 06.
- Registrasi: nama, email ATAU nomor HP (deteksi otomatis), kata sandi min 8 dengan tombol mata, pilih kabupaten/kota, centang syarat.
- Login bisa pakai email atau nomor HP.
- "Daftar dengan Google" via Socialite (bila akun baru, minta pilih kabupaten/kota setelahnya).
- Ilustrasi onboarding dibuat sebagai SVG inline (peta berwarna + pin, lonceng + pill Sedang→Tinggi, kartu laporan + kamera).
- Halaman /syarat dan /privasi berisi draf teks yang wajar.
Tulis test untuk registrasi & login dengan email dan nomor HP.
```

## Tahap 4 — Sumber data hotspot

```
Kerjakan CLAUDE.md §8.1–§8.3:
- Interface HotspotProvider, HotspotDTO, SipongiProvider, FirmsProvider, HotspotAggregator (dedup 1 km/3 jam, corroborated).
- SipongiProvider membaca URL & parameter dari .env, parser toleran berbagai nama field, simpan raw, lempar exception terkendali bila format tak dikenal.
- Command karsa:sync-hotspots: coba sumber sesuai prioritas di settings, fallback otomatis, tentukan district_id dengan point-in-polygon (tulis fungsi ray-casting sendiri, dukung Polygon & MultiPolygon, pakai bbox dulu agar cepat), catat ke data_sync_logs.
- Test parser dengan fixture JSON contoh (buat fixture SiPongi berformat perkiraan + fixture CSV FIRMS asli).
Sebelum mulai, beri saya langkah singkat memeriksa endpoint SiPongi+ lewat DevTools, dan tulis bagian mana yang harus saya sesuaikan setelah mendapat contoh respons aslinya.
```

> Setelah tahap ini: buka laman peta SiPongi+ → DevTools → Network → salin satu contoh respons hotspot → simpan sebagai `tests/Fixtures/sipongi_sample.json` → jalankan prompt berikut:
>
> ```
> Saya sudah menyimpan contoh respons asli di tests/Fixtures/sipongi_sample.json dan URL-nya di .env. Sesuaikan SipongiProvider dengan format asli ini, perbarui test-nya, lalu jalankan sync sekali dan tunjukkan hasilnya.
> ```

## Tahap 5 — Data cuaca

```
Kerjakan CLAUDE.md §8.5: OpenMeteoProvider (utama) dan BmkgProvider (pelengkap, pakai kolom bmkg_adm4 bila terisi).
Command karsa:sync-weather menyimpan observasi harian 30 hari ke belakang + prakiraan 3 hari per kecamatan, menghitung dry_days.
Hemat permintaan: Open-Meteo mendukung banyak koordinat dalam satu request — manfaatkan itu.
Test dengan fixture respons.
```

## Tahap 6 — Model risiko AHP

```
Kerjakan CLAUDE.md §9 dan §10:
- AhpCalculator (eigenvector + CR) dengan test memakai contoh matriks dari literatur yang hasilnya bisa dicek manual.
- RiskScorer (sub-skor, skor akhir, level), DataConfidence, Co2Estimator.
- Command karsa:calculate-risk menyimpan risk_scores untuk semua kecamatan yang dipantau + skor kabupaten.
- Deteksi kenaikan level & susun "penyebab utama" (§9.5).
- Proyeksi 72 jam memakai prakiraan cuaca 3 hari (hotspot diasumsikan sama dengan saat ini).
- Daftarkan jadwal di routes/console.php sesuai §17.
Tunjukkan contoh keluaran untuk Kecamatan Ciruas.
```

## Tahap 7 — API internal & Peta Risiko

```
Buat endpoint /api/v1 sesuai CLAUDE.md §15 (dengan cache & rate limit), lalu halaman /peta sesuai docs/design/07:
- Leaflet + tile CartoDB, choropleth kecamatan berwarna level, hotspot (warna per confidence, cluster), laporan terverifikasi (ikon berbeda).
- Legenda, tombol zoom custom seperti desain, filter lapisan, toggle 24 jam / 7 hari.
- Klik kecamatan → kartu bawah berganti (nama, pill, skor, sparkline 7 hari Chart.js, tombol "Lihat detail wilayah").
- Default fokus ke kabupaten pengguna (atau Kab. Serang untuk tamu), tombol "Lokasi saya".
- Tombol "Buka peta SiPongi+" dan disclaimer hotspot ≠ titik api.
- Atribusi sumber data di pojok peta.
```

## Tahap 8 — Menu, Detail Wilayah, Aksi Pencegahan, Peringatan

```
Buat halaman sesuai desain:
- /menu (docs/design/02) + kartu risiko wilayah saya di atas + bottom nav.
- /wilayah/{slug} (docs/design/08): gauge SVG setengah lingkaran beranimasi sekali saat muncul, 3 kartu cuaca, faktor risiko dengan bar, indikator keyakinan data, estimasi CO₂ indikatif, waktu pembaruan & sumber.
- /aksi (docs/design/09): level & anjuran, rekomendasi 72 jam dari DB dengan filter audiens, checklist tersimpan di localStorage, /aksi/panduan.
- /peringatan (docs/design/11): peringatan aktif, penyebab utama, riwayat.
- /tentang berisi metodologi skoring yang transparan (tampilkan bobot AHP aktif & CR).
```

## Tahap 9 — Pelaporan warga & verifikasi

```
Kerjakan CLAUDE.md §11:
- /lapor sesuai docs/design/10: kamera langsung, pratinjau, lokasi otomatis + pin bisa digeser, jenis kejadian, deskripsi, validasi ramah.
- MediaProcessor (EXIF GPS & waktu, versi publik tanpa EXIF, perceptual hash sederhana dHash), TrustScorer lengkap dengan bendera merah.
- /lapor/saya (riwayat & status).
- Rate limit & honeypot.
- Test TrustScorer untuk skenario: laporan valid dekat hotspot, foto duplikat, lokasi di luar Indonesia, spam.
```

## Tahap 10 — Panel admin (bagian 1)

```
Buat panel /admin sesuai CLAUDE.md §7 poin 1, 2, 3, 6, 11 dengan Livewire:
Dasbor (kartu ringkas, peta gabungan, grafik 30 hari, status sumber data), Hotspot (tabel+filter+sinkron manual+ekspor), Wilayah (impor GeoJSON via upload, edit kerentanan dengan sub-faktor), Laporan Warga (antrean verifikasi, detail dengan peta & jarak ke hotspot, aksi verifikasi/tolak/teruskan), Pengguna (peran, blokir, wilayah tugas verifikator).
Verifikator hanya melihat laporan di wilayah tugasnya. Semua aksi tercatat di activity_logs.
Desain admin: bersih, tetap memakai palet KARSA, tabel padat dan mudah dipindai.
```

## Tahap 11 — Panel admin (bagian 2)

```
Lanjutkan panel admin: Model Risiko AHP (input matriks Saaty dengan dropdown 1/9…9 yang otomatis mengisi sel kebalikannya, hitung bobot & CR secara langsung, tolak simpan bila CR > 0,10, simulator skor, riwayat versi), Validasi Historis (tabel kontingensi, akurasi, recall, false alarm rate, ekspor CSV untuk bahan KTI), Rekomendasi Aksi, KARSA News (Trix), Kontak Instansi, Pengaturan, Log Aktivitas.
```

## Tahap 12 — Notifikasi & peringatan dini

```
Kerjakan CLAUDE.md §13:
- Web Push (VAPID) + halaman /notifikasi dengan tombol aktifkan & petunjuk iOS.
- Wilayah pantauan di /profil (maks 3 kecamatan).
- Alert otomatis dari karsa:calculate-risk → push + in-app + email instansi, anti-spam 12 jam.
- Laporan terverifikasi → email instansi wilayah + tombol "Kirim via WhatsApp" di admin.
- Peringatan/siaran manual dari admin.
Buat command karsa:test-alert {kecamatan} untuk mensimulasikan kenaikan level saat demo.
```

## Tahap 13 — KARSA News & Program Aksi

```
Buat /news dan /news/{slug} sesuai docs/design/12 (tab Semua/Berita/Edukasi, thumbnail ilustrasi otomatis bila tidak ada gambar).
Buat /program dan /program/{slug} + admin-nya sesuai CLAUDE.md §12 (daftar relawan, info donasi manual, progres, laporan penggunaan dana, pengaturan donasi_enabled dan catatan izin). Desain mengikuti gaya layar lain.
```

## Tahap 14 — PWA & offline-first

```
Kerjakan CLAUDE.md §14:
manifest (ikon dari logo perisai-pohon, maskable, shortcuts), sw.js dengan strategi cache sesuai spesifikasi, halaman /offline, label "data terakhir (offline)" di peta & detail wilayah, antrean laporan offline di IndexedDB + Background Sync + cadangan event online, tombol "Pasang aplikasi" + petunjuk iOS.
Jelaskan cara saya mengetes mode offline di Chrome DevTools dan di HP.
```

## Tahap 15 — Pemolesan & uji

```
Audit seluruh aplikasi:
1) bandingkan tiap halaman publik dengan docs/design/ dan perbaiki selisih jarak, ukuran font, warna, radius;
2) aksesibilitas (kontras, label form, fokus keyboard, reduced motion);
3) performa (lazy load peta, ukuran bundle, cache query);
4) keamanan (otorisasi admin/verifikator, upload, rate limit);
5) jalankan php artisan test dan perbaiki yang gagal;
6) uji ketahanan: kosongkan SIPONGI_HOTSPOT_URL dan pastikan fallback FIRMS & tampilan "data terakhir" berjalan.
Beri laporan temuan dan perbaikan.
```

## Tahap 16 — Deploy ke cPanel

```
Buat panduan deploy ke shared hosting cPanel langkah demi langkah di docs/deploy.md:
struktur folder (aplikasi di luar public_html, isi public ke public_html atau subdomain), .env produksi, php artisan key:generate, migrate --seed, storage:link (atau alternatifnya bila symlink diblokir), optimize, cron schedule:run tiap menit, pembuatan kunci VAPID, cek HTTPS, dan checklist setelah deploy.
```

---

## Prompt perbaikan (pakai kapan saja)

**Tampilan melenceng dari desain**
```
Halaman {nama} belum sesuai docs/design/{file}.png. Bandingkan elemen per elemen (urutan, ukuran, warna, jarak, radius, ikon) lalu perbaiki. Sebutkan perbedaan yang kamu temukan sebelum mengubah kode.
```

**Error**
```
Muncul error berikut: {tempel error + langkah memunculkannya}. Cari akar masalahnya dulu, jelaskan singkat, baru perbaiki. Tambahkan test agar tidak terulang.
```

**Sumber data berubah format**
```
Sinkron SiPongi gagal (lihat data_sync_logs terbaru). Ini contoh respons terbaru: {tempel}. Sesuaikan parser tanpa merusak dukungan format lama.
```

**Data untuk KTI**
```
Buat ringkasan data untuk bab hasil KTI periode {tanggal}–{tanggal}: jumlah hotspot per sumber, distribusi level risiko per kecamatan, jumlah & tingkat verifikasi laporan, hasil validasi historis, bobot AHP dan CR yang dipakai. Keluarkan sebagai tabel Markdown dan CSV.
```

**Demo lomba**
```
Siapkan mode demo: command karsa:demo-seed yang mengisi data hotspot, cuaca, laporan, dan peringatan fiktif-realistis 14 hari terakhir untuk Kabupaten Serang (ditandai is_demo agar mudah dihapus), plus command karsa:demo-clear.
```
