# Panduan Deploy KARSA ke Shared Hosting cPanel

Panduan ini mengasumsikan hosting cPanel dengan PHP 8.2+, MySQL, akses SSH atau
Terminal cPanel, dan dukungan Cron Jobs. Jika SSH tidak tersedia, sebagian
besar perintah `composer`/`artisan` bisa dijalankan lewat fitur **Terminal**
di cPanel (bila ada) atau dijalankan secara lokal lalu di-upload hasilnya.

## 1. Struktur folder di server

Aplikasi Laravel **tidak** ditaruh langsung di `public_html` (folder itu bisa
diakses publik apa adanya). Struktur yang aman:

```
/home/USER/
  karsa/                  <- seluruh isi repo Laravel (di LUAR public_html)
    app/ bootstrap/ config/ database/ ...
    public/               <- folder ini yang isinya disalin/symlink ke public_html
  public_html/            <- isi dari karsa/public disalin/symlink ke sini
    (atau public_html/karsa/ bila pakai subdomain/subfolder)
```

Dua pendekatan umum:

- **Domain utama**: salin seluruh isi `karsa/public/*` ke `public_html/`, lalu
  edit `public_html/index.php` agar path `require`-nya menunjuk ke
  `../karsa/vendor/autoload.php` dan `../karsa/bootstrap/app.php` (naik satu
  level dari lokasi aslinya karena sekarang berada langsung di `public_html`).
- **Subdomain** (mis. `app.domainanda.com`): arahkan document root subdomain
  langsung ke `karsa/public` lewat menu **Subdomains** di cPanel -- tidak
  perlu mengedit `index.php` sama sekali. **Cara ini lebih dianjurkan** karena
  lebih sederhana dan tidak mengubah file bawaan Laravel.

## 2. Upload kode

1. Build aset di komputer lokal terlebih dahulu (server shared hosting
   biasanya tidak punya Node.js):
   ```bash
   npm install
   npm run build
   ```
2. Kompres seluruh folder proyek (**kecuali** `node_modules`, `.git`,
   `.tools`) menjadi zip, lalu upload lewat File Manager cPanel atau `scp`,
   dan ekstrak di `/home/USER/karsa`.
3. Pastikan folder `public/build/` (hasil `npm run build`) ikut ter-upload --
   folder ini wajib ada karena `@vite()` di Blade membaca `public/build/manifest.json`.

## 3. Dependensi PHP

Lewat SSH/Terminal cPanel, di dalam folder `karsa/`:

```bash
composer install --no-dev --optimize-autoloader
```

Gunakan `--no-dev` di produksi (Pest, Pint, dsb tidak perlu ikut ter-install).

## 4. Berkas `.env` produksi

Salin `.env.example` menjadi `.env`, lalu sesuaikan:

```
APP_NAME=KARSA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.domainanda.com          # sesuaikan domain/subdomain asli
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=namauser_karsa                   # buat lewat MySQL Databases di cPanel
DB_USERNAME=namauser_karsa
DB_PASSWORD=...

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp                             # gunakan SMTP hosting atau layanan pihak ketiga
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@domainanda.com

# Isi setelah tahap verifikasi masing-masing (lihat CLAUDE.md §8, §13)
SIPONGI_HOTSPOT_URL=
FIRMS_MAP_KEY=
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:kontak@domainanda.com
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://app.domainanda.com/auth/google/callback

SUPERADMIN_EMAIL=admin@domainanda.com
SUPERADMIN_PASSWORD=                          # ganti setelah login pertama
```

**PENTING**: `APP_DEBUG` harus `false` di produksi -- bila `true`, detail
error (termasuk isi `.env`) bisa terekspos ke publik.

