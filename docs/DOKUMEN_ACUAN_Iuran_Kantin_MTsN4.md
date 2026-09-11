# DOKUMEN ACUAN
## Aplikasi Pencatatan Iuran Kantin — MTsN 4 Jombang
**Versi:** Draft v7 — DB & baseURL dev terkunci, kartu anggota jadi model depan-belakang dengan background upload dinamis via Setting, template awal sudah dibuat, coding akan dikerjakan lewat ChatGPT

---

## 1. GAMBARAN UMUM

Aplikasi ini **bukan** aplikasi kasir/jual-beli kantin, dan **bukan** iuran dari siswa. Ini adalah aplikasi pencatatan **iuran dari penjual/pedagang** yang berjualan di lingkungan kantin madrasah kepada pihak madrasah, sekaligus pencatatan kas kantin secara sederhana (pemasukan iuran, pengeluaran operasional, dan setoran ke pimpinan).

**Tujuan aplikasi:**
- Mencatat pemasukan iuran harian dari tiap penjual kantin
- Mencatat pengeluaran terkait operasional kantin
- Mencatat setoran kas ke pimpinan madrasah
- Menyediakan rekap/laporan kas kantin untuk pimpinan

**Bukan bagian dari scope:**
- Tidak ada fitur piutang/tunggakan (siapa belum bayar) — cukup catat yang sudah bayar
- Tidak terintegrasi dengan SisisFour (Absensi/BK) — berdiri sendiri, database terpisah

---

## 2. ARSITEKTUR & STACK TEKNIS

Mengikuti konvensi yang sudah baku dari proyek-proyek sebelumnya (SisisFour), untuk konsistensi:

| Aspek | Keputusan |
|---|---|
| Framework | CodeIgniter 4, **standard MVC** (flat Controllers/Models/Views, bukan HMVC) |
| UI Theme | Sneat Free (Bootstrap 5) |
| Library pendukung | jQuery, DataTables (Responsive plugin), SweetAlert2, Dompdf (untuk bukti setoran & form cetak mingguan), GD/Intervention Image (kompresi foto nota otomatis <500 KB; render kartu anggota ke JPG), Endroid QR Code / phpqrcode (generate QR kartu anggota), html5-qrcode / jsQR (scan QR via kamera browser, client-side JS — solusi karena banyak Android tidak punya scanner QR bawaan) |
| Asset | Semua file lokal di `assets/`, tidak pakai CDN |
| Struktur folder | `public/` dipindah ke root project (kompatibel shared hosting), `.htaccess` proteksi untuk `app/`, `writable/`, `vendor/`, `.env` |
| Database | Migrations untuk skema, Seeders untuk data referensi (kategori penjual, kategori pengeluaran) |
| Session | DB session (bukan file-based) |
| Naming | Tidak boleh ada file `index.php` di views; views `{modul}_{aksi}.php`; controller PascalCase; model `{Nama}Model.php`; service `{Nama}Service.php` |
| Komentar kode | Bahasa Indonesia |
| Git | Kerja langsung di `main`, `.gitignore` standar CI4 |
| Database name | `iuran_dev` |
| Dev baseURL | `http://localhost/iuran_dev` |

---

## 3. ROLE & HAK AKSES

Aplikasi ini sederhana, hanya 2 role:

| Role | Hak Akses |
|---|---|
| **Operator** (merangkap Admin) | Full akses: kelola master data penjual, kategori, input transaksi iuran, input pengeluaran, catat setoran, lihat semua laporan |
| **Pimpinan** | Read-only: lihat dashboard, rekap kas, laporan iuran/pengeluaran/setoran — tidak bisa input/edit apa pun |

Tidak perlu RBAC matrix kompleks seperti SisisFour — cukup 2 role hardcoded, tidak perlu menu-editable.

---

## 4. STRUKTUR DATABASE (Draft Skema)

