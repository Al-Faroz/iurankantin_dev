<?php
$useDataTables = true;
$dataTableSelector = '#table-penjual';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="card">
    <div class="card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
        <div>
            <h5 class="mb-1">Daftar Penjual Kantin</h5>
            <p class="text-body-secondary mb-0">Data pedagang yang terdaftar di lingkungan kantin madrasah.</p>
        </div>
        <a href="<?= esc($baseUrl) ?>/penjual/tambah" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i>Tambah Penjual
        </a>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table" id="table-penjual">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Penjual</th>
                    <th>Golongan</th>
                    <th>Lokasi</th>
                    <th>No. HP</th>
                    <th>Status</th>
                    <th class="no-sort text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($penjual as $index => $row): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="fw-semibold"><?= esc($row['nama_penjual']) ?></td>
                    <td>
                        <?= esc($row['nama_golongan'] ?? '-') ?>
                        <div class="small text-body-secondary">Rp <?= number_format((float) ($row['nominal_iuran'] ?? 0), 0, ',', '.') ?></div>
                    </td>
                    <td><?= esc($row['lokasi_lapak'] ?: '-') ?></td>
                    <td><?= esc($row['no_hp'] ?: '-') ?></td>
                    <td>
                        <?php if ($row['status_aktif'] === 'Aktif'): ?>
                            <span class="badge bg-label-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-label-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-nowrap">
                        <a href="<?= esc($baseUrl) ?>/penjual/<?= (int) $row['id_penjual'] ?>" class="btn btn-sm btn-outline-secondary" title="Detail">
                            <i class="icon-base bx bx-show"></i>
                        </a>
                        <a href="<?= esc($baseUrl) ?>/penjual/<?= (int) $row['id_penjual'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="icon-base bx bx-edit"></i>
                        </a>
                        <form action="<?= esc($baseUrl) ?>/penjual/<?= (int) $row['id_penjual'] ?>/arsipkan"
                              method="post" class="d-inline"
                              data-confirm-title="Arsipkan penjual?"
                              data-confirm-text="Penjual tidak lagi muncul di transaksi baru dan QR kartunya menjadi tidak valid. Riwayat transaksi tetap tersimpan."
                              data-confirm-button="Ya, arsipkan">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Arsipkan">
                                <i class="icon-base bx bx-archive"></i>
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