## 5. Generate key & migrasi database

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force        # hanya sekali, saat instalasi awal
```

Flag `--force` diperlukan karena `APP_ENV=production` biasanya meminta
konfirmasi interaktif yang tidak bisa dijawab di server.

## 6. Storage & symlink

```bash
php artisan storage:link
```

Bila shared hosting **memblokir symlink** (banyak paket hosting murah
melakukan ini), sebagai alternatif:

- Salin folder `storage/app/public` ke `public/storage` secara manual, lalu
  jalankan ulang setiap kali ada perubahan (kurang ideal), **atau**
- Ubah `FILESYSTEM_DISK` untuk berkas publik agar disajikan lewat route
  bertanda tangan (`Storage::url()` dengan disk kustom) -- opsi ini lebih
  aman untuk media laporan warga yang memang privat by design (lihat
  CLAUDE.md §19), sehingga sebenarnya laporan **tidak perlu** symlink publik
  sama sekali; hanya `storage:link` untuk aset publik umum (jika ada) yang
  memerlukannya.

## 7. Kunci VAPID (Web Push)

```bash
php artisan webpush:vapid
```

Salin `VAPID_PUBLIC_KEY` dan `VAPID_PRIVATE_KEY` yang dihasilkan ke `.env`.

## 8. Optimasi

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Jalankan ulang perintah ini setiap kali `.env` atau kode rute/config berubah
(config yang di-cache tidak lagi membaca `.env` secara langsung).

## 9. Cron job (scheduler)

Di cPanel: **Cron Jobs** → tambah entri baru, jadwal **setiap menit**:

```
* * * * * php /home/USER/karsa/artisan schedule:run >> /dev/null 2>&1
```

Scheduler inilah yang menjalankan `queue:work --stop-when-empty` tiap menit,
sinkronisasi hotspot/cuaca tiap jam, dan perhitungan risiko (lihat
`routes/console.php` & CLAUDE.md §17). **Tidak perlu** queue worker permanen
karena keterbatasan shared hosting.

## 10. HTTPS

PWA, geolokasi, kamera, dan Web Push **mewajibkan HTTPS**. Aktifkan SSL
gratis lewat menu **SSL/TLS Status** → **AutoSSL** di cPanel, lalu pastikan
`APP_URL` di `.env` memakai `https://`. `AppServiceProvider` sudah memaksa
skema HTTPS otomatis saat `APP_ENV=production`.

## 11. Isi kunci API sesuai kebutuhan

- **SiPongi+**: ikuti langkah verifikasi DevTools di CLAUDE.md §8.1 sebelum
  mengisi `SIPONGI_HOTSPOT_URL`.
- **NASA FIRMS**: daftar gratis di
  `https://firms.modaps.eosdis.nasa.gov/api/map_key/` untuk `FIRMS_MAP_KEY`.
- **Google OAuth**: buat kredensial di Google Cloud Console, redirect URI
  harus persis sama dengan `GOOGLE_REDIRECT_URI`.

## 12. Checklist setelah deploy

- [ ] `https://app.domainanda.com/up` mengembalikan 200 (health check Laravel).
- [ ] `/masuk` dan `/daftar` bisa diakses & mengirim data (coba daftar akun uji).
- [ ] Superadmin bisa login ke `/admin` dengan kredensial dari `.env`.
- [ ] `php artisan karsa:sync-weather` berhasil (cek `data_sync_logs`).
- [ ] Cron job aktif -- cek log cPanel atau tabel `jobs`/`data_sync_logs`
      bertambah otomatis setelah beberapa menit.
- [ ] Ikon & manifest PWA termuat (`/manifest.webmanifest`, `/icons/*`),
      dan tombol "Pasang aplikasi" muncul di Chrome/Edge Android atau
      instruksi iOS muncul di Safari.
- [ ] Kirim satu laporan uji di `/lapor`, pastikan foto tersimpan & muncul
      di antrean admin `/admin/laporan`.
- [ ] `APP_DEBUG=false` dan halaman error tidak menampilkan stack trace.
- [ ] Jalankan `php artisan test` di server (bila memungkinkan) atau pastikan
      sudah hijau di lingkungan lokal sebelum deploy.
