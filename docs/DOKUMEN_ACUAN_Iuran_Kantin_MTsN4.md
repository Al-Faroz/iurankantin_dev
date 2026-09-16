# Dokumen Acuan Aplikasi Iuran Kantin MTsN 4 Jombang

Dokumen ini adalah **baseline utama** aplikasi Iuran Kantin MTsN 4 Jombang. Isinya menggambarkan tujuan, aturan bisnis, arsitektur, struktur data, fitur, keamanan, UI/UX, dan cara operasional aplikasi dalam kondisi saat ini. Dokumen ini bukan catatan revisi dan bukan changelog.

## 1. Tujuan dan Ruang Lingkup

Aplikasi digunakan untuk pencatatan **iuran penjual/pedagang kantin kepada madrasah**, pengeluaran operasional kantin, setoran kas kepada pimpinan, pelaporan kas, serta pengelolaan kartu anggota kantin.

Aplikasi bukan aplikasi kasir/POS, bukan aplikasi jual-beli kantin, dan bukan aplikasi iuran siswa. Sistem juga tidak mengelola piutang atau tunggakan penjual; transaksi iuran hanya dicatat ketika pembayaran benar-benar diterima.

Tujuan utama aplikasi:

- mencatat pemasukan iuran harian penjual kantin;
- mencatat pengeluaran operasional kantin;
- mencatat setoran kas yang sudah benar-benar diserahkan kepada pimpinan;
- menghitung saldo kas berjalan;
- menyediakan laporan dan export Excel;
- menyediakan form kontrol manual mingguan;
- menyediakan kartu anggota kantin dan verifikasi QR.

## 2. Role dan Hak Akses

Aplikasi menggunakan dua role tetap.

| Role | Hak akses |
| --- | --- |
| **Operator** | Mengelola master data, user, setting, input/koreksi transaksi, setoran, kartu anggota, scanner QR, dashboard, laporan, PDF, dan export Excel. |
| **Pimpinan** | Read-only untuk Dashboard dan seluruh Laporan. Tidak memiliki akses ke route input/edit/hapus/master/setting/user. |

Hak akses tulis tidak hanya disembunyikan di UI, tetapi dilindungi route dengan filter Operator. Status dan role user aktif divalidasi kembali terhadap database pada request terproteksi agar perubahan role/nonaktif berlaku tanpa menunggu session kedaluwarsa.

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
| Scanner | jsQR lokal + BarcodeDetector fallback bila tersedia |
| Session | CodeIgniter Database Session (`ci_sessions`) |
| Timezone aplikasi | `Asia/Jakarta` |
| Asset runtime | Lokal, tanpa CDN |
| Routing | Explicit routes; Auto Routing dinonaktifkan |
| Deployment | Front controller `index.php` berada di root project untuk shared hosting |

Struktur controller, model, service, dan view mengikuti MVC standar CodeIgniter 4. View menggunakan penamaan spesifik seperti `penjual_index.php`, `penjual_form.php`, `laporan_rekap_kas.php`; tidak menggunakan view generik `index.php`.

## 4. Keamanan Dasar

Ketentuan keamanan aplikasi:

- seluruh route aplikasi didefinisikan secara eksplisit;
- Auto Routing dinonaktifkan;
- seluruh aksi tulis menggunakan HTTP POST dan dilindungi CSRF;
- session login disimpan di database;
- password disimpan menggunakan `password_hash()` dan diverifikasi dengan `password_verify()`;
- session ID diregenerasi setelah login berhasil;
- login dibatasi dengan throttling per IP;
- filter login memvalidasi kembali akun aktif ke database;
- route Operator memvalidasi role terkini dari database;
- Secure Headers CodeIgniter aktif secara global;
- detail error database tidak ditampilkan pada environment production;
- production wajib HTTPS, secure cookie, HttpOnly cookie, dan SameSite Lax;
- `.htaccess` root memblokir akses langsung ke `app/`, `vendor/`, `writable/`, `tests/`, `docs/`, `.env`, Composer files, dan file sensitif lain;
- `uploads/.htaccess` menonaktifkan directory listing dan memblokir eksekusi ekstensi script umum;
- PDF menonaktifkan remote resource dan eksekusi PHP;
- export Excel memperlakukan input pengguna sebagai teks literal untuk mencegah formula injection.

