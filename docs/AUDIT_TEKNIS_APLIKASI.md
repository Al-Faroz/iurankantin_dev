# Audit Teknis Aplikasi Iuran Kantin MTsN 4 Jombang

**Tanggal audit:** 16 September 2026

Dokumen ini memisahkan hasil audit teknis dari Dokumen Acuan. Dokumen Acuan berfungsi sebagai baseline aplikasi; file ini mencatat masalah yang ditemukan, perbaikan yang sudah dilakukan, risiko yang masih tersisa, dan keputusan operasional yang masih diperlukan.

## 1. Ruang Lingkup Audit

Area yang diperiksa meliputi routing/hak akses, login/session/CSRF, master data, seluruh transaksi, Dashboard, Laporan/Excel, Kartu/QR, upload file, migration/schema, `.htaccess`, dependency Composer, shared hosting, CLI `spark`, CI, dan automated test.

## 2. Temuan yang Sudah Diperbaiki

### A. Session lama masih mempercayai role/status login

Request terproteksi sekarang memvalidasi `id_user`, status aktif, dan role terhadap database. Perubahan role/nonaktif berlaku tanpa menunggu session kedaluwarsa.

**Status:** selesai.

### B. Lifecycle file bukti tidak konsisten dengan database

Delete Pengeluaran/Setoran dan Koreksi sekarang memeriksa hasil database sebelum membersihkan file. Upload yang gagal disimpan ke database juga dibersihkan.

**Status:** selesai.

### C. Branding lama dapat hilang bila update Setting gagal

File lama baru dibersihkan setelah database berhasil menyimpan path pengganti.

**Status:** selesai.

### D. Formula injection export Excel

String diekspor menggunakan `TYPE_STRING`; data numerik tetap numerik. Regression test Excel memastikan teks seperti `=2+2` dan nomor HP berawalan nol tetap literal.

**Status:** selesai.

### E. Validasi tanggal filter hanya memeriksa pola

Filter Laporan/Koreksi sekarang memastikan tanggal kalender benar-benar valid.

**Status:** selesai.

### F. CRUD master dapat memberi pesan sukses saat write gagal

Write Penjual, Golongan, Kategori, User, serta generate kode kartu sekarang memeriksa hasil database.

**Status:** selesai.

### G. Duplikasi Iuran Penjual pada tanggal yang sama

Aturan bisnis: **satu Penjual maksimal satu Iuran per tanggal**.

Proteksi:

1. UI menandai Penjual yang sudah `Tercatat`;
2. service server menolak duplikat;
3. unique index `uniq_iuran_penjual_tanggal`;
4. migration `100011` dan SQL manual production.

**Status:** source selesai; production wajib memastikan unique index sudah diterapkan setelah data historis bersih.

### H. Histori Golongan berubah ketika master Penjual berubah

`transaksi_iuran` sekarang menyimpan `id_golongan_snapshot`, `nama_golongan_snapshot`, dan `nominal_golongan_snapshot`. Laporan dan Koreksi memakai snapshot tersebut.

Migration `100012` dan `docs/SQL_100012_GOLONGAN_SNAPSHOT_IURAN.sql` tersedia. Data existing di-backfill dari kondisi Golongan pada saat SQL/migration dijalankan.

**Status:** selesai; production wajib menjalankan SQL snapshot sebelum source yang membaca field tersebut aktif.

### I. Bukti transaksi dapat diakses dengan URL file langsung

Perbaikan:

1. upload bukti Nota/Setoran baru disimpan di `writable/uploads/...`;
2. tombol/thumbnail bukti memakai route Operator terautentikasi;
3. response menggunakan cache privat/no-store dan `nosniff`;
4. resolver storage membatasi prefix dan menolak path traversal;
5. file legacy tetap kompatibel tetapi HTTP langsung diblokir;
6. delete/koreksi dapat membersihkan path legacy maupun privat;
7. regression test memverifikasi resolver private/legacy, delete, dan penolakan path traversal.

**Status:** selesai.

### J. `bukti_setoran` tidak tersedia pada fresh install migration-only

Migration idempotent `100013_AddBuktiSetoranToSetoranPimpinan` sudah ditambahkan. Hosting existing tetap dapat menggunakan SQL phpMyAdmin tanpa konflik.

**Status:** selesai.

### K. Download Kartu GET dapat memutasi database

Sebelumnya route download satuan memanggil `ensureCodes()` sehingga request GET berpotensi membuat kode kartu.

Perbaikan:

- download JPG/ZIP satuan melalui GET sekarang hanya membaca kartu yang sudah memiliki kode;
- pembuatan/regenerate kode tetap POST;
- download massal diubah menjadi POST + CSRF karena proses tersebut memang boleh membuat kode untuk Penjual aktif yang belum memiliki kartu.

**Status:** selesai.

### L. Upload branding hanya dibatasi ukuran file

Gambar kecil secara byte tetapi sangat besar secara resolusi dapat menghabiskan memori GD/Intervention saat render.

Perbaikan: upload branding sekarang dibatasi maksimal **6000 px per sisi dan 24 megapiksel**, selain batas file 5 MB.

**Status:** selesai.

### M. `spark` masih mengarah ke folder `public/` yang sudah tidak ada

Karena front controller dipindahkan ke root, `php spark routes` sebelumnya tetap berhasil tetapi selalu menghasilkan warning `chdir(): No such file or directory`.

