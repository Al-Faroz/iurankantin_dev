# Aplikasi Iuran Kantin MTsN 4 Jombang

Aplikasi CodeIgniter 4 untuk pencatatan iuran **penjual/pedagang kantin kepada madrasah**, pengeluaran kas, setoran ke pimpinan, laporan, serta kartu anggota kantin dengan QR.

> Aplikasi ini bukan aplikasi iuran siswa dan bukan aplikasi POS/kasir.

Dokumen acuan utama pengembangan ada di:

```text
docs/DOKUMEN_ACUAN_Iuran_Kantin_MTsN4.md
```

## Stack

- PHP 8.2+
- CodeIgniter 4
- MySQL/MariaDB
- Sneat Bootstrap 5
- jQuery
- DataTables + Responsive
- SweetAlert2
- ApexCharts
- Dompdf
- PhpSpreadsheet
- Intervention Image + GD
- Endroid QR Code
- jsQR lokal untuk scanner browser

Seluruh asset aplikasi harus tersedia **lokal**, tanpa CDN saat runtime.

## Struktur deployment

Project mengikuti skema shared-hosting pada dokumen acuan: isi folder `public/` CodeIgniter telah dipindahkan ke root project. Karena itu `.htaccess` root memblokir akses web langsung ke source/configuration seperti:

- `app/`
- `vendor/`
- `writable/`
- `tests/`
- `.env`
- `composer.json` / `composer.lock`
- `spark`

Asset publik tetap berada di `assets/`, `assets-app/`, dan file upload publik yang memang dibutuhkan berada di `uploads/`.

## Requirement PHP

Aktifkan minimal extension berikut:

```text
intl
mbstring
mysqli
fileinfo
gd
zip
```

`zip` dibutuhkan untuk download kartu anggota lengkap/bulk. `gd` dibutuhkan untuk kompresi bukti nota, render kartu, dan QR PNG.

## Setup lokal — database existing

Untuk database development yang sudah mempunyai 8 tabel bisnis dan sudah pernah menjalankan migration `100001` sampai `100008`, jangan reset database dan jangan menjalankan seeder awal lagi.

```powershell
git pull origin main
php spark migrate:status
```

Pastikan migration berikut sudah berstatus migrated:

```text
2026-09-11-100001_CreateGolonganPenjualTable
2026-09-11-100002_CreateKategoriPengeluaranTable
2026-09-11-100003_CreateUsersTable
2026-09-11-100004_CreatePenjualTable
2026-09-11-100005_CreateSettingTable
2026-09-11-100006_CreateTransaksiIuranTable
2026-09-11-100007_CreateTransaksiPengeluaranTable
2026-09-11-100008_CreateSetoranPimpinanTable
```

Migration tambahan aplikasi:

```text
2026-09-13-100009_CreateCiSessionsTable
```

Jika hanya `100009` yang pending, jalankan:

```powershell
php spark migrate
```

Aplikasi memakai **database-backed session** pada tabel `ci_sessions`.

## Fresh install

Untuk database kosong:

```powershell
composer install
php spark migrate
```

Sebelum menjalankan seeder fresh-install, isi `.env` dengan password awal yang hanya diketahui administrator:

```dotenv
seed.operatorPassword = "GANTI_DENGAN_PASSWORD_OPERATOR"
seed.pimpinanPassword = "GANTI_DENGAN_PASSWORD_PIMPINAN"
```

Kemudian:

```powershell
php spark db:seed IuranKantinSeeder
```

Seeder membuat tiga golongan awal, singleton setting, akun Operator, dan akun Pimpinan. **Jangan jalankan seeder ini pada database existing yang sudah memiliki data awal.**

## `.env` lokal

Contoh konfigurasi development:

```dotenv
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost/iuran_dev/'

database.default.hostname = localhost
database.default.database = iuran_dev
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
```

`.env` tidak boleh di-commit.

## Scanner QR lokal

File scanner aplikasi:

```text
assets-app/qrcode-lib/kartu-scanner.js
```

Decoder jsQR dipin ke **jsQR 1.4.0**, commit resmi:

```text
34d8eec1ec5d85496f3948ff02fcfe6406f89d81
```

Jika `assets-app/qrcode-lib/jsQR.js` belum tersedia, ambil sekali ke project lokal:

