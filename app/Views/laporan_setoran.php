<?php
$useDataTables = true;
$dataTableSelector = '#table-laporan-setoran';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$query = http_build_query(array_filter($filter, static fn ($value): bool => $value !== null && $value !== ''));
?>
<?= $this->include('layout_header') ?>
<?= $this->include('laporan_nav') ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= esc($baseUrl) ?>/laporan/setoran">
            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-4">
                    <label class="form-label" for="tanggal_awal">Tanggal Awal</label>
                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="<?= esc($filter['tanggal_awal']) ?>">
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label" for="tanggal_akhir">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="<?= esc($filter['tanggal_akhir']) ?>">
                </div>
                <div class="col-12 col-md-4 d-flex flex-column flex-sm-row gap-2">
                    <button class="btn btn-primary flex-fill" type="submit">Terapkan Filter</button>
                    <a class="btn btn-success flex-fill" href="<?= esc($baseUrl) ?>/laporan/setoran/export?<?= esc($query) ?>">Export Excel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div><small class="text-body-secondary">Total Setoran Terfilter</small><h4 class="mb-0">Rp <?= number_format($total, 0, ',', '.') ?></h4></div>
        <span class="badge bg-label-info p-3"><i class="icon-base bx bx-transfer fs-3"></i></span>
    </div>
</div>

<div class="card">
    <div class="card-datatable table-responsive">
        <table class="table" id="table-laporan-setoran">
            <thead><tr><th>Tanggal Form</th><th>Periode Awal</th><th>Periode Akhir</th><th>Nominal</th><th>Keterangan</th><th>Operator</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td data-order="<?= esc($row['tanggal_form']) ?>"><?= esc(date('d-m-Y', strtotime($row['tanggal_form']))) ?></td>
                    <td><?= esc(date('d-m-Y', strtotime($row['periode_awal']))) ?></td>
                    <td><?= esc(date('d-m-Y', strtotime($row['periode_akhir']))) ?></td>
                    <td data-order="<?= esc((string) $row['nominal']) ?>">Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?></td>
                    <td><?= esc($row['keterangan'] ?: '-') ?></td>
                    <td><?= esc($row['nama_operator']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('layout_footer') ?>