### 4.1 `penjual` (Master Data Penjual)
| Field | Tipe | Keterangan |
|---|---|---|
| id_penjual | INT PK | |
| nama_penjual | VARCHAR | |
| id_golongan | INT FK | ke `golongan_penjual` |
| no_hp | VARCHAR | nullable |
| lokasi_lapak | VARCHAR | nullable, mis. "Lapak A3" |
| status_aktif | ENUM(Aktif/Nonaktif) | |
| tanggal_daftar | DATE | |
| kode_kartu | VARCHAR | unik, auto-generate saat kartu pertama kali dibuat, ditampilkan di kartu |
| kode_verifikasi | VARCHAR | unik, random string, di-encode ke QR untuk verifikasi kartu |
| created_at, updated_at | DATETIME | |

### 4.2 `golongan_penjual` (Master Golongan — 3 golongan, nominal tetap)
| Field | Tipe | Keterangan |
|---|---|---|
| id_golongan | INT PK | |
| nama_golongan | VARCHAR | Golongan 1 / 2 / 3 |
| nominal_iuran | DECIMAL | Gol 1 = 10.000, Gol 2 = 7.500, Gol 3 = 5.000 — dipakai sebagai prefill di form bulk insert, operator tetap bisa override manual per transaksi |

### 4.3 `transaksi_iuran` (Pemasukan)
| Field | Tipe | Keterangan |
|---|---|---|
| id_transaksi | INT PK | |
| id_penjual | INT FK | |
| tanggal | DATE | |
| nominal | DECIMAL | diisi manual oleh operator saat input (prefill dari nominal_iuran golongan, tapi bisa diubah) |
| keterangan | VARCHAR | nullable |
| id_operator | INT FK | siapa yang input, untuk jejak/log |
| created_at | DATETIME | |

### 4.4 `kategori_pengeluaran` (Master Kategori Pengeluaran)
| Field | Tipe | Keterangan |
|---|---|---|
| id_kategori_keluar | INT PK | |
| nama_kategori | VARCHAR | tidak ada data awal/seed — operator isi sendiri lewat menu Master Kategori Pengeluaran |

### 4.5 `transaksi_pengeluaran`
| Field | Tipe | Keterangan |
|---|---|---|
| id_pengeluaran | INT PK | |
| tanggal | DATE | |
| id_kategori_keluar | INT FK | |
| nominal | DECIMAL | |
| keterangan | VARCHAR | |
| bukti_nota | VARCHAR | nullable, path file upload foto nota — opsional, kalau diupload otomatis dikompresi ke bawah 500 KB |
| id_operator | INT FK | |
| created_at | DATETIME | |

### 4.6 `setoran_pimpinan`
| Field | Tipe | Keterangan |
|---|---|---|
| id_setoran | INT PK | |
| tanggal_form | DATE | tanggal saat form/bukti dibuat |
| periode_awal | DATE | tanggal awal periode setoran (mis. rentang iuran yang disetorkan) |
| periode_akhir | DATE | tanggal akhir periode setoran |
| nominal | DECIMAL | besar setoran |
| keterangan | VARCHAR | nullable |
| id_operator | INT FK | yang menyetor/input |
| created_at | DATETIME | |

### 4.7 `users`
| Field | Tipe | Keterangan |
|---|---|---|
| id_user | INT PK | |
| nama | VARCHAR | |
| username | VARCHAR | |
| password | VARCHAR (hashed) | |
| role | ENUM(Operator/Pimpinan) | |
| status_aktif | ENUM | |

### 4.8 `setting` (baris tunggal, data madrasah & branding)
| Field | Tipe | Keterangan |
|---|---|---|
| nama_madrasah | VARCHAR | header PDF bukti setoran & form mingguan |
| alamat_madrasah | VARCHAR | nullable |
| logo | VARCHAR | path upload logo madrasah |
| background_kartu_depan | VARCHAR | path upload background sisi depan Kartu Anggota Kantin |
| background_kartu_belakang | VARCHAR | path upload background sisi belakang Kartu Anggota Kantin |
| updated_at | DATETIME | |

---

## 5. MODUL & FITUR

