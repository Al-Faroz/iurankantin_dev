# Audit Teknis Aplikasi Iuran Kantin MTsN 4 Jombang

**Tanggal audit:** 16 September 2026

Dokumen ini memisahkan hasil audit teknis dari Dokumen Acuan. Dokumen Acuan tetap berfungsi sebagai baseline aplikasi; file ini mencatat bug yang ditemukan, perbaikan yang sudah dilakukan, risiko yang masih tersisa, dan rekomendasi prioritas berikutnya.

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
- struktur migration/schema;
- `.htaccess` dan deployment shared hosting;
- dependency Composer;
- CI dan automated test.

## 2. Bug yang Ditemukan dan Sudah Diperbaiki

### A. Session lama masih mempercayai role/status saat login

**Risiko:** akun yang sudah dinonaktifkan atau diubah rolenya dapat tetap memakai session lama sampai logout/kedaluwarsa.

**Perbaikan:** request terproteksi sekarang memvalidasi `id_user`, `status_aktif`, dan role terhadap database. Data session nama/username/role disinkronkan dengan database.

**Status:** diperbaiki.

### B. Hapus Setoran melalui Koreksi tidak membersihkan bukti foto

**Risiko:** file `uploads/bukti_setoran/...` menjadi file yatim setelah record database dihapus.

**Perbaikan:** Koreksi Setoran mengambil record lama, memastikan hard delete database berhasil, lalu membersihkan bukti foto yang terkait.

**Status:** diperbaiki.

### C. File transaksi dapat terhapus walaupun delete database gagal

**Risiko:** bukti foto hilang sementara record transaksi masih ada bila operasi database gagal pada production.

**Perbaikan:** delete Pengeluaran/Setoran sekarang diperiksa hasilnya. File bukti baru dihapus setelah hard delete database berhasil.

**Status:** diperbaiki.

### D. Upload nota dapat menjadi yatim bila insert Pengeluaran gagal

**Risiko:** upload berhasil tetapi insert database gagal; sebelumnya controller tetap dapat meninggalkan file tanpa record.

**Perbaikan:** hasil insert diperiksa dan file baru dibersihkan bila insert gagal.

**Status:** diperbaiki.

### E. Branding lama berpotensi terhapus bila penyimpanan Setting gagal

**Risiko:** file baru sudah diupload, update database gagal, tetapi file lama dapat dibersihkan sehingga path database menunjuk file yang hilang.

**Perbaikan:** hasil insert/update Setting sekarang diperiksa. File lama hanya dihapus setelah database berhasil menyimpan path baru.

**Status:** diperbaiki.

### F. Formula injection pada export Excel

**Risiko:** teks dari input pengguna yang diawali karakter formula dapat ditafsirkan sebagai formula spreadsheet. Selain itu teks seperti nomor HP berawalan nol dapat berubah tipe.

**Perbaikan:** seluruh nilai PHP bertipe string diekspor dengan `TYPE_STRING`; data numerik tetap ditulis sebagai angka.

**Status:** diperbaiki.

### G. Filter tanggal hanya memeriksa pola, bukan kalender valid

**Risiko:** nilai seperti `2026-99-99` memenuhi pola `YYYY-MM-DD` tetapi bukan tanggal valid.

**Perbaikan:** filter Laporan dan Koreksi sekarang memvalidasi tanggal menggunakan `DateTimeImmutable::createFromFormat()` dan pencocokan hasil format.

**Status:** diperbaiki.

### H. Folder bukti Setoran belum tercermin di `.gitignore`

**Risiko:** struktur source lokal membingungkan dan folder upload baru mudah terlewat.

**Perbaikan:** exception folder `uploads/bukti_setoran/` ditambahkan bersama folder upload lain.

**Status:** diperbaiki.

### I. CRUD master dapat memberi pesan berhasil saat write database gagal

**Risiko:** pada production dengan detail error database dimatikan, insert/update/archive yang gagal dapat diikuti redirect sukses bila return write tidak diperiksa.

**Perbaikan:** write Penjual, Golongan, Kategori Pengeluaran, dan User sekarang memeriksa hasil database sebelum menampilkan pesan berhasil.

**Status:** diperbaiki.

### J. Generate kode kartu tidak memeriksa kegagalan update database

**Risiko:** render/download kartu dapat berlanjut walaupun `kode_kartu`/`kode_verifikasi` gagal dipersistenkan.

**Perbaikan:** `ensureCodes()` sekarang menghentikan proses dengan error bila update kode gagal.

**Status:** diperbaiki.

## 3. Temuan Prioritas Tinggi yang Membutuhkan Keputusan Bisnis

### 3.1 Duplikasi Iuran Penjual pada tanggal yang sama

Saat ini database tidak memiliki unique constraint `(id_penjual, tanggal)` dan service Input Iuran tidak menolak record lama pada tanggal yang sama.

