<?php
$useDataTables = true;
$dataTableSelector = '#table-pengeluaran';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="card">
    <div class="card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
        <div>
            <h5 class="mb-1">Daftar Pengeluaran</h5>
            <p class="text-body-secondary mb-0">Pencatatan biaya operasional kantin yang sudah dikeluarkan.</p>
        </div>
        <a href="<?= esc($baseUrl) ?>/pengeluaran/tambah" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i>Input Pengeluaran
        </a>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table" id="table-pengeluaran">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Nominal</th>
                    <th>Keterangan</th>
                    <th>Nota</th>
                    <th>Operator</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pengeluaran as $row): ?>
                <tr>
                    <td data-order="<?= esc($row['tanggal']) ?>"><?= esc(date('d-m-Y', strtotime($row['tanggal']))) ?></td>
                    <td><span class="badge bg-label-primary"><?= esc($row['nama_kategori']) ?></span></td>
                    <td data-order="<?= esc((string) $row['nominal']) ?>">Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?></td>
                    <td><?= esc($row['keterangan'] ?: '-') ?></td>
                    <td>
                        <?php if ($row['bukti_nota']): ?>
                            <a href="<?= esc($baseUrl . '/' . ltrim($row['bukti_nota'], '/')) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Lihat</a>
                        <?php else: ?>
                            <span class="text-body-secondary">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($row['nama_operator']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('layout_footer') ?>