1. **Login** — 2 role (Operator, Pimpinan)
2. **Master Penjual** — CRUD data penjual, assign golongan
3. **Master Golongan Penjual** — CRUD 3 golongan + nominal iuran tetap (10.000 / 7.500 / 5.000)
4. **Master Kategori Pengeluaran** — CRUD kategori pengeluaran
5. **Input Iuran (Bulk Insert)** — form harian menampilkan daftar SEMUA penjual sekaligus dalam satu tabel, urutan: golongan iuran tertinggi dulu (Gol 1 → Gol 2 → Gol 3), lalu nama ascending di dalam tiap golongan. Tiap baris punya input bar nominal (prefill dari nominal_iuran golongan, bisa diubah manual per baris). Operator isi nominal yang bayar saja (baris yang tidak bayar dikosongkan/skip), lalu simpan sekaligus dalam satu submit
6. **Input Pengeluaran** — pilih kategori pengeluaran → nominal → keterangan → upload foto nota (opsional, kalau diupload otomatis dikompresi ke bawah 500 KB) → simpan
7. **Setoran ke Pimpinan** — 2 tahap terpisah:
   - **Cetak Form Setoran (PDF)**: operator isi tanggal form, periode awal, periode akhir, dan besar setoran → cetak PDF (format default, header dari Setting data madrasah, tanda tangan dikosongkan) → dipakai operator sebagai bukti fisik saat menyerahkan dana ke pimpinan. Tahap ini murni untuk cetak, belum tersimpan ke database
   - **Input Setoran (simpan ke database)**: setelah dana benar-benar diserahkan ke pimpinan, operator input ulang data setoran yang sama (tanggal form, periode awal, periode akhir, nominal, keterangan) ke aplikasi agar tercatat resmi di `setoran_pimpinan` dan masuk perhitungan saldo kas
8. **Cetak Form Iuran Mingguan (PDF)** — generate form kosong berisi daftar penjual (urutan sama seperti form bulk insert: golongan tertinggi dulu, nama ascending) dengan kolom tanggal Sabtu, Minggu, Senin, Selasa, Rabu, Kamis (6 hari), ditambah kolom **Total per Penjual** di sisi kanan. Dipakai operator sebagai lembar pencatatan manual di lapangan sebelum diinput ke aplikasi. Header sama seperti bukti setoran (ambil dari Setting data madrasah)
9. **Kartu Anggota Kantin** — kartu digital per penjual, tanpa foto (nama & data teks saja), dengan QR code untuk verifikasi & shortcut:
   - **Generate kartu**: sistem auto-generate `kode_kartu` & `kode_verifikasi` untuk tiap penjual (sekali generate, dipakai terus kecuali di-generate ulang manual)
   - **Layout/posisi elemen** — mengikuti persis template referensi `kartu-pelajar.zip` (canvas 1011×638px, elemen posisi absolut), field disesuaikan ke data penjual:

     | Elemen template asli | Posisi (left, top) | Dipakai untuk kartu anggota kantin |
     |---|---|---|
     | `.photo-box` (foto 3:4) | 760, 73 | **Dihapus** — tidak ada foto penjual |
     | `.qr-box` (QR 1:1) | 810, 375 | Tetap — QR verifikasi (encode kode_verifikasi) |
     | `.qr-caption` | 790, 505 | Tetap — teks `kode_kartu` |
     | `.nama` | 40, 175 | Tetap — `nama_penjual` |
     | `.meta-col` (grid 2 kolom, 4 item) | 40, 340 | Tetap, isi 4 item: **Golongan, Lokasi/Lapak, No. HP, Tanggal Bergabung** (gantikan NIS/Kelas/Jenis Kelamin/Tahun Ajaran) |
     | `.ttl` | 40, 460 | **Tidak dipakai** (dikosongkan) — tidak ada data setara (tempat tanggal lahir) di penjual |
     | `.alamat` | 40, 490 | **Tidak dipakai** (dikosongkan) — tidak ada data setara |

     *(Kalau mau baris `.ttl`/`.alamat` diisi sesuatu juga — misal Status Aktif atau teks footer statis — tinggal bilang, tinggal disesuaikan.)*
   - **Kegunaan QR (kombinasi, tergantung siapa yang scan)**:
     - **Belum login** (satpam, guru piket, pimpinan, siapa pun scan pakai HP) → tampil **halaman verifikasi publik** read-only tanpa login, berisi: Nama Penjual, Golongan, Status (Aktif/Nonaktif). Tidak menampilkan data sensitif (no HP, riwayat iuran, dll) — fungsinya sekadar cek cepat "penjual ini terdaftar resmi atau tidak", status otomatis ikut berubah kalau penjual dinonaktifkan
     - **Sudah login sebagai Operator** → otomatis redirect ke **halaman profil/detail penjual di dalam aplikasi** (bukan halaman publik), berisi data lengkap + riwayat transaksi iuran penjual itu — jadi scan kartu bisa dipakai operator sebagai shortcut cari penjual tanpa perlu cari manual, misalnya untuk input iuran susulan di luar form bulk harian
     - Logic pembeda dua tampilan di atas cukup 1 controller/route yang sama, dicek dari session login aktif atau tidak
   - **Scan QR built-in di aplikasi** (bukan andalkan aplikasi kamera bawaan HP): tersedia menu "Scan Kartu" khusus untuk Operator, membuka kamera HP langsung lewat browser (pakai library JS seperti html5-qrcode/jsQR via `getUserMedia`), decode QR di sisi client, lalu langsung diarahkan ke halaman profil penjual — ini jadi solusi karena banyak HP Android (terutama yang murah/lama) tidak punya fitur scan QR otomatis di kamera bawaannya, kendala yang sama pernah dialami di aplikasi sebelumnya. **Catatan teknis: fitur kamera browser (`getUserMedia`) butuh koneksi HTTPS di production — dikonfirmasi hosting sudah support SSL** (sama seperti hosting SisisFour)
   - **Background/desain — depan & belakang, upload dinamis lewat Setting** (bukan file statis hardcode, sama seperti pola Kartu Pelajar Digital di SisisFour): kartu punya 2 sisi seperti kartu fisik biasa —
     - **Sisi Depan**: berisi data dinamis (nama, golongan, lokasi/lapak, no. HP, tanggal bergabung, QR) sesuai tabel mapping di atas, di-overlay di atas gambar background depan
     - **Sisi Belakang**: statis, tanpa data dinamis — biasanya berisi alamat madrasah/kontak/syarat kartu, sepenuhnya mengikuti gambar background belakang yang diupload
     - Template awal (HTML/CSS, canvas 1011×638px, posisi elemen sesuai tabel mapping) **sudah dibuat** sebagai draft/starting point (lihat file terlampir), background di dalamnya tinggal diganti setelah operator upload lewat Setting
   - **Download satuan**: tombol download per penjual, output **2 file JPG (depan + belakang)** atau digabung jadi 1 file JPG (depan-belakang bersisian) — *(perlu dikonfirmasi preferensinya)*
   - **Download bulk**: tombol "Download Semua" → generate kartu depan+belakang untuk semua penjual aktif sekaligus, dibungkus dalam 1 file **ZIP**
   - Render JPG dilakukan server-side (HTML/CSS template → image, atau digambar langsung pakai GD/Imagick di atas background)
