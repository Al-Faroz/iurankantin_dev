<?php
$useDataTables = true;
$dataTableSelector = '#table-kartu';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$backgroundDepanSiap = ! empty($setting['background_kartu_depan']);
$backgroundBelakangSiap = ! empty($setting['background_kartu_belakang']);
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<?php if (! $backgroundDepanSiap || ! $backgroundBelakangSiap): ?>
    <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
        <i class="icon-base bx bx-error-circle fs-4 mt-1"></i>
        <div>
            <div class="fw-semibold">Background kartu belum lengkap.</div>
            <div class="small">
                Upload background kartu depan dan belakang melalui
                <a href="<?= esc($baseUrl) ?>/setting" class="alert-link">Setting</a>
                sebelum melakukan download kartu.
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4 mb-6">
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-body d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-1">Kartu Anggota Kantin</h5>
                    <p class="text-body-secondary mb-0">
                        QR kartu akan membuka verifikasi publik, atau detail penjual jika discan oleh Operator yang sudah login.
                    </p>
                </div>
                <a href="<?= esc($baseUrl) ?>/kartu/scan" class="btn btn-primary text-nowrap">
                    <i class="icon-base bx bx-qr-scan me-1"></i>Scan Kartu
                </a>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="mb-3">Download Massal</h6>
                <div class="d-grid gap-2">
                    <form action="<?= esc($baseUrl) ?>/kartu/download/semua-depan.zip" method="post" class="d-grid">
                        <?= csrf_field() ?>
                        <button type="submit"
                                class="btn btn-outline-primary"
                                <?= $backgroundDepanSiap ? '' : 'disabled' ?>>
                            <i class="icon-base bx bx-archive-in me-1"></i>Semua Depan
                        </button>
                    </form>
                    <form action="<?= esc($baseUrl) ?>/kartu/download/semua-lengkap.zip" method="post" class="d-grid">
                        <?= csrf_field() ?>
                        <button type="submit"
                                class="btn btn-outline-secondary"
                                <?= ($backgroundDepanSiap && $backgroundBelakangSiap) ? '' : 'disabled' ?>>
                            <i class="icon-base bx bx-archive me-1"></i>Semua Lengkap
                        </button>
                    </form>
                </div>
                <div class="form-text mt-2">Download massal dapat membuat kode untuk Penjual aktif yang belum memiliki kartu.</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-1">Daftar Kartu Penjual</h5>
        <p class="text-body-secondary mb-0">Kode kartu bersifat tetap. Regenerate hanya mengganti token verifikasi sehingga QR lama tidak berlaku.</p>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table" id="table-kartu">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Penjual</th>
                    <th>Golongan</th>
                    <th>Status</th>
                    <th>Kode Kartu</th>
                    <th class="no-sort text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($penjual as $index => $row): ?>
                <?php
                $id = (int) $row['id_penjual'];
                $sudahAdaKode = ! empty($row['kode_kartu']) && ! empty($row['kode_verifikasi']);
                ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <a href="<?= esc($baseUrl) ?>/penjual/<?= $id ?>" class="fw-semibold text-heading">
                            <?= esc($row['nama_penjual']) ?>
                        </a>
                        <div class="small text-body-secondary"><?= esc($row['lokasi_lapak'] ?: '-') ?></div>
                    </td>
                    <td><?= esc($row['nama_golongan'] ?? '-') ?></td>
                    <td>
                        <?php if ($row['status_aktif'] === 'Aktif'): ?>
                            <span class="badge bg-label-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-label-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($sudahAdaKode): ?>
                            <code><?= esc($row['kode_kartu']) ?></code>
                        <?php else: ?>
                            <span class="badge bg-label-warning">Belum dibuat</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-nowrap">
                        <?php if (! $sudahAdaKode): ?>
                            <form action="<?= esc($baseUrl) ?>/kartu/<?= $id ?>/generate" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-primary" title="Buat kode kartu dan QR">
                                    <i class="icon-base bx bx-qr"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?= esc($baseUrl) ?>/kartu/<?= $id ?>/depan.jpg"
                               class="btn btn-sm btn-outline-primary<?= $backgroundDepanSiap ? '' : ' disabled' ?>"
                               title="Download JPG depan">
                                <i class="icon-base bx bx-image"></i>
                            </a>
                            <a href="<?= esc($baseUrl) ?>/kartu/<?= $id ?>/lengkap.zip"
                               class="btn btn-sm btn-outline-secondary<?= ($backgroundDepanSiap && $backgroundBelakangSiap) ? '' : ' disabled' ?>"
                               title="Download depan dan belakang ZIP">
                                <i class="icon-base bx bx-download"></i>
                            </a>
                            <form action="<?= esc($baseUrl) ?>/kartu/<?= $id ?>/regenerate" method="post" class="d-inline form-regenerate" data-nama="<?= esc($row['nama_penjual'], 'attr') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Regenerate token QR">
                                    <i class="icon-base bx bx-refresh"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.form-regenerate').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var nama = form.dataset.nama || 'penjual ini';
            if (! window.confirm('Regenerate QR ' + nama + '? QR lama akan langsung tidak berlaku.')) {
                event.preventDefault();
            }
        });
    });
});
</script>

<?= $this->include('layout_footer') ?>
