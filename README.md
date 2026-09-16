# Aplikasi Iuran Kantin MTsN 4 Jombang

Aplikasi CodeIgniter 4 untuk pencatatan **iuran penjual/pedagang kantin kepada madrasah**, pengeluaran operasional, setoran resmi ke pimpinan, laporan kas, serta Kartu Anggota Kantin dengan QR.

> Aplikasi ini bukan aplikasi iuran siswa dan bukan POS/kasir.

Dokumen utama:

```text
docs/DOKUMEN_ACUAN_Iuran_Kantin_MTsN4.md
docs/AUDIT_TEKNIS_APLIKASI.md
docs/DEPLOYMENT_SHARED_HOSTING.md
```

## Stack

- PHP 8.2+
- CodeIgniter 4
- MySQL/MariaDB
- Sneat Bootstrap 5
- jQuery + DataTables Responsive
- SweetAlert2 + ApexCharts
- Dompdf
- PhpSpreadsheet
- Intervention Image + GD
- Endroid QR Code
- jsQR lokal

Seluruh asset runtime disediakan lokal tanpa CDN.

## Struktur deployment

Front controller `index.php` berada di root project untuk shared hosting. File `spark` juga sudah disesuaikan dengan layout root tersebut sehingga perintah CLI lokal tidak lagi mengacu ke folder `public/` yang tidak ada.

`.htaccess` root memblokir akses langsung ke source/configuration sensitif. Bukti Nota dan Setoran baru disimpan di `writable/uploads/...` dan hanya ditampilkan melalui route Operator terautentikasi. File bukti lama yang masih berada di `uploads/bukti_nota/` atau `uploads/bukti_setoran/` tetap kompatibel, tetapi akses HTTP langsung ke folder tersebut diblokir.

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

Untuk menjalankan seluruh automated test lokal, aktifkan juga `sqlite3`.

## Update lokal

```powershell
cd G:\xampp\htdocs\iuran_dev
git pull origin main
composer install
php spark migrate:status
php spark migrate
php spark routes
vendor\bin\phpunit -c phpunit.dist.xml
```

Jangan menjalankan seeder pada database development/production existing yang sudah berisi data operasional.

## Migration

Migration aplikasi saat ini:

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
2026-09-16-100011_AddUniqueIuranPerPenjualTanggal
2026-09-16-100012_AddGolonganSnapshotToTransaksiIuran
2026-09-16-100013_AddBuktiSetoranToSetoranPimpinan
```

Migration `100011`–`100013` dibuat agar aman terhadap schema yang sebelumnya sudah diterapkan manual pada hosting. Untuk production tanpa terminal, gunakan SQL phpMyAdmin sesuai `docs/DEPLOYMENT_SHARED_HOSTING.md`.

SQL snapshot Golongan lengkap tersedia di:

```text
docs/SQL_100012_GOLONGAN_SNAPSHOT_IURAN.sql
```

## Fresh install

```powershell
composer install
php spark migrate
```

Isi password awal seeder melalui `.env`:

```dotenv
seed.operatorPassword = "GANTI_DENGAN_PASSWORD_OPERATOR"
seed.pimpinanPassword = "GANTI_DENGAN_PASSWORD_PIMPINAN"
```

Kemudian jalankan satu kali pada database kosong:

```powershell
php spark db:seed IuranKantinSeeder
```

Seeder **tidak boleh** dijalankan pada database existing.

## Aturan Iuran

Input Iuran adalah bulk input seluruh Penjual aktif. Penjual yang membayar dicentang dan nominal diprefill dari Golongan tetapi dapat dioverride.

Aturan bisnis penting:

- satu Penjual maksimal satu transaksi Iuran pada tanggal yang sama;
- UI menandai transaksi yang sudah ada sebagai **Tercatat**;
- service server menolak duplikasi;
- database dilindungi unique index `(id_penjual, tanggal)`;
- transaksi menyimpan snapshot ID/nama/nominal default Golongan agar histori tidak berubah saat master Golongan/Penjual berubah.

## Pengeluaran dan Setoran

Pengeluaran dapat memiliki bukti nota opsional. Setoran Resmi baru wajib memiliki bukti foto. JPG/JPEG/PNG maksimal 10 MB dikompresi menjadi JPG dengan target di bawah 500 KB.

Upload bukti baru disimpan privat:

```text
writable/uploads/bukti_nota/
writable/uploads/bukti_setoran/
```

Bukti hanya dapat dilihat melalui route Operator. Bukti tidak ditambahkan ke laporan/export.

Setoran Pimpinan tetap dua tahap: **Cetak Form Setoran** tidak menulis database; **Input Setoran Resmi** baru mencatat transaksi dan mengurangi saldo.

## Dashboard dan laporan

Dashboard menampilkan ringkasan bulan berjalan, saldo kas keseluruhan, Aksi Cepat Operator, serta tiga grafik:

- Iuran harian bulan berjalan;
- Iuran enam bulan terakhir;
- Pengeluaran enam bulan terakhir.

Laporan tersedia untuk Iuran, Pengeluaran, Setoran, dan Rekap Kas. Rekap Kas menggabungkan Iuran menjadi satu total per tanggal; Pengeluaran dan Setoran tetap per transaksi. Export Excel mengikuti filter, filename membawa periode, dan data string ditulis literal untuk mencegah formula injection.

## Kartu dan QR

Canvas kartu `1011 x 638 px`, tanpa foto. Kartu depan berisi Nama, Golongan, Lokasi/Lapak, No. HP, Tanggal Bergabung, Alamat, QR, dan Kode Kartu.

Generate/regenerate kode adalah aksi POST. Download kartu satuan via GET tidak lagi membuat state baru. Download massal memakai POST karena dapat membuat kode untuk Penjual aktif yang belum memilikinya.

QR publik hanya menampilkan Nama, Golongan, dan Status. Operator yang sudah login diarahkan ke Detail Penjual. Scanner memakai jsQR lokal dan membutuhkan HTTPS di production.

## Branding

Upload logo/background menerima JPG/JPEG/PNG maksimal 5 MB. Selain ukuran file, aplikasi membatasi resolusi maksimal **6000 px per sisi dan 24 megapiksel** untuk menghindari penggunaan memori berlebihan saat render.

## Backup

Backup operasional minimal mencakup:

```text
database
uploads/branding/
uploads/bukti_nota/          # legacy bila masih direferensikan
uploads/bukti_setoran/       # legacy bila masih direferensikan
writable/uploads/bukti_nota/
writable/uploads/bukti_setoran/
```

## Production

Gunakan HTTPS dan `CI_ENVIRONMENT = production`. Atur `app.baseURL`, database, secure cookie, permission `writable/`, dan trusted proxy sesuai hosting. Bila hosting tidak menyediakan Composer, sertakan `vendor/` hasil `composer install --no-dev --optimize-autoloader` dari lokal.

Untuk update schema production tanpa terminal, **jalankan SQL yang diperlukan melalui phpMyAdmin sebelum source baru yang bergantung pada field tersebut diaktifkan**. Jangan reset database atau menjalankan seeder.

## CI

GitHub Actions memeriksa:

- validitas dan instalasi dependency Composer;
- `composer audit --locked`;
- syntax PHP pada `app/` dan `tests/`;
- syntax JavaScript utama;
- kompilasi route;
- PHPUnit menggunakan database test terisolasi/SQLite.

Regression test saat ini mencakup keamanan storage bukti transaksi serta export Excel formula-safe, selain test framework yang sudah ada.