10. **Setting** — data madrasah (nama madrasah, alamat, upload logo) yang dipakai sebagai header di PDF (bukti setoran, form mingguan); **tambahan: upload Background Kartu Depan & Background Kartu Belakang** untuk Kartu Anggota Kantin (format gambar, dinamis, bisa diganti kapan saja tanpa ubah kode)
11. **Dashboard** — saldo kas berjalan (Total Iuran − Total Pengeluaran − Total Setoran), grafik tren harian/bulanan, ringkasan hari ini
12. **Laporan**
    - Laporan Iuran per periode (tanggal/bulan), filter per penjual/golongan
    - Laporan Pengeluaran per periode, filter per kategori
    - Laporan Setoran per periode
    - Rekap Kas (buku kas gabungan: semua pemasukan-pengeluaran-setoran berurutan tanggal, dengan saldo berjalan)
    - Export Excel untuk semua laporan di atas

---

## 6. ALUR KERJA (Business Flow)

**Alur mingguan (persiapan):**
1. Operator cetak Form Iuran Mingguan (PDF) di awal minggu → dipakai untuk mencatat manual iuran tiap penjual per hari di lapangan

**Alur harian:**
1. Operator input iuran lewat form bulk insert (satu tabel semua penjual, urut golongan tertinggi → nama ascending), isi nominal hanya untuk penjual yang bayar hari itu, submit sekaligus (data dari catatan manual di form mingguan dipindah ke sini)
2. Operator input pengeluaran jika ada (sesuai kebutuhan, tidak harus tiap hari)
3. Saat mau menyetor ke pimpinan: operator isi form (tanggal form, periode awal, periode akhir, besar setoran) → cetak PDF → bawa fisik dana sesuai nominal di form → serahkan ke pimpinan → baru setelah itu operator input data yang sama ke aplikasi (tersimpan resmi di database)
4. Saldo kas = akumulasi (Iuran masuk) − (Pengeluaran) − (Setoran ke pimpinan)