**Dampak:** Operator dapat membuka kembali tanggal yang sama dan menyimpan Penjual yang sama sekali lagi sehingga pemasukan dan saldo bertambah dua kali.

**Keputusan yang dibutuhkan:** pastikan apakah aturan bisnis adalah maksimal satu transaksi Iuran per Penjual per tanggal.

Jika jawabannya **ya**, rekomendasi implementasi:

1. validasi server sebelum insert batch;
2. tampilkan Penjual yang sudah tercatat sebagai sudah bayar/disabled saat membuka tanggal tersebut;
3. tambahkan unique index database melalui SQL phpMyAdmin:

```sql
ALTER TABLE `transaksi_iuran`
ADD UNIQUE KEY `uniq_iuran_penjual_tanggal` (`id_penjual`, `tanggal`);
```

Sebelum menambah index harus dipastikan tidak ada duplikasi historis.

### 3.2 Histori Golongan Iuran mengikuti Golongan Penjual saat ini

`transaksi_iuran` hanya menyimpan `id_penjual`, tanggal, dan nominal. Laporan Iuran mengambil nama Golongan melalui relasi Penjual **saat laporan dibuka**.

**Dampak:** bila Penjual berpindah Golongan, transaksi lama akan terlihat seolah-olah berasal dari Golongan baru. Filter histori berdasarkan Golongan juga ikut berubah.

**Rekomendasi:** bila histori Golongan harus immutable, simpan snapshot Golongan pada transaksi saat pembayaran, misalnya `id_golongan_snapshot` dan/atau `nama_golongan_snapshot`.

Perubahan ini membutuhkan SQL database dan keputusan format histori sebelum implementasi.

### 3.3 File bukti transaksi berada di web root publik

Bukti Nota dan Bukti Setoran saat ini disimpan di `uploads/` dan dibuka dengan URL langsung. Nama file acak dan eksekusi script sudah diblokir, tetapi file tetap dapat diakses tanpa autentikasi bila URL diketahui.

**Dampak:** bukti operasional bukan data publik idealnya tidak disajikan langsung dari document root.

**Rekomendasi arsitektur:** pindahkan bukti ke storage privat seperti `writable/uploads/` dan sediakan route download/view yang mewajibkan Operator. Ini memerlukan pemindahan file existing dan perubahan URL/path, sehingga sebaiknya dilakukan sebagai pekerjaan terencana.

### 3.4 Schema `bukti_setoran` berada di luar migration

Kolom `bukti_setoran` sengaja diterapkan manual melalui phpMyAdmin karena pola deployment hosting. Akibatnya `php spark migrate` pada database kosong belum cukup menghasilkan seluruh schema yang dibutuhkan source saat ini.

**Mitigasi saat ini:** README, Dokumen Acuan, dan checklist deployment sudah mencantumkan SQL manual yang wajib dijalankan.

**Risiko tersisa:** admin baru dapat melewatkan SQL manual.

**Rekomendasi:** pertahankan satu checklist/schema SQL manual yang menjadi sumber kebenaran untuk perubahan hosting non-migration, atau pada masa depan putuskan kembali apakah migration boleh digunakan untuk fresh install tetapi SQL tetap disediakan untuk hosting existing.

## 4. Temuan Prioritas Menengah

### 4.1 Tanggal transaksi masa depan masih diizinkan

Input Iuran, Pengeluaran, dan Setoran memvalidasi format tanggal tetapi tidak menolak tanggal sesudah hari ini.

Dashboard card/grafik bulan berjalan dibatasi sampai hari ini, sedangkan `Saldo Kas` menjumlahkan seluruh transaksi tanpa batas tanggal.

**Dampak:** bila transaksi masa depan tersimpan, saldo dapat memasukkan transaksi yang belum muncul pada tren/card periode berjalan.

**Rekomendasi:** tentukan apakah future date harus ditolak. Bila ya, tambahkan validasi server pada seluruh transaksi keuangan.

### 4.2 Setoran dapat melebihi saldo kas

Tidak ada validasi yang membatasi nominal Setoran Resmi terhadap saldo kas yang tersedia.

**Dampak:** salah input dapat membuat saldo negatif.

**Rekomendasi:** minimal tampilkan warning/konfirmasi ketika setoran melebihi saldo, atau blokir jika aturan bisnis memang tidak pernah mengizinkan saldo negatif.

### 4.3 Download Kartu melalui GET dapat membuat kode baru

Route download JPG/ZIP memanggil `ensureCodes()`. Bila Penjual belum punya `kode_kartu`/`kode_verifikasi`, request GET download dapat menulis ke database.

**Dampak teknis:** GET idealnya idempotent dan tidak melakukan mutasi state.

