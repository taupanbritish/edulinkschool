# Aplikasi Web — Sistem Informasi Sekolah

Aplikasi Laravel untuk SMA Edulink School: website publik, panel admin, ruang guru, dan API yang dipakai aplikasi mobile siswa/orang tua. Identitas sekolah (nama, logo, kontak, sambutan, video, kurikulum) dibaca dari satu halaman **Pengaturan**, sehingga instalasi yang sama bisa dipakai sekolah lain tanpa mengubah kode.

Panduan lengkap seluruh proyek (termasuk aplikasi Flutter dan deploy) ada di [`../README.md`](../README.md) dan [`../DEPLOY.md`](../DEPLOY.md). Berkas ini fokus pada aplikasi web di folder `web/`.

---

## Teknologi

| Bagian | Keterangan |
| --- | --- |
| Framework | Laravel 13.17 (PHP >= 8.3, diuji pada PHP 8.5) |
| Tampilan | Blade + CSS/JS di `public/` (tanpa langkah build) |
| Database | PostgreSQL (diuji PostgreSQL 18); tes memakai SQLite in-memory |
| API mobile | Laravel Sanctum (token Bearer) |
| Dokumen | `dompdf` (PDF), `phpoffice/phpspreadsheet` (impor & ekspor Excel) |
| Pengujian | PHPUnit 12 |

Node.js/npm **tidak diperlukan** untuk menjalankan aplikasi. Berkas Vite/Tailwind hanya tersisa dari scaffold dan tidak dipakai layout aplikasi (`layouts/*.blade.php` memuat `public/css/app.css` langsung).

---

## Prasyarat

- PHP 8.3+ dengan ekstensi `pdo_pgsql`, `mbstring`, `fileinfo`, `gd`, `zip`, dan `dom`.
- Composer 2.
- PostgreSQL 16+ beserta `pg_dump` dan `pg_restore` (untuk backup/restore dari panel).
- Tidak wajib Node.js.

Di mesin pengembangan proyek ini, PHP disediakan Laravel Herd:

```bash
export PATH="$HOME/Library/Application Support/Herd/bin:$PATH"
```

---

## Menjalankan secara lokal

### 1. Siapkan database

Contoh untuk cluster PostgreSQL lokal proyek (port `5433`, terpisah dari PostgreSQL sistem di `5432`):

```bash
/Library/PostgreSQL/18/bin/pg_ctl -D "$HOME/pgdata/sekolah" \
  -l /tmp/pg-sekolah.log -o "-p 5433" start
```

Pada server Linux cukup gunakan PostgreSQL bawaan distro. Buat peran dan database bila belum ada:

```sql
CREATE ROLE sekolah LOGIN PASSWORD 'sekolah';
CREATE DATABASE sekolah_app OWNER sekolah;
```

### 2. Siapkan aplikasi

```bash
composer install
cp .env.example .env        # lewati bila .env sudah ada
php artisan key:generate
```

Sesuaikan koneksi database di `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=sekolah_app
DB_USERNAME=sekolah
DB_PASSWORD=sekolah
```

Lalu jalankan migrasi beserta data demo:

```bash
php artisan migrate --seed
```

> Seeder mengisi akun, siswa, guru, dan konten contoh. Seeder tidak idempotent — jangan dijalankan dua kali pada database yang sama. `migrate:fresh --seed` menghapus **seluruh** data lebih dulu; gunakan hanya saat menyiapkan ulang data demo, jangan di server produksi.

### 3. Jalankan server

```bash
php -d upload_max_filesize=80M -d post_max_size=96M \
    artisan serve --host=127.0.0.1 --port=8000
```

Batas unggah 2 MB bawaan PHP tidak cukup untuk video galeri berita. Di PHP-FPM/CGI (misalnya Herd) `public/.user.ini` sudah menaikkan batas ke 80 MB/96 MB; server bawaan memerlukan opsi `-d` di atas.

### 4. Pastikan berjalan

```bash
curl -s http://127.0.0.1:8000/api/sekolah | head -c 200
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/
```

Berhasil bila JSON memuat nama sekolah dan halaman beranda membalas `200`. Panel admin ada di <http://127.0.0.1:8000/login>.

---

## Akun demo

