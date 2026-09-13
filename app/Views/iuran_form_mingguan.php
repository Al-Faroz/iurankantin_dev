<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$tanggalSabtu = old('tanggal_sabtu') ?: $tanggalSabtuDefault;
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-1">Cetak Form Iuran Mingguan</h5>
                <p class="text-body-secondary mb-0">Form kosong untuk pencatatan manual di lapangan, berisi Sabtu sampai Kamis dan total per penjual.</p>
            </div>
            <div class="card-body">
                <form action="<?= esc($baseUrl) ?>/iuran/form-mingguan/pdf" method="post" target="_blank">
                    <?= csrf_field() ?>
                    <div class="mb-5">
                        <label for="tanggal_sabtu" class="form-label">Tanggal Sabtu Awal Minggu <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggal_sabtu" name="tanggal_sabtu" value="<?= esc((string) $tanggalSabtu) ?>" required>
                        <div class="form-text">Tanggal harus jatuh pada hari Sabtu. Lima kolom berikutnya dibuat otomatis: Minggu, Senin, Selasa, Rabu, Kamis.</div>
                    </div>

                    <div class="alert alert-info">
                        Penjual pada PDF hanya yang berstatus aktif, dengan urutan nominal golongan tertinggi terlebih dahulu lalu nama A–Z.
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                        <a href="<?= esc($baseUrl) ?>/iuran" class="btn btn-outline-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-printer me-1"></i>Buka PDF Mingguan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layout_footer') ?>
