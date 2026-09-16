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
$buktiSetoran = $setoran['bukti_setoran'] ?? null;
$buktiUrl = $isEdit && $buktiSetoran
    ? $baseUrl . '/setoran/' . (int) $setoran['id_setoran'] . '/bukti'
    : null;
$formAction = $isEdit
    ? $baseUrl . '/setoran/' . (int) $setoran['id_setoran'] . '/update'
    : $baseUrl . '/setoran/simpan';
$saldoTersedia = (float) ($saldoTersedia ?? 0);
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
                        Gunakan hanya setelah dana benar-benar sudah diserahkan kepada pimpinan. Bukti foto wajib dilampirkan.
                    <?php endif; ?>
                </p>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2 mb-4">
                    <?= $isEdit ? 'Saldo tersedia sebelum setoran ini' : 'Saldo kas tersedia saat ini' ?>:
                    <strong>Rp <?= number_format($saldoTersedia, 0, ',', '.') ?></strong>.
                    Jika nominal setoran melebihi saldo, aplikasi akan memberi warning tetapi tetap dapat disimpan setelah konfirmasi.
                </div>

                <form action="<?= esc($formAction) ?>" method="post" enctype="multipart/form-data" id="form-setoran-resmi" data-saldo-tersedia="<?= esc((string) $saldoTersedia) ?>">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label for="tanggal_form" class="form-label">Tanggal Form <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_form" name="tanggal_form" value="<?= esc((string) $tanggalForm) ?>" max="<?= esc((string) $tanggalMaks) ?>" required>
                            <div class="form-text">Tanggal Setoran Resmi tidak boleh melebihi hari ini.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="nominal" class="form-label">Nominal Setoran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="nominal" name="nominal" value="<?= esc((string) $nominal) ?>" min="1" step="1" required>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="saldo-warning">
                            <div class="alert alert-warning mb-0">
                                <div class="fw-semibold"><i class="icon-base bx bx-error me-1"></i>Nominal setoran melebihi saldo kas tersedia.</div>
                                <div class="small mt-1" id="saldo-warning-detail"></div>
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
                            <label for="bukti_setoran" class="form-label">
                                Foto Bukti Setoran
                                <?php if (! $isEdit): ?><span class="text-danger">*</span><?php endif; ?>
                            </label>
                            <input type="file"
                                   class="form-control"
                                   id="bukti_setoran"
                                   name="bukti_setoran"
                                   accept="image/jpeg,image/png"
                                   capture="environment"
                                   <?= $isEdit ? '' : 'required' ?>>
                            <div class="form-text">
                                JPG/JPEG/PNG, maksimal 10 MB sebelum kompresi. Otomatis disimpan sebagai JPG di bawah 500 KB.
                                <?= $isEdit ? 'Kosongkan jika tidak ingin mengganti bukti yang sudah ada.' : '' ?>
                            </div>

                            <?php if ($isEdit && $buktiSetoran && $buktiUrl): ?>
                                <div class="d-flex align-items-center gap-3 mt-3 p-3 border rounded">
                                    <a href="<?= esc($buktiUrl) ?>" target="_blank" rel="noopener" class="d-block flex-shrink-0">
                                        <img src="<?= esc($buktiUrl) ?>"
                                             alt="Bukti setoran saat ini"
                                             class="rounded border"
                                             style="width:72px;height:72px;object-fit:cover;">
                                    </a>
                                    <div>
                                        <div class="fw-semibold">Bukti saat ini</div>
                                        <div class="small text-body-secondary mb-1">Bukti lama tetap dipakai jika tidak memilih foto baru.</div>
                                        <a href="<?= esc($buktiUrl) ?>" target="_blank" rel="noopener" class="small">Lihat ukuran penuh</a>
                                    </div>
                                </div>
                            <?php elseif ($isEdit): ?>
                                <div class="alert alert-warning py-2 mt-3 mb-0">
                                    Setoran lama ini belum memiliki bukti foto. Anda dapat menambahkannya sekarang.
                                </div>
                            <?php endif; ?>
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
                            Pastikan nominal, periode, dan bukti foto sesuai dengan penyerahan dana yang benar-benar sudah dilakukan.
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-setoran-resmi');
    const nominalInput = document.getElementById('nominal');
    const warningBox = document.getElementById('saldo-warning');
    const warningDetail = document.getElementById('saldo-warning-detail');

    if (!form || !nominalInput) {
        return;
    }

    const saldoTersedia = Number(form.dataset.saldoTersedia || 0);
    const rupiah = new Intl.NumberFormat('id-ID');

    function exceedsBalance() {
        return Number(nominalInput.value || 0) > saldoTersedia;
    }

    function syncWarning() {
        const nominal = Number(nominalInput.value || 0);
        const over = nominal > saldoTersedia;

        if (warningBox) {
            warningBox.classList.toggle('d-none', !over);
        }
        if (warningDetail && over) {
            warningDetail.textContent = 'Setoran Rp ' + rupiah.format(nominal)
                + ' melebihi saldo tersedia Rp ' + rupiah.format(saldoTersedia)
                + '. Jika tetap disimpan, saldo kas akan menjadi negatif.';
        }
    }

    nominalInput.addEventListener('input', syncWarning);
    syncWarning();

    form.addEventListener('submit', function (event) {
        if (!exceedsBalance() || form.dataset.saldoConfirmed === '1') {
            return;
        }

        event.preventDefault();
        const nominal = Number(nominalInput.value || 0);
        const text = 'Nominal setoran Rp ' + rupiah.format(nominal)
            + ' melebihi saldo tersedia Rp ' + rupiah.format(saldoTersedia)
            + '. Setoran tetap boleh disimpan dan akan membuat saldo kas negatif. Lanjutkan?';

        if (!window.Swal) {
            if (window.confirm(text)) {
                form.dataset.saldoConfirmed = '1';
                form.requestSubmit();
            }
            return;
        }

        Swal.fire({
            title: 'Setoran melebihi saldo',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Tetap simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                form.dataset.saldoConfirmed = '1';
                form.requestSubmit();
            }
        });
    });
});
</script>

<?= $this->include('layout_footer') ?>