## 5. Struktur Database Operasional

### 5.1 `golongan_penjual`

Master golongan penjual dan nominal iuran default.

Field utama:

- `id_golongan` — primary key;
- `nama_golongan`;
- `nominal_iuran` — nominal default untuk Input Iuran;
- `deleted_at`, `created_at`, `updated_at`.

Master menggunakan soft delete. Golongan yang masih digunakan penjual aktif tidak boleh diarsipkan.

### 5.2 `kategori_pengeluaran`

Master kategori pengeluaran.

Field utama:

- `id_kategori_keluar` — primary key;
- `nama_kategori`;
- `deleted_at`, `created_at`, `updated_at`.

Kategori diisi Operator dan menggunakan soft delete.

### 5.3 `users`

Akun aplikasi.

Field utama:

- `id_user` — primary key;
- `nama`;
- `username` — unik;
- `password` — hash password;
- `role` — `Operator` atau `Pimpinan`;
- `status_aktif` — `Aktif` atau `Nonaktif`;
- `created_at`, `updated_at`.

Operator yang sedang dipakai login tidak boleh menonaktifkan atau menurunkan role akunnya sendiri melalui form User.

### 5.4 `penjual`

Master penjual/pedagang kantin.

Field utama:

- `id_penjual` — primary key;
- `nama_penjual`;
- `id_golongan` — FK ke `golongan_penjual`;
- `no_hp` — nullable;
- `lokasi_lapak` — nullable;
- `alamat` — nullable, maksimal 255 karakter;
- `status_aktif` — `Aktif` atau `Nonaktif`;
- `tanggal_daftar`;
- `kode_kartu` — kode kartu persisten dan unik;
- `kode_verifikasi` — token verifikasi QR unik;
- `deleted_at`, `created_at`, `updated_at`.

Penjual menggunakan soft delete agar histori transaksi tetap tersimpan.

### 5.5 `setting`

Singleton setting aplikasi, menggunakan baris `id_setting = 1`.

Field utama:

- `nama_madrasah`;
- `alamat_madrasah`;
- `logo`;
- `background_kartu_depan`;
- `background_kartu_belakang`;
- `updated_at`.

### 5.6 `transaksi_iuran`

Pemasukan iuran penjual.

Field utama:

- `id_transaksi` — primary key;
- `id_penjual` — FK ke `penjual`;
- `tanggal`;
- `nominal`;
- `keterangan` — nullable;
- `id_operator` — FK ke `users`;
- `created_at`.

Transaksi tidak menggunakan soft delete. Koreksi data yang salah dilakukan dengan hard delete melalui menu Koreksi Transaksi.

### 5.7 `transaksi_pengeluaran`

Pengeluaran operasional kas kantin.

Field utama:

- `id_pengeluaran` — primary key;
- `tanggal`;
- `id_kategori_keluar` — FK ke kategori;
- `nominal`;
- `keterangan` — nullable;
- `bukti_nota` — path foto bukti, nullable;
- `id_operator`;
- `created_at`.

### 5.8 `setoran_pimpinan`

Setoran kas yang sudah benar-benar diserahkan kepada pimpinan.

Field utama:

- `id_setoran` — primary key;
- `tanggal_form`;
- `periode_awal`;
- `periode_akhir`;
- `nominal`;
- `keterangan` — nullable;
- `bukti_setoran` — path foto bukti setoran;
- `id_operator`;
- `created_at`.

`bukti_setoran` wajib untuk input Setoran Resmi baru. Record lama yang dibuat sebelum fitur bukti tersedia dapat memiliki nilai `NULL`.

### 5.9 `ci_sessions`

Tabel session CodeIgniter 4. Session aplikasi tidak menggunakan file session sebagai penyimpanan utama.

### 5.10 Catatan pengelolaan schema

Migration aplikasi saat ini mencakup migration bisnis awal, tabel session, dan penambahan `alamat` pada `penjual` sampai migration `100010`.

