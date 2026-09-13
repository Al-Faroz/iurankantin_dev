<?php
$useDataTables = true;
$dataTableSelector = '#table-user';
$baseUrl = rtrim((string) config('App')->baseURL, '/');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="card">
    <div class="card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
        <div><h5 class="mb-1">Daftar User</h5><p class="text-body-secondary mb-0">Kelola akun Operator dan Pimpinan.</p></div>
        <a href="<?= esc($baseUrl) ?>/user/tambah" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>Tambah User</a>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table" id="table-user">
            <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Status</th><th class="no-sort text-center">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($users as $row): ?>
                <tr>
                    <td class="fw-semibold"><?= esc($row['nama']) ?></td>
                    <td><?= esc($row['username']) ?></td>
                    <td><span class="badge bg-label-<?= $row['role'] === 'Operator' ? 'primary' : 'info' ?>"><?= esc($row['role']) ?></span></td>
                    <td><span class="badge bg-label-<?= $row['status_aktif'] === 'Aktif' ? 'success' : 'secondary' ?>"><?= esc($row['status_aktif']) ?></span></td>
                    <td class="text-center"><a href="<?= esc($baseUrl) ?>/user/<?= (int) $row['id_user'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('layout_footer') ?>
