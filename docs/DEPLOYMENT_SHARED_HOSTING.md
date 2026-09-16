# Checklist Deployment Shared Hosting

Dokumen ini adalah checklist deployment **Aplikasi Iuran Kantin MTsN 4 Jombang** untuk hosting yang dikelola terutama melalui File Manager dan phpMyAdmin.

## 1. Backup sebelum deployment

Simpan backup di lokasi di luar document root hosting:

- dump database MySQL/MariaDB;
- folder `uploads/branding/`;
- folder `uploads/bukti_nota/`;
- folder `uploads/bukti_setoran/`;
- file `.env` production disimpan aman di luar repository.

Jangan menjalankan seeder pada database operasional yang sudah berisi data.

## 2. Requirement server

Gunakan PHP **8.2+** dan aktifkan minimal extension:

```text
intl
mbstring
mysqli
fileinfo
gd
zip
```

Rekomendasi PHP untuk proses PDF, Excel, gambar, dan ZIP:

```text
memory_limit >= 256M
upload_max_filesize >= 12M
post_max_size >= 16M
max_execution_time >= 60
```

## 3. Persiapan source untuk upload ZIP

Jika hosting tidak memiliki Composer/SSH, jalankan di komputer lokal:

```powershell
cd G:\xampp\htdocs\iuran_dev
git pull origin main
composer install --no-dev --optimize-autoloader --no-interaction
php spark migrate:status
php spark routes
```

ZIP deployment harus memuat minimal:

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

Pastikan file tersembunyi `.htaccess` root dan `uploads/.htaccess` benar-benar ikut ZIP. Jangan memasukkan `.env` lokal ke ZIP bila credential lokal berbeda dari production.

## 4. Schema database production

Database existing harus sudah memiliki seluruh migration sampai `100010` yang menambahkan `penjual.alamat`.

Fitur foto bukti **Input Setoran Resmi** menggunakan kolom manual yang sengaja tidak dibuat sebagai migration hosting. Sebelum source yang memakai fitur tersebut aktif, cek lewat phpMyAdmin:

```sql
SHOW COLUMNS FROM `setoran_pimpinan` LIKE 'bukti_setoran';
```

Jika query tidak menghasilkan baris, jalankan:

```sql
ALTER TABLE `setoran_pimpinan`
ADD COLUMN `bukti_setoran` VARCHAR(255) NULL
AFTER `keterangan`;
```

Jangan import ulang database development ke production hanya untuk menambah satu kolom. Jangan menjalankan seeder pada database existing.

Bila menggunakan dump database existing yang sudah memiliki tabel `ci_sessions`, disarankan membersihkan session lama setelah import:

```sql
TRUNCATE TABLE `ci_sessions`;
```

Perintah tersebut tidak menghapus akun User atau transaksi.

## 5. `.env` production

Buat `.env` pada root yang sama dengan `index.php`:

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

`app.baseURL` harus menggunakan domain/subfolder production sebenarnya dan diakhiri `/`.

Jika provider memberi hostname database selain `localhost`, gunakan hostname dari panel hosting.

Jika HTTPS diterminasi oleh reverse proxy/CDN dan aplikasi mengalami redirect loop, konfigurasi proxy tepercaya melalui `Config\App::$proxyIPs` berdasarkan dokumentasi provider. Jangan menonaktifkan HTTPS atau secure cookie hanya untuk menghilangkan redirect loop.

## 6. Permission

PHP/web server harus dapat menulis ke:

```text
writable/
uploads/branding/
uploads/bukti_nota/
uploads/bukti_setoran/
```

Gunakan permission minimum yang bekerja pada hosting, umumnya `755` atau `775` tergantung owner/group. Hindari `777` bila tidak benar-benar diwajibkan provider.

## 7. Apache / document root

Extract ZIP langsung ke document root subdomain sehingga `index.php` berada di document root, bukan di folder ganda seperti `public_html/iuran_dev/iuran_dev/index.php`.

Project memakai `.htaccess` untuk rewrite dan proteksi source. Sesudah deploy, URL berikut **harus ditolak** dengan 403/404:

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

Folder publik yang memang harus dapat dilayani:

```text
/assets/
/assets-app/
/uploads/
```

`uploads/.htaccess` wajib ikut terdeploy agar file script/executable tidak dapat dijalankan dari folder upload.

Jika server menghasilkan 500 segera setelah deploy, cek error log hosting. Beberapa provider membatasi directive `Options`; sesuaikan hanya berdasarkan kebutuhan provider tanpa menghapus proteksi file sensitif.

## 8. HTTPS dan QR scanner

HTTPS wajib pada production karena kamera browser memakai secure context.

Uji pada Chrome Android/Chromium:

- izin kamera dapat diberikan;
- scan QR kartu membuka URL domain production;
- QR publik hanya menampilkan Nama, Golongan, Status;
- Operator yang login diarahkan ke Detail Penjual internal;
- QR lama tidak valid setelah regenerate kode verifikasi;
- scan dari file gambar juga bekerja.

## 9. Smoke test setelah go-live

Lakukan dari browser desktop dan mobile:

```text
[ ] Login Operator
[ ] Login Pimpinan
[ ] Nonaktifkan akun uji dan pastikan session kehilangan akses pada request berikutnya
[ ] Logout
[ ] Sidebar desktop collapse/expand dan state tersimpan setelah reload
[ ] Menu mobile overlay dapat buka/tutup
[ ] Pagination, search, responsive table
[ ] Input Iuran bulk
[ ] Pengeluaran + upload/kompres nota
[ ] Cetak Form Iuran Mingguan
[ ] Download Form Setoran dua copy dalam satu A4
[ ] Input Setoran Resmi + upload/kompres bukti
[ ] Edit Setoran tanpa mengganti bukti
[ ] Edit Setoran dengan bukti baru
[ ] Koreksi/hapus Setoran dan pastikan bukti terkait ikut bersih
[ ] Laporan Iuran/Pengeluaran/Setoran
[ ] Rekap Kas: Iuran satu total per tanggal
[ ] Export Excel sesuai filter dan filename membawa periode
[ ] Dashboard ringkasan bulan berjalan + tiga grafik
[ ] Upload logo/background kartu
[ ] Favicon mengikuti logo
[ ] Generate/download kartu JPG/ZIP
[ ] Scan QR kamera dan file gambar
[ ] Verifikasi role Pimpinan tidak memiliki aksi tulis
```

## 10. Backup operasional

Setelah production aktif, backup terjadwal minimal harus mencakup:

- database;
- `uploads/branding/`;
- `uploads/bukti_nota/`;
- `uploads/bukti_setoran/`.

Kode aplikasi dapat dipulihkan dari Git, tetapi database dan file upload adalah data operasional yang tidak ada di repository.

## 11. Diagnostik cepat

Jika aplikasi 500 atau tidak berjalan setelah upload, cek secara berurutan:

1. versi PHP dan extension wajib;
2. keberadaan `vendor/`;
3. sintaks dan credential `.env`;
4. permission `writable/` dan folder upload;
5. keberadaan kolom database yang dibutuhkan;
6. `.htaccess` dan dukungan rewrite;
7. error log pada `writable/logs/` atau panel hosting.

Jika CSS/JS 404, biasanya document root/extract ZIP atau `app.baseURL` tidak sesuai. Jika homepage bekerja tetapi route lain 404, periksa rewrite `.htaccess`.

## 12. Setelah deployment

Jangan mengaktifkan `development` pada production. Gunakan log server/writable untuk diagnosis dan jangan menampilkan detail exception/database kepada pengguna umum.