Kolom `bukti_setoran` pada `setoran_pimpinan` adalah perubahan schema operasional yang diterapkan **manual melalui SQL/phpMyAdmin**, sesuai metode deployment hosting yang tidak memiliki terminal. SQL yang diperlukan pada database yang belum memiliki kolom tersebut:

```sql
ALTER TABLE `setoran_pimpinan`
ADD COLUMN `bukti_setoran` VARCHAR(255) NULL
AFTER `keterangan`;
```

Jangan membuat atau menjalankan seeder pada database operasional existing hanya untuk menerapkan perubahan schema.

## 6. Master Penjual

Operator dapat menambah, melihat, mengedit, dan mengarsipkan Penjual. Form mencakup nama, golongan, nomor HP, lokasi/lapak, alamat, status, dan tanggal bergabung.

Daftar Penjual:

- diurutkan berdasarkan nominal golongan terbesar, lalu nama A–Z;
- menampilkan alamat secara ringkas bersama identitas penjual;
- mendukung DataTables Responsive;
- memiliki Export Excel Data Penjual.

Export Penjual berisi nama, golongan, nominal default, alamat, nomor HP, lokasi/lapak, status, tanggal bergabung, dan kode kartu. Karena merupakan master data dan bukan laporan periode, filename export Penjual tidak memakai rentang tanggal.

## 7. Input Iuran Harian

Input Iuran menggunakan satu form bulk untuk seluruh Penjual aktif.

Aturan UI dan proses:

- Penjual diurutkan berdasarkan nominal golongan tertinggi lalu nama A–Z;
- setiap Penjual memiliki switch `Bayar`;
- nominal diprefill dari `nominal_iuran` golongan;
- Operator boleh mengubah nominal transaksi yang dipilih;
- Penjual yang tidak dicentang tidak disimpan;
- minimal satu Penjual harus dipilih;
- nominal terpilih harus lebih dari nol;
- penyimpanan batch dilakukan dalam database transaction;
- total Penjual terpilih dan total nominal dihitung langsung di UI;
- layout mobile dibuat compact agar nyaman dipakai di Chrome Android.

## 8. Form Iuran Mingguan

Operator dapat mencetak PDF form kontrol mingguan dengan tanggal awal wajib hari Sabtu.

Form berisi enam hari:

- Sabtu;
- Minggu;
- Senin;
- Selasa;
- Rabu;
- Kamis;

Terdapat kolom Total per Penjual. Daftar Penjual mengikuti urutan Input Iuran: nominal golongan tertinggi kemudian nama A–Z. Header menggunakan data madrasah dari Setting.

## 9. Pengeluaran

Input Pengeluaran mencakup:

- tanggal;
- kategori;
- nominal;
- keterangan opsional;
- foto bukti nota opsional.

Foto bukti menerima JPG/JPEG/PNG maksimal 10 MB sebelum kompresi. Server menurunkan resolusi maksimal dan mengonversi hasil menjadi JPG dengan target ukuran di bawah 500 KB. DataTable Pengeluaran menampilkan link untuk melihat bukti yang tersedia.

Jika penyimpanan transaksi ke database gagal setelah file berhasil dibuat, file baru dibersihkan agar tidak menjadi file yatim.

## 10. Setoran ke Pimpinan

Setoran menggunakan dua tahap yang sengaja dipisahkan.

### Tahap 1 — Cetak Form Setoran

Operator mengisi tanggal form, periode awal, periode akhir, dan nominal, lalu menghasilkan PDF dua salinan pada satu A4. Tahap ini **tidak membuat transaksi database** dan belum mengurangi saldo kas.

### Tahap 2 — Input Setoran Resmi

Dilakukan setelah dana benar-benar diserahkan kepada pimpinan. Operator mengisi:

- tanggal form;
- periode awal;
- periode akhir;
- nominal;
- keterangan opsional;
- foto bukti setoran wajib untuk record baru.

Foto menerima JPG/JPEG/PNG maksimal 10 MB sebelum kompresi, dikonversi menjadi JPG, dan ditargetkan di bawah 500 KB.

Pada Edit Setoran:

- bukti lama tetap digunakan bila tidak upload foto baru;
- upload foto baru mengganti bukti lama setelah update database berhasil.

