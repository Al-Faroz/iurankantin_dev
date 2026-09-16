# Dokumen Acuan Aplikasi Iuran Kantin MTsN 4 Jombang

Dokumen ini adalah **baseline utama** Aplikasi Iuran Kantin MTsN 4 Jombang dalam kondisi operasional saat ini. Dokumen ini bukan changelog dan bukan dokumen revisi bertingkat. Jika terjadi perbedaan antara implementasi lama dan dokumen ini, pengembangan berikutnya mengikuti baseline ini.

## 1. Tujuan Aplikasi

Aplikasi digunakan untuk pencatatan **iuran penjual/pedagang kantin kepada madrasah**, pengeluaran operasional kantin, setoran kas kepada pimpinan, pelaporan kas, dan pengelolaan Kartu Anggota Kantin.

Aplikasi bukan POS/kasir, bukan aplikasi jual-beli, dan bukan aplikasi iuran siswa.

Fungsi utama:

- mencatat Iuran Penjual;
- mencatat Pengeluaran;
- mencatat Setoran Resmi ke Pimpinan;
- menghitung Saldo Kas berjalan;
- menyediakan Dashboard dan grafik;
- menyediakan Laporan dan export Excel;
- menyediakan PDF kontrol mingguan dan form Setoran;
- menyediakan Kartu Anggota, QR publik, dan scanner;
- menyediakan jejak audit transaksi secara sederhana melalui file log.

## 2. Role dan Hak Akses

### Operator

Operator memiliki akses penuh untuk:

- Dashboard;
- master Golongan, Kategori Pengeluaran, Penjual, User, dan Setting;
- Input Iuran;
- Pengeluaran;
- Setoran;
- Koreksi Transaksi;
- Kartu Anggota dan scanner;
- seluruh Laporan, PDF, dan export Excel.

### Pimpinan

Pimpinan bersifat **read-only** untuk:

- Dashboard;
- Laporan Iuran;
- Laporan Pengeluaran;
- Laporan Setoran;
- Rekap Kas;
- export Excel laporan.

Route mutasi data dilindungi filter Operator. Status aktif dan role user divalidasi kembali terhadap database pada request terproteksi agar perubahan akun berlaku tanpa menunggu session kedaluwarsa.

## 3. Stack Teknis

| Komponen | Implementasi |
| --- | --- |
| Backend | PHP 8.2+ |
| Framework | CodeIgniter 4 |
| Database | MySQL/MariaDB, `utf8mb4` |
| UI | Sneat Free + Bootstrap 5 |
| Tabel | DataTables + Responsive |
| Alert/konfirmasi | SweetAlert2 |
| Grafik | ApexCharts |
| PDF | Dompdf |
| Excel | PhpSpreadsheet |
| Gambar | GD + Intervention Image |
| QR | Endroid QR Code |
| Scanner | jsQR lokal + BarcodeDetector fallback |
| Session | Database Session Handler |
| Timezone | `Asia/Jakarta` |
| Asset runtime | Lokal, tanpa CDN |
| Routing | Explicit route, Auto Routing nonaktif |

Front controller `index.php` berada di root project untuk deployment shared hosting.

## 4. Prinsip Keamanan

Baseline keamanan aplikasi:

- mutasi data menggunakan POST + CSRF;
- Auto Routing nonaktif;
- password memakai hashing standar PHP;
- login menggunakan throttling;
- session login disimpan di database;
- session ID diregenerasi setelah login;
- role/status user divalidasi ulang ke database;
- Secure Headers aktif global;
- DBDebug production nonaktif;
- production wajib HTTPS;
- source/configuration sensitif diblokir `.htaccess`;
- Dompdf tidak mengaktifkan remote resource dan PHP execution;
- export Excel menulis input pengguna sebagai literal text untuk mencegah formula injection;
- bukti transaksi hanya dapat dibuka melalui route Operator;
- bukti baru disimpan di `writable/uploads/`, bukan document root;
- file bukti legacy di `uploads/` tetap kompatibel tetapi HTTP direct diblokir;
- upload branding dibatasi ukuran, dimensi, dan total piksel;
- dependency terkunci diaudit oleh `composer audit --locked` di CI.

## 5. Struktur Database

### 5.1 `golongan_penjual`

Field utama:

