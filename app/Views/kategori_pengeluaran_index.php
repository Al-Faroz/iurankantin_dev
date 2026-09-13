<?php
$useDataTables = true;
$dataTableSelector = '#table-kategori';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1">Kategori Pengeluaran</h5>
            <small class="text-body-secondary">Kelola kategori untuk pencatatan pengeluaran kantin.</small>
        </div>
        <a href="<?= esc($baseUrl) ?>/kategori-pengeluaran/tambah" class="btn btn-primary">Tambah Kategori</a>
    </div>
    <div class="table-responsive">
        <table class="table" id="table-kategori">
            <thead><tr><th>No</th><th>Nama Kategori</th><th class="no-sort text-center">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($kategori as $index => $row): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= esc($row['nama_kategori']) ?></td>
                    <td class="text-center text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="<?= esc($baseUrl) ?>/kategori-pengeluaran/<?= (int) $row['id_kategori_keluar'] ?>/edit" title="Edit">
                            <i class="icon-base bx bx-edit"></i>
                        </a>
                        <form action="<?= esc($baseUrl) ?>/kategori-pengeluaran/<?= (int) $row['id_kategori_keluar'] ?>/arsipkan"
                              method="post" class="d-inline"
                              data-confirm-title="Arsipkan kategori?"
                              data-confirm-text="Kategori tidak lagi tersedia untuk transaksi baru. Riwayat pengeluaran lama tetap tersimpan."
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