Pada penghapusan permanen Setoran, file bukti ikut dibersihkan setelah transaksi database benar-benar berhasil dihapus.

Bukti foto Setoran hanya digunakan pada form Setoran dan DataTable Setoran. Bukti tersebut tidak ditambahkan ke Laporan Setoran maupun export Excel Laporan Setoran.

## 11. Koreksi Transaksi

Koreksi hanya digunakan untuk transaksi yang benar-benar salah input dan bersifat hard delete.

Menu memiliki filter periode dan tiga tab:

- Iuran;
- Pengeluaran;
- Setoran.

Koreksi Iuran menggunakan tampilan compact mirip Input Iuran. Data dikelompokkan per tanggal, diurutkan nominal golongan terbesar lalu nama A–Z, dan setiap transaksi memiliki aksi hapus eksplisit dengan konfirmasi SweetAlert2.

Penghapusan Pengeluaran membersihkan bukti nota terkait. Penghapusan Setoran membersihkan bukti setoran terkait. File baru dibersihkan hanya setelah operasi database berhasil agar database dan filesystem tetap konsisten.

## 12. Dashboard

Urutan Dashboard adalah:

1. Ringkasan Bulan Berjalan;
2. Aksi Cepat Operator;
3. Grafik Tren.

Ringkasan Bulan Berjalan menampilkan:

- Iuran Masuk bulan berjalan;
- Pengeluaran bulan berjalan;
- Setoran Pimpinan bulan berjalan;
- Saldo Kas berjalan keseluruhan.

Saldo Kas menggunakan rumus:

```text
Total seluruh Iuran - Total seluruh Pengeluaran - Total seluruh Setoran Resmi
```

Pada mobile, empat kartu ringkasan menggunakan grid 2×2 dan padding compact agar Aksi Cepat lebih cepat terlihat.

Aksi Cepat Operator menyediakan:

- Input Iuran Hari Ini;
- Cetak Form Mingguan.

Dashboard memiliki tiga grafik:

- **Tren Arus Kas Masuk Bulan Berjalan** — total iuran per hari dari tanggal 1 sampai hari ini;
- **Tren Arus Kas Masuk 6 Bulan Terakhir** — total iuran per bulan;
- **Tren Arus Pengeluaran 6 Bulan Terakhir** — total pengeluaran per bulan.

## 13. Laporan dan Export Excel

Laporan tersedia untuk Operator dan Pimpinan:

- Laporan Iuran;
- Laporan Pengeluaran;
- Laporan Setoran;
- Rekap Kas.

Filter tanggal default adalah tanggal 1 bulan berjalan sampai hari ini. Filter menerima tanggal kalender yang valid dan menukar tanggal awal/akhir bila urutannya terbalik.

### Rekap Kas

Rekap Kas menampilkan Saldo Awal, transaksi periode, dan Saldo Akhir secara kronologis.

Khusus Iuran, Rekap Kas tidak menampilkan satu baris per nama Penjual. Semua Iuran pada tanggal yang sama digabung menjadi satu baris `Total Iuran Harian (n transaksi)`. Pengeluaran dan Setoran tetap tampil per transaksi.

Saldo berjalan dihitung setelah setiap entry kronologis.

### Export Excel

Format tanggal export menggunakan `DD-MM-YYYY` secara konsisten. Filename seluruh laporan berbasis periode membawa tanggal filter, misalnya:

```text
laporan-iuran_01-09-2026_sd_16-09-2026.xlsx
laporan-pengeluaran_01-09-2026_sd_16-09-2026.xlsx
laporan-setoran_01-09-2026_sd_16-09-2026.xlsx
rekap-kas_01-09-2026_sd_16-09-2026.xlsx
```

Data teks diekspor sebagai literal string agar tidak ditafsirkan Excel sebagai formula dan agar data seperti nomor HP berawalan nol tidak berubah.

## 14. Kartu Anggota Kantin

Kartu tidak menggunakan foto Penjual. Ukuran canvas adalah **1011 × 638 px**.

Sisi depan berisi:

- Nama Penjual;
- Golongan;
- Lokasi/Lapak;
- No. HP;
- Tanggal Bergabung;
- Alamat;
- QR verifikasi;
- Kode Kartu.

