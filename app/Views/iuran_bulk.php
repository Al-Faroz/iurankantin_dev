<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$oldBayar = old('bayar');
$oldBayar = is_array($oldBayar) ? $oldBayar : [];
$oldNominal = old('nominal');
$oldNominal = is_array($oldNominal) ? $oldNominal : [];
$tanggal = old('tanggal') ?: $tanggalDefault;
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<style>
    .bulk-table .seller-name { min-width: 220px; }
    .bulk-table .nominal-input { min-width: 150px; }
    .bulk-sticky { position: sticky; bottom: 0; z-index: 10; }

    @media (max-width: 767.98px) {
        .bulk-table thead { display: none; }
        .bulk-table,
        .bulk-table tbody { display: block; width: 100%; }
        .bulk-table tr {
            display: grid;
            grid-template-columns: 54px minmax(0, 1fr) 118px;
            align-items: center;
            gap: .55rem;
            width: 100%;
            border-bottom: 1px solid var(--bs-border-color);
            padding: .65rem .1rem;
        }
        .bulk-table tr:last-child { border-bottom: 0; }
        .bulk-table td { display: block; width: auto; border: 0; padding: 0; }
        .bulk-table td[data-label]::before { display: none; }
        .bulk-table .seller-name { min-width: 0; overflow: hidden; }
        .bulk-table .seller-line {
            display: flex;
            align-items: center;
            gap: .35rem;
            min-width: 0;
            white-space: nowrap;
        }
        .bulk-table .seller-line .seller-text {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }
        .bulk-table .seller-location { display: none; }
        .bulk-table .nominal-input { min-width: 0; width: 118px; }
        .bulk-table .nominal-input .input-group-text { padding-left: .45rem; padding-right: .45rem; }
        .bulk-table .nominal-input .form-control { min-width: 0; padding-left: .45rem; padding-right: .35rem; }
        .bulk-table .form-check-label { display: none; }
        .bulk-table .form-check { padding-left: 2.35rem; }
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
                <table class="table bulk-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:80px">Bayar</th>
                            <th>Penjual / Golongan</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($penjual as $row): ?>
                        <?php
                        $id = (int) $row['id_penjual'];
                        $checked = array_key_exists((string) $id, $oldBayar) || array_key_exists($id, $oldBayar);
                        $nominalValue = $oldNominal[$id] ?? $oldNominal[(string) $id] ?? $row['nominal_iuran'];
                        $namaGolongan = trim((string) $row['nama_golongan']);
                        $golonganSingkat = $namaGolongan;
                        if (preg_match('/^Golongan\s*(.+)$/i', $namaGolongan, $match) === 1) {
                            $kode = preg_replace('/\s+/', '', trim((string) ($match[1] ?? '')));
                            if ($kode !== '') {
                                $golonganSingkat = 'G' . $kode;
                            }
                        }
                        ?>
                        <tr>
                            <td data-label="Bayar">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input iuran-check" type="checkbox" name="bayar[<?= $id ?>]" value="1" id="bayar-<?= $id ?>" <?= $checked ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="bayar-<?= $id ?>">Bayar</label>
                                </div>
                            </td>
                            <td data-label="Penjual" class="seller-name">
                                <div class="seller-line">
                                    <span class="seller-text fw-semibold"><?= esc($row['nama_penjual']) ?></span>
                                    <span class="badge bg-label-primary flex-shrink-0" title="<?= esc($namaGolongan) ?>"><?= esc($golonganSingkat) ?></span>
                                </div>
                                <small class="seller-location text-body-secondary"><?= esc($row['lokasi_lapak'] ?: 'Lokasi belum diisi') ?></small>
                            </td>
                            <td data-label="Nominal">
                                <div class="input-group nominal-input">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control iuran-nominal" name="nominal[<?= $id ?>]" value="<?= esc((string) $nominalValue) ?>" min="1" step="1" <?= $checked ? '' : 'disabled' ?>>
                                </div>
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

        function sync() {
            nominal.disabled = !check.checked;
            recalc();
        }

        check.addEventListener('change', sync);
        nominal.addEventListener('input', recalc);
        sync();
    });
});
</script>

<?= $this->include('layout_footer') ?>
