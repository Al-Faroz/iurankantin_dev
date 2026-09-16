# Dokumen Acuan Aplikasi Iuran Kantin MTsN 4 Jombang

Dokumen ini adalah **baseline utama** Aplikasi Iuran Kantin MTsN 4 Jombang. Isinya menggambarkan tujuan, aturan bisnis, arsitektur, struktur data, fitur, keamanan, UI/UX, dan cara operasional aplikasi dalam kondisi saat ini. Dokumen ini bukan changelog dan bukan dokumen revisi bertingkat.

## 1. Tujuan dan Ruang Lingkup

Aplikasi digunakan untuk pencatatan **iuran penjual/pedagang kantin kepada madrasah**, pengeluaran operasional kantin, setoran kas kepada pimpinan, pelaporan kas, serta pengelolaan kartu anggota kantin.

Aplikasi bukan aplikasi kasir/POS, bukan aplikasi jual-beli kantin, dan bukan aplikasi iuran siswa. Sistem tidak mengelola piutang/tunggakan; transaksi iuran hanya dicatat ketika pembayaran benar-benar diterima.

Tujuan utama:

- mencatat pemasukan iuran harian penjual kantin;
- mencatat pengeluaran operasional kantin;
- mencatat setoran kas yang benar-benar sudah diserahkan kepada pimpinan;
- menghitung saldo kas berjalan;
- menyediakan laporan dan export Excel;
- menyediakan form kontrol manual mingguan;
- menyediakan kartu anggota kantin dan verifikasi QR.

## 2. Role dan Hak Akses

| Role | Hak akses |
| --- | --- |
| **Operator** | Mengelola master data, user, setting, transaksi, koreksi transaksi, setoran, kartu anggota, scanner QR, dashboard, laporan, PDF, dan export Excel. |
| **Pimpinan** | Read-only untuk Dashboard dan seluruh Laporan. Tidak memiliki akses ke route input/edit/hapus/master/setting/user. |

Hak akses tulis dilindungi route dengan filter Operator. Status dan role user aktif divalidasi kembali terhadap database pada request terproteksi agar perubahan role atau status Nonaktif berlaku tanpa menunggu session kedaluwarsa.

## 3. Arsitektur dan Stack Teknis

| Aspek | Standar aplikasi |
| --- | --- |
| Backend | PHP 8.2+ dan CodeIgniter 4, standard MVC/flat structure |
| Database | MySQL/MariaDB, charset `utf8mb4` |
| UI | Sneat Free, Bootstrap 5 |
| Tabel | DataTables + Responsive |
| Interaksi | jQuery, SweetAlert2 |
| Grafik | ApexCharts |
| PDF | Dompdf |
| Excel | PhpSpreadsheet |
| Gambar | GD + Intervention Image |
| QR | Endroid QR Code |
| Scanner | jsQR lokal + BarcodeDetector fallback |
| Session | CodeIgniter Database Session (`ci_sessions`) |
| Timezone | `Asia/Jakarta` |
| Asset runtime | Lokal, tanpa CDN |
| Routing | Explicit routes; Auto Routing dinonaktifkan |
| Deployment | Front controller `index.php` berada di root project untuk shared hosting |

View menggunakan nama spesifik seperti `penjual_index.php`, `penjual_form.php`, dan `laporan_rekap_kas.php`. Logic berat dipisahkan ke Service bila diperlukan.

## 4. Keamanan Dasar

Ketentuan keamanan aplikasi:

- semua route didefinisikan eksplisit dan Auto Routing nonaktif;
- aksi tulis menggunakan POST + CSRF;
- session login disimpan di database;
- password menggunakan `password_hash()` / `password_verify()`;
- session ID diregenerasi setelah login;
- login menggunakan throttling per IP;
- filter login memvalidasi akun aktif dan role terkini dari database;
- Secure Headers aktif global;
- DBDebug production dinonaktifkan;
- production wajib HTTPS dan secure cookie;
- `.htaccess` root memblokir source/configuration sensitif;
- `uploads/.htaccess` memblokir directory listing dan eksekusi script umum;
- Dompdf menonaktifkan remote resource dan PHP execution;
- export Excel menulis input pengguna sebagai string literal untuk mencegah formula injection.

## 5. Struktur Database Operasional

### 5.1 `golongan_penjual`

Field utama: `id_golongan`, `nama_golongan`, `nominal_iuran`, `deleted_at`, `created_at`, `updated_at`.

