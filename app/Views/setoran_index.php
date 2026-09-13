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
        <p class="text-body-secondary mb-0">Data dapat diunduh ulang, dikoreksi melalui Edit, atau dihapus permanen bila salah input.</p>
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
                    <th class="no-sort no-filter text-center">Aksi</th>
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
                    <td class="text-center text-nowrap">
                        <a href="<?= esc($baseUrl) ?>/setoran/<?= (int) $row['id_setoran'] ?>/cetak-ulang" class="btn btn-sm btn-outline-secondary" title="Download ulang form">
                            <i class="icon-base bx bx-download"></i>
                        </a>
                        <a href="<?= esc($baseUrl) ?>/setoran/<?= (int) $row['id_setoran'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit setoran">
                            <i class="icon-base bx bx-edit"></i>
                        </a>
                        <form action="<?= esc($baseUrl) ?>/setoran/<?= (int) $row['id_setoran'] ?>/hapus" method="post" class="d-inline"
                              data-confirm-title="Hapus setoran?"
                              data-confirm-text="Setoran Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?> akan dihapus permanen dan saldo kas langsung berubah."
                              data-confirm-button="Ya, hapus permanen">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus permanen">
                                <i class="icon-base bx bx-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('layout_footer') ?>
