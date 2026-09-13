<?php
$useDataTables = true;
$dataTableSelector = '#table-golongan';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="card">
    <div class="card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
        <div>
            <h5 class="mb-1">Daftar Golongan Penjual</h5>
            <p class="text-body-secondary mb-0">Nominal ini menjadi nilai awal saat input iuran dan tetap dapat dioverride per transaksi.</p>
        </div>
        <a href="<?= esc($baseUrl) ?>/golongan/tambah" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i>Tambah Golongan
        </a>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table" id="table-golongan">
            <thead>
                <tr>
                    <th style="width:70px">No</th>
                    <th>Nama Golongan</th>
                    <th>Nominal Iuran</th>
                    <th class="no-sort text-center" style="width:120px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($golongan as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="fw-semibold"><?= esc($row['nama_golongan']) ?></td>
                        <td data-order="<?= esc((string) $row['nominal_iuran']) ?>">Rp <?= number_format((float) $row['nominal_iuran'], 0, ',', '.') ?></td>
                        <td class="text-center">
                            <a href="<?= esc($baseUrl) ?>/golongan/<?= (int) $row['id_golongan'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="icon-base bx bx-edit"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('layout_footer') ?>
