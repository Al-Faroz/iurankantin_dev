# Aplikasi Iuran Kantin MTsN 4 Jombang

Aplikasi CodeIgniter 4 untuk pencatatan **iuran penjual/pedagang kantin kepada madrasah**, Pengeluaran, Setoran Resmi ke Pimpinan, Laporan Kas, serta Kartu Anggota Kantin dengan QR.

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
- jQuery
- DataTables Responsive
- SweetAlert2
- ApexCharts
- Dompdf
- PhpSpreadsheet
- Intervention Image + GD
- Endroid QR Code
- jsQR lokal

Asset runtime disediakan lokal tanpa CDN.

## Fitur Utama

- Dashboard ringkasan bulan berjalan + Saldo Kas keseluruhan;
- Input Iuran bulk;
- satu Penjual maksimal satu Iuran per tanggal;
- snapshot Golongan historis transaksi Iuran;
- Pengeluaran + bukti nota;
- Setoran dua tahap + bukti foto;
- warning bila Setoran melebihi saldo, tetapi Operator tetap dapat mengonfirmasi;
- tanggal transaksi masa depan ditolak;
- Koreksi Transaksi;
- audit trail file-based tanpa tabel database;
- Laporan Iuran/Pengeluaran/Setoran;
- Rekap Kas dengan Iuran satu total per tanggal;
- export Excel berperiode;
- Kartu Anggota, QR publik, scanner kamera/file;
- bukti transaksi privat melalui route Operator.

## Struktur Deployment

Front controller `index.php` berada di root project untuk shared hosting.

Folder penting:

```text
app/
assets/
assets-app/
uploads/
writable/
vendor/
docs/
index.php
.htaccess
spark
```

Upload baru bukti transaksi disimpan privat:

```text
writable/uploads/bukti_nota/
writable/uploads/bukti_setoran/
```

File legacy berikut tetap didukung tetapi HTTP direct diblokir:

```text
uploads/bukti_nota/
uploads/bukti_setoran/
```

## Migration

Migration baseline saat ini:

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

Jangan menjalankan seeder pada database existing yang sudah berisi data operasional.

## Update Lokal

```powershell
cd G:\xampp\htdocs\iuran_dev
git pull origin main
composer install --prefer-dist --no-interaction
php spark migrate:status
php spark migrate
php spark routes
.\vendor\bin\phpunit.bat -c phpunit.dist.xml
composer audit --locked
```

Empat seeder lokal lama yang tidak dilacak Git tidak perlu ditambah, dihapus, atau dijalankan.

## Aturan Tanggal

Tanggal transaksi masa depan tidak diperbolehkan untuk:

- Iuran;
- Pengeluaran;
- Tanggal Form Setoran;
- Setoran Resmi.

UI membatasi input tanggal dan server tetap memvalidasi ulang.

## Iuran

Input Iuran menggunakan bulk seluruh Penjual aktif. Nominal diprefill dari Golongan dan dapat dioverride.

Penjual yang sudah membayar pada tanggal tersebut tampil **Tercatat** dan dikunci.

Database menggunakan unique index `(id_penjual, tanggal)`.

Transaksi menyimpan snapshot Golongan agar histori tidak berubah saat master Penjual/Golongan diubah.

## Pengeluaran

Pengeluaran dapat dilengkapi bukti nota opsional.

JPG/JPEG/PNG maksimal 10 MB sebelum kompresi dan ditargetkan menjadi JPG < 500 KB.

## Setoran

Setoran terdiri dari:

1. **Cetak Form Setoran** — hanya PDF, tidak insert database;
2. **Input Setoran Resmi** — dilakukan setelah dana benar-benar diserahkan dan baru mengurangi saldo.

Bukti foto wajib untuk Setoran Resmi baru.

Jika nominal Setoran melebihi saldo, aplikasi menampilkan warning SweetAlert. Operator tetap dapat memilih **Tetap simpan**, sehingga saldo kas dapat menjadi negatif sesuai kebijakan operasional.

## Audit Trail

Audit transaksi sederhana disimpan tanpa perubahan database:

```text
writable/logs/audit-transaksi-YYYY-MM.log
```

Satu baris = satu JSON event.

Audit mencatat CREATE/UPDATE/DELETE transaksi penting beserta waktu, Operator, IP, dan ringkasan data.

File audit harus ikut backup operasional.

## Dashboard

Dashboard menampilkan:

- Iuran bulan berjalan;
- Pengeluaran bulan berjalan;
- Setoran bulan berjalan;
- Saldo Kas keseluruhan;
- grafik Iuran harian bulan berjalan;
- grafik Iuran 6 bulan;
- grafik Pengeluaran 6 bulan.

## Laporan dan Excel

Tersedia Laporan Iuran, Pengeluaran, Setoran, dan Rekap Kas.

Rekap Kas menggabungkan seluruh Iuran pada tanggal yang sama menjadi satu total harian. Pengeluaran dan Setoran tetap per transaksi.

Filename export Excel membawa periode filter. Export Penjual tidak membawa periode karena merupakan master data.

## Kartu dan QR

Canvas kartu `1011 x 638 px` tanpa foto Penjual.

QR publik hanya menampilkan Nama, Golongan, dan Status.

Operator login yang memindai QR diarahkan ke Detail Penjual.

HTTPS wajib di production untuk akses kamera scanner.

## Branding

Upload logo/background kartu menerima JPG/JPEG/PNG dengan batas:

```text
maksimum 5 MB
maksimum 6000 px per sisi
maksimum 24 megapiksel
```

## CI

GitHub Actions menjalankan:

- Composer validate/install;
- `composer audit --locked`;
- PHP syntax lint;
- JavaScript syntax lint;
- route compilation;
- PHPUnit.

## Backup

Backup minimal:

- database;
- `.env` production;
- `uploads/branding/`;
- file bukti legacy;
- `writable/uploads/`;
- `writable/logs/audit-transaksi-*.log`.

Jangan menyimpan credential production di repository.
