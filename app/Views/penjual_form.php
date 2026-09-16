<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$isEdit = is_array($penjual);
$action = $isEdit
    ? $baseUrl . '/penjual/' . (int) $penjual['id_penjual'] . '/update'
    : $baseUrl . '/penjual/simpan';
$today = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');

$namaPenjual = old('nama_penjual') ?: ($penjual['nama_penjual'] ?? '');
$idGolongan = old('id_golongan') ?: ($penjual['id_golongan'] ?? '');
$noHp = old('no_hp') ?: ($penjual['no_hp'] ?? '');
$lokasiLapak = old('lokasi_lapak') ?: ($penjual['lokasi_lapak'] ?? '');
$alamat = old('alamat') ?: ($penjual['alamat'] ?? '');
$statusAktif = old('status_aktif') ?: ($penjual['status_aktif'] ?? 'Aktif');
$tanggalDaftar = old('tanggal_daftar') ?: ($penjual['tanggal_daftar'] ?? $today);
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-1"><?= $isEdit ? 'Edit' : 'Tambah' ?> Penjual</h5>
                <p class="text-body-secondary mb-0">Isi data pedagang kantin dan golongan iurannya.</p>
            </div>
            <div class="card-body">
                <form action="<?= esc($action) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-4">
                        <div class="col-12 col-md-7">
                            <label for="nama_penjual" class="form-label">Nama Penjual <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_penjual" name="nama_penjual" value="<?= esc((string) $namaPenjual) ?>" maxlength="150" required autofocus>
                        </div>

                        <div class="col-12 col-md-5">
                            <label for="id_golongan" class="form-label">Golongan <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_golongan" name="id_golongan" required>
                                <option value="">Pilih golongan</option>
                                <?php foreach ($golongan as $row): ?>
                                    <option value="<?= (int) $row['id_golongan'] ?>" <?= (string) $idGolongan === (string) $row['id_golongan'] ? 'selected' : '' ?>>
                                        <?= esc($row['nama_golongan']) ?> — Rp <?= number_format((float) $row['nominal_iuran'], 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="no_hp" class="form-label">No. HP</label>
                            <input type="tel" class="form-control" id="no_hp" name="no_hp" value="<?= esc((string) $noHp) ?>" maxlength="20" inputmode="tel" placeholder="Contoh: 081234567890">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="lokasi_lapak" class="form-label">Lokasi / Lapak</label>
                            <input type="text" class="form-control" id="lokasi_lapak" name="lokasi_lapak" value="<?= esc((string) $lokasiLapak) ?>" maxlength="100" placeholder="Contoh: Kantin Utama">
                        </div>

                        <div class="col-12">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" maxlength="255" placeholder="Alamat tempat tinggal penjual"><?= esc((string) $alamat) ?></textarea>
                            <div class="form-text">Maksimal 255 karakter. Alamat ini juga akan dicetak pada Kartu Anggota.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="tanggal_daftar" class="form-label">Tanggal Bergabung <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_daftar" name="tanggal_daftar" value="<?= esc((string) $tanggalDaftar) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="status_aktif" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status_aktif" name="status_aktif" required>
                                <option value="Aktif" <?= $statusAktif === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="Nonaktif" <?= $statusAktif === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-6">
                        <a href="<?= esc($baseUrl) ?>/penjual" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layout_footer') ?>