Dibuat oleh `DatabaseSeeder`. Ganti kata sandi sebelum dipakai di lingkungan nyata.

| Peran | Email | Kata sandi | Akses |
| --- | --- | --- | --- |
| Administrator | `admin@sekolah.id` | `admin123` | Seluruh panel `/admin`, termasuk Pengguna Panel, Pengaturan, Backup & Restore |
| Petugas Web | `petugas@sekolah.id` | `petugas123` | Panel `/admin` tanpa Pengaturan, Pengguna Panel, dan Backup/Restore |
| Kepala Sekolah | `kepsek@sekolah.id` | `kepsek123` | Panel `/admin` mode baca-saja |
| Guru | `guru@sekolah.id` | `guru123` | Ruang `/guru`; wali kelas X-1 untuk absensi |
| Siswa | `siswa@sekolah.id` | `siswa123` | API mobile |
| Orang tua | `ortu@sekolah.id` | `ortu123` | API mobile, dua anak tertaut |

Guru lain (`sri.wahyuni@`, `ahmad.fauzi@`, `ratna.dewi@sekolah.id`) memakai kata sandi `guru123`; siswa tambahan (`siswa2@`, `siswa3@sekolah.id`) memakai `siswa123`.

---

## Peta halaman

| Halaman | URL |
| --- | --- |
| Website publik | `/` |
| Profil sekolah / guru / tata usaha | `/profil`, `/profil/guru`, `/tata-usaha` |
| Struktur organisasi | `/struktur-organisasi` |
| Fasilitas / ekstrakurikuler | `/fasilitas`, `/ekstrakurikuler` |
| Kesiswaan | `/kesiswaan/organisasi-siswa`, `/kesiswaan/bimbingan-konseling` |
| Prestasi | `/prestasi/akademik`, `/prestasi/non-akademik` |
| Akademik | `/akademik/kurikulum`, `/akademik/kalender`, `/akademik/alumni` |
| Berita & majalah siswa | `/berita`, `/berita/majalah` |
| Pencarian cepat | `/cari?q=OSN` |
| Kontak | `/kontak` |
| Masuk / keluar | `/login`, `POST /logout` |
| Panel admin | `/admin` |
| Ruang guru | `/guru` |
| API mobile | `/api` |

Menu **Pengaturan** di panel admin berisi tiga tab: **Pengaturan Sekolah**, **Backup Database**, dan **Restore Database**.

---

## Peran dan hak akses

- **Administrator** — seluruh CRUD, akun panel, identitas sekolah, serta backup/restore database.
- **Petugas Web** — CRUD data dan konten di `/admin`, tanpa Pengaturan, Pengguna Panel, dan Backup/Restore.
- **Kepala Sekolah** — hanya membaca. Middleware `panel.writable` menolak semua permintaan ubah data.
- **Guru** — ruang `/guru`: input nilai untuk kelas yang diajar dan absensi hanya untuk kelas yang menjadi wali kelasnya; ekspor laporan PDF/XLSX.
- **Siswa / Orang tua** — tidak memakai panel web; mengakses data melalui API dan aplikasi mobile. Orang tua hanya dapat memilih anak yang tertaut.

Middleware yang dipakai: `auth`, `role:<peran>`, `panel.writable`.

---

## Fitur utama

**Website publik** — beranda dengan statistik, sambutan kepala sekolah, visi-misi, video YouTube, pengumuman, fasilitas, ekstrakurikuler, prestasi, kurikulum, kalender akademik, alumni, tata usaha, struktur organisasi, kesiswaan, berita dengan galeri foto/video, majalah/artikel siswa, kontak (formulir dibatasi 5 kiriman/menit), pencarian cepat, serta pengalih warna aksen dan mode gelap.

**Panel admin** — siswa, guru, wali murid, kelas, mata pelajaran (beserta penugasan guru), jadwal, tata usaha, struktur organisasi, berita/pengumuman, artikel siswa, pesan & saran, fasilitas, ekstrakurikuler, organisasi siswa, bimbingan konseling, prestasi, kelompok mapel, kalender akademik, alumni, pengguna panel, impor Excel (siswa/guru/kelas/mapel), pembuatan akun otomatis (guru/siswa/wali), dan tab Pengaturan.

