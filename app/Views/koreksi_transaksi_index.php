<?php
$useDataTables = true;
$dataTableSelector = '.table-koreksi';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="alert alert-warning d-flex gap-2" role="alert">
    <i class="icon-base bx bx-error-circle fs-4 mt-1"></i>
    <div>
        <div class="fw-semibold">Gunakan hanya untuk transaksi yang benar-benar salah input.</div>
        <div class="small">Koreksi di halaman ini menghapus transaksi secara permanen dan langsung mengubah saldo kas. Tindakan tidak dapat dibatalkan.</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= esc($baseUrl) ?>/koreksi-transaksi" class="row g-3 align-items-end">
            <div class="col-12 col-sm-5">
                <label class="form-label" for="tanggal_awal">Tanggal Awal</label>
                <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" value="<?= esc($filter['tanggal_awal']) ?>" required>
            </div>
            <div class="col-12 col-sm-5">
                <label class="form-label" for="tanggal_akhir">Tanggal Akhir</label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" value="<?= esc($filter['tanggal_akhir']) ?>" required>
            </div>
            <div class="col-12 col-sm-2 d-grid">
                <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-filter-alt me-1"></i>Terapkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-1">Koreksi Transaksi</h5>
        <p class="text-body-secondary mb-0">Periode <?= esc(date('d-m-Y', strtotime($filter['tanggal_awal']))) ?> s.d. <?= esc(date('d-m-Y', strtotime($filter['tanggal_akhir']))) ?>.</p>
    </div>
    <div class="card-body pt-0">
        <ul class="nav nav-pills flex-column flex-sm-row gap-2 mb-4" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-iuran" type="button" role="tab">Iuran (<?= count($iuran) ?>)</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pengeluaran" type="button" role="tab">Pengeluaran (<?= count($pengeluaran) ?>)</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-setoran" type="button" role="tab">Setoran (<?= count($setoran) ?>)</button></li>
        </ul>

        <div class="tab-content p-0">
            <div class="tab-pane fade show active" id="tab-iuran" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-koreksi">
                        <thead><tr><th>Tanggal</th><th>Penjual</th><th>Nominal</th><th>Keterangan</th><th>Operator</th><th class="no-sort text-center">Koreksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($iuran as $row): ?>
                            <tr>
                                <td data-order="<?= esc($row['tanggal']) ?>"><?= esc(date('d-m-Y', strtotime($row['tanggal']))) ?></td>
                                <td><?= esc($row['nama_penjual']) ?></td>
                                <td data-order="<?= esc((string) $row['nominal']) ?>">Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?></td>
                                <td><?= esc($row['keterangan'] ?: '-') ?></td>
                                <td><?= esc($row['nama_operator']) ?></td>
                                <td class="text-center">
                                    <form action="<?= esc($baseUrl) ?>/koreksi-transaksi/iuran/<?= (int) $row['id_transaksi'] ?>/hapus" method="post" class="d-inline"
                                          data-confirm-title="Hapus transaksi iuran?"
                                          data-confirm-text="Transaksi <?= esc($row['nama_penjual'], 'attr') ?> sebesar Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?> akan dihapus permanen."
                                          data-confirm-button="Ya, hapus permanen">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="tanggal_awal" value="<?= esc($filter['tanggal_awal']) ?>">
                                        <input type="hidden" name="tanggal_akhir" value="<?= esc($filter['tanggal_akhir']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus permanen"><i class="icon-base bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-pengeluaran" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-koreksi">
                        <thead><tr><th>Tanggal</th><th>Kategori</th><th>Nominal</th><th>Keterangan</th><th>Operator</th><th class="no-sort text-center">Koreksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($pengeluaran as $row): ?>
                            <tr>
                                <td data-order="<?= esc($row['tanggal']) ?>"><?= esc(date('d-m-Y', strtotime($row['tanggal']))) ?></td>
                                <td><?= esc($row['nama_kategori']) ?></td>
                                <td data-order="<?= esc((string) $row['nominal']) ?>">Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?></td>
                                <td><?= esc($row['keterangan'] ?: '-') ?></td>
                                <td><?= esc($row['nama_operator']) ?></td>
                                <td class="text-center">
                                    <form action="<?= esc($baseUrl) ?>/koreksi-transaksi/pengeluaran/<?= (int) $row['id_pengeluaran'] ?>/hapus" method="post" class="d-inline"
                                          data-confirm-title="Hapus transaksi pengeluaran?"
                                          data-confirm-text="Pengeluaran <?= esc($row['nama_kategori'], 'attr') ?> sebesar Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?> akan dihapus permanen dan nota terkait ikut dibersihkan."
                                          data-confirm-button="Ya, hapus permanen">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="tanggal_awal" value="<?= esc($filter['tanggal_awal']) ?>">
                                        <input type="hidden" name="tanggal_akhir" value="<?= esc($filter['tanggal_akhir']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus permanen"><i class="icon-base bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-setoran" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-koreksi">
                        <thead><tr><th>Tanggal Form</th><th>Periode</th><th>Nominal</th><th>Keterangan</th><th>Operator</th><th class="no-sort text-center">Koreksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($setoran as $row): ?>
                            <tr>
                                <td data-order="<?= esc($row['tanggal_form']) ?>"><?= esc(date('d-m-Y', strtotime($row['tanggal_form']))) ?></td>
                                <td><?= esc(date('d-m-Y', strtotime($row['periode_awal']))) ?> s.d. <?= esc(date('d-m-Y', strtotime($row['periode_akhir']))) ?></td>
                                <td data-order="<?= esc((string) $row['nominal']) ?>">Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?></td>
                                <td><?= esc($row['keterangan'] ?: '-') ?></td>
                                <td><?= esc($row['nama_operator']) ?></td>
                                <td class="text-center">
                                    <form action="<?= esc($baseUrl) ?>/koreksi-transaksi/setoran/<?= (int) $row['id_setoran'] ?>/hapus" method="post" class="d-inline"
                                          data-confirm-title="Hapus transaksi setoran?"
                                          data-confirm-text="Setoran sebesar Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?> akan dihapus permanen dan saldo kas akan dihitung kembali."
                                          data-confirm-button="Ya, hapus permanen">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="tanggal_awal" value="<?= esc($filter['tanggal_awal']) ?>">
                                        <input type="hidden" name="tanggal_akhir" value="<?= esc($filter['tanggal_akhir']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus permanen"><i class="icon-base bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layout_footer') ?>
