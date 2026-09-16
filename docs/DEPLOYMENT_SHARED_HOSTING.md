# Checklist Deployment Shared Hosting

Dokumen ini adalah checklist deployment **Aplikasi Iuran Kantin MTsN 4 Jombang** untuk hosting yang dikelola melalui File Manager dan phpMyAdmin.

## 1. Backup Sebelum Deployment

Backup wajib:

- dump database MySQL/MariaDB;
- `.env` production di lokasi aman;
- `uploads/branding/`;
- `uploads/bukti_nota/` legacy;
- `uploads/bukti_setoran/` legacy;
- `writable/uploads/bukti_nota/`;
- `writable/uploads/bukti_setoran/`;
- `writable/logs/audit-transaksi-*.log` bila sudah ada.

Jangan menjalankan seeder pada database production existing.

## 2. Requirement Server

Gunakan PHP 8.2+ dengan extension minimal:

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

## 3. Persiapan Source Lokal

```powershell
cd G:\xampp\htdocs\iuran_dev
git pull origin main
composer install --no-dev --optimize-autoloader --no-interaction
php spark migrate:status
php spark routes
```

Jika ingin verifikasi development sebelum membuat ZIP:

```powershell
composer install --prefer-dist --no-interaction
.\vendor\bin\phpunit.bat -c phpunit.dist.xml
composer audit --locked
```

## 4. File yang Harus Ikut ZIP

Minimal:

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
spark
```

Pastikan file tersembunyi ikut:

```text
.htaccess
uploads/.htaccess
uploads/bukti_nota/.htaccess
uploads/bukti_setoran/.htaccess
```

Jangan memasukkan `.env` development ke paket hosting.

## 5. Schema Database Existing

Migration baseline saat ini sampai `100013`.

### 5.1 `bukti_setoran`

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

### 5.2 Unique Iuran per Penjual per Tanggal

Cek duplikasi:

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

Jika hasil 0 baris, cek index:

```sql
SHOW INDEX FROM `transaksi_iuran`
WHERE `Key_name` = 'uniq_iuran_penjual_tanggal';
```

Jika belum ada:

```sql
ALTER TABLE `transaksi_iuran`
ADD UNIQUE KEY `uniq_iuran_penjual_tanggal` (`id_penjual`, `tanggal`);
```

### 5.3 Snapshot Golongan Iuran

Gunakan file:

```text
docs/SQL_100012_GOLONGAN_SNAPSHOT_IURAN.sql
```

Verifikasi setelah SQL:

```sql
SELECT COUNT(*) AS `jumlah_belum_snapshot`
FROM `transaksi_iuran`
WHERE `id_golongan_snapshot` IS NULL
   OR `nama_golongan_snapshot` IS NULL
   OR `nominal_golongan_snapshot` IS NULL;
```

Hasil yang diharapkan:

```text
0
```

### 5.4 Jangan Reset Database

Jangan import ulang database development hanya untuk memperbarui schema.

Jangan menjalankan seeder pada database existing.

## 6. `.env` Production

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

`app.baseURL` wajib HTTPS dan diakhiri `/`.

## 7. Permission

PHP/web server harus dapat menulis ke:

```text
writable/
writable/logs/
writable/uploads/
writable/uploads/bukti_nota/
writable/uploads/bukti_setoran/
uploads/branding/
```

Gunakan permission minimum yang bekerja, umumnya `755`/`775` tergantung provider. Hindari `777` jika tidak diperlukan.

## 8. Proteksi Document Root

URL berikut harus ditolak 403/404:

```text
/app/
/vendor/
/writable/
/tests/
/docs/
/.github/
/.git/
/.env
/composer.json
/composer.lock
/README.md
/spark
```

URL bukti legacy juga harus ditolak direct:

```text
/uploads/bukti_nota/NAMA_FILE.jpg
/uploads/bukti_setoran/NAMA_FILE.jpg
```

Bukti tetap harus bisa dibuka melalui tombol **Lihat** setelah login Operator.

## 9. HTTPS dan Scanner

HTTPS wajib untuk kamera browser.

Uji:

- izin kamera;
- QR publik hanya menampilkan Nama/Golongan/Status;
- Operator login diarahkan ke Detail Penjual;
- QR lama tidak berlaku setelah regenerate;
- scan file gambar bekerja.

## 10. Smoke Test Setelah Go-Live

```text
[ ] Login Operator
[ ] Login Pimpinan
[ ] Pimpinan read-only
[ ] Akun Nonaktif kehilangan akses
[ ] Dashboard mobile/desktop
[ ] Input Iuran bulk
[ ] Duplicate Iuran ditolak
[ ] Tanggal Iuran masa depan ditolak
[ ] Snapshot Golongan transaksi baru terisi
[ ] Pengeluaran + bukti
[ ] Tanggal Pengeluaran masa depan ditolak
[ ] Cetak Form Iuran Mingguan
[ ] Cetak Form Setoran tanpa insert DB
[ ] Tanggal Form Setoran masa depan ditolak
[ ] Input Setoran Resmi + bukti
[ ] Tanggal Setoran Resmi masa depan ditolak
[ ] Setoran > saldo menampilkan warning
[ ] Setelah konfirmasi, Setoran > saldo tetap bisa disimpan
[ ] Edit Setoran
[ ] Delete/Koreksi transaksi
[ ] Audit log bertambah di writable/logs/audit-transaksi-YYYY-MM.log
[ ] Laporan Iuran/Pengeluaran/Setoran
[ ] Rekap Kas Iuran satu total per tanggal
[ ] Export Excel filename berperiode
[ ] Kartu JPG/ZIP
[ ] Download massal kartu POST + CSRF
[ ] Scan QR
[ ] Bukti transaksi direct URL ditolak
[ ] Bukti melalui route Operator berhasil
```

## 11. Audit Trail

Audit transaksi disimpan tanpa tabel database di:

```text
writable/logs/audit-transaksi-YYYY-MM.log
```

File ini wajib masuk backup operasional.

Jika transaksi berhasil tetapi audit file tidak bertambah, cek:

- permission `writable/logs/`;
- error log aplikasi;
- kapasitas storage hosting.

## 12. Backup Berkala

Backup berkala minimal:

- database;
- branding;
- seluruh bukti transaksi legacy dan privat;
- audit log transaksi.

Kode aplikasi dapat dipulihkan dari Git, tetapi database/upload/audit log tidak.

## 13. Diagnostik Cepat

Jika aplikasi 500 setelah update, cek:

1. PHP 8.2+ dan extension;
2. `vendor/`;
3. `.env`;
4. permission `writable/`;
5. schema database;
6. `.htaccess`;
7. `writable/logs/` dan error log hosting.

Jika CSS/JS 404, cek `app.baseURL` dan lokasi extract ZIP.

Jika route 404 tetapi homepage hidup, cek rewrite `.htaccess`.

## 14. Setelah Deployment

Jangan aktifkan `development` di production. Gunakan log untuk diagnosis dan jangan tampilkan exception/database detail ke pengguna umum.