**Pengaturan sekolah** — identitas, NPSN, akreditasi, tahun berdiri, logo, favicon, kontak, jam layanan, tautan Maps, media sosial, foto & sambutan kepala sekolah, visi-misi, sejarah, video beranda, serta nama dan penjelasan kurikulum.

**Backup & restore** — lihat bagian di bawah.

---

## Media dan unggahan

- Foto siswa/guru/staf/alumni, logo, dan foto kepala sekolah: JPG/PNG/WEBP, maksimal 2 MB.
- Favicon: PNG/ICO/JPG/WEBP, maksimal 1 MB.
- Galeri berita: hingga 12 foto (JPG/PNG/WEBP, 4 MB per foto) dan 4 video (MP4/WEBM, 50 MB per video), ditambah tautan YouTube.
- Berkas disimpan di disk privat `storage/app/public` dan disajikan lewat rute `/media/{path}` yang hanya mengizinkan awalan `photos/` atau `videos/`. Symlink `public/storage` tidak dipakai.
- Data demo menyalin foto contoh ke `storage/app/public/photos/demo/`; seeder tidak menimpa berkas yang sudah diunggah.

Batas unggah efektif ditentukan oleh `upload_max_filesize`/`post_max_size` PHP. Untuk restore database, batasnya juga dibatasi `DB_RESTORE_UPLOAD_MAX`.

---

## API mobile

Semua endpoint mengembalikan JSON. Endpoint bertanda 🔒 memerlukan header `Authorization: Bearer <token>` dari `POST /api/login`.

| Metode | Endpoint | Keterangan |
| --- | --- | --- |
| GET | `/api/sekolah` | Profil sekolah (termasuk logo dan inisial) |
| GET | `/api/pengumuman` | Daftar berita/pengumuman, filter `category` |
| GET | `/api/pengumuman/{slug}` | Detail berita beserta galeri |
| POST | `/api/login` | Masuk (`email`, `password`, `device_name`) |
| GET 🔒 | `/api/me` | Akun dan siswa tertaut |
| POST 🔒 | `/api/logout` | Cabut token aktif |
| GET 🔒 | `/api/anak` | Daftar anak (orang tua) |
| GET 🔒 | `/api/beranda` | Ringkasan beranda |
| GET 🔒 | `/api/jadwal` | Jadwal per hari (`day` 1–6) |
| GET 🔒 | `/api/nilai` | Rekap nilai per mapel |
| GET 🔒 | `/api/absensi` | Rekap absensi (`month`, `year`) |
| GET 🔒 | `/api/profil` | Profil siswa, wali, dan sekolah |

Endpoint data siswa menerima `student_id` opsional; orang tua hanya boleh memilih anak yang tertaut (selain itu `403`).

Contoh:

```bash
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"siswa@sekolah.id","password":"siswa123"}' \
  | php -r 'echo json_decode(stream_get_contents(STDIN))->token;')

curl -s http://127.0.0.1:8000/api/beranda -H "Authorization: Bearer $TOKEN"
```

---

## Backup dan restore database

Backup memakai `pg_dump --format=custom` dan disimpan di disk privat `storage/app/backups/database/`. Restore menerima unggahan berkas `.dump` yang telah diunduh dari halaman Backup Database.

```dotenv
DB_DUMP_BINARY=/path/ke/pg_dump
DB_RESTORE_BINARY=/path/ke/pg_restore
DB_BACKUP_KEEP=7
DB_BACKUP_AT=02:00
DB_RESTORE_TIMEOUT=600
DB_RESTORE_UPLOAD_MAX=512000
```

```bash
php artisan backup:database     # buat backup manual + rapikan retensi
php artisan schedule:list       # pastikan backup:database terdaftar pukul 02:00
```

