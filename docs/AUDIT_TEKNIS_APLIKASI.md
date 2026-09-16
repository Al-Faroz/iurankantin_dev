# Audit Teknis Aplikasi Iuran Kantin MTsN 4 Jombang

**Tanggal audit:** 16 September 2026

Dokumen ini mencatat hasil audit teknis aplikasi, perbaikan yang sudah diterapkan, serta area yang masih layak dipantau. Dokumen Acuan tetap menjadi baseline utama aturan aplikasi.

## 1. Area yang Diaudit

Audit mencakup:

- routing dan hak akses;
- login, session, CSRF, secure headers;
- master data;
- Iuran;
- Pengeluaran;
- Setoran;
- Koreksi Transaksi;
- Dashboard;
- Laporan dan Excel;
- Kartu/QR/scanner;
- upload file;
- migration/schema;
- shared hosting;
- dependency Composer;
- GitHub Actions dan PHPUnit.

## 2. Temuan yang Sudah Ditutup

### A. Session lama masih mempercayai role/status

Role dan status user divalidasi kembali terhadap database pada request terproteksi.

**Status:** selesai.

### B. Lifecycle file bukti dapat tidak konsisten dengan database

File baru dibersihkan bila insert gagal. Delete Pengeluaran/Setoran baru menghapus file setelah delete database berhasil.

**Status:** selesai.

### C. Branding lama dapat hilang ketika update database gagal

File lama baru dihapus setelah path baru berhasil tersimpan.

**Status:** selesai.

### D. Formula injection Excel

String ditulis sebagai literal `TYPE_STRING`; nilai numerik tetap numerik.

**Status:** selesai.

### E. Filter tanggal hanya memeriksa format

Tanggal kalender divalidasi dengan ketat.

**Status:** selesai.

### F. CRUD master dapat menampilkan sukses ketika write gagal

Write master dan generate kode kartu sekarang memeriksa hasil database.

**Status:** selesai.

### G. Duplicate Iuran per Penjual per tanggal

Baseline sekarang menetapkan satu Penjual maksimal satu Iuran per tanggal.

Proteksi:

1. UI menandai `Tercatat`;
2. server menolak duplikasi;
3. unique index database `uniq_iuran_penjual_tanggal`.

**Status:** selesai.

### H. Histori Golongan berubah mengikuti master saat ini

Transaksi Iuran menyimpan snapshot ID, Nama, dan nominal default Golongan.

**Status:** selesai.

### I. Bukti transaksi dapat diakses dengan URL langsung

Upload baru masuk `writable/uploads`. File legacy tetap kompatibel tetapi URL direct diblokir. Akses bukti memakai route Operator.

**Status:** selesai.

### J. `bukti_setoran` tidak tersedia pada fresh install migration-only

Migration idempotent `100013` sudah tersedia.

**Status:** selesai.

### K. Download Kartu GET dapat membuat state baru

Download massal yang dapat memicu pembuatan kode dipindah ke POST + CSRF. Download individual tidak membuat kode baru secara diam-diam.

**Status:** selesai.

### L. Upload branding hanya dibatasi ukuran file

Sekarang dibatasi juga maksimal 6000 px per sisi dan 24 megapiksel.

**Status:** selesai.

### M. `spark` masih mengarah ke folder `public/`

CLI sudah disesuaikan dengan front controller root sehingga `php spark routes` tidak lagi menghasilkan warning `chdir()`.

**Status:** selesai.

### N. PHPUnit belum menjadi bagian CI

PHPUnit sekarang dijalankan di GitHub Actions. Test contoh SQLite bawaan AppStarter yang tidak menguji aplikasi sudah dihapus dari baseline.

**Status:** selesai.

### O. Dependency belum diaudit otomatis

`composer audit --locked` sekarang blocking di CI.

**Status:** selesai.

### P. Tanggal transaksi masa depan masih diizinkan

Keputusan operasional: **tanggal transaksi masa depan dilarang**.

Implementasi:

- Input Iuran membatasi tanggal sampai hari ini;
- Pengeluaran membatasi tanggal sampai hari ini;
- Form Setoran dan Setoran Resmi membatasi tanggal sampai hari ini;
- server memvalidasi ulang sehingga manipulasi request tetap ditolak.

**Status:** selesai.

### Q. Setoran dapat melebihi saldo tanpa peringatan

Keputusan operasional: **tidak diblokir, tetapi wajib warning**.

Implementasi:

- form menampilkan saldo tersedia;
- warning tampil saat nominal melebihi saldo;
- submit meminta konfirmasi SweetAlert;
- Operator dapat memilih Tetap Simpan;
- audit log menandai `melebihi_saldo`;
- Edit membandingkan terhadap saldo sebelum transaksi yang sedang diedit.

**Status:** selesai sesuai kebijakan.

### R. Belum ada jejak koreksi transaksi

Keputusan operasional: audit trail sederhana tanpa mengubah database.

Implementasi menggunakan:

```text
writable/logs/audit-transaksi-YYYY-MM.log
```

Setiap baris berformat JSON dan memuat waktu, aksi, jenis transaksi, ID transaksi, operator, IP, dan ringkasan data.

Aksi yang dicatat mencakup create Iuran/Pengeluaran/Setoran, update Setoran, delete Setoran, dan seluruh hard delete melalui Koreksi Transaksi.

**Status:** selesai.

## 3. Quality Assurance Saat Ini

GitHub Actions menjalankan:

- Composer validate;
- Composer install;
- Composer security audit;
- PHP syntax lint;
- JavaScript syntax lint;
- route compilation;
- PHPUnit.

Regression test aplikasi saat ini mencakup:

- private/legacy proof resolver;
- proteksi path traversal;
- Excel literal/formula-safe;
- audit trail file-based.

## 4. Area yang Masih Layak Ditingkatkan

Tidak ada temuan audit kritis yang saat ini diketahui belum ditangani. Peningkatan berikut bersifat hardening/quality, bukan blocker operasional:

1. tambah automated test untuk autentikasi/role;
2. tambah test duplicate Iuran dan snapshot Golongan dengan database test terisolasi;
3. tambah test saldo/Rekap Kas;
4. tambah retensi/rotasi khusus audit log bila volume transaksi membesar;
5. dokumentasikan prosedur review audit log berkala;
6. pertimbangkan monitoring kapasitas `writable/` di hosting.

## 5. Risiko Operasional yang Tetap Harus Dipahami

- Setoran > saldo **memang diizinkan oleh kebijakan** setelah warning dan dapat membuat saldo negatif.
- Audit trail berbasis file wajib ikut backup; jika folder `writable/logs/` hilang, jejak audit tidak dapat dipulihkan dari database.
- Data histori Golongan sebelum snapshot dibuat tidak dapat direkonstruksi sempurna bila Penjual pernah pindah Golongan sebelum backfill.
- File bukti legacy tetap perlu dipertahankan selama record database masih menunjuk ke file tersebut.

## 6. Kesimpulan Audit

Aplikasi saat ini sudah memiliki perlindungan yang memadai untuk operasional internal madrasah: explicit routes, CSRF, role validation, private proof storage, duplicate protection Iuran, snapshot histori Golongan, export aman, dependency audit, PHPUnit, pembatasan tanggal transaksi, warning Setoran > saldo, dan audit trail transaksi sederhana.

Fokus pengembangan selanjutnya dapat berpindah dari perbaikan bug dasar ke peningkatan coverage test dan observability operasional.