- `id_golongan`;
- `nama_golongan`;
- `nominal_iuran`;
- `deleted_at`;
- `created_at`;
- `updated_at`.

Golongan memakai soft delete.

### 5.2 `kategori_pengeluaran`

Field utama:

- `id_kategori_keluar`;
- `nama_kategori`;
- `deleted_at`;
- `created_at`;
- `updated_at`.

Kategori memakai soft delete.

### 5.3 `users`

Field utama:

- `id_user`;
- `nama`;
- `username` unik;
- `password` hash;
- `role`;
- `status_aktif`;
- timestamps.

### 5.4 `penjual`

Field utama:

- `id_penjual`;
- `nama_penjual`;
- `id_golongan`;
- `no_hp` nullable;
- `lokasi_lapak` nullable;
- `alamat` nullable;
- `status_aktif`;
- `tanggal_daftar`;
- `kode_kartu` unik/persisten;
- `kode_verifikasi` unik;
- soft delete dan timestamps.

### 5.5 `setting`

Singleton `id_setting = 1` dengan:

- `nama_madrasah`;
- `alamat_madrasah`;
- `logo`;
- `background_kartu_depan`;
- `background_kartu_belakang`;
- `updated_at`.

### 5.6 `transaksi_iuran`

Field utama:

- `id_transaksi`;
- `id_penjual`;
- `id_golongan_snapshot`;
- `nama_golongan_snapshot`;
- `nominal_golongan_snapshot`;
- `tanggal`;
- `nominal` aktual yang dibayar;
- `keterangan` nullable;
- `id_operator`;
- `created_at`.

Aturan database:

```text
UNIQUE (id_penjual, tanggal)
```

Satu Penjual maksimal satu transaksi Iuran pada tanggal yang sama.

### 5.7 `transaksi_pengeluaran`

Field utama:

- `id_pengeluaran`;
- `tanggal`;
- `id_kategori_keluar`;
- `nominal`;
- `keterangan`;
- `bukti_nota`;
- `id_operator`;
- `created_at`.

### 5.8 `setoran_pimpinan`

Field utama:

- `id_setoran`;
- `tanggal_form`;
- `periode_awal`;
- `periode_akhir`;
- `nominal`;
- `keterangan`;
- `bukti_setoran`;
- `id_operator`;
- `created_at`.

### 5.9 `ci_sessions`

Dipakai oleh Database Session Handler CodeIgniter.

## 6. Migration Operasional

Migration baseline:

```text
100001 CreateGolonganPenjualTable
100002 CreateKategoriPengeluaranTable
100003 CreateUsersTable
100004 CreatePenjualTable
100005 CreateSettingTable
100006 CreateTransaksiIuranTable
100007 CreateTransaksiPengeluaranTable
100008 CreateSetoranPimpinanTable
100009 CreateCiSessionsTable
100010 AddAlamatToPenjualTable
100011 AddUniqueIuranPerPenjualTanggal
100012 AddGolonganSnapshotToTransaksiIuran
100013 AddBuktiSetoranToSetoranPimpinan
```

Untuk hosting existing tanpa terminal, perubahan schema dapat diterapkan melalui SQL phpMyAdmin yang ekuivalen. Jangan reset database dan jangan menjalankan seeder pada database operasional existing.

## 7. Aturan Tanggal Transaksi

**Tanggal transaksi masa depan tidak diperbolehkan.**

Aturan ini berlaku pada:

- tanggal Input Iuran;
- tanggal Pengeluaran;
- tanggal Form Setoran;
- tanggal Setoran Resmi.

UI memberi `max = hari ini` dan server tetap melakukan validasi sehingga manipulasi request tidak dapat menyimpan tanggal transaksi masa depan.

Periode awal/akhir Setoran tetap divalidasi agar periode awal tidak melewati periode akhir.

## 8. Master Penjual

Operator dapat:

- tambah Penjual;
- lihat detail;
- edit;
- arsipkan;
- export Excel.

Data meliputi Nama, Golongan, HP, Lokasi/Lapak, Alamat, Status, dan Tanggal Bergabung.

Daftar Penjual diurutkan menurut nominal Golongan terbesar lalu Nama A–Z.

## 9. Input Iuran

Input Iuran menggunakan form bulk seluruh Penjual aktif.