- Backup harian dijalankan scheduler Laravel pukul `02:00` (`DB_BACKUP_AT`), menyimpan `DB_BACKUP_KEEP` berkas terbaru. Di server, daftarkan cron `* * * * * cd /path/ke/web && php artisan schedule:run`.
- Restore dilakukan administrator di **Pengaturan → Restore Database**: unggah `.dump`, ketik `PULIHKAN`, sistem memvalidasi dump, membuat backup pengaman `sekolah-pengaman-<tanggal>-<jam>.dump`, mengaktifkan mode pemeliharaan, memulihkan, menghapus berkas unggahan sementara, lalu mengeluarkan semua pengguna. Setelah berhasil, halaman masuk menampilkan **Berhasil Restore Database**.
- Bila restore gagal, mode pemeliharaan dimatikan otomatis dan pesan galat tampil di halaman restore. Hanya satu restore dapat berjalan (berkas kunci `storage/framework/database-restore.lock`).
- **Dump hanya berisi tabel database.** Foto, video, dan berkas unggahan lain tidak termasuk dan harus dicadangkan terpisah (mis. `rsync`).

---

## Pengujian

```bash
php artisan test            # seluruh suite
php artisan test --filter=DatabaseBackupTest
```

Tes berjalan dengan `APP_ENV=testing`, database SQLite in-memory, cache/session array, dan tidak menyentuh database pengembangan. Backup/restore diuji memakai stub `pg_dump`/`pg_restore`, bukan database nyata. Jangan menjalankan tes atau `db:seed` di server produksi.

Perapian kode:

```bash
./vendor/bin/pint
```

---

## Struktur direktori

```
web/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/        # CRUD panel admin
│   │   ├── Api/          # endpoint mobile (Sanctum)
│   │   ├── Guru/         # nilai, absensi, ekspor
│   │   ├── PublicController.php
│   │   └── NewsController.php
│   ├── Imports/          # impor Excel siswa/guru/kelas/mapel
│   ├── Models/
│   └── Support/          # DatabaseBackup, Media, Photo, SpreadsheetReader, Youtube, School
├── config/               # konfigurasi aplikasi (termasuk backup.php)
├── database/
│   ├── migrations/       # skema akademik & konten
│   └── seeders/          # data demo + foto contoh
├── public/
│   ├── css/app.css       # design system, aksen warna, mode gelap
│   └── js/               # theme.js, admin.js
├── resources/views/      # Blade: site, admin, guru, auth, components
├── routes/               # web.php, api.php, console.php
├── storage/app/          # media publik & berkas backup (privat)
└── tests/Feature/        # tes fitur web, API, panel, backup
```

---

## Perintah harian

```bash
php artisan serve                 # jalankan server (tambahkan -d untuk batas unggah)
php artisan migrate               # menerapkan migrasi baru
php artisan route:list
php artisan test
php artisan backup:database
php artisan schedule:work         # jalankan scheduler lokal tanpa cron
php artisan optimize / optimize:clear
```

Seeder demo **tidak idempotent**; jalankan `--seed` hanya saat menyiapkan data awal atau setelah `migrate:fresh`. Setelah data nyata masuk, gunakan `php artisan migrate` saja.

---

## Pemecahan masalah

| Gejala | Penyebab & solusi |
| --- | --- |
| `SQLSTATE[08006] connection refused` | PostgreSQL belum menyala atau port salah. Pastikan host/port di `.env` sesuai (contoh lokal: `5433`). |
| Login berhasil tetapi `403` | Peran tidak cocok dengan halaman. Admin/petugas/kepala sekolah → `/admin`, guru → `/guru`. |
| `419 Page Expired` | Sesi/CSRF kedaluwarsa atau cookie terblokir; muat ulang halaman masuk. |
| Unggahan gagal / `413` | Melebihi `upload_max_filesize`/`post_max_size`; naikkan batas PHP atau jalankan server dengan opsi `-d`. |
| `pg_dump`/`pg_restore` tidak ditemukan | Pasang `postgresql-client` dan isi `DB_DUMP_BINARY`/`DB_RESTORE_BINARY` dengan path absolut. |
| API membalas `401 Sesi Anda telah berakhir` | Token dicabut atau kedaluwarsa (misalnya setelah `migrate:fresh`); login ulang dari aplikasi. |
| Perubahan tampilan tidak muncul | Hapus cache tampilan: `php artisan view:clear`. |

---

## Dokumentasi terkait

- [`../README.md`](../README.md) — panduan lengkap proyek, data demo, dan aplikasi Flutter.
- [`../DEPLOY.md`](../DEPLOY.md) — penyiapan VPS/Nginx/PostgreSQL, HTTPS, backup, dan rollback.
