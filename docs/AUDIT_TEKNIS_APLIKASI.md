# Audit Teknis Aplikasi Iuran Kantin MTsN 4 Jombang

**Tanggal audit:** 16 September 2026

Dokumen ini memisahkan hasil audit teknis dari Dokumen Acuan. Dokumen Acuan berfungsi sebagai baseline aplikasi; file ini mencatat masalah yang ditemukan, perbaikan yang sudah dilakukan, risiko yang masih tersisa, dan prioritas berikutnya.

## 1. Ruang Lingkup Audit

Area yang diperiksa meliputi routing/hak akses, login/session/CSRF, master data, seluruh transaksi, Dashboard, Laporan/Excel, Kartu/QR, upload file, migration/schema, `.htaccess`, dependency Composer, shared hosting, CI, dan automated test.

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

String diekspor menggunakan `TYPE_STRING`; data numerik tetap numerik.

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
3. route mengirim gambar dengan `Cache-Control: private, no-store` dan `X-Content-Type-Options: nosniff`;
4. resolver storage membatasi prefix dan menolak path traversal;
5. file legacy di `uploads/bukti_nota/` dan `uploads/bukti_setoran/` tetap kompatibel tetapi akses HTTP langsung diblokir oleh root `.htaccess` dan `.htaccess` folder masing-masing;
6. delete/koreksi dapat membersihkan path legacy maupun privat.

**Status:** selesai.

### J. `bukti_setoran` tidak tersedia pada fresh install migration-only

Migration idempotent `100013_AddBuktiSetoranToSetoranPimpinan` sudah ditambahkan. Hosting existing tetap dapat menggunakan SQL phpMyAdmin tanpa konflik bila migration kelak dijalankan.

**Status:** selesai.

## 3. Temuan Prioritas Menengah yang Masih Tersisa

### 3.1 Tanggal transaksi masa depan masih diizinkan

Input Iuran, Pengeluaran, dan Setoran memvalidasi format tetapi belum menolak tanggal setelah hari ini.

**Dampak:** Saldo Kas dapat memasukkan transaksi masa depan sementara beberapa card/grafik hanya menghitung sampai hari ini.

**Perlu keputusan bisnis:** apakah future date harus selalu ditolak atau memang diperlukan untuk backdate/penjadwalan tertentu.

### 3.2 Setoran dapat melebihi saldo kas

Tidak ada validasi yang membatasi Setoran Resmi terhadap saldo kas tersedia.

**Dampak:** salah input dapat membuat saldo negatif.

**Perlu keputusan bisnis:** blokir keras atau cukup warning/konfirmasi.

### 3.3 Download Kartu melalui GET dapat membuat kode baru

Route download dapat memanggil `ensureCodes()`, sehingga GET berpotensi menulis state bila kode belum ada.

**Rekomendasi:** buat kode saat Penjual dibuat, wajibkan Generate sebelum download, atau ubah flow mutasi menjadi POST.

### 3.4 Belum ada audit trail perubahan transaksi keuangan

Edit Setoran dan hard delete transaksi belum memiliki tabel log data sebelum/sesudah.

**Rekomendasi:** bila pertanggungjawaban formal membutuhkan jejak koreksi, tambahkan audit log transaksi.

### 3.5 Upload branding belum memiliki batas dimensi piksel

Batas ukuran file ada, tetapi gambar berdimensi sangat besar masih dapat meningkatkan penggunaan memori saat render.

**Rekomendasi:** batasi dimensi atau normalisasi background kartu saat upload.

## 4. Quality Assurance dan Automated Test

GitHub Actions saat ini menjalankan:

- `composer validate`;
- `composer install`;
- PHP syntax lint;
- JavaScript syntax lint;
- `php spark routes`.

Coverage test bisnis masih terbatas. Prioritas automated test:

- autentikasi dan role;
- akun Nonaktif kehilangan akses;
- duplicate Iuran;
- snapshot Golongan;
- Input Iuran batch/rollback;
- saldo dan Rekap Kas;
- filter/export;
- lifecycle dan otorisasi bukti privat;
- QR publik hanya data minimum.

Setelah test aplikasi stabil, PHPUnit sebaiknya masuk workflow CI.

## 5. Dependency dan Supply Chain

Dependency utama yang dikunci saat audit antara lain CodeIgniter 4.7.4, Dompdf 3.1.6, PhpSpreadsheet 5.9.0, dan Endroid QR Code 6.0.9.

Rekomendasi operasional: jalankan `composer audit --locked` sebelum paket deployment production dan pertimbangkan memasukkannya ke CI.

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
- Setoran dua tahap;
- Rekap Kas saldo awal/berjalan;
- export berperiode dan formula-safe;
- duplicate Iuran terlindungi berlapis;
- snapshot Golongan historis;
- schema fresh install semakin konsisten;
- UI utama mobile-responsive.

## 7. Urutan Pekerjaan Berikutnya

Prioritas teknis selanjutnya:

1. tambah automated test aplikasi dan PHPUnit di CI;
2. perbaiki mutasi state Kartu pada route GET;
3. tambah batas dimensi upload branding;
4. putuskan kebijakan tanggal masa depan;
5. putuskan kebijakan Setoran melebihi saldo;
6. pertimbangkan audit trail transaksi keuangan.

Poin 4–6 memerlukan keputusan operasional/bisnis sebelum diubah.
