# Checklist Deployment Shared Hosting

Dokumen ini adalah checklist final deployment **Aplikasi Iuran Kantin MTsN 4 Jombang** setelah UAT lokal selesai.

## 1. Backup sebelum deployment

Simpan backup di lokasi di luar document root hosting:

- dump database MySQL/MariaDB;
- folder `uploads/branding/`;
- folder `uploads/bukti_nota/`;
- file `.env` lokal hanya sebagai referensi konfigurasi, jangan dipublikasikan.

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

Nilai dapat disesuaikan dengan kebijakan hosting dan jumlah data.

## 3. Install source dan dependency

Dari root aplikasi:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php spark migrate:status
php spark routes
```

Jika ada migration yang benar-benar pending pada server baru, backup database terlebih dahulu lalu jalankan:

```bash
php spark migrate
```

Jangan menjalankan migration secara membabi buta pada database lama tanpa melihat `migrate:status`.

## 4. `.env` production

Buat `.env` dari file contoh `env`. Minimum production:

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

`app.baseURL` harus menggunakan domain/subfolder production yang sebenarnya dan diakhiri `/`.

Jika HTTPS diterminasi oleh reverse proxy/CDN dan aplikasi mengalami redirect loop, konfigurasi proxy tepercaya melalui `Config\App::$proxyIPs` berdasarkan dokumentasi/provider hosting. Jangan menonaktifkan HTTPS hanya untuk menghilangkan redirect loop.

## 5. Permission

PHP/web server harus dapat menulis ke:

```text
writable/
uploads/branding/
uploads/bukti_nota/
```

Gunakan permission paling minimum yang bekerja pada hosting (umumnya folder `755` atau `775` tergantung owner/group). Hindari `777` kecuali benar-benar diwajibkan provider dan tidak ada alternatif.

## 6. Apache / document root

Project ini sengaja memakai struktur di mana front controller `index.php` berada di root project. Pastikan `.htaccess` aktif dan `mod_rewrite`/rewrite provider bekerja.

Sesudah deploy, URL berikut **harus ditolak** (403/404, bukan menampilkan isi file):

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

Folder publik yang memang harus bisa dilayani:

```text
/assets/
/assets-app/
/uploads/
```

`uploads/.htaccess` harus ikut terdeploy agar file script/executable tidak dapat dijalankan dari folder upload.

## 7. HTTPS dan QR scanner

HTTPS wajib pada production karena kamera browser memakai secure context.

Uji pada Chrome Android/Chromium:

- izin kamera dapat diberikan;
- scan QR kartu membuka URL domain production;
- QR publik hanya menampilkan Nama, Golongan, Status;
- Operator yang login diarahkan ke detail internal Penjual;
- QR lama tidak valid setelah regenerate kode verifikasi.

## 8. Smoke test setelah go-live

Lakukan dari browser desktop dan mobile:

```text
[ ] Login Operator
[ ] Login Pimpinan
[ ] Logout
[ ] Sidebar desktop collapse/expand dan state tersimpan setelah reload
[ ] Menu mobile overlay dapat buka/tutup
[ ] Pagination, search, responsive table
[ ] Input Iuran bulk
[ ] Pengeluaran + upload nota
[ ] Cetak form iuran mingguan
[ ] Download form Setoran 2 copy dalam 1 A4
[ ] Input/Edit/Delete/Cetak Ulang Setoran
[ ] Laporan Iuran/Pengeluaran/Setoran
[ ] Rekap Kas dan saldo berjalan
[ ] Export Excel sesuai filter
[ ] Upload logo/background kartu
[ ] Favicon mengikuti logo
[ ] Generate/download kartu JPG/ZIP
[ ] Scan QR kamera dan file gambar
[ ] Verifikasi role Pimpinan tidak memiliki aksi tulis
```

## 9. Backup operasional

Setelah production aktif, backup terjadwal minimal harus mencakup:

- database;
- `uploads/branding/`;
- `uploads/bukti_nota/`.

Kode aplikasi dapat dipulihkan dari Git, tetapi database dan file upload adalah data operasional yang tidak ada di repository.

## 10. Setelah deployment

Jangan mengaktifkan `development` di server production. Jika terjadi error, lihat file log pada `writable/logs/` dan jangan menampilkan debug detail kepada pengguna.