Aturan:

- tanggal dapat dipilih sampai hari ini;
- Penjual yang sudah memiliki transaksi pada tanggal tersebut tampil **Tercatat** dan dikunci;
- nominal default berasal dari Golongan dan dapat dioverride;
- Penjual yang tidak dicentang tidak disimpan;
- minimal satu transaksi harus dipilih;
- nominal harus > 0;
- server memeriksa duplikasi sebelum insert;
- database unique index menjadi perlindungan terakhir;
- seluruh transaksi dalam satu submit disimpan menggunakan database transaction;
- Golongan saat transaksi dicatat disalin ke field snapshot;
- transaksi yang salah diperbaiki melalui Koreksi Transaksi, bukan edit langsung.

## 10. Snapshot Golongan Iuran

Setiap transaksi Iuran baru menyimpan:

- ID Golongan saat transaksi;
- Nama Golongan saat transaksi;
- Nominal default Golongan saat transaksi.

Tujuannya agar histori tidak berubah ketika Penjual pindah Golongan atau master Golongan diubah kemudian.

Nominal transaksi aktual tetap disimpan terpisah dan tidak diganti oleh snapshot nominal default.

## 11. Form Iuran Mingguan

PDF kontrol manual menggunakan periode Sabtu sampai Kamis dengan kolom:

- Sabtu;
- Minggu;
- Senin;
- Selasa;
- Rabu;
- Kamis;
- Total.

Form tidak membuat transaksi database.

## 12. Pengeluaran

Input Pengeluaran meliputi:

- tanggal sampai hari ini;
- kategori;
- nominal;
- keterangan opsional;
- bukti nota opsional.

Bukti menerima JPG/JPEG/PNG maksimal 10 MB sebelum kompresi dan ditargetkan menjadi JPG < 500 KB.

Upload baru disimpan di:

```text
writable/uploads/bukti_nota/
```

Tombol **Lihat** memakai route Operator terautentikasi.

## 13. Setoran ke Pimpinan

Setoran menggunakan dua tahap.

### Tahap 1 — Cetak Form Setoran

Operator mengisi:

- tanggal form sampai hari ini;
- periode awal;
- periode akhir;
- nominal.

Tahap ini hanya menghasilkan PDF dan **tidak mengurangi saldo**.

### Tahap 2 — Input Setoran Resmi

Dilakukan setelah dana benar-benar diserahkan kepada pimpinan.

Data:

- tanggal setoran sampai hari ini;
- periode;
- nominal;
- keterangan opsional;
- bukti foto wajib untuk record baru.

Bukti baru disimpan di:

```text
writable/uploads/bukti_setoran/
```

Edit tidak mewajibkan upload ulang jika bukti lama masih ada.

## 14. Kebijakan Setoran Melebihi Saldo

Setoran Resmi **boleh melebihi saldo kas**, tetapi aplikasi wajib memberi warning sebelum penyimpanan.

Perilaku:

- form menampilkan saldo tersedia;
- jika nominal > saldo, warning terlihat langsung;
- saat submit muncul konfirmasi SweetAlert;
- Operator masih dapat memilih **Tetap simpan**;
- transaksi tidak diblokir;
- audit log menandai `melebihi_saldo = true`;
- saldo kas dapat menjadi negatif setelah konfirmasi.

Saat Edit Setoran, saldo pembanding dihitung sebagai saldo saat ini ditambah kembali nominal Setoran lama sehingga perbandingan menggunakan saldo sebelum transaksi yang sedang diedit.

## 15. Koreksi Transaksi

Koreksi Transaksi menyediakan hard delete untuk:

- Iuran;
- Pengeluaran;
- Setoran.

Koreksi digunakan hanya untuk data salah input.

Penghapusan Pengeluaran dan Setoran membersihkan file bukti setelah delete database berhasil.

Setiap hard delete dicatat ke audit trail sebelum data historis aplikasi tidak lagi tersedia di tabel transaksi.

## 16. Audit Trail Transaksi

Audit trail dibuat **tanpa perubahan database**.

Lokasi:

```text
writable/logs/audit-transaksi-YYYY-MM.log
```

Format: satu JSON per baris.

Data yang dicatat minimal:

