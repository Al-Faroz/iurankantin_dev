<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$isEdit = is_array($golongan);
$action = $isEdit
    ? $baseUrl . '/golongan/' . (int) $golongan['id_golongan'] . '/update'
    : $baseUrl . '/golongan/simpan';
$namaGolongan = old('nama_golongan') ?: ($golongan['nama_golongan'] ?? '');
$nominalIuran = old('nominal_iuran') ?: ($golongan['nominal_iuran'] ?? '');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-1"><?= $isEdit ? 'Edit' : 'Tambah' ?> Golongan Penjual</h5>
                <p class="text-body-secondary mb-0">Atur nama golongan dan nominal iuran default.</p>
            </div>
            <div class="card-body">
                <form action="<?= esc($action) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label for="nama_golongan" class="form-label">Nama Golongan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_golongan" name="nama_golongan" value="<?= esc((string) $namaGolongan) ?>" maxlength="50" required>
                    </div>

                    <div class="mb-6">
                        <label for="nominal_iuran" class="form-label">Nominal Iuran <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="nominal_iuran" name="nominal_iuran" value="<?= esc((string) $nominalIuran) ?>" min="1" step="1" required>
                        </div>
                        <div class="form-text">Nominal ini hanya menjadi prefill pada form iuran harian dan tetap dapat diubah per transaksi.</div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                        <a href="<?= esc($baseUrl) ?>/golongan" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base bx bx-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layout_footer') ?>
