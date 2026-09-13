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

<div class="row g-6">
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
                <div><h5 class="mb-1">Tren Arus Kas Bersih</h5><p class="text-body-secondary mb-0">Iuran dikurangi pengeluaran dan setoran resmi per bulan.</p></div>
                <div class="text-md-end"><small class="text-body-secondary d-block">Per <?= esc(date('d-m-Y', strtotime($today))) ?></small></div>
            </div>
            <div class="card-body pt-0"><div id="cash-trend-chart" style="min-height:300px"></div></div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
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

<script src="<?= esc($baseUrl) ?>/assets/vendor/libs/apex-charts/apexcharts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const target = document.querySelector('#cash-trend-chart');
    if (!target || !window.ApexCharts) return;

    const chart = new ApexCharts(target, {
        chart: { type: 'bar', height: 300, toolbar: { show: false } },
        series: [{ name: 'Arus Kas Bersih', data: <?= json_encode($chartValues, JSON_NUMERIC_CHECK) ?> }],
        xaxis: { categories: <?= json_encode($chartLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?> },
        dataLabels: { enabled: false },
        plotOptions: { bar: { borderRadius: 6, columnWidth: '48%' } },
        tooltip: { y: { formatter: function (value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value); } } },
        yaxis: { labels: { formatter: function (value) { return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value); } } },
        grid: { borderColor: 'rgba(0,0,0,.08)' }
    });
    chart.render();
});
</script>

<?= $this->include('layout_footer') ?>