Golongan adalah master data soft delete. Golongan yang masih digunakan Penjual aktif tidak boleh diarsipkan.

### 5.2 `kategori_pengeluaran`

Field utama: `id_kategori_keluar`, `nama_kategori`, `deleted_at`, `created_at`, `updated_at`.

Kategori diisi Operator dan menggunakan soft delete.

### 5.3 `users`

Field utama: `id_user`, `nama`, `username`, `password`, `role`, `status_aktif`, `created_at`, `updated_at`.

`username` unik. Operator yang sedang digunakan login tidak boleh menonaktifkan atau mengubah akunnya sendiri menjadi Pimpinan.

### 5.4 `penjual`

Field utama:

- `id_penjual`;
- `nama_penjual`;
- `id_golongan`;
- `no_hp` nullable;
- `lokasi_lapak` nullable;
- `alamat` nullable, maksimal 255 karakter;
- `status_aktif`;
- `tanggal_daftar`;
- `kode_kartu` unik dan persisten;
- `kode_verifikasi` unik;
- `deleted_at`, `created_at`, `updated_at`.

Penjual menggunakan soft delete agar histori transaksi tetap tersimpan.

### 5.5 `setting`

Singleton setting `id_setting = 1` dengan field `nama_madrasah`, `alamat_madrasah`, `logo`, `background_kartu_depan`, `background_kartu_belakang`, dan `updated_at`.

### 5.6 `transaksi_iuran`

Field utama:

- `id_transaksi`;
- `id_penjual`;
- `id_golongan_snapshot` — ID Golongan saat transaksi dicatat;
- `nama_golongan_snapshot` — nama Golongan saat transaksi dicatat;
- `nominal_golongan_snapshot` — nominal default Golongan saat transaksi dicatat;
- `tanggal`;
- `nominal` — nominal Iuran yang benar-benar dibayar;
- `keterangan` nullable;
- `id_operator`;
- `created_at`.

**Aturan bisnis terkunci:** satu Penjual maksimal memiliki **satu transaksi Iuran pada satu tanggal**.

Perlindungan dilakukan berlapis:

1. halaman Input Iuran menandai Penjual yang sudah tercatat pada tanggal terpilih;
2. service server menolak request duplikat;
3. database menggunakan unique index `uniq_iuran_penjual_tanggal (id_penjual, tanggal)`.

Setiap transaksi baru menyimpan snapshot Golongan agar histori Laporan Iuran tidak berubah ketika Golongan Penjual, nama Golongan, atau nominal default Golongan diubah kemudian. Snapshot tidak mengubah nominal transaksi aktual.

Transaksi Iuran tidak memakai soft delete. Data salah dikoreksi melalui hard delete pada menu Koreksi Transaksi.

### 5.7 `transaksi_pengeluaran`

Field utama: `id_pengeluaran`, `tanggal`, `id_kategori_keluar`, `nominal`, `keterangan`, `bukti_nota`, `id_operator`, `created_at`.

### 5.8 `setoran_pimpinan`

Field utama: `id_setoran`, `tanggal_form`, `periode_awal`, `periode_akhir`, `nominal`, `keterangan`, `bukti_setoran`, `id_operator`, `created_at`.

`bukti_setoran` wajib untuk Setoran Resmi baru; record lama dapat memiliki nilai `NULL`.

### 5.9 `ci_sessions`

Session CodeIgniter disimpan melalui Database Session Handler.

### 5.10 Migration dan perubahan schema

Migration aplikasi saat ini:

- `100001` s.d. `100008` — tabel bisnis awal;
- `100009` — `ci_sessions`;
- `100010` — penambahan `alamat` pada `penjual`;
- `100011` — unique index satu Iuran per Penjual per tanggal;
- `100012` — snapshot Golongan pada `transaksi_iuran`.

Migration `100011` bersifat aman terhadap index yang sudah dibuat manual: bila index sudah ada, migration tidak membuat ulang. Sebelum membuat index, migration memeriksa duplikasi historis dan menghentikan proses bila masih ditemukan data ganda.

Migration `100012` bersifat idempotent terhadap kolom/index snapshot yang sudah dibuat manual. Data historis yang belum mempunyai snapshot di-*backfill* menggunakan kondisi Golongan Penjual saat migration/SQL dijalankan. Karena sistem sebelumnya tidak menyimpan histori perubahan Golongan, keadaan Golongan sebelum tanggal backfill tidak dapat direkonstruksi otomatis.

