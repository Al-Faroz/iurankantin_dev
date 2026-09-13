<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$namaMadrasah = old('nama_madrasah') ?: ($setting['nama_madrasah'] ?? '');
$alamatMadrasah = old('alamat_madrasah') ?: ($setting['alamat_madrasah'] ?? '');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<form action="<?= esc($baseUrl) ?>/setting/update" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row g-6">
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-1">Data Madrasah</h5>
                    <p class="text-body-secondary mb-0">Dipakai pada header PDF dan identitas aplikasi.</p>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="nama_madrasah" class="form-label">Nama Madrasah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_madrasah" name="nama_madrasah" value="<?= esc((string) $namaMadrasah) ?>" maxlength="150" required>
                    </div>

                    <div class="mb-4">
                        <label for="alamat_madrasah" class="form-label">Alamat Madrasah</label>
                        <textarea class="form-control" id="alamat_madrasah" name="alamat_madrasah" rows="3" maxlength="255"><?= esc((string) $alamatMadrasah) ?></textarea>
                    </div>

                    <div>
                        <label for="logo" class="form-label">Logo Madrasah</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/jpeg,image/png">
                        <div class="form-text">JPG/JPEG/PNG, maksimal 5 MB. Kosongkan jika tidak ingin mengganti.</div>
                        <?php if (! empty($setting['logo'])): ?>
                            <div class="mt-3">
                                <img src="<?= esc($baseUrl . '/' . ltrim($setting['logo'], '/')) ?>" alt="Logo madrasah" class="rounded border p-2 bg-white" style="max-width:120px;max-height:120px;object-fit:contain">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-1">Background Kartu Anggota</h5>
                    <p class="text-body-secondary mb-0">Desain dapat diganti tanpa mengubah kode aplikasi.</p>
                </div>
                <div class="card-body">
                    <div class="mb-5">
                        <label for="background_kartu_depan" class="form-label">Background Kartu Depan</label>
                        <input type="file" class="form-control" id="background_kartu_depan" name="background_kartu_depan" accept="image/jpeg,image/png">
                        <div class="form-text">Disarankan rasio 1011×638 px.</div>
                        <?php if (! empty($setting['background_kartu_depan'])): ?>
                            <img src="<?= esc($baseUrl . '/' . ltrim($setting['background_kartu_depan'], '/')) ?>" alt="Background kartu depan" class="img-fluid rounded border mt-3">
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="background_kartu_belakang" class="form-label">Background Kartu Belakang</label>
                        <input type="file" class="form-control" id="background_kartu_belakang" name="background_kartu_belakang" accept="image/jpeg,image/png">
                        <div class="form-text">Sisi belakang bersifat statis dan sepenuhnya mengikuti gambar ini.</div>
                        <?php if (! empty($setting['background_kartu_belakang'])): ?>
                            <img src="<?= esc($baseUrl . '/' . ltrim($setting['background_kartu_belakang'], '/')) ?>" alt="Background kartu belakang" class="img-fluid rounded border mt-3">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-5">
        <button type="submit" class="btn btn-primary btn-lg"><i class="icon-base bx bx-save me-1"></i>Simpan Setting</button>
    </div>
</form>

<?= $this->include('layout_footer') ?>