Sisi belakang bersifat statis dan menggunakan gambar background dari Setting.

`kode_kartu` dibuat sekali dan dipertahankan. `kode_verifikasi` berupa token acak 64 karakter hex dan boleh diregenerate. Regenerate token membuat QR lama tidak valid tanpa mengganti kode kartu.

Download tersedia:

- JPG sisi depan per Penjual;
- ZIP depan + belakang per Penjual;
- ZIP seluruh kartu depan Penjual aktif;
- ZIP seluruh kartu lengkap Penjual aktif.

Background depan dan belakang diupload melalui Setting.

## 15. Verifikasi dan Scanner QR

QR menyimpan URL verifikasi aplikasi.

Jika QR dibuka oleh pengunjung umum, halaman publik hanya menampilkan:

- Nama Penjual;
- Golongan;
- Status.

Nomor HP, alamat, riwayat Iuran, dan data internal lain tidak ditampilkan pada halaman publik.

Jika QR dibuka ketika session Operator aktif, aplikasi mengarahkan ke Detail Penjual internal yang menampilkan data Penjual dan maksimal 20 transaksi Iuran terbaru.

Menu Scan Kartu menggunakan kamera browser melalui `getUserMedia`, decoder jsQR lokal, dan BarcodeDetector sebagai fallback bila tersedia. Scanner juga dapat membaca gambar QR dari file. URL hasil scan harus berasal dari origin aplikasi yang sama dan path verifikasi aplikasi.

HTTPS wajib untuk kamera pada production.

## 16. Setting dan Branding

Operator dapat mengatur:

- Nama Madrasah;
- Alamat Madrasah;
- Logo;
- Background Kartu Depan;
- Background Kartu Belakang.

Upload branding menerima JPG/JPEG/PNG dengan batas ukuran file. File lama baru dihapus setelah path file baru berhasil disimpan ke database.

Logo digunakan untuk favicon dinamis dan dokumen PDF sesuai kebutuhan aplikasi.

## 17. UI/UX

Prinsip UI aplikasi:

- mobile-first, terutama untuk penggunaan Chrome Android;
- komponen mengikuti Sneat/Bootstrap 5;
- DataTables Responsive untuk tabel utama;
- SweetAlert2 untuk konfirmasi tindakan penting;
- badge konsisten untuk status/kategori;
- input uang memakai label Rupiah yang jelas;
- tombol aksi tulis tidak ditampilkan untuk Pimpinan;
- sidebar desktop dapat collapse/expand dan state disimpan di browser;
- menu mobile menggunakan overlay;
- asset aplikasi dan library runtime disajikan lokal;
- halaman Input Iuran dan Koreksi Iuran dibuat compact khusus layar kecil.

## 18. Struktur File Penting

