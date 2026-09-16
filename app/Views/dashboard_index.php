<?php $baseUrl = rtrim((string) config('App')->baseURL, '/'); ?>
<?= $this->include('layout_header') ?>

<div class="row g-6 mb-6">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3"><span class="badge bg-label-primary p-2"><i class="icon-base bx bx-wallet fs-4"></i></span><small class="text-body-secondary">Hari ini</small></div>
            <p class="mb-1 text-body-secondary">Iuran Masuk</p><h4 class="mb-0">Rp <?= number_format($iuranHariIni, 0, ',', '.') ?></h4>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3"><span class="badge bg-label-danger p-2"><i class="icon-base bx bx-receipt fs-4"></i></span><small class="text-body-secondary">Hari ini</small></div>
            <p class="mb-1 text-body-secondary">Pengeluaran</p><h4 class="mb-0">Rp <?= number_format($pengeluaranHariIni, 0, ',', '.') ?></h4>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3"><span class="badge bg-label-info p-2"><i class="icon-base bx bx-transfer fs-4"></i></span><small class="text-body-secondary">Hari ini</small></div>
            <p class="mb-1 text-body-secondary">Setoran Pimpinan</p><h4 class="mb-0">Rp <?= number_format($setoranHariIni, 0, ',', '.') ?></h4>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3"><span class="badge bg-label-success p-2"><i class="icon-base bx bx-money fs-4"></i></span><small class="text-body-secondary">Berjalan</small></div>
            <p class="mb-1 text-body-secondary">Saldo Kas</p><h4 class="mb-0">Rp <?= number_format($saldoKas, 0, ',', '.') ?></h4>
        </div></div>
    </div>
</div>

<div class="row g-6 mb-6">
    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
                <div>
                    <h5 class="mb-1">Tren Arus Kas Masuk Bulan Berjalan</h5>
                    <p class="text-body-secondary mb-0">Akumulasi iuran masuk per hari sejak awal bulan sampai hari ini.</p>
                </div>
                <div class="text-md-end">
                    <small class="text-body-secondary d-block">Total bulan berjalan</small>
                    <span class="fw-semibold">Rp <?= number_format((float) $iuranBulanBerjalan['total'], 0, ',', '.') ?></span>
                </div>
            </div>
            <div class="card-body pt-0"><div id="iuran-current-month-chart" style="min-height:320px"></div></div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column justify-content-between gap-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div><p class="mb-1 text-body-secondary">Penjual Aktif</p><h3 class="mb-0"><?= number_format($penjualAktif, 0, ',', '.') ?></h3></div>
                    <span class="badge bg-label-primary rounded p-3"><i class="icon-base bx bx-store fs-2"></i></span>
                </div>
                <div>
                    <h6>Rumus Saldo Kas</h6>
                    <p class="text-body-secondary mb-0">Total Iuran − Total Pengeluaran − Total Setoran Pimpinan yang sudah dicatat resmi.</p>
                </div>
                <div class="small text-body-secondary">
                    Data grafik diperbarui langsung dari transaksi yang tersimpan hingga <?= esc(date('d-m-Y', strtotime($today))) ?>.
                </div>
                <?php if (session()->get('role') === 'Operator'): ?>
                    <div class="d-grid gap-2">
                        <a href="<?= esc($baseUrl) ?>/iuran" class="btn btn-primary">Input Iuran Hari Ini</a>
                        <a href="<?= esc($baseUrl) ?>/iuran/form-mingguan" class="btn btn-outline-primary">Cetak Form Mingguan</a>
                    </div>
                <?php else: ?>
                    <a href="<?= esc($baseUrl) ?>/laporan/rekap-kas" class="btn btn-outline-primary">Lihat Rekap Kas</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-6">
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="mb-1">Tren Arus Kas Masuk 6 Bulan Terakhir</h5>
                    <p class="text-body-secondary mb-0">Total iuran masuk per bulan.</p>
                </div>
                <span class="badge bg-label-primary">Iuran</span>
            </div>
            <div class="card-body pt-0"><div id="iuran-six-month-chart" style="min-height:300px"></div></div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="mb-1">Tren Arus Pengeluaran 6 Bulan Terakhir</h5>
                    <p class="text-body-secondary mb-0">Total pengeluaran kas per bulan.</p>
                </div>
                <span class="badge bg-label-danger">Pengeluaran</span>
            </div>
            <div class="card-body pt-0"><div id="expense-six-month-chart" style="min-height:300px"></div></div>
        </div>
    </div>
</div>

<script src="<?= esc($baseUrl) ?>/assets/vendor/libs/apex-charts/apexcharts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.ApexCharts) return;

    function rupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
    }

    function compactRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            notation: 'compact',
            maximumFractionDigits: 1
        }).format(value || 0);
    }

    function renderTrend(selector, seriesName, labels, values, height) {
        const target = document.querySelector(selector);
        if (!target) return;

        const chart = new ApexCharts(target, {
            chart: {
                type: 'line',
                height: height,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            series: [{ name: seriesName, data: values }],
            xaxis: {
                categories: labels,
                labels: { rotate: -45, hideOverlappingLabels: true }
            },
            yaxis: {
                min: 0,
                labels: { formatter: compactRupiah }
            },
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 3, hover: { size: 5 } },
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: rupiah } },
            grid: { borderColor: 'rgba(0,0,0,.08)' },
            noData: { text: 'Belum ada data transaksi.' }
        });
        chart.render();
    }

    renderTrend(
        '#iuran-current-month-chart',
        'Iuran Masuk',
        <?= json_encode($iuranBulanBerjalan['labels'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        <?= json_encode($iuranBulanBerjalan['values'], JSON_NUMERIC_CHECK) ?>,
        320
    );

    renderTrend(
        '#iuran-six-month-chart',
        'Iuran Masuk',
        <?= json_encode($iuranEnamBulan['labels'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        <?= json_encode($iuranEnamBulan['values'], JSON_NUMERIC_CHECK) ?>,
        300
    );

    renderTrend(
        '#expense-six-month-chart',
        'Pengeluaran',
        <?= json_encode($pengeluaranEnamBulan['labels'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        <?= json_encode($pengeluaranEnamBulan['values'], JSON_NUMERIC_CHECK) ?>,
        300
    );
});
</script>

<?= $this->include('layout_footer') ?>
