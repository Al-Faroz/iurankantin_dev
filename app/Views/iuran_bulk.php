<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$oldBayar = old('bayar');
$oldBayar = is_array($oldBayar) ? $oldBayar : [];
$oldNominal = old('nominal');
$oldNominal = is_array($oldNominal) ? $oldNominal : [];
$oldKeterangan = old('keterangan');
$oldKeterangan = is_array($oldKeterangan) ? $oldKeterangan : [];
$tanggal = old('tanggal') ?: $tanggalDefault;
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<style>
    .bulk-table .seller-name { min-width: 190px; }
    .bulk-table .nominal-input { min-width: 150px; }
    .bulk-table .note-input { min-width: 180px; }
    .bulk-sticky { position: sticky; bottom: 0; z-index: 10; }

    @media (max-width: 767.98px) {
        .bulk-table thead { display: none; }
        .bulk-table, .bulk-table tbody, .bulk-table tr, .bulk-table td { display: block; width: 100%; }
        .bulk-table tr { border: 1px solid var(--bs-border-color); border-radius: .5rem; margin-bottom: .75rem; padding: .75rem; background: var(--bs-body-bg); }
        .bulk-table td { border: 0; padding: .35rem 0; }
        .bulk-table td[data-label]::before { content: attr(data-label); display: block; font-size: .75rem; color: var(--bs-secondary-color); margin-bottom: .2rem; }
        .bulk-table .seller-name { min-width: 0; }
        .bulk-table .nominal-input, .bulk-table .note-input { min-width: 0; }
    }
</style>

<form action="<?= esc($baseUrl) ?>/iuran/simpan" method="post" id="form-iuran">
    <?= csrf_field() ?>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-end g-3">
                <div class="col-12 col-md-4">
                    <label for="tanggal" class="form-label">Tanggal Iuran <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= esc((string) $tanggal) ?>" required>
                </div>
                <div class="col-12 col-md-8">
                    <div class="alert alert-primary mb-0 py-2">
                        Centang <strong>Bayar</strong> hanya untuk penjual yang membayar hari ini. Nominal terisi otomatis dari golongan dan tetap bisa diubah.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-1">Daftar Penjual Aktif</h5>
            <p class="text-body-secondary mb-0">Urutan: nominal golongan tertinggi, lalu nama penjual A–Z.</p>
        </div>

        <?php if ($penjual): ?>
            <div class="table-responsive px-3 pb-3">
                <table class="table bulk-table align-middle">
                    <thead>
                        <tr>
                            <th style="width:80px">Bayar</th>
                            <th>Penjual</th>
                            <th>Golongan</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($penjual as $row): ?>
                        <?php
                        $id = (int) $row['id_penjual'];
                        $checked = array_key_exists((string) $id, $oldBayar) || array_key_exists($id, $oldBayar);
                        $nominalValue = $oldNominal[$id] ?? $oldNominal[(string) $id] ?? $row['nominal_iuran'];
                        $ketValue = $oldKeterangan[$id] ?? $oldKeterangan[(string) $id] ?? '';
                        ?>
                        <tr>
                            <td data-label="Status Bayar">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input iuran-check" type="checkbox" name="bayar[<?= $id ?>]" value="1" id="bayar-<?= $id ?>" <?= $checked ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="bayar-<?= $id ?>">Bayar</label>
                                </div>
                            </td>
                            <td data-label="Penjual" class="seller-name">
                                <div class="fw-semibold"><?= esc($row['nama_penjual']) ?></div>
                                <small class="text-body-secondary"><?= esc($row['lokasi_lapak'] ?: 'Lokasi belum diisi') ?></small>
                            </td>
                            <td data-label="Golongan">
                                <span class="badge bg-label-primary"><?= esc($row['nama_golongan']) ?></span>
                            </td>
                            <td data-label="Nominal">
                                <div class="input-group nominal-input">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control iuran-nominal" name="nominal[<?= $id ?>]" value="<?= esc((string) $nominalValue) ?>" min="1" step="1" <?= $checked ? '' : 'disabled' ?>>
                                </div>
                            </td>
                            <td data-label="Keterangan">
                                <input type="text" class="form-control iuran-keterangan note-input" name="keterangan[<?= $id ?>]" value="<?= esc((string) $ketValue) ?>" maxlength="255" placeholder="Opsional" <?= $checked ? '' : 'disabled' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card-body text-center text-body-secondary py-5">
                Belum ada penjual aktif. Tambahkan atau aktifkan penjual terlebih dahulu.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($penjual): ?>
        <div class="bulk-sticky bg-body mt-4 py-3 border-top">
            <div class="container-fluid px-0 d-flex flex-column flex-sm-row gap-3 align-items-sm-center justify-content-between">
                <div>
                    <div class="small text-body-secondary">Terpilih</div>
                    <div class="fw-semibold"><span id="selected-count">0</span> penjual &middot; Rp <span id="selected-total">0</span></div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="icon-base bx bx-save me-1"></i>Simpan Iuran
                </button>
            </div>
        </div>
    <?php endif; ?>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checks = document.querySelectorAll('.iuran-check');
    const countEl = document.getElementById('selected-count');
    const totalEl = document.getElementById('selected-total');

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(value || 0);
    }

    function recalc() {
        let count = 0;
        let total = 0;

        checks.forEach(function (check) {
            const row = check.closest('tr');
            const nominal = row.querySelector('.iuran-nominal');
            if (check.checked) {
                count++;
                total += Number(nominal.value || 0);
            }
        });

        if (countEl) countEl.textContent = count;
        if (totalEl) totalEl.textContent = formatNumber(total);
    }

    checks.forEach(function (check) {
        const row = check.closest('tr');
        const nominal = row.querySelector('.iuran-nominal');
        const note = row.querySelector('.iuran-keterangan');

        function sync() {
            nominal.disabled = !check.checked;
            note.disabled = !check.checked;
            recalc();
        }

        check.addEventListener('change', sync);
        nominal.addEventListener('input', recalc);
        sync();
    });
});
</script>

<?= $this->include('layout_footer') ?>