**Alur pimpinan:**
- Login → lihat dashboard saldo & tren → buka laporan detail sesuai kebutuhan → export jika perlu

---

## 7. KONVENSI TEKNIS

Sama seperti proyek CI4 lain milik madrasah ini:
- Soft delete untuk master data (`penjual`, `golongan_penjual`, `kategori_pengeluaran`) agar histori transaksi tidak rusak; hard delete untuk transaksi yang salah input (dengan konfirmasi)
- DataTables Responsive untuk semua tabel data
- Client-side pagination
- Filter aktif harus diikuti export (export sesuai data yang difilter, bukan semua data)

---

## 8. STRUKTUR FOLDER & FILE

Berdasarkan aset Sneat (`assets.zip`) dan referensi halaman demo (`html.zip`) yang dikirim, serta konvensi CI4 standard MVC yang sudah dikunci di Bab 2 & 7:

```
iuran-kantin/
├── app/
│   ├── Config/                     # Config bawaan CI4 (Database, Routes, App, dst)
│   ├── Controllers/
│   │   ├── Auth.php                 # Login/logout
│   │   ├── Dashboard.php
│   │   ├── Penjual.php              # Master Penjual
│   │   ├── GolonganPenjual.php      # Master Golongan (3 golongan, nominal tetap)
│   │   ├── KategoriPengeluaran.php  # Master Kategori Pengeluaran
│   │   ├── Iuran.php                # Input iuran (bulk insert)
│   │   ├── Pengeluaran.php          # Input pengeluaran + bukti nota
│   │   ├── Setoran.php              # Cetak form setoran (PDF) + input setoran ke DB
│   │   ├── KartuAnggota.php         # Generate & download kartu (satuan/bulk)
│   │   ├── Verifikasi.php           # Halaman scan/verifikasi QR (publik & redirect internal)
│   │   ├── Laporan.php              # Semua laporan + export Excel
│   │   ├── Setting.php              # Data madrasah (nama, alamat, logo)
│   │   └── User.php                 # Kelola akun Operator/Pimpinan
│   ├── Models/
│   │   ├── PenjualModel.php
│   │   ├── GolonganPenjualModel.php
│   │   ├── KategoriPengeluaranModel.php
│   │   ├── TransaksiIuranModel.php
│   │   ├── TransaksiPengeluaranModel.php
│   │   ├── SetoranPimpinanModel.php
│   │   ├── SettingModel.php
│   │   └── UserModel.php
│   ├── Services/                    # Logic berat dipisah dari Controller
│   │   ├── IuranService.php         # Proses simpan bulk insert
│   │   ├── SetoranService.php       # Generate PDF form setoran
│   │   ├── KartuAnggotaService.php  # Generate kode kartu, render JPG, bundling ZIP
│   │   ├── QrService.php            # Generate QR, resolve logic verifikasi vs redirect
│   │   ├── PdfService.php           # Wrapper Dompdf (bukti setoran, form mingguan)
│   │   └── LaporanService.php       # Query rekap kas & export Excel (PhpSpreadsheet)
│   ├── Filters/
│   │   ├── AuthFilter.php           # Wajib login
│   │   └── OperatorOnlyFilter.php   # Blokir Pimpinan dari halaman input/edit
│   ├── Views/                       # Flat, naming {modul}_{aksi}.php — pecahan dari html.zip (Sneat)
│   │   ├── layout_header.php        # <head>, sidebar menu, navbar (dari index.html Sneat)
│   │   ├── layout_footer.php        # footer + vendor JS includes
│   │   ├── auth_login.php           # dari auth-login-basic.html
│   │   ├── dashboard_index.php      # dari index.html (disederhanakan sesuai Bab 5)
│   │   ├── penjual_index.php        # dari tables-basic.html + DataTables
│   │   ├── penjual_form.php         # dari form-layouts-vertical.html
│   │   ├── golongan_index.php
│   │   ├── kategori_pengeluaran_index.php
│   │   ├── iuran_bulk.php           # form bulk insert khas (bukan dari template standar)
│   │   ├── pengeluaran_index.php
│   │   ├── pengeluaran_form.php
│   │   ├── setoran_cetak_form.php   # form isi tanggal form/periode/nominal sebelum cetak PDF
│   │   ├── setoran_index.php        # daftar setoran tersimpan
│   │   ├── setoran_input.php        # form input resmi ke DB (tahap 2)
│   │   ├── kartu_index.php          # daftar penjual + tombol download satuan/bulk
│   │   ├── kartu_scan.php           # halaman scan QR built-in (kamera browser)
│   │   ├── verifikasi_publik.php    # halaman publik hasil scan (belum login)
│   │   ├── laporan_iuran.php
│   │   ├── laporan_pengeluaran.php
│   │   ├── laporan_setoran.php
│   │   ├── laporan_rekap_kas.php
│   │   ├── setting_index.php
│   │   ├── user_index.php
│   │   ├── pdf_bukti_setoran.php    # template khusus untuk Dompdf (bukan halaman biasa)
│   │   ├── pdf_form_mingguan.php    # template khusus untuk Dompdf
│   │   ├── kartu_template.php       # template render kartu anggota (HTML → JPG)
│   │   └── errors/                  # 404/403 custom (opsional, bawaan CI4 bisa dipakai)
│   └── Database/
│       ├── Migrations/              # 1 file per tabel (penjual, golongan_penjual, dst)
│       └── Seeds/                   # akun awal Operator/Pimpinan; TIDAK ada seed kategori_pengeluaran (diisi manual)
├── assets/                          # dari assets.zip — Sneat theme, tracked di Git
│   ├── css/  (custom.css, demo.css)
│   ├── js/   (main.js, custom.js, config.js, dst)
│   ├── vendor/
│   │   ├── libs/ (datatables, sweetalert2, select2, apex-charts, jquery, dst)
│   │   ├── css/  (core.css, pages/)
│   │   └── js/   (bootstrap.js, menu.js, helpers.js)
│   ├── img/  (illustrations, icons, backgrounds, favicon, avatars)
│   └── fonts/ (Poppins)
├── assets-app/                      # ASET KHUSUS APLIKASI INI (baru, di luar tema Sneat)
│   ├── qrcode-lib/                  # html5-qrcode / jsQR (untuk scan QR built-in)
│   └── kartu-background/            # file background kartu anggota kantin (menyusul)
├── uploads/                         # dinamis, TIDAK tracked Git, punya .htaccess blokir eksekusi script
│   ├── bukti_nota/                  # foto nota pengeluaran (opsional, hasil kompresi <500KB)
│   └── branding/                    # logo madrasah dari menu Setting
├── writable/                        # bawaan CI4 (cache, logs, session, dst)
├── vendor/                          # composer packages (termasuk Dompdf, PhpSpreadsheet, Endroid QR Code)
├── index.php                        # dipindah dari public/ ke root
├── .htaccess                        # dipindah dari public/ ke root
├── .env
├── .gitignore
├── composer.json
└── README.md                        # instruksi deploy ke hosting
```

