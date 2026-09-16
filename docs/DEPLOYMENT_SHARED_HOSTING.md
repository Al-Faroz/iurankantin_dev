# Checklist Deployment Shared Hosting

Dokumen ini adalah checklist deployment **Aplikasi Iuran Kantin MTsN 4 Jombang** untuk hosting yang dikelola terutama melalui File Manager dan phpMyAdmin.

## 1. Backup sebelum deployment

Simpan backup di luar document root hosting:

- dump database MySQL/MariaDB;
- `uploads/branding/`;
- `uploads/bukti_nota/`;
- `uploads/bukti_setoran/`;
- `.env` production di lokasi aman di luar repository.

Jangan menjalankan seeder pada database operasional existing.

## 2. Requirement server

Gunakan PHP **8.2+** dan aktifkan minimal:

```text
intl
mbstring
mysqli
fileinfo
gd
zip
```

Rekomendasi:

```text
memory_limit >= 256M
upload_max_filesize >= 12M
post_max_size >= 16M
max_execution_time >= 60
```

## 3. Persiapan source untuk upload ZIP

Jika hosting tidak memiliki Composer/SSH, jalankan lokal:

```powershell
cd G:\xampp\htdocs\iuran_dev
git pull origin main
composer install --no-dev --optimize-autoloader --no-interaction
php spark migrate:status
php spark routes
```

ZIP minimal memuat:

```text
app/
assets/
assets-app/
uploads/
vendor/
writable/
.htaccess
index.php
composer.json
composer.lock
```

Pastikan `.htaccess` root dan `uploads/.htaccess` ikut ZIP. Jangan memasukkan `.env` lokal jika credential berbeda dari production.

## 4. Schema database production

### 4.1 Kolom `bukti_setoran`

Cek:

```sql
SHOW COLUMNS FROM `setoran_pimpinan` LIKE 'bukti_setoran';
```

Jika belum ada:

```sql
ALTER TABLE `setoran_pimpinan`
ADD COLUMN `bukti_setoran` VARCHAR(255) NULL
AFTER `keterangan`;
```

### 4.2 Aturan satu Iuran per Penjual per tanggal

Source terbaru menetapkan aturan bisnis:

> satu Penjual maksimal satu transaksi Iuran pada tanggal yang sama.

Sebelum membuat unique index, **wajib cek duplikasi historis**:

```sql
SELECT
    `id_penjual`,
    `tanggal`,
    COUNT(*) AS `jumlah`
FROM `transaksi_iuran`
GROUP BY `id_penjual`, `tanggal`
HAVING COUNT(*) > 1
ORDER BY `tanggal`, `id_penjual`;
```

Jika query menghasilkan **0 baris**, lanjutkan:

```sql
ALTER TABLE `transaksi_iuran`
ADD UNIQUE KEY `uniq_iuran_penjual_tanggal` (`id_penjual`, `tanggal`);
```

Lalu verifikasi:

```sql
SHOW INDEX FROM `transaksi_iuran`
WHERE `Key_name` = 'uniq_iuran_penjual_tanggal';
```

Jika query duplikasi menghasilkan baris, **jangan jalankan ALTER TABLE dulu**. Koreksi transaksi ganda terlebih dahulu dengan memastikan record mana yang benar. Setelah bersih, ulangi query deteksi dan baru buat unique index.

Migration `100011_AddUniqueIuranPerPenjualTanggal` juga tersedia di source. Migration tersebut:

- berhenti bila menemukan duplikasi historis;
- tidak membuat ulang index bila index sudah diterapkan manual melalui phpMyAdmin.

Jadi, jika suatu saat terminal tersedia dan `php spark migrate` dijalankan setelah index dibuat manual, migration tetap aman dan hanya akan mencatat status migration.

### 4.3 Jangan reset database

Jangan import ulang database development ke production hanya untuk menambah schema. Jangan menjalankan seeder pada database existing.

Jika restore menggunakan dump yang membawa session lama, session dapat dibersihkan dengan:

```sql
TRUNCATE TABLE `ci_sessions`;
```

Perintah tersebut tidak menghapus User atau transaksi.

## 5. `.env` production

Contoh:

