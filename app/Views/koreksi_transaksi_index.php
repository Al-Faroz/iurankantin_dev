<?php
$useDataTables = true;
$dataTableSelector = '.table-koreksi';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$iuranPerTanggal = [];
foreach ($iuran as $row) {
    $iuranPerTanggal[$row['tanggal']][] = $row;
}
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<style>
    .koreksi-iuran-table .seller-name { min-width: 220px; }
    .koreksi-iuran-table .nominal-cell { min-width: 140px; }
    .koreksi-date-block + .koreksi-date-block { border-top: 1px solid var(--bs-border-color); }

    @media (max-width: 767.98px) {
        .koreksi-iuran-table thead { display: none; }
        .koreksi-iuran-table,
        .koreksi-iuran-table tbody { display: block; width: 100%; }
        .koreksi-iuran-table tr {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr) 112px;
            align-items: center;
            gap: .55rem;
            width: 100%;
            border-bottom: 1px solid var(--bs-border-color);
            padding: .65rem .1rem;
        }
        .koreksi-iuran-table tr:last-child { border-bottom: 0; }
        .koreksi-iuran-table td { display: block; width: auto; border: 0; padding: 0; }
        .koreksi-iuran-table .seller-name { min-width: 0; overflow: hidden; }
        .koreksi-iuran-table .seller-line {
            display: flex;
            align-items: center;
            gap: .35rem;
            min-width: 0;
            white-space: nowrap;
        }
        .koreksi-iuran-table .seller-text {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .koreksi-iuran-table .seller-meta { display: none; }
        .koreksi-iuran-table .operator-cell { display: none; }
        .koreksi-iuran-table .nominal-cell {
            min-width: 0;
            text-align: right;
            font-size: .875rem;
        }
    }
</style>

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
                <?php if ($iuranPerTanggal): ?>
                    <div class="border rounded overflow-hidden">
                        <?php foreach ($iuranPerTanggal as $tanggal => $rowsTanggal): ?>
                            <?php
                            $totalTanggal = array_sum(array_map(static fn (array $item): float => (float) $item['nominal'], $rowsTanggal));
                            ?>
                            <section class="koreksi-date-block">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-1 px-3 py-2 bg-lighter">
                                    <div>
                                        <span class="fw-semibold"><?= esc(date('d-m-Y', strtotime($tanggal))) ?></span>
                                        <span class="text-body-secondary small ms-1"><?= count($rowsTanggal) ?> transaksi</span>
                                    </div>
                                    <span class="small fw-semibold">Rp <?= number_format($totalTanggal, 0, ',', '.') ?></span>
                                </div>
                                <div class="table-responsive px-3">
                                    <table class="table koreksi-iuran-table align-middle mb-0" data-datatable="false">
                                        <thead>
                                            <tr>
                                                <th style="width:80px">Koreksi</th>
                                                <th>Penjual / Golongan</th>
                                                <th>Nominal</th>
                                                <th>Operator</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($rowsTanggal as $row): ?>
                                            <?php
                                            $namaGolongan = trim((string) ($row['nama_golongan'] ?? ''));
                                            $golonganSingkat = $namaGolongan ?: '-';
                                            if (preg_match('/^Golongan\s*(.+)$/i', $namaGolongan, $match) === 1) {
                                                $kode = preg_replace('/\s+/', '', trim((string) ($match[1] ?? '')));
                                                if ($kode !== '') {
                                                    $golonganSingkat = 'G' . $kode;
                                                }
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <form action="<?= esc($baseUrl) ?>/koreksi-transaksi/iuran/<?= (int) $row['id_transaksi'] ?>/hapus" method="post"
                                                          data-confirm-title="Hapus transaksi iuran?"
                                                          data-confirm-text="Iuran <?= esc($row['nama_penjual'], 'attr') ?> tanggal <?= esc(date('d-m-Y', strtotime($row['tanggal'])), 'attr') ?> sebesar Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?> akan dihapus permanen."
                                                          data-confirm-button="Ya, hapus permanen">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="tanggal_awal" value="<?= esc($filter['tanggal_awal']) ?>">
                                                        <input type="hidden" name="tanggal_akhir" value="<?= esc($filter['tanggal_akhir']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus transaksi salah">
                                                            <i class="icon-base bx bx-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="seller-name">
                                                    <div class="seller-line">
                                                        <span class="seller-text fw-semibold"><?= esc($row['nama_penjual']) ?></span>
                                                        <span class="badge bg-label-primary flex-shrink-0" title="<?= esc($namaGolongan) ?>"><?= esc($golonganSingkat) ?></span>
                                                    </div>
                                                    <small class="seller-meta text-body-secondary">
                                                        <?= esc($row['lokasi_lapak'] ?: 'Lokasi belum diisi') ?>
                                                        <?php if (! empty($row['keterangan'])): ?> · <?= esc($row['keterangan']) ?><?php endif; ?>
                                                    </small>
                                                </td>
                                                <td class="nominal-cell fw-semibold">Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?></td>
                                                <td class="operator-cell"><span class="small text-body-secondary"><?= esc($row['nama_operator']) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-body-secondary py-5">
                        Tidak ada transaksi iuran pada periode ini.
                    </div>
                <?php endif; ?>
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