**Catatan migrasi dari referensi:**
- Isi `assets.zip` dipakai apa adanya sebagai folder `assets/` di root project (tidak diubah struktur internalnya), supaya update tema Sneat di masa depan gampang tinggal timpa folder ini.
- File-file di `html.zip` **tidak dipakai langsung** sebagai view — tiap halaman dipecah dan disusun ulang mengikuti naming convention `{modul}_{aksi}.php`, bagian yang berulang (sidebar menu, navbar, footer, script includes) dipisah ke `layout_header.php` & `layout_footer.php` supaya tidak duplikasi di 20+ file view.

---

## 9. KETENTUAN UI/UX

Berlaku untuk seluruh halaman aplikasi, mengacu ke komponen Sneat yang sudah tersedia di `html.zip`:

| Kebutuhan Halaman | Komponen Sneat yang Dipakai | Catatan |
|---|---|---|
| Layout dasar (sidebar + navbar + konten) | `index.html` (struktur `layout-wrapper` > `layout-menu` + `layout-page`) | Sidebar collapsible ke hamburger di layar kecil — wajib, karena mayoritas akses dari HP Android |
| Login | `auth-login-basic.html` | Tanpa fitur "Register" (akun dibuat manual oleh Operator lewat menu User) |
| Tabel data (Penjual, Laporan, dst) | `tables-basic.html` + DataTables (vendor/libs/datatables) | Wajib pakai plugin **Responsive** (kolom collapse jadi detail expand di layar kecil, bukan scroll horizontal) — sudah dikunci di Bab 7 |
| Form tambah/edit | `form-layouts-vertical.html` | Halaman terpisah (bukan modal), konsisten dengan naming `_form.php`; dropdown yang datanya banyak (misal pilih penjual) pakai **Select2** biar bisa dicari |
| Konfirmasi hapus/aksi penting | SweetAlert2 (bukan modal Bootstrap manual) | Dipakai juga untuk konfirmasi sebelum submit setoran (karena melibatkan uang) |
| Notifikasi sukses/gagal | SweetAlert2 toast atau `ui-toasts.html` | Konsisten satu jenis saja di seluruh aplikasi, jangan campur SweetAlert2 & toast bawaan Bootstrap |
| Dashboard ringkasan | `cards-basic.html` untuk kartu ringkasan (Total Iuran Hari Ini, Saldo Kas, dst) + Apex Charts untuk grafik tren | Grafik cukup 1 line/bar chart tren kas per bulan, tidak perlu banyak chart seperti dashboard analytics bawaan Sneat |
| Status Aktif/Nonaktif, kategori, dsb | `ui-badges.html` | Konvensi warna: **Aktif = hijau (success)**, **Nonaktif = abu-abu (secondary)** — konsisten di semua tabel |
| Form bulk insert iuran | Tidak ada padanan langsung di template Sneat — dibangun custom di atas `tables-basic.html`, tiap baris tabel diubah jadi input nominal (bukan teks statis) |
| Halaman scan QR (`kartu_scan.php`) | Custom (kamera browser), pakai `layouts-blank.html` sebagai basis (tanpa sidebar/navbar) supaya area kamera lega, khususnya di layar HP kecil |
| Halaman verifikasi publik (`verifikasi_publik.php`) | `layouts-blank.html` juga — halaman ringan, tanpa login, tanpa sidebar |

