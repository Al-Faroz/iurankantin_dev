<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$isEdit = is_array($kategori);
$action = $isEdit
    ? $baseUrl . '/kategori-pengeluaran/' . (int) $kategori['id_kategori_keluar'] . '/update'
    : $baseUrl . '/kategori-pengeluaran/simpan';
$namaKategori = old('nama_kategori') ?: ($kategori['nama_kategori'] ?? '');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-1"><?= $isEdit ? 'Edit' : 'Tambah' ?> Kategori Pengeluaran</h5>
                <p class="text-body-secondary mb-0">Kategori dipakai untuk mengelompokkan transaksi pengeluaran.</p>
            </div>
            <div class="card-body">
                <form action="<?= esc($action) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-6">
                        <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= esc((string) $namaKategori) ?>" maxlength="100" required autofocus>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                        <a href="<?= esc($baseUrl) ?>/kategori-pengeluaran" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layout_footer') ?>