**Rekomendasi:** pilih salah satu pola: generate kode saat Penjual dibuat, wajib tekan tombol Generate sebelum download, atau ubah flow download menjadi POST bila perlu membuat state.

### 4.4 Tidak ada audit trail perubahan transaksi keuangan

Transaksi menyimpan Operator pencatat awal, tetapi edit Setoran dan hard delete Koreksi tidak memiliki tabel audit tersendiri.

**Dampak:** setelah koreksi, tidak ada histori siapa mengubah/menghapus nilai lama selain log aplikasi/server bila tersedia.

**Rekomendasi:** untuk kebutuhan pertanggungjawaban lebih tinggi, tambahkan audit log transaksi (aksi, user, waktu, data sebelum/sesudah).

### 4.5 Upload branding belum dibatasi berdasarkan dimensi piksel

Ukuran file branding dibatasi, tetapi gambar dengan dimensi sangat besar masih dapat membutuhkan memori tinggi saat diproses/render.

**Rekomendasi:** tambahkan validasi dimensi maksimum atau normalisasi background kartu ke ukuran canvas yang dibutuhkan.

## 5. Quality Assurance dan Automated Test

Repository masih didominasi test bawaan CodeIgniter. Belum ada coverage otomatis yang memverifikasi aturan bisnis utama aplikasi.

GitHub Actions saat ini menjalankan:

- `composer validate`;
- `composer install`;
- PHP syntax lint;
- JavaScript syntax lint;
- `php spark routes`.

**Rekomendasi prioritas tinggi:** tambahkan test aplikasi untuk:

- autentikasi dan role Operator/Pimpinan;
- akun Nonaktif kehilangan akses;
- Input Iuran batch dan rollback;
- pencegahan duplicate Iuran jika aturan telah disetujui;
- saldo kas;
- agregasi Iuran harian Rekap Kas;
- filter periode;
- export filename dan formula-safe text;
- siklus upload/hapus bukti;
- verifikasi QR publik hanya menampilkan data minimum.

Setelah test aplikasi stabil, tambahkan PHPUnit ke workflow CI.

## 6. Dependency dan Supply Chain

`composer.lock` saat audit mengunci antara lain:

- CodeIgniter Framework `v4.7.4`;
- Dompdf `v3.1.6`;
- PhpSpreadsheet `5.9.0`;
- Endroid QR Code `6.0.9`.

Versi CodeIgniter 4.7.4 mencakup perbaikan security release Juli 2026. Dompdf 3.1.6 juga merupakan versi patch untuk advisory chroot Juli 2026. PhpSpreadsheet 5.9.0 adalah rilis yang lebih baru dari patch keamanan seri 5.8.x.

**Rekomendasi:** jalankan `composer audit --locked` sebelum setiap paket deployment production dan pertimbangkan menambahkannya ke CI setelah memastikan akses advisory Composer pada GitHub Actions stabil.

## 7. Temuan yang Dinilai Baik / Sudah Memadai

- Auto Routing nonaktif dan route eksplisit.
- Aksi tulis utama menggunakan POST + CSRF.
- Operator/Pimpinan dipisahkan di route dan UI.
- Login throttling aktif.
- Session database aktif.
- Password menggunakan hash bawaan PHP.
- DBDebug production dinonaktifkan.
- Secure Headers global aktif.
- Source/configuration diblokir oleh `.htaccess`.
- Upload memakai nama file server-side acak dan direktori upload memblokir script executable.
- Dompdf mematikan remote resource dan PHP execution.
- Scanner QR membatasi hasil ke origin/path verifikasi aplikasi sendiri.
- Halaman QR publik tidak mengambil No. HP/alamat/riwayat transaksi.
- File upload transaksi dikompresi dan mempunyai batas ukuran.
- Setoran tetap memakai alur dua tahap sehingga cetak PDF tidak langsung mengurangi saldo.
- Rekap Kas menghitung saldo awal dan saldo berjalan secara kronologis.
- Export laporan mengikuti filter periode dan filename membawa periode.
- UI utama sudah mobile-responsive dan Dashboard dibuat compact.

## 8. Urutan Pekerjaan yang Direkomendasikan

Prioritas berikutnya setelah audit ini:

1. putuskan aturan **duplicate Iuran per Penjual per tanggal**;
2. putuskan kebutuhan **snapshot Golongan historis**;
3. rencanakan **storage privat untuk bukti Nota/Setoran**;
4. tambah **automated test aplikasi** dan jalankan PHPUnit di CI;
5. putuskan kebijakan **tanggal masa depan** dan **Setoran melebihi saldo**;
6. pertimbangkan audit trail transaksi jika aplikasi digunakan untuk pertanggungjawaban formal jangka panjang.

Perubahan pada poin 1, 2, 3, dan 5 tidak dilakukan otomatis dalam audit karena mengubah aturan bisnis, schema, atau alur operasional.