**Prinsip umum:**
- **Mobile-first** — aplikasi ini didesain untuk dipakai sehari-hari lewat Chrome Android, jadi semua halaman input (terutama form bulk insert iuran) harus nyaman dipakai satu tangan/layar kecil, tombol submit selalu terlihat tanpa perlu scroll jauh
- **Konsisten satu tema** — semua modul pakai palet warna & komponen Sneat yang sama, tidak bikin style custom yang menyimpang dari tema kecuali benar-benar perlu (kartu anggota, halaman scan QR)
- **Read-only untuk Pimpinan** — halaman yang diakses Pimpinan (Dashboard, Laporan) menyembunyikan/menonaktifkan semua tombol aksi (tambah/edit/hapus/input), bukan sekadar dibatasi lewat route saja
- **Aksesibilitas offline lambat** — asumsikan koneksi internet madrasah kadang lambat; asset harus lokal (sudah dikunci di Bab 2), hindari lazy-load berlebihan dari CDN eksternal

---

## 10. HAL YANG MASIH PERLU DIKONFIRMASI

- [ ] Download satuan Kartu Anggota: mau **2 file JPG terpisah** (depan.jpg + belakang.jpg) atau **digabung jadi 1 file JPG** (depan-belakang bersisian dalam satu gambar)?

Sisanya sudah terkonfirmasi, termasuk logic QR (kombinasi verifikasi publik + shortcut internal operator), struktur folder, ketentuan UI/UX, nama database (`iuran_dev`), dan baseURL dev (`http://localhost/iuran_dev`). Dokumen ini sudah bisa dilempar ke ChatGPT untuk mulai coding modul inti, termasuk modul Kartu Anggota Kantin (template depan/belakang sudah dibuat, tinggal upload background lewat menu Setting).
