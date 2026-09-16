# Audit Teknis Aplikasi Iuran Kantin MTsN 4 Jombang

**Tanggal audit:** 16 September 2026

Dokumen ini memisahkan hasil audit teknis dari Dokumen Acuan. Dokumen Acuan berfungsi sebagai baseline aplikasi; file ini mencatat bug yang ditemukan, perbaikan yang sudah dilakukan, risiko yang masih tersisa, dan prioritas berikutnya.

## 1. Ruang Lingkup Audit

Area yang diperiksa:

- routing dan hak akses;
- login, session, CSRF, secure headers;
- master Penjual/Golongan/Kategori/User;
- Input Iuran dan Koreksi Transaksi;
- Pengeluaran dan file bukti;
- Setoran Pimpinan dan file bukti;
- Dashboard dan perhitungan saldo;
- Laporan, Rekap Kas, dan export Excel;
- Kartu Anggota, QR, dan scanner;
- upload/branding;
- migration/schema;
- `.htaccess` dan shared hosting;
- dependency Composer;
- CI dan automated test.

## 2. Bug/Robustness yang Sudah Diperbaiki

### A. Session lama masih mempercayai role/status login

Request terproteksi sekarang memvalidasi `id_user`, `status_aktif`, dan role terhadap database. Session nama/username/role disinkronkan dengan database.

**Status:** selesai.

### B. Koreksi Setoran tidak membersihkan bukti foto

Koreksi Setoran sekarang mengambil record lama, memastikan hard delete berhasil, lalu menghapus file bukti terkait.

**Status:** selesai.

### C. File transaksi dapat terhapus walaupun delete database gagal

Penghapusan Pengeluaran/Setoran sekarang memeriksa hasil database. File hanya dibersihkan setelah hard delete berhasil.

**Status:** selesai.

### D. Upload nota dapat menjadi yatim bila insert gagal

Hasil insert Pengeluaran diperiksa dan file baru dibersihkan bila write gagal.

**Status:** selesai.

### E. Branding lama berpotensi terhapus bila update Setting gagal

File lama baru dihapus setelah insert/update Setting berhasil menyimpan path baru.

**Status:** selesai.

### F. Formula injection pada export Excel

Nilai PHP bertipe string diekspor sebagai `TYPE_STRING`, sementara data numerik tetap sebagai angka.

**Status:** selesai.

### G. Filter tanggal hanya memeriksa pola

Laporan dan Koreksi sekarang memvalidasi tanggal kalender menggunakan `DateTimeImmutable::createFromFormat()`.

**Status:** selesai.

### H. Folder bukti Setoran belum konsisten di `.gitignore`

`uploads/bukti_setoran/` sudah dicantumkan bersama upload dinamis lain.

**Status:** selesai.

### I. CRUD master dapat memberi pesan berhasil saat write gagal

Write Penjual, Golongan, Kategori Pengeluaran, dan User sekarang memeriksa hasil database sebelum menampilkan sukses.

**Status:** selesai.

### J. Generate kode kartu tidak memeriksa kegagalan update

`ensureCodes()` menghentikan proses bila penyimpanan `kode_kartu`/`kode_verifikasi` gagal.

**Status:** selesai.

### K. Duplikasi Iuran Penjual pada tanggal yang sama

Aturan bisnis sudah diputuskan: **satu Penjual maksimal satu transaksi Iuran pada satu tanggal**.

Perbaikan yang diterapkan:

1. Input Iuran memuat transaksi yang sudah ada pada tanggal terpilih;
2. Penjual yang sudah tercatat ditampilkan sebagai `Tercatat` dan tidak dapat dicentang lagi;
3. perubahan tanggal Input Iuran memuat ulang status pembayaran;
4. `IuranService` memeriksa duplikasi sebelum insert batch;
5. unique index database `uniq_iuran_penjual_tanggal (id_penjual, tanggal)` disediakan melalui migration `100011`;
6. migration menghentikan proses bila masih ada duplikasi historis;
7. migration idempotent terhadap index yang sudah dibuat manual melalui phpMyAdmin.

**Status:** implementasi source selesai; unique index production harus diterapkan setelah verifikasi data historis tidak mengandung duplikasi.

## 3. Temuan Prioritas Tinggi yang Masih Tersisa

### 3.1 Histori Golongan Iuran mengikuti Golongan Penjual saat ini

`transaksi_iuran` hanya menyimpan `id_penjual`, tanggal, dan nominal. Laporan mengambil Golongan melalui data Penjual saat laporan dibuka.

**Dampak:** bila Penjual pindah Golongan, transaksi lama dapat terlihat sebagai Golongan baru dan filter histori ikut berubah.

**Rekomendasi:** bila histori Golongan harus immutable, simpan snapshot Golongan pada transaksi.

### 3.2 File bukti transaksi berada di web root publik