```dotenv
CI_ENVIRONMENT = production

app.baseURL = 'https://DOMAIN-ANDA/'
app.forceGlobalSecureRequests = true

cookie.secure = true
cookie.httponly = true
cookie.samesite = 'Lax'

database.default.hostname = localhost
database.default.database = NAMA_DATABASE
database.default.username = USER_DATABASE
database.default.password = PASSWORD_DATABASE
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_general_ci
```

`app.baseURL` harus menggunakan URL production sebenarnya dan diakhiri `/`.

Jika HTTPS diterminasi oleh reverse proxy/CDN dan terjadi redirect loop, konfigurasi proxy tepercaya sesuai provider. Jangan menonaktifkan HTTPS atau secure cookie sebagai solusi permanen.

## 6. Permission

PHP/web server harus dapat menulis ke:

```text
writable/
uploads/branding/
uploads/bukti_nota/
uploads/bukti_setoran/
```

Gunakan permission minimum yang bekerja, umumnya `755`/`775` tergantung owner/group. Hindari `777` kecuali provider benar-benar mewajibkan.

## 7. Apache / document root

Extract ZIP sehingga `index.php` berada langsung pada document root subdomain.

URL berikut harus ditolak 403/404:

```text
/app/
/vendor/
/writable/
/docs/
/scripts/
/.github/
/.git/
/.env
/env
/composer.json
/composer.lock
/README.md
/spark
```

Folder publik yang memang dilayani:

```text
/assets/
/assets-app/
/uploads/
```

`uploads/.htaccess` wajib ikut deployment.

## 8. HTTPS dan QR scanner

HTTPS wajib karena kamera browser memakai secure context.

Uji Chrome Android/Chromium:

- izin kamera dapat diberikan;
- QR membuka domain production;
- publik hanya melihat Nama, Golongan, Status;
- Operator login diarahkan ke Detail Penjual;
- QR lama tidak valid setelah regenerate;
- scan file gambar bekerja.

## 9. Smoke test setelah go-live

```text
[ ] Login Operator
[ ] Login Pimpinan
[ ] Akun Nonaktif kehilangan akses pada request berikutnya
[ ] Sidebar desktop/mobile
[ ] DataTables responsive/search/pagination
[ ] Input Iuran bulk
[ ] Ganti tanggal Input Iuran dan cek status Tercatat
[ ] Penjual yang sudah tercatat tidak dapat dipilih lagi
[ ] Coba request duplikat dan pastikan server menolak
[ ] Koreksi Iuran lalu input ulang Penjual pada tanggal yang sama
[ ] Pengeluaran + upload/kompres nota
[ ] Cetak Form Iuran Mingguan
[ ] Cetak Form Setoran tanpa insert database
[ ] Input/Edit/Delete Setoran Resmi + bukti foto
[ ] Laporan Iuran/Pengeluaran/Setoran
[ ] Rekap Kas: Iuran satu total per tanggal
[ ] Export Excel sesuai filter dan filename periode
[ ] Dashboard ringkasan + tiga grafik
[ ] Upload logo/background kartu
[ ] Generate/download kartu JPG/ZIP
[ ] Scan QR kamera dan file gambar
[ ] Role Pimpinan tidak memiliki aksi tulis
```

## 10. Backup operasional

Backup terjadwal minimal:

- database;
- `uploads/branding/`;
- `uploads/bukti_nota/`;
- `uploads/bukti_setoran/`.

Kode aplikasi dapat dipulihkan dari Git, tetapi database dan upload adalah data operasional.

## 11. Diagnostik cepat

Jika aplikasi 500 atau gagal setelah upload, cek:

1. PHP dan extension;
2. `vendor/`;
3. `.env`;
4. permission;
5. schema database, termasuk `bukti_setoran` dan unique index Iuran;
6. `.htaccess`/rewrite;
7. `writable/logs/` atau error log panel hosting.

Jika CSS/JS 404, periksa document root/extract ZIP dan `app.baseURL`. Jika homepage bekerja tetapi route lain 404, periksa rewrite `.htaccess`.

## 12. Setelah deployment

Jangan aktifkan environment `development` di production. Gunakan log server/writable untuk diagnosis dan jangan menampilkan detail exception/database kepada pengguna umum.
