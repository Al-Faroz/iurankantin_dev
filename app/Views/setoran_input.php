<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$setoran = $setoran ?? null;
$isEdit = $setoran !== null;
$tanggalForm = old('tanggal_form') ?: ($setoran['tanggal_form'] ?? $tanggalDefault);
$periodeAwal = old('periode_awal') ?: ($setoran['periode_awal'] ?? '');
$periodeAkhir = old('periode_akhir') ?: ($setoran['periode_akhir'] ?? '');
$nominal = old('nominal') ?: ($setoran['nominal'] ?? '');
$keterangan = old('keterangan');
if ($keterangan === null) {
    $keterangan = $setoran['keterangan'] ?? '';
}
$formAction = $isEdit
    ? $baseUrl . '/setoran/' . (int) $setoran['id_setoran'] . '/update'
    : $baseUrl . '/setoran/simpan';
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-primary">
            <div class="card-header">
                <h5 class="mb-1"><?= $isEdit ? 'Edit Setoran Pimpinan' : 'Tahap 2 — Input Setoran Resmi' ?></h5>
                <p class="text-body-secondary mb-0">
                    <?php if ($isEdit): ?>
                        Perbaiki data setoran yang sudah tercatat. Perubahan langsung memengaruhi perhitungan saldo kas.
                    <?php else: ?>
                        Gunakan hanya setelah dana benar-benar sudah diserahkan kepada pimpinan. Data ini akan mengurangi saldo kas.
                    <?php endif; ?>
                </p>
            </div>
            <div class="card-body">
                <form action="<?= esc($formAction) ?>" method="post" id="form-setoran-resmi">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label for="tanggal_form" class="form-label">Tanggal Form <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_form" name="tanggal_form" value="<?= esc((string) $tanggalForm) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="nominal" class="form-label">Nominal Setoran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="nominal" name="nominal" value="<?= esc((string) $nominal) ?>" min="1" step="1" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="periode_awal" class="form-label">Periode Awal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="periode_awal" name="periode_awal" value="<?= esc((string) $periodeAwal) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="periode_akhir" class="form-label">Periode Akhir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="periode_akhir" name="periode_akhir" value="<?= esc((string) $periodeAkhir) ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" maxlength="255" placeholder="Opsional"><?= esc((string) $keterangan) ?></textarea>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-5 mb-0">
                        <?php if ($isEdit): ?>
                            Pastikan perubahan memang merupakan koreksi data. Nominal setoran ikut menentukan saldo kas berjalan.
                        <?php else: ?>
                            Pastikan nominal dan periode sama dengan form fisik yang telah diserahkan. Setelah disimpan, transaksi masuk perhitungan saldo kas.
                        <?php endif; ?>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-5">
                        <a href="<?= esc($baseUrl) ?>/setoran" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base bx bx-save me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Setoran Resmi' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layout_footer') ?>
