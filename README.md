# Aplikasi Iuran Kantin MTsN 4 Jombang

Aplikasi CodeIgniter 4 untuk pencatatan **iuran penjual/pedagang kantin kepada madrasah**, pengeluaran operasional, setoran resmi ke pimpinan, laporan kas, serta Kartu Anggota Kantin dengan QR.

> Aplikasi ini bukan aplikasi iuran siswa dan bukan POS/kasir.

Dokumen acuan utama:

```text
docs/DOKUMEN_ACUAN_Iuran_Kantin_MTsN4.md
```

Checklist deployment shared hosting:

```text
docs/DEPLOYMENT_SHARED_HOSTING.md
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
- jsQR lokal

Seluruh asset runtime disediakan lokal tanpa CDN.

## Struktur deployment

Front controller `index.php` berada di root project untuk kompatibilitas shared hosting. `.htaccess` root memblokir akses langsung ke source/configuration seperti `app/`, `vendor/`, `writable/`, `tests/`, `docs/`, `.env`, Composer files, dan `spark`.

Asset publik berada di `assets/`, `assets-app/`, dan `uploads/`. Folder `uploads/` memiliki `.htaccess` tersendiri untuk menonaktifkan directory listing dan memblokir eksekusi file script umum.

## Requirement PHP

Aktifkan minimal:

```text
intl
mbstring
mysqli
fileinfo
gd
zip
```

`gd` diperlukan untuk kompresi bukti dan render kartu. `zip` diperlukan untuk download kartu lengkap/bulk dan PhpSpreadsheet.

## Update lokal

```powershell
cd G:\xampp\htdocs\iuran_dev
git pull origin main
composer install
php spark migrate:status
php spark routes
```

Jangan menjalankan seeder pada database development/production existing yang sudah berisi data operasional.

## Schema database existing

Migration aplikasi sampai saat ini:

```text
2026-09-11-100001_CreateGolonganPenjualTable
2026-09-11-100002_CreateKategoriPengeluaranTable
2026-09-11-100003_CreateUsersTable
2026-09-11-100004_CreatePenjualTable
2026-09-11-100005_CreateSettingTable
2026-09-11-100006_CreateTransaksiIuranTable
2026-09-11-100007_CreateTransaksiPengeluaranTable
2026-09-11-100008_CreateSetoranPimpinanTable
2026-09-13-100009_CreateCiSessionsTable
2026-09-16-100010_AddAlamatToPenjualTable
```

Selain migration tersebut, fitur bukti foto Setoran Resmi memakai kolom yang diterapkan manual melalui phpMyAdmin sesuai kebijakan hosting:

```sql
ALTER TABLE `setoran_pimpinan`
ADD COLUMN `bukti_setoran` VARCHAR(255) NULL
AFTER `keterangan`;
```

Jalankan SQL tersebut **hanya jika kolom belum ada**. Cek terlebih dahulu:

```sql
SHOW COLUMNS FROM `setoran_pimpinan` LIKE 'bukti_setoran';
```

## Fresh install

Untuk database kosong:

```powershell
composer install
php spark migrate
```

Setelah migration, tambahkan kolom `bukti_setoran` menggunakan SQL manual di atas. Kemudian, bila memang membuat instalasi baru, isi password awal seeder melalui `.env`:

```dotenv
seed.operatorPassword = "GANTI_DENGAN_PASSWORD_OPERATOR"
seed.pimpinanPassword = "GANTI_DENGAN_PASSWORD_PIMPINAN"
```

Lalu jalankan satu kali:

```powershell
php spark db:seed IuranKantinSeeder
```

Seeder fresh-install tidak boleh dijalankan pada database existing.

## `.env` lokal

Contoh development:

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

## Alur transaksi

**Iuran** menggunakan input bulk seluruh Penjual aktif. Penjual yang membayar dicentang, nominal diprefill dari Golongan dan dapat dioverride.

**Pengeluaran** dapat dilengkapi bukti nota opsional. JPG/JPEG/PNG maksimal 10 MB sebelum kompresi dikonversi menjadi JPG dengan target di bawah 500 KB.

**Setoran Pimpinan** terdiri dari dua tahap: Cetak Form Setoran tidak memasukkan data ke database; Input Setoran Resmi dilakukan setelah dana benar-benar diserahkan dan baru mengurangi saldo. Setoran Resmi baru wajib memiliki foto bukti yang juga dikompresi menjadi JPG di bawah 500 KB.

## Dashboard

Dashboard menampilkan ringkasan bulan berjalan, saldo kas keseluruhan, Aksi Cepat Operator, serta tiga grafik:

- iuran harian bulan berjalan;
- iuran enam bulan terakhir;
- pengeluaran enam bulan terakhir.

## Laporan

Tersedia Laporan Iuran, Pengeluaran, Setoran, dan Rekap Kas. Rekap Kas menggabungkan seluruh Iuran pada tanggal yang sama menjadi satu total harian, sementara Pengeluaran dan Setoran tetap per transaksi.

Filename export Excel laporan membawa periode filter. Export Penjual tidak memakai periode karena merupakan master data.

## Kartu dan QR

Canvas Kartu Anggota berukuran `1011 x 638 px`. Kartu depan berisi nama, golongan, lokasi/lapak, No. HP, tanggal bergabung, alamat, QR, dan kode kartu. Background depan/belakang diupload dari Setting.

QR publik hanya menampilkan Nama, Golongan, dan Status. Operator yang sudah login diarahkan ke Detail Penjual. Scanner browser memakai jsQR lokal dan membutuhkan HTTPS pada production untuk akses kamera.

## Upload dan backup

Data upload dinamis:

```text
uploads/branding/
uploads/bukti_nota/
uploads/bukti_setoran/
```

Ketiga folder tersebut dan database wajib masuk backup operasional. Folder harus writable oleh PHP di hosting.

## Production

Gunakan HTTPS dan `CI_ENVIRONMENT = production`. Atur `app.baseURL`, database, secure cookie, dan trusted proxy sesuai hosting. Bila hosting tidak menyediakan Composer/SSH, jalankan `composer install --no-dev --optimize-autoloader` di lokal lalu sertakan `vendor/` dalam ZIP deployment.

Jangan menyimpan credential production di repository.

## CI

GitHub Actions saat ini memeriksa:

- validitas Composer;
- instalasi dependency;
- syntax PHP seluruh `app/`;
- syntax JavaScript utama;
- kompilasi route.

Audit teknis dan rekomendasi pengembangan dicatat terpisah agar Dokumen Acuan tetap menjadi baseline operasional yang bersih.