```powershell
Invoke-WebRequest `
  -Uri "https://raw.githubusercontent.com/cozmo/jsQR/34d8eec1ec5d85496f3948ff02fcfe6406f89d81/dist/jsQR.js" `
  -OutFile ".\assets-app\qrcode-lib\jsQR.js"
```

Lalu track sebagai vendor asset:

```powershell
git add assets-app/qrcode-lib/jsQR.js
git commit -m "build: vendor jsQR 1.4.0 locally"
git push origin main
```

Scanner juga menggunakan `BarcodeDetector` sebagai fallback pada browser Chromium yang mendukungnya. Kamera browser pada production membutuhkan **HTTPS**; `localhost` dapat memakai kamera pada browser modern.

## Background kartu anggota

Ukuran canvas kartu adalah:

```text
1011 x 638 px
```

Upload background kartu depan dan belakang melalui menu **Setting**. Background belakang bersifat statis. Kartu depan dirender server-side dan berisi data penjual serta QR verifikasi.

Perilaku QR:

- Pengunjung umum: hanya melihat Nama Penjual, Golongan, dan Status.
- Operator yang sudah login: diarahkan ke detail internal Penjual dan riwayat iurannya.

`kode_kartu` bersifat persisten. Regenerate QR hanya mengganti `kode_verifikasi`, sehingga QR lama tidak berlaku lagi tanpa mengganti kode kartu.

## Pengeluaran

Foto bukti nota bersifat opsional dan hanya menerima JPG/PNG. Server mengecilkan gambar dan mengompres hasil menjadi JPG dengan target **di bawah 500 KB**.

## Setoran pimpinan

Setoran memakai dua tahap terpisah:

1. **Cetak Form Setoran** — hanya menghasilkan PDF dan tidak menyimpan transaksi.
2. **Input Setoran Resmi** — dilakukan setelah uang benar-benar diserahkan kepada pimpinan; tahap ini baru menyimpan `setoran_pimpinan` dan mengurangi saldo kas.

## Laporan

Tersedia:

- Laporan Iuran
- Laporan Pengeluaran
- Laporan Setoran
- Rekap Kas dengan saldo berjalan

Export Excel menggunakan filter yang sama dengan halaman laporan.

## Role

### Operator

Dapat melakukan input transaksi, mengelola master data, setting, kartu anggota, scanner, user, dan melihat laporan.

### Pimpinan

Read-only untuk Dashboard dan Laporan. Tombol aksi/input tidak ditampilkan di UI dan route tulis dilindungi filter Operator.

## Pemeriksaan sebelum UAT

Jalankan dari root project:

```powershell
composer install
php spark migrate:status
php spark routes
```

Syntax PHP juga diperiksa otomatis melalui GitHub Actions workflow `.github/workflows/ci.yml`.

Checklist minimum browser:

```text
[ ] Login Operator
[ ] Login Pimpinan dan pastikan tidak ada menu input/master
[ ] Tambah/edit/arsip master data
[ ] Input iuran bulk beberapa penjual
[ ] Input pengeluaran + foto nota
[ ] Cetak form iuran mingguan
[ ] Cetak form setoran tanpa perubahan saldo
[ ] Simpan setoran resmi dan cek saldo
[ ] Filter tiap laporan dan bandingkan hasil Excel
[ ] Upload logo/background kartu
[ ] Generate kartu depan dan ZIP lengkap
[ ] Scan QR sebagai publik
[ ] Scan QR sebagai Operator
[ ] Regenerate QR lalu pastikan QR lama tidak valid
[ ] Uji tampilan Android Chrome
```

## Production / shared hosting

Sebelum production:

- gunakan HTTPS;
- set `CI_ENVIRONMENT = production`;
- sesuaikan `app.baseURL` ke URL HTTPS production;
- pastikan `writable/`, `uploads/branding/`, dan `uploads/bukti_nota/` writable oleh PHP;
- jalankan `composer install --no-dev --optimize-autoloader`;
- jalankan migration yang masih pending;
- jangan upload `.env` ke repository;
- verifikasi URL langsung ke `/app`, `/vendor`, `/writable`, dan `/.env` menghasilkan akses ditolak;
- ganti password akun awal sebelum aplikasi dipakai operasional.

## Branch pengembangan

Sesuai dokumen acuan, pengembangan project ini dilakukan langsung pada branch `main`.