Perbaikan: `spark` sekarang menggunakan root project sebagai `FCPATH` dan memuat `app/Config/Paths.php` langsung dari root, sama seperti `index.php`.

**Status:** selesai.

### N. Automated test tidak dijalankan oleh CI

CI sekarang menjalankan PHPUnit menggunakan SQLite test connection yang terisolasi dan tidak menyentuh database production. Test baru mencakup protected proof storage dan formula-safe Excel export.

Konfigurasi coverage bawaan yang tidak digunakan dihapus agar `failOnWarning=true` tetap bermakna dan tidak gagal hanya karena coverage driver sengaja tidak dipasang.

**Status:** selesai; coverage bisnis masih perlu terus diperluas.

### O. Dependency security audit belum otomatis

GitHub Actions sekarang menjalankan `composer audit --locked --no-interaction`. Advisory pada dependency terkunci akan menggagalkan CI.

**Status:** selesai.

## 3. Temuan yang Masih Membutuhkan Keputusan Operasional/Bisnis

### 3.1 Tanggal transaksi masa depan masih diizinkan

Input Iuran, Pengeluaran, dan Setoran memvalidasi format tetapi belum menolak tanggal setelah hari ini.

**Dampak:** Saldo Kas dapat memasukkan transaksi masa depan sementara beberapa card/grafik hanya menghitung sampai hari ini.

**Keputusan yang dibutuhkan:** apakah seluruh transaksi keuangan harus maksimal tanggal hari ini, atau ada kebutuhan mencatat tanggal masa depan.

### 3.2 Setoran dapat melebihi saldo kas

Tidak ada validasi yang membatasi Setoran Resmi terhadap saldo kas tersedia.

**Dampak:** salah input dapat membuat saldo negatif.

**Keputusan yang dibutuhkan:** blokir keras bila nominal > saldo, atau izinkan dengan warning/konfirmasi.

### 3.3 Belum ada audit trail perubahan transaksi keuangan

Edit Setoran dan hard delete melalui Koreksi belum memiliki tabel log yang menyimpan nilai sebelum/sesudah, user, waktu, dan alasan koreksi.

**Keputusan yang dibutuhkan:** apakah jejak audit formal diperlukan untuk pertanggungjawaban jangka panjang.

## 4. Quality Assurance dan Automated Test

GitHub Actions sekarang menjalankan:

- `composer validate`;
- `composer install`;
- `composer audit --locked`;
- PHP syntax lint untuk `app/` dan `tests/`;
- JavaScript syntax lint;
- `php spark routes`;
- PHPUnit.

Regression test saat audit mencakup antara lain:

- resolusi bukti privat dan legacy;
- penolakan path traversal bukti;
- cleanup bukti privat;
- Excel menjaga formula-looking text sebagai string;
- Excel menjaga nomor HP berawalan nol sebagai string;
- test framework/database starter yang berjalan pada SQLite terisolasi.

Coverage bisnis yang disarankan berikutnya:

- autentikasi dan role Operator/Pimpinan;
- akun Nonaktif kehilangan akses;
- duplicate Iuran di service + database;
- snapshot Golongan setelah master berubah;
- Input Iuran batch/rollback;
- saldo dan Rekap Kas;
- protected proof controller authorization;
- QR publik hanya data minimum.

## 5. Dependency dan Supply Chain

Dependency utama saat audit antara lain CodeIgniter 4.7.4, Dompdf 3.1.6, PhpSpreadsheet 5.9.0, dan Endroid QR Code 6.0.9.

`composer audit --locked` sekarang merupakan pemeriksaan CI blocking. Paket deployment production tetap disarankan dibuat dari `composer.lock`, bukan update dependency spontan di hosting.

## 6. Area yang Sudah Memadai

- explicit route dan Auto Routing nonaktif;
- POST + CSRF untuk mutasi;
- role Operator/Pimpinan di route dan UI;
- throttling login;
- database session;
- password hashing;
- DBDebug production nonaktif;
- Secure Headers global;
- proteksi source/configuration;
- bukti transaksi privat;
- Dompdf remote/PHP execution nonaktif;
- scanner QR membatasi origin/path;
- upload gambar dikompresi;
- branding dibatasi ukuran byte dan dimensi;
- Setoran dua tahap;
- Rekap Kas saldo awal/berjalan;
- export berperiode dan formula-safe;
- duplicate Iuran terlindungi berlapis;
- snapshot Golongan historis;
- migration fresh install semakin konsisten;
- GET download kartu tidak lagi menulis state;
- CLI `spark` konsisten dengan layout root;
- CI mencakup dependency audit + PHPUnit;
- UI utama mobile-responsive.

## 7. Urutan Pekerjaan Berikutnya

Pekerjaan teknis yang dapat dilakukan tanpa keputusan bisnis besar berikutnya adalah memperluas test bisnis otomatis. Untuk perubahan perilaku operasional, keputusan berikut perlu ditetapkan terlebih dahulu:

1. **Tanggal masa depan:** blokir semua transaksi di atas hari ini atau tetap izinkan?
2. **Setoran melebihi saldo:** blokir keras atau hanya warning?
3. **Audit trail:** apakah edit/hapus transaksi keuangan perlu disimpan permanen dalam tabel audit?

Setelah tiga kebijakan ini ditetapkan, implementasi dapat diteruskan tanpa asumsi bisnis tersembunyi.