Kolom `bukti_setoran` merupakan perubahan schema operasional yang diterapkan manual melalui SQL/phpMyAdmin pada hosting existing:

```sql
ALTER TABLE `setoran_pimpinan`
ADD COLUMN `bukti_setoran` VARCHAR(255) NULL
AFTER `keterangan`;
```

Untuk unique Iuran, cek data lama lebih dulu:

```sql
SELECT `id_penjual`, `tanggal`, COUNT(*) AS `jumlah`
FROM `transaksi_iuran`
GROUP BY `id_penjual`, `tanggal`
HAVING COUNT(*) > 1;
```

Jika query tersebut menghasilkan **0 baris**, unique index dapat diterapkan manual:

```sql
ALTER TABLE `transaksi_iuran`
ADD UNIQUE KEY `uniq_iuran_penjual_tanggal` (`id_penjual`, `tanggal`);
```

Untuk snapshot Golongan, gunakan SQL lengkap pada `docs/SQL_100012_GOLONGAN_SNAPSHOT_IURAN.sql`. Inti perubahan schema:

```sql
ALTER TABLE `transaksi_iuran`
    ADD COLUMN IF NOT EXISTS `id_golongan_snapshot` INT(11) UNSIGNED NULL AFTER `id_penjual`,
    ADD COLUMN IF NOT EXISTS `nama_golongan_snapshot` VARCHAR(100) NULL AFTER `id_golongan_snapshot`,
    ADD COLUMN IF NOT EXISTS `nominal_golongan_snapshot` DECIMAL(12,2) NULL AFTER `nama_golongan_snapshot`;
```

SQL lengkap juga melakukan backfill data lama, membuat index `idx_iuran_golongan_snapshot`, dan menyediakan query verifikasi.

Jangan menjalankan seeder pada database operasional existing untuk menerapkan perubahan schema.

## 6. Master Penjual

Operator dapat menambah, melihat, mengedit, dan mengarsipkan Penjual. Form mencakup nama, golongan, nomor HP, lokasi/lapak, alamat, status, dan tanggal bergabung.

Daftar Penjual diurutkan berdasarkan nominal golongan terbesar lalu nama A–Z, mendukung DataTables Responsive, dan memiliki Export Excel Data Penjual.

Export Penjual mencakup nama, golongan, nominal default, alamat, nomor HP, lokasi/lapak, status, tanggal bergabung, dan kode kartu.

## 7. Input Iuran Harian

Input Iuran menggunakan form bulk seluruh Penjual aktif.

Aturan proses:

- tanggal aktif dapat dipilih Operator;
- perubahan tanggal memuat ulang status Iuran pada tanggal tersebut;
- Penjual diurutkan berdasarkan nominal golongan tertinggi lalu nama A–Z;
- Penjual yang belum tercatat memiliki switch `Bayar`;
- nominal diprefill dari nominal golongan dan boleh dioverride;
- Penjual yang sudah tercatat tampil berstatus **Tercatat** dan tidak dapat dipilih lagi;
- Penjual tidak dicentang tidak disimpan;
- minimal satu Penjual harus dipilih;
- nominal terpilih harus lebih dari nol;
- service server memeriksa duplikasi lagi sebelum insert;
- saat insert, ID/nama/nominal default Golongan disalin ke field snapshot transaksi;
- penyimpanan batch menggunakan database transaction;
- unique index database menjadi perlindungan terakhir terhadap request bersamaan;
- total Penjual terpilih dan nominal dihitung langsung di UI;
- layout dibuat compact untuk Chrome Android.

Jika transaksi lama salah, Operator harus menghapusnya melalui Koreksi Transaksi terlebih dahulu sebelum memasukkan ulang Penjual pada tanggal yang sama.

## 8. Form Iuran Mingguan

PDF kontrol mingguan menggunakan tanggal awal Sabtu dan memiliki kolom Sabtu, Minggu, Senin, Selasa, Rabu, Kamis, serta Total. Daftar Penjual mengikuti urutan Input Iuran.

## 9. Pengeluaran

Input Pengeluaran mencakup tanggal, kategori, nominal, keterangan opsional, dan foto bukti nota opsional.

Foto menerima JPG/JPEG/PNG maksimal 10 MB sebelum kompresi dan ditargetkan menjadi JPG di bawah 500 KB. Bila penyimpanan database gagal setelah file dibuat, file baru dibersihkan.

