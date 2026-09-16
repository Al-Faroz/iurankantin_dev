# Checklist Deployment Shared Hosting

Dokumen ini adalah checklist deployment **Aplikasi Iuran Kantin MTsN 4 Jombang** untuk hosting yang dikelola terutama melalui File Manager dan phpMyAdmin.

## 1. Backup sebelum deployment

Simpan backup di luar document root hosting:

- dump database MySQL/MariaDB;
- `uploads/branding/`;
- file legacy `uploads/bukti_nota/` dan `uploads/bukti_setoran/` jika masih dipakai record lama;
- `writable/uploads/bukti_nota/`;
- `writable/uploads/bukti_setoran/`;
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

Pastikan file berikut ikut ZIP:

```text
.htaccess
uploads/.htaccess
uploads/bukti_nota/.htaccess
uploads/bukti_setoran/.htaccess
```

Jangan memasukkan `.env` lokal jika credential berbeda dari production.

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

Migration idempotent `100013_AddBuktiSetoranToSetoranPimpinan` tersedia untuk fresh install/lokal. Jika kolom sudah dibuat manual, migration tidak menambah ulang.

### 4.2 Aturan satu Iuran per Penjual per tanggal

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

Jika hasilnya **0 baris**:

```sql
ALTER TABLE `transaksi_iuran`
ADD UNIQUE KEY `uniq_iuran_penjual_tanggal` (`id_penjual`, `tanggal`);
```

Verifikasi:

```sql
SHOW INDEX FROM `transaksi_iuran`
WHERE `Key_name` = 'uniq_iuran_penjual_tanggal';
```

Jika ditemukan duplikasi, koreksi data ganda terlebih dahulu. Migration `100011_AddUniqueIuranPerPenjualTanggal` akan berhenti bila duplikasi masih ada dan aman terhadap index yang sudah dibuat manual.

### 4.3 Snapshot Golongan transaksi Iuran

**SQL snapshot harus diterapkan sebelum source yang membaca field snapshot diaktifkan.** File lengkap:

```text
docs/SQL_100012_GOLONGAN_SNAPSHOT_IURAN.sql
```

SQL utama:

```sql
ALTER TABLE `transaksi_iuran`
    ADD COLUMN IF NOT EXISTS `id_golongan_snapshot` INT(11) UNSIGNED NULL AFTER `id_penjual`,
    ADD COLUMN IF NOT EXISTS `nama_golongan_snapshot` VARCHAR(100) NULL AFTER `id_golongan_snapshot`,
    ADD COLUMN IF NOT EXISTS `nominal_golongan_snapshot` DECIMAL(12,2) NULL AFTER `nama_golongan_snapshot`;

UPDATE `transaksi_iuran` AS `ti`
INNER JOIN `penjual` AS `p`
    ON `p`.`id_penjual` = `ti`.`id_penjual`
LEFT JOIN `golongan_penjual` AS `g`
    ON `g`.`id_golongan` = `p`.`id_golongan`
SET
    `ti`.`id_golongan_snapshot` = COALESCE(`ti`.`id_golongan_snapshot`, `p`.`id_golongan`),
    `ti`.`nama_golongan_snapshot` = COALESCE(`ti`.`nama_golongan_snapshot`, `g`.`nama_golongan`),
    `ti`.`nominal_golongan_snapshot` = COALESCE(`ti`.`nominal_golongan_snapshot`, `g`.`nominal_iuran`)
WHERE `ti`.`id_golongan_snapshot` IS NULL
   OR `ti`.`nama_golongan_snapshot` IS NULL
   OR `ti`.`nominal_golongan_snapshot` IS NULL;

ALTER TABLE `transaksi_iuran`
ADD INDEX IF NOT EXISTS `idx_iuran_golongan_snapshot` (`id_golongan_snapshot`);
```

Verifikasi:

