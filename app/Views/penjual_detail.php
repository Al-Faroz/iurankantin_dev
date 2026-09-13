<?php $baseUrl = rtrim((string) config('App')->baseURL, '/'); ?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row g-6">
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="mb-1"><?= esc($penjual['nama_penjual']) ?></h5>
                    <span class="badge <?= $penjual['status_aktif'] === 'Aktif' ? 'bg-label-success' : 'bg-label-secondary' ?>">
                        <?= esc($penjual['status_aktif']) ?>
                    </span>
                </div>
                <a href="<?= esc($baseUrl) ?>/penjual/<?= (int) $penjual['id_penjual'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 mb-3">Golongan</dt>
                    <dd class="col-7 mb-3"><?= esc($penjual['nama_golongan'] ?? '-') ?></dd>

                    <dt class="col-5 mb-3">Iuran Default</dt>
                    <dd class="col-7 mb-3">Rp <?= number_format((float) ($penjual['nominal_iuran'] ?? 0), 0, ',', '.') ?></dd>

                    <dt class="col-5 mb-3">Lokasi / Lapak</dt>
                    <dd class="col-7 mb-3"><?= esc($penjual['lokasi_lapak'] ?: '-') ?></dd>

                    <dt class="col-5 mb-3">No. HP</dt>
                    <dd class="col-7 mb-3"><?= esc($penjual['no_hp'] ?: '-') ?></dd>

                    <dt class="col-5 mb-3">Bergabung</dt>
                    <dd class="col-7 mb-3"><?= esc(date('d-m-Y', strtotime($penjual['tanggal_daftar']))) ?></dd>

                    <dt class="col-5">Kode Kartu</dt>
                    <dd class="col-7 mb-0"><?= esc($penjual['kode_kartu'] ?: 'Belum dibuat') ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">Riwayat Iuran Terbaru</h5>
                <p class="text-body-secondary mb-0">Maksimal 20 transaksi terakhir penjual ini.</p>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Tanggal</th><th>Nominal</th><th>Keterangan</th></tr></thead>
                    <tbody>
                    <?php if ($riwayat): ?>
                        <?php foreach ($riwayat as $row): ?>
                            <tr>
                                <td><?= esc(date('d-m-Y', strtotime($row['tanggal']))) ?></td>
                                <td>Rp <?= number_format((float) $row['nominal'], 0, ',', '.') ?></td>
                                <td><?= esc($row['keterangan'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-body-secondary py-5">Belum ada transaksi iuran.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="<?= esc($baseUrl) ?>/penjual" class="btn btn-outline-secondary"><i class="icon-base bx bx-arrow-back me-1"></i>Kembali</a>
</div>

<?= $this->include('layout_footer') ?>
