<?= $this->include('layout_header') ?>

<div class="row g-6 mb-6">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-label-primary p-2"><i class="icon-base bx bx-wallet fs-4"></i></span>
                    <small class="text-body-secondary">Hari ini</small>
                </div>
                <p class="mb-1 text-body-secondary">Iuran Masuk</p>
                <h4 class="mb-0">Rp <?= number_format($iuranHariIni, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-label-danger p-2"><i class="icon-base bx bx-receipt fs-4"></i></span>
                    <small class="text-body-secondary">Hari ini</small>
                </div>
                <p class="mb-1 text-body-secondary">Pengeluaran</p>
                <h4 class="mb-0">Rp <?= number_format($pengeluaranHariIni, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-label-info p-2"><i class="icon-base bx bx-transfer fs-4"></i></span>
                    <small class="text-body-secondary">Hari ini</small>
                </div>
                <p class="mb-1 text-body-secondary">Setoran Pimpinan</p>
                <h4 class="mb-0">Rp <?= number_format($setoranHariIni, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-label-success p-2"><i class="icon-base bx bx-money fs-4"></i></span>
                    <small class="text-body-secondary">Berjalan</small>
                </div>
                <p class="mb-1 text-body-secondary">Saldo Kas</p>
                <h4 class="mb-0">Rp <?= number_format($saldoKas, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-6">
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                    <div>
                        <h5 class="card-title mb-2">Ringkasan Kas Kantin</h5>
                        <p class="card-text text-body-secondary mb-0">
                            Saldo berjalan dihitung dari seluruh iuran masuk dikurangi pengeluaran dan setoran yang sudah tercatat resmi.
                        </p>
                    </div>
                    <div class="text-md-end">
                        <small class="text-body-secondary d-block">Tanggal</small>
                        <span class="fw-semibold"><?= esc(date('d-m-Y', strtotime($today))) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <p class="mb-1 text-body-secondary">Penjual Aktif</p>
                    <h3 class="mb-0"><?= number_format($penjualAktif, 0, ',', '.') ?></h3>
                </div>
                <span class="badge bg-label-primary rounded p-3"><i class="icon-base bx bx-store fs-2"></i></span>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layout_footer') ?>