```text
app/
├── Config/
├── Controllers/
│   ├── Auth.php
│   ├── Dashboard.php
│   ├── GolonganPenjual.php
│   ├── Iuran.php
│   ├── KartuAnggota.php
│   ├── KategoriPengeluaran.php
│   ├── KoreksiTransaksi.php
│   ├── Laporan.php
│   ├── Pengeluaran.php
│   ├── Penjual.php
│   ├── Setoran.php
│   ├── Setting.php
│   ├── User.php
│   └── Verifikasi.php
├── Filters/
│   ├── AuthFilter.php
│   └── OperatorOnlyFilter.php
├── Models/
├── Services/
│   ├── AuthSessionService.php
│   ├── BrandingUploadService.php
│   ├── BuktiNotaService.php
│   ├── BuktiSetoranService.php
│   ├── IuranService.php
│   ├── KartuAnggotaService.php
│   ├── LaporanService.php
│   ├── PdfService.php
│   └── QrService.php
├── Views/
└── Database/
    ├── Migrations/
    └── Seeds/

assets/                       # Sneat dan library lokal
assets-app/qrcode-lib/        # scanner dan jsQR lokal
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

File upload dinamis tidak menjadi source code dan harus dibackup bersama database.

## 19. Konvensi Pengembangan

- Controller menggunakan PascalCase.
- Model menggunakan nama `{Nama}Model.php`.
- Logic berat ditempatkan di Service bila layak dipisah.
- View menggunakan nama spesifik `{modul}_{aksi}.php`.
- Komentar aplikasi menggunakan Bahasa Indonesia.
- Aksi mutasi data menggunakan POST.
- Master data menggunakan soft delete bila histori transaksi harus tetap utuh.
- Transaksi salah menggunakan hard delete melalui alur koreksi dan konfirmasi.
- Perubahan schema database hosting yang tidak dapat memakai terminal harus disediakan sebagai SQL phpMyAdmin, bukan mengandalkan perintah terminal.
- Seeder hanya untuk fresh install dan tidak boleh dijalankan pada database operasional existing.

## 20. Deployment Shared Hosting

Production minimal membutuhkan PHP 8.2+ dengan extension:

```text
intl
mbstring
mysqli
fileinfo
gd
zip
```

Ketentuan deployment:

- `CI_ENVIRONMENT = production`;
- `app.baseURL` memakai URL HTTPS production;
- `app.forceGlobalSecureRequests = true` bila konfigurasi proxy/hosting mendukung dengan benar;
- `cookie.secure = true`;
- `writable/`, `uploads/branding/`, `uploads/bukti_nota/`, dan `uploads/bukti_setoran/` harus writable oleh PHP;
- `.htaccess` root dan `uploads/.htaccess` wajib ikut ZIP deployment;
- `vendor/` ikut ZIP bila hosting tidak menjalankan Composer;
- jangan commit atau membagikan `.env` production;
- backup database dan seluruh folder upload sebelum update;
- jangan menjalankan seeder pada database existing;
- perubahan schema manual harus dijalankan di phpMyAdmin sebelum source baru yang membutuhkannya dipakai.

## 21. Backup Operasional

Backup produksi minimal mencakup:

- dump database MySQL/MariaDB;
- `uploads/branding/`;
- `uploads/bukti_nota/`;
- `uploads/bukti_setoran/`;
- salinan konfigurasi `.env` disimpan aman di luar web root/repository.

Source aplikasi dapat dipulihkan dari Git, tetapi database dan file upload adalah data operasional yang tidak dapat dibuat ulang dari repository.

## 22. Smoke Test Wajib

Setelah update source atau deployment, verifikasi minimal:

- login Operator dan Pimpinan;
- role Pimpinan benar-benar read-only;
- akun Nonaktif kehilangan akses pada request berikutnya;
- sidebar desktop/mobile;
- CRUD dan arsip master;
- Input Iuran bulk;
- Koreksi Iuran;
- Input Pengeluaran dan kompres bukti nota;
- Cetak Form Iuran Mingguan;
- Cetak Form Setoran tanpa insert database;
- Input/Edit/Delete Setoran Resmi dan bukti foto;
- Dashboard card dan tiga grafik;
- seluruh filter Laporan;
- Rekap Kas dan saldo berjalan;
- export Excel dan filename periode;
- upload logo/background;
- generate/download kartu;
- verifikasi QR publik;
- scan QR sebagai Operator;
- HTTPS dan izin kamera pada perangkat Android;
- akses langsung ke source/configuration menghasilkan 403/404.

## 23. Batas Perubahan yang Membutuhkan Keputusan Bisnis

Beberapa aturan tidak boleh diubah otomatis hanya karena kebutuhan teknis. Perubahan berikut harus diputuskan secara eksplisit sebelum implementasi:

- apakah satu Penjual hanya boleh memiliki satu transaksi Iuran pada tanggal yang sama;
- apakah transaksi dengan tanggal masa depan harus ditolak;
- apakah Setoran Resmi harus dibatasi agar tidak melebihi saldo kas;
- apakah perubahan Golongan Penjual harus mempertahankan snapshot golongan pada histori Iuran;
- apakah bukti transaksi harus dipindah dari folder publik ke storage privat dan disajikan melalui route terautentikasi;
- apakah perubahan transaksi keuangan perlu audit trail selain data Operator pencatat.

Selama belum ada keputusan baru, aplikasi mengikuti perilaku operasional yang dijelaskan di dokumen ini.