Bukti Nota dan Bukti Setoran berada di `uploads/` dan dapat dibuka dengan URL langsung bila nama URL diketahui. Nama file acak dan eksekusi script sudah diblokir, tetapi file belum berada di storage privat.

**Rekomendasi:** pindahkan ke `writable/uploads/` dan sajikan melalui route terautentikasi Operator.

### 3.3 Schema `bukti_setoran` masih di luar migration

Kolom `bukti_setoran` diterapkan manual melalui phpMyAdmin untuk deployment existing. Fresh install dengan migration saja belum menghasilkan field tersebut bila SQL manual tidak dijalankan.

**Rekomendasi:** pada pekerjaan schema berikutnya, putuskan apakah perubahan manual existing tetap dipertahankan sebagai SQL operasional sekaligus ditambahkan migration idempotent untuk fresh install.

## 4. Temuan Prioritas Menengah

### 4.1 Tanggal transaksi masa depan masih diizinkan

Input Iuran, Pengeluaran, dan Setoran memvalidasi format tetapi belum menolak tanggal setelah hari ini.

**Dampak:** Saldo Kas dapat memasukkan transaksi masa depan sementara beberapa card/grafik hanya menghitung sampai hari ini.

### 4.2 Setoran dapat melebihi saldo kas

Tidak ada validasi yang membatasi Setoran Resmi terhadap saldo kas tersedia.

**Dampak:** salah input dapat membuat saldo negatif.

### 4.3 Download Kartu melalui GET dapat membuat kode baru

Route download dapat memanggil `ensureCodes()`, sehingga GET berpotensi menulis state bila kode belum ada.

**Rekomendasi:** buat kode saat Penjual dibuat, wajibkan Generate sebelum download, atau ubah flow mutasi menjadi POST.

### 4.4 Belum ada audit trail perubahan transaksi keuangan

Edit Setoran dan hard delete transaksi belum memiliki tabel log perubahan sebelum/sesudah.

### 4.5 Upload branding belum memiliki batas dimensi piksel

Batas ukuran file ada, tetapi gambar dengan dimensi sangat besar masih dapat meningkatkan penggunaan memori ketika dirender.

## 5. Quality Assurance dan Automated Test

GitHub Actions saat ini menjalankan:

- `composer validate`;
- `composer install`;
- PHP syntax lint;
- JavaScript syntax lint;
- `php spark routes`.

Automated test aplikasi masih terbatas. Prioritas test berikutnya:

- autentikasi Operator/Pimpinan;
- akun Nonaktif kehilangan akses;
- satu Penjual maksimal satu Iuran per tanggal;
- Input Iuran batch dan rollback;
- saldo kas;
- Rekap Kas;
- filter periode;
- export Excel formula-safe;
- lifecycle file bukti;
- QR publik hanya menampilkan data minimum.

Setelah test aplikasi stabil, PHPUnit sebaiknya ditambahkan ke workflow CI.

## 6. Dependency dan Supply Chain

`composer.lock` pada audit mengunci dependency utama modern, termasuk CodeIgniter 4.7.4, Dompdf 3.1.6, PhpSpreadsheet 5.9.0, dan Endroid QR Code 6.0.9.

Rekomendasi operasional: jalankan `composer audit --locked` sebelum paket deployment production dan pertimbangkan menambahkannya ke CI.

## 7. Area yang Sudah Memadai

- Auto Routing nonaktif dan route eksplisit.
- POST + CSRF pada aksi tulis.
- Operator/Pimpinan dipisah di route dan UI.
- Login throttling aktif.
- Database session aktif.
- Password hashing standar PHP.
- DBDebug production nonaktif.
- Secure Headers global aktif.
- Source/configuration diblokir `.htaccess`.
- Upload menggunakan nama server-side acak.
- Dompdf tidak mengizinkan remote resource/PHP execution.
- Scanner QR membatasi URL ke origin/path aplikasi.
- QR publik tidak mengambil data sensitif internal.
- Upload transaksi dikompresi dan dibatasi ukuran.
- Setoran tetap memakai dua tahap.
- Rekap Kas menghitung saldo awal dan berjalan.
- Export mengikuti filter dan filename periode.
- Input Iuran sudah memiliki perlindungan duplikasi berlapis.
- UI utama mobile-responsive.

## 8. Urutan Pekerjaan Berikutnya

Prioritas setelah implementasi duplicate Iuran:

1. putuskan kebutuhan **snapshot Golongan historis**;
2. rencanakan **storage privat bukti Nota/Setoran**;
3. tambah **automated test aplikasi** dan PHPUnit di CI;
4. putuskan kebijakan **tanggal masa depan**;
5. putuskan apakah **Setoran melebihi saldo** harus diblokir;
6. pertimbangkan audit trail transaksi;
7. tambahkan batas dimensi upload branding.

Poin tersebut tidak diubah otomatis karena memengaruhi schema, aturan bisnis, atau alur operasional.