## 10. Setoran ke Pimpinan

Setoran menggunakan dua tahap terpisah.

### Tahap 1 — Cetak Form Setoran

Operator mengisi tanggal form, periode awal, periode akhir, dan nominal untuk menghasilkan PDF. Tahap ini **tidak membuat transaksi database** dan tidak mengurangi saldo.

### Tahap 2 — Input Setoran Resmi

Dilakukan setelah dana benar-benar diserahkan kepada pimpinan. Data meliputi tanggal form, periode, nominal, keterangan opsional, dan foto bukti wajib untuk record baru.

Edit Setoran mempertahankan bukti lama bila tidak ada upload baru. Penghapusan Setoran membersihkan file bukti hanya setelah delete database berhasil.

## 11. Koreksi Transaksi

Koreksi hanya untuk data yang benar-benar salah input dan menggunakan hard delete dengan konfirmasi.

Tersedia tab Iuran, Pengeluaran, dan Setoran dengan filter periode. Penghapusan Pengeluaran/Setoran juga membersihkan file bukti terkait setelah operasi database berhasil.

Koreksi Iuran menampilkan Golongan dari snapshot historis transaksi. Untuk aturan unique Iuran, Koreksi Transaksi adalah mekanisme resmi bila Penjual harus diinput ulang pada tanggal yang sama.

## 12. Dashboard

Dashboard menampilkan:

- Iuran bulan berjalan;
- Pengeluaran bulan berjalan;
- Setoran bulan berjalan;
- Saldo Kas keseluruhan;
- jumlah Penjual aktif;
- tren Iuran harian bulan berjalan;
- tren Iuran 6 bulan;
- tren Pengeluaran 6 bulan.

Saldo Kas:

```text
Total seluruh Iuran - Total seluruh Pengeluaran - Total seluruh Setoran Resmi
```

## 13. Laporan dan Export Excel

Tersedia Laporan Iuran, Pengeluaran, Setoran, dan Rekap Kas. Filter default tanggal 1 bulan berjalan sampai hari ini dan hanya menerima tanggal kalender valid.

Laporan Iuran menampilkan dan memfilter Golongan berdasarkan snapshot transaksi, dengan fallback ke Golongan Penjual saat ini hanya untuk record legacy yang belum mempunyai snapshot. Dengan demikian perubahan Golongan Penjual tidak mengubah klasifikasi transaksi baru yang sudah tercatat.

Rekap Kas menampilkan Saldo Awal, transaksi periode, dan Saldo Akhir kronologis. Iuran pada tanggal yang sama diagregasi menjadi satu baris `Total Iuran Harian (n transaksi)`.

Export Excel mengikuti filter aktif, menggunakan format tanggal `DD-MM-YYYY`, filename membawa periode, dan string input pengguna ditulis sebagai literal text.

## 14. Kartu Anggota Kantin

Canvas kartu **1011 × 638 px**, tanpa foto Penjual.

Sisi depan berisi Nama, Golongan, Lokasi/Lapak, No. HP, Tanggal Bergabung, Alamat, QR verifikasi, dan Kode Kartu. Sisi belakang statis dari background Setting.

`kode_kartu` persisten. Regenerate hanya mengganti `kode_verifikasi` sehingga QR lama tidak berlaku.

Download tersedia per Penjual dan bulk dalam format JPG/ZIP.

## 15. Verifikasi dan Scanner QR

Halaman publik QR hanya menampilkan Nama Penjual, Golongan, dan Status. Nomor HP, alamat, serta riwayat Iuran tidak ditampilkan.

Operator yang sudah login diarahkan ke Detail Penjual internal. Scanner memakai kamera browser, jsQR lokal, BarcodeDetector fallback, serta hanya menerima URL origin/path verifikasi aplikasi sendiri.

HTTPS wajib untuk kamera production.

## 16. Setting dan Branding

Operator dapat mengatur Nama Madrasah, Alamat Madrasah, Logo, Background Kartu Depan, dan Background Kartu Belakang.

Upload branding menerima JPG/JPEG/PNG. File lama baru dihapus setelah path baru berhasil disimpan ke database.

## 17. UI/UX

Prinsip UI:

- mobile-first;
- Sneat/Bootstrap 5;
- DataTables Responsive;
- SweetAlert2 untuk aksi penting;
- badge status konsisten;
- tombol aksi tulis tidak tampil untuk Pimpinan;
- sidebar desktop collapse/expand dengan state browser;
- menu mobile overlay;
- asset runtime lokal;
- Input/Koreksi Iuran compact di layar kecil.