- waktu `Asia/Jakarta`;
- jenis aksi;
- jenis transaksi;
- ID transaksi bila tersedia;
- ID/Nama/Username Operator;
- IP request;
- ringkasan data transaksi.

Aksi yang dicatat:

- `CREATE_BULK` Iuran;
- `CREATE` Pengeluaran;
- `CREATE` Setoran;
- `UPDATE` Setoran;
- `DELETE` Setoran dari halaman Setoran;
- `DELETE_KOREKSI` Iuran;
- `DELETE_KOREKSI` Pengeluaran;
- `DELETE_KOREKSI` Setoran.

Audit log bersifat pendukung. Jika file audit gagal ditulis, transaksi utama tidak dibatalkan; kegagalan audit dicatat ke log error aplikasi.

File audit wajib diperlakukan sebagai data operasional dan ikut backup.

## 17. Dashboard

Dashboard menampilkan ringkasan bulan berjalan:

- Iuran Masuk;
- Pengeluaran;
- Setoran Pimpinan;
- Saldo Kas berjalan keseluruhan.

Urutan UI:

1. Ringkasan Bulan Berjalan compact;
2. Aksi Cepat Operator;
3. grafik Iuran harian bulan berjalan;
4. grafik Iuran 6 bulan;
5. grafik Pengeluaran 6 bulan.

Saldo Kas:

```text
Total seluruh Iuran - Total seluruh Pengeluaran - Total seluruh Setoran Resmi
```

## 18. Laporan

Tersedia:

- Laporan Iuran;
- Laporan Pengeluaran;
- Laporan Setoran;
- Rekap Kas.

Filter default adalah tanggal 1 bulan berjalan sampai hari ini.

Laporan Iuran memakai snapshot Golongan untuk histori.

Rekap Kas:

- menampilkan Saldo Awal;
- Iuran diringkas menjadi satu total per tanggal;
- Pengeluaran tetap per transaksi;
- Setoran tetap per transaksi;
- menampilkan saldo berjalan dan Saldo Akhir.

## 19. Export Excel

Export mengikuti filter aktif.

Format tanggal:

```text
DD-MM-YYYY
```

Filename laporan membawa periode, contoh:

```text
laporan-iuran_01-09-2026_sd_16-09-2026.xlsx
laporan-pengeluaran_01-09-2026_sd_16-09-2026.xlsx
laporan-setoran_01-09-2026_sd_16-09-2026.xlsx
rekap-kas_01-09-2026_sd_16-09-2026.xlsx
```

Export Penjual tidak memakai periode karena merupakan master data.

String input pengguna ditulis sebagai literal text agar tidak berubah menjadi formula Excel dan agar nomor seperti No. HP tidak kehilangan digit awal.

## 20. Kartu Anggota

Ukuran canvas:

```text
1011 x 638 px
```

Kartu tidak memakai foto Penjual.

Sisi depan memuat:

- Nama;
- Golongan;
- Lokasi/Lapak;
- No. HP;
- Tanggal Bergabung;
- Alamat;
- QR;
- Kode Kartu.

`kode_kartu` persisten. Regenerate hanya mengganti `kode_verifikasi`.

Download per Penjual tersedia sebagai JPG depan dan ZIP lengkap. Download massal memakai POST + CSRF agar proses yang mungkin menghasilkan kode baru tidak menggunakan GET untuk mutasi state.

## 21. QR dan Scanner

QR publik hanya menampilkan:

- Nama;
- Golongan;
- Status.

Alamat, HP, dan histori transaksi tidak ditampilkan ke publik.

Jika discan oleh Operator yang sudah login, aplikasi mengarahkan ke Detail Penjual internal.

Scanner memakai jsQR lokal dan membutuhkan HTTPS untuk kamera production.

## 22. Setting dan Branding

Setting mengelola:

- Nama Madrasah;
- Alamat Madrasah;
- Logo;
- Background Kartu Depan;
- Background Kartu Belakang.

Upload branding menerima JPG/JPEG/PNG dengan batas:

- maksimal 5 MB;
- maksimal 6000 px per sisi;
- maksimal 24 megapiksel.

File lama baru dibersihkan setelah database berhasil menyimpan path baru.

## 23. Struktur Penyimpanan Upload

