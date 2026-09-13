<?php
$useDataTables = true;
$dataTableSelector = '#table-laporan-pengeluaran';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$query = http_build_query(array_filter($filter, static fn ($value): bool => $value !== null && $value !== ''));
?>
<?= $this->include('layout_header') ?>
<?= $this->include('laporan_nav') ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= esc($baseUrl) ?>/laporan/pengeluaran">
            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label" for="tanggal_awal">Tanggal Awal</label>
                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="<?= esc($filter['tanggal_awal']) ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="tanggal_akhir">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="<?= esc($filter['tanggal_akhir']) ?>">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="id_kategori_keluar">Kategori</label>
                    <select class="form-select" id="id_kategori_keluar" name="id_kategori_keluar">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kategoriOptions as $option): ?>
                            <option value="<?= (int) $option['id_kategori_keluar'] ?>" <?= (string) ($filter['id_kategori_keluar'] ?? '') === (string) $option['id_kategori_keluar'] ? 'selected' : '' ?>><?= esc($option['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                <button class="btn btn-primary" type="submit"><i class="icon-base bx bx-filter-alt me-1"></i>Terapkan Filter</button>
                <a class="btn btn-success" href="<?= esc($baseUrl) ?>/laporan/pengeluaran/export?<?= esc($query) ?>"><i class="icon-base bx bx-spreadsheet me-1"></i>Export Excel</a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div><small class="text-body-secondary">Total Pengeluaran Terfilter</small><h4 class="mb-0">Rp <?= number_format($total, 0, ',', '.') ?></h4></div>
        <span class="badge bg-label-danger p-3"><i class="icon-base bx bx-receipt fs-3"></i></span>
    </div>
</div>

<div class="card">
    <div class="card-datatable table-responsive">
        <table class="table" id="table-laporan-pengeluaran">
            <thead><tr><th>Tanggal</th><th>Kategori</th><th>Nominal</th><th>Keterangan</th><th>Nota</th><th>Operator</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td data-order="<?= esc($row['tanggal']) ?>"><?= esc(date('d-m-Y', strtotime($row['tanggal']))) ?></td>
                    <td><?= esc($row['nama_kategori']) ?></td>
                    <td data-order="<?= esc((string) $row['nominal']) ?>">Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?></td>
                    <td><?= esc($row['keterangan'] ?: '-') ?></td>
                    <td><?php if ($row['bukti_nota']): ?><a href="<?= esc($baseUrl . '/' . ltrim($row['bukti_nota'], '/')) ?>" target="_blank" rel="noopener">Lihat</a><?php else: ?>-<?php endif; ?></td>
                    <td><?= esc($row['nama_operator']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('layout_footer') ?>