## 18. Struktur File Penting

```text
app/
├── Config/
├── Controllers/
├── Filters/
├── Models/
├── Services/
├── Views/
└── Database/
    ├── Migrations/
    └── Seeds/
assets/
assets-app/qrcode-lib/
uploads/
├── branding/
├── bukti_nota/
└── bukti_setoran/
writable/
vendor/
docs/
index.php
.htaccess
composer.json
```

File upload dinamis harus dibackup bersama database.

## 19. Konvensi Pengembangan

- Controller PascalCase.
- Model `{Nama}Model.php`.
- View `{modul}_{aksi}.php`.
- Komentar aplikasi Bahasa Indonesia.
- Mutasi data menggunakan POST.
- Master menggunakan soft delete jika histori harus dipertahankan.
- Transaksi salah dikoreksi dengan hard delete melalui alur resmi.
- Perubahan schema hosting tanpa terminal harus memiliki SQL phpMyAdmin yang ekuivalen.
- Seeder hanya untuk fresh install.

## 20. Deployment Shared Hosting

Production minimal PHP 8.2+ dengan extension `intl`, `mbstring`, `mysqli`, `fileinfo`, `gd`, dan `zip`.

Ketentuan deployment:

- `CI_ENVIRONMENT = production`;
- `app.baseURL` HTTPS;
- secure cookie aktif;
- `writable/` serta folder upload writable;
- `.htaccess` root dan uploads ikut deployment;
- `vendor/` ikut bila Composer tidak tersedia di hosting;
- `.env` tidak pernah masuk repository;
- backup database + seluruh uploads sebelum update;
- jangan menjalankan seeder pada database existing;
- perubahan schema diterapkan sebelum source baru digunakan.

Untuk penerapan unique Iuran pada hosting tanpa terminal: jalankan query deteksi duplikasi terlebih dahulu. Hanya jika hasilnya 0 baris, jalankan `ALTER TABLE ... ADD UNIQUE KEY` sebagaimana Bab 5.10.

Untuk snapshot Golongan, jalankan `docs/SQL_100012_GOLONGAN_SNAPSHOT_IURAN.sql` melalui phpMyAdmin **sebelum** source terbaru yang membaca kolom snapshot diaktifkan.

## 21. Backup Operasional

Backup minimal mencakup dump database, `uploads/branding/`, `uploads/bukti_nota/`, `uploads/bukti_setoran/`, dan salinan `.env` yang disimpan aman di luar repository/web root.

## 22. Smoke Test Wajib

Setelah deployment, verifikasi:

- login Operator/Pimpinan;
- role Pimpinan read-only;
- akun Nonaktif kehilangan akses;
- CRUD/arsip master;
- Input Iuran bulk;
- ubah tanggal Input Iuran dan cek status **Tercatat**;
- coba submit Penjual yang sudah tercatat dan pastikan ditolak;
- Koreksi Iuran lalu pastikan Penjual dapat diinput ulang pada tanggal tersebut;
- cek satu transaksi Iuran lama, ubah Golongan Penjual, lalu pastikan transaksi lama tetap menampilkan Golongan snapshot di Laporan Iuran/Koreksi;
- pastikan transaksi Iuran baru menyimpan `id_golongan_snapshot`, `nama_golongan_snapshot`, dan `nominal_golongan_snapshot`;
- Pengeluaran + bukti;
- Form Iuran Mingguan;
- cetak dan simpan Setoran Resmi;
- Dashboard dan grafik;
- filter Laporan/Rekap Kas/export Excel;
- Kartu/QR/scanner;
- HTTPS dan proteksi source/configuration.

## 23. Perubahan yang Masih Membutuhkan Keputusan Bisnis

Aturan **satu Penjual maksimal satu Iuran per tanggal** dan **snapshot Golongan historis Iuran** sudah diputuskan dan menjadi baseline aplikasi.

Hal berikut masih membutuhkan keputusan eksplisit sebelum perubahan besar dilakukan:

- apakah transaksi dengan tanggal masa depan harus ditolak;
- apakah Setoran Resmi harus dibatasi agar tidak melebihi saldo kas;
- apakah bukti transaksi harus dipindahkan ke storage privat;
- apakah transaksi keuangan memerlukan audit trail perubahan/hapus.