```text
uploads/
├── branding/                 # publik untuk asset branding aplikasi
├── bukti_nota/               # legacy, HTTP direct diblokir
└── bukti_setoran/            # legacy, HTTP direct diblokir

writable/uploads/
├── bukti_nota/               # bukti baru, privat
└── bukti_setoran/            # bukti baru, privat
```

File bukti privat disajikan melalui controller Operator dengan header `private, no-store` dan `nosniff`.

## 24. UI/UX

Prinsip:

- mobile-first;
- Bootstrap 5/Sneat;
- DataTables Responsive;
- SweetAlert2 untuk aksi penting;
- card Dashboard compact pada mobile;
- badge status konsisten;
- aksi tulis disembunyikan dari Pimpinan;
- sidebar desktop collapse/expand menyimpan state browser;
- menu mobile overlay;
- asset runtime lokal.

## 25. CI dan Automated Test

GitHub Actions menjalankan:

- `composer validate`;
- `composer install`;
- `composer audit --locked`;
- PHP syntax lint untuk `app/` dan `tests/`;
- JavaScript syntax lint;
- `php spark routes`;
- PHPUnit.

Test aplikasi mencakup minimal:

- private/legacy proof storage resolver;
- proteksi path traversal;
- Excel literal/formula-safe;
- audit trail file-based.

Test contoh database AppStarter CodeIgniter yang hanya bergantung SQLite dan tidak menguji aplikasi ini tidak menjadi bagian baseline.

## 26. Deployment Shared Hosting

Requirement minimum:

- PHP 8.2+;
- `intl`;
- `mbstring`;
- `mysqli`;
- `fileinfo`;
- `gd`;
- `zip`.

Production menggunakan HTTPS, `CI_ENVIRONMENT = production`, secure cookie, dan DBDebug off.

`writable/` harus writable oleh PHP, termasuk:

```text
writable/logs/
writable/uploads/bukti_nota/
writable/uploads/bukti_setoran/
```

Jika hosting tidak memiliki Composer/SSH, `vendor/` disiapkan lokal dan ikut ZIP deployment.

## 27. Backup Operasional

Backup minimal:

- dump database;
- `.env` production di lokasi aman;
- `uploads/branding/`;
- file legacy `uploads/bukti_nota/`;
- file legacy `uploads/bukti_setoran/`;
- `writable/uploads/bukti_nota/`;
- `writable/uploads/bukti_setoran/`;
- `writable/logs/audit-transaksi-*.log`.

Database, upload, dan audit log adalah data operasional yang tidak dapat dipulihkan hanya dari Git.

## 28. Smoke Test Wajib

Setelah deployment:

- login Operator dan Pimpinan;
- pastikan Pimpinan read-only;
- pastikan akun Nonaktif kehilangan akses;
- Input Iuran bulk;
- ubah tanggal Iuran dan pastikan tanggal masa depan ditolak;
- pastikan duplicate Iuran ditolak;
- Pengeluaran + bukti;
- pastikan tanggal Pengeluaran masa depan ditolak;
- Cetak Form Setoran;
- Input Setoran Resmi + bukti;
- pastikan tanggal Setoran masa depan ditolak;
- masukkan nominal Setoran > saldo dan pastikan warning muncul tetapi tetap dapat dikonfirmasi;
- Edit Setoran;
- Koreksi Iuran/Pengeluaran/Setoran;
- cek `writable/logs/audit-transaksi-YYYY-MM.log` bertambah setelah transaksi/koreksi;
- Dashboard dan grafik;
- Laporan dan export Excel;
- Kartu/QR/scanner;
- akses URL langsung file bukti legacy harus ditolak;
- route bukti melalui Operator harus bekerja;
- `php spark routes` bersih;
- PHPUnit hijau.

## 29. Konvensi Pengembangan

- Controller PascalCase.
- Model `{Nama}Model.php`.
- View `{modul}_{aksi}.php`.
- Komentar aplikasi menggunakan Bahasa Indonesia.
- Mutasi data menggunakan POST.
- Master memakai soft delete jika histori harus dipertahankan.
- Transaksi salah dikoreksi melalui hard delete resmi.
- Perubahan schema untuk hosting tanpa terminal harus memiliki SQL phpMyAdmin ekuivalen.
- Seeder hanya untuk fresh install.
- Jangan import ulang database development untuk menerapkan perubahan schema production.
