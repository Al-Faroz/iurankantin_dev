<?php
$useDataTables = true;
$dataTableSelector = '#table-setoran';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="d-flex flex-column flex-sm-row gap-2 justify-content-between align-items-sm-center mb-4">
    <div>
        <h5 class="mb-1">Setoran ke Pimpinan</h5>
        <p class="text-body-secondary mb-0">Cetak bukti terlebih dahulu, lalu catat resmi setelah dana benar-benar diserahkan.</p>
    </div>
    <div class="d-flex flex-column flex-sm-row gap-2">
        <a href="<?= esc($baseUrl) ?>/setoran/cetak" class="btn btn-outline-primary"><i class="icon-base bx bx-printer me-1"></i>Cetak Form Setoran</a>
        <a href="<?= esc($baseUrl) ?>/setoran/input" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>Input Setoran Resmi</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-1">Setoran Tercatat</h5>
        <p class="text-body-secondary mb-0">Hanya data tahap kedua yang tersimpan di database dan mengurangi saldo kas.</p>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table" id="table-setoran">
            <thead>
                <tr>
                    <th>Tanggal Form</th>
                    <th>Periode</th>
                    <th>Nominal</th>
                    <th>Keterangan</th>
                    <th>Operator</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($setoran as $row): ?>
                <tr>
                    <td data-order="<?= esc($row['tanggal_form']) ?>"><?= esc(date('d-m-Y', strtotime($row['tanggal_form']))) ?></td>
                    <td><?= esc(date('d-m-Y', strtotime($row['periode_awal']))) ?> s.d. <?= esc(date('d-m-Y', strtotime($row['periode_akhir']))) ?></td>
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