```sql
SELECT COUNT(*) AS `jumlah_belum_snapshot`
FROM `transaksi_iuran`
WHERE `id_golongan_snapshot` IS NULL
   OR `nama_golongan_snapshot` IS NULL
   OR `nominal_golongan_snapshot` IS NULL;
```

Hasil yang diharapkan: `0`.

### 4.4 Jangan reset database

Jangan import ulang database development ke production hanya untuk menambah schema. Jangan menjalankan seeder pada database existing.

Jika restore menggunakan dump yang membawa session lama:

```sql
TRUNCATE TABLE `ci_sessions`;
```

## 5. `.env` production

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

`app.baseURL` harus memakai URL production sebenarnya dan diakhiri `/`.

## 6. Permission dan storage bukti privat

PHP/web server harus dapat menulis ke:

```text
writable/
writable/uploads/
writable/uploads/bukti_nota/
writable/uploads/bukti_setoran/
uploads/branding/
```

Folder `writable/uploads/bukti_nota/` dan `writable/uploads/bukti_setoran/` dapat dibuat otomatis oleh aplikasi saat upload pertama bila `writable/` writable.

Upload bukti baru disimpan di `writable/uploads/...`. File bukti lama boleh tetap berada di `uploads/bukti_nota/` atau `uploads/bukti_setoran/`; aplikasi membacanya melalui filesystem tetapi akses HTTP langsung harus ditolak.

Gunakan permission minimum yang bekerja, umumnya `755`/`775`. Hindari `777` kecuali provider benar-benar mewajibkan.

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
/uploads/bukti_nota/NAMA_FILE_LAMA.jpg
/uploads/bukti_setoran/NAMA_FILE_LAMA.jpg
```

Folder publik yang tetap dilayani antara lain `/assets/`, `/assets-app/`, dan branding publik yang memang diperlukan. Bukti Nota/Setoran tidak boleh dilayani langsung.

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
[ ] Request duplikat ditolak
[ ] Koreksi Iuran lalu input ulang pada tanggal yang sama
[ ] Snapshot Golongan transaksi baru terisi
[ ] Ubah Golongan Penjual uji dan transaksi lama tetap memakai Golongan historis
[ ] Pengeluaran + upload bukti baru
[ ] Bukti Pengeluaran dapat dibuka melalui tombol Lihat saat Operator login
[ ] URL langsung file bukti lama di uploads/bukti_nota menghasilkan 403
[ ] Cetak Form Iuran Mingguan
[ ] Cetak Form Setoran tanpa insert database
[ ] Input/Edit/Delete Setoran Resmi + bukti baru
[ ] Bukti Setoran dapat dibuka melalui route Operator
[ ] URL langsung file legacy di uploads/bukti_setoran menghasilkan 403
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
- file legacy `uploads/bukti_nota/` dan `uploads/bukti_setoran/` selama masih direferensikan database;
- `writable/uploads/bukti_nota/`;
- `writable/uploads/bukti_setoran/`.

Kode aplikasi dapat dipulihkan dari Git, tetapi database dan upload adalah data operasional.

## 11. Diagnostik cepat

Jika aplikasi 500 atau gagal setelah upload, cek:

1. PHP dan extension;
2. `vendor/`;
3. `.env`;
4. permission `writable/`;
5. schema database, termasuk `bukti_setoran`, unique index Iuran, dan snapshot Golongan;
6. `.htaccess`/rewrite;
7. `writable/logs/` atau error log panel hosting.

Jika bukti menghasilkan 404, cek path pada database dan keberadaan file fisik di folder legacy/private. Jika menghasilkan 403 pada URL `/uploads/bukti_*`, itu perilaku yang memang diharapkan; gunakan tombol **Lihat** dari aplikasi.

## 12. Setelah deployment

Jangan aktifkan environment `development` di production. Gunakan log server/writable untuk diagnosis dan jangan menampilkan detail exception/database kepada pengguna umum.
