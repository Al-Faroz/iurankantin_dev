<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$tanggal = old('tanggal') ?: $tanggalDefault;
$idKategori = old('id_kategori_keluar') ?: '';
$nominal = old('nominal') ?: '';
$keterangan = old('keterangan') ?: '';
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-1">Input Pengeluaran</h5>
                <p class="text-body-secondary mb-0">Bukti nota bersifat opsional dan otomatis dikompresi menjadi JPG di bawah 500 KB.</p>
            </div>
            <div class="card-body">
                <?php if (! $kategori): ?>
                    <div class="alert alert-warning">
                        Belum ada kategori pengeluaran. Tambahkan kategori terlebih dahulu sebelum mencatat transaksi.
                    </div>
                    <a href="<?= esc($baseUrl) ?>/kategori-pengeluaran/tambah" class="btn btn-primary">Tambah Kategori Pengeluaran</a>
                <?php else: ?>
                    <form action="<?= esc($baseUrl) ?>/pengeluaran/simpan" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="row g-4">
                            <div class="col-12 col-md-4">
                                <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= esc((string) $tanggal) ?>" required>
                            </div>

                            <div class="col-12 col-md-8">
                                <label for="id_kategori_keluar" class="form-label">Kategori Pengeluaran <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_kategori_keluar" name="id_kategori_keluar" required>
                                    <option value="">Pilih kategori</option>
                                    <?php foreach ($kategori as $row): ?>
                                        <option value="<?= (int) $row['id_kategori_keluar'] ?>" <?= (string) $idKategori === (string) $row['id_kategori_keluar'] ? 'selected' : '' ?>>
                                            <?= esc($row['nama_kategori']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-5">
                                <label for="nominal" class="form-label">Nominal <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="nominal" name="nominal" value="<?= esc((string) $nominal) ?>" min="1" step="1" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-7">
                                <label for="bukti_nota" class="form-label">Foto Bukti Nota <span class="text-body-secondary">(opsional)</span></label>
                                <input type="file" class="form-control" id="bukti_nota" name="bukti_nota" accept="image/jpeg,image/png" capture="environment">
                                <div class="form-text">JPG/JPEG/PNG, maksimal 10 MB sebelum kompresi. Kamera belakang akan ditawarkan di perangkat yang mendukung.</div>
                            </div>

                            <div class="col-12">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3" maxlength="255" placeholder="Contoh: Pembelian gas elpiji kantin"><?= esc((string) $keterangan) ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-6">
                            <a href="<?= esc($baseUrl) ?>/pengeluaran" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>Simpan Pengeluaran</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layout_footer') ?>
