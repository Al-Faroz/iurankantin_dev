<?php
$useDataTables = true;
$dataTableSelector = '#table-rekap-kas';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$query = http_build_query(array_filter($filter, static fn ($value): bool => $value !== null && $value !== ''));
?>
<?= $this->include('layout_header') ?>
<?= $this->include('laporan_nav') ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= esc($baseUrl) ?>/laporan/rekap-kas">
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
                    <a class="btn btn-success flex-fill" href="<?= esc($baseUrl) ?>/laporan/rekap-kas/export?<?= esc($query) ?>">Export Excel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-6">
        <div class="card h-100"><div class="card-body"><small class="text-body-secondary">Saldo Awal Periode</small><h4 class="mb-0">Rp <?= number_format((float) $rekap['saldo_awal'], 0, ',', '.') ?></h4></div></div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card h-100"><div class="card-body"><small class="text-body-secondary">Saldo Akhir Periode</small><h4 class="mb-0">Rp <?= number_format((float) $rekap['saldo_akhir'], 0, ',', '.') ?></h4></div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-1">Buku Kas Gabungan</h5>
        <p class="text-body-secondary mb-0">Iuran sebagai pemasukan; pengeluaran dan setoran pimpinan sebagai pengurang saldo.</p>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table" id="table-rekap-kas">
            <thead><tr><th>Tanggal</th><th>Jenis</th><th>Uraian</th><th>Masuk</th><th>Keluar</th><th>Saldo</th></tr></thead>
            <tbody>
            <?php foreach ($rekap['entries'] as $row): ?>
                <tr>
                    <td data-order="<?= esc($row['tanggal']) ?>"><?= esc(date('d-m-Y', strtotime($row['tanggal']))) ?></td>
                    <td>
                        <?php $badge = $row['jenis'] === 'Iuran' ? 'success' : ($row['jenis'] === 'Pengeluaran' ? 'danger' : 'info'); ?>
                        <span class="badge bg-label-<?= esc($badge) ?>"><?= esc($row['jenis']) ?></span>
                    </td>
                    <td><?= esc($row['uraian']) ?></td>
                    <td data-order="<?= esc((string) $row['masuk']) ?>"><?= $row['masuk'] > 0 ? 'Rp ' . number_format((float) $row['masuk'], 0, ',', '.') : '-' ?></td>
                    <td data-order="<?= esc((string) $row['keluar']) ?>"><?= $row['keluar'] > 0 ? 'Rp ' . number_format((float) $row['keluar'], 0, ',', '.') : '-' ?></td>
                    <td data-order="<?= esc((string) $row['saldo']) ?>" class="fw-semibold">Rp <?= number_format((float) $row['saldo'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('layout_footer') ?>
