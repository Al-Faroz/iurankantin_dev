<?php $baseUrl = rtrim((string) config('App')->baseURL, '/'); ?>
<?= $this->include('layout_header') ?>

<style>
    .dashboard-summary-head { margin-bottom: .65rem; }
    .dashboard-summary-grid { --bs-gutter-x: .6rem; --bs-gutter-y: .6rem; }
    .dashboard-summary-card .card-body { padding: .7rem; }
    .dashboard-summary-icon {
        display: inline-flex;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        flex: 0 0 2rem;
    }
    .dashboard-summary-icon .icon-base { font-size: 1.1rem !important; }
    .dashboard-summary-label {
        font-size: .72rem;
        line-height: 1.15;
        margin-bottom: .12rem;
    }
    .dashboard-summary-value {
        font-size: .98rem;
        line-height: 1.15;
        letter-spacing: -.02em;
        white-space: nowrap;
    }
    .dashboard-actions-card .card-body { padding: .65rem; }
    .dashboard-action-buttons .btn {
        min-height: 40px;
        padding: .45rem .55rem;
        font-size: .78rem;
        line-height: 1.15;
    }

    @media (min-width: 576px) {
        .dashboard-summary-head { margin-bottom: 1rem; }
        .dashboard-summary-grid { --bs-gutter-x: 1rem; --bs-gutter-y: 1rem; }
        .dashboard-summary-card .card-body { padding: 1rem; }
        .dashboard-summary-icon {
            width: 2.4rem;
            height: 2.4rem;
            flex-basis: 2.4rem;
        }
        .dashboard-summary-icon .icon-base { font-size: 1.35rem !important; }
        .dashboard-summary-label { font-size: .82rem; }
        .dashboard-summary-value { font-size: 1.2rem; }
        .dashboard-actions-card .card-body { padding: 1rem; }
        .dashboard-action-buttons .btn {
            min-height: 42px;
            padding: .5rem .8rem;
            font-size: .875rem;
        }
    }
</style>

<div class="dashboard-summary-head d-flex align-items-end justify-content-between gap-2">
    <div>
        <h5 class="mb-0">Ringkasan Bulan Berjalan</h5>
        <small class="text-body-secondary">
            <?= esc(date('d-m-Y', strtotime($monthStart))) ?> s.d. <?= esc(date('d-m-Y', strtotime($today))) ?>
        </small>
    </div>
</div>

<div class="row dashboard-summary-grid mb-3 mb-md-4">
    <div class="col-6 col-xl-3">
        <div class="card h-100 dashboard-summary-card">
            <div class="card-body d-flex align-items-center gap-2">
                <span class="badge bg-label-primary dashboard-summary-icon"><i class="icon-base bx bx-wallet"></i></span>
                <div class="min-w-0">
                    <div class="text-body-secondary dashboard-summary-label">Iuran Masuk</div>
                    <div class="fw-semibold dashboard-summary-value">Rp <?= number_format($iuranBulanIni, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card h-100 dashboard-summary-card">
            <div class="card-body d-flex align-items-center gap-2">
                <span class="badge bg-label-danger dashboard-summary-icon"><i class="icon-base bx bx-receipt"></i></span>
                <div class="min-w-0">
                    <div class="text-body-secondary dashboard-summary-label">Pengeluaran</div>
                    <div class="fw-semibold dashboard-summary-value">Rp <?= number_format($pengeluaranBulanIni, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card h-100 dashboard-summary-card">
            <div class="card-body d-flex align-items-center gap-2">
                <span class="badge bg-label-info dashboard-summary-icon"><i class="icon-base bx bx-transfer"></i></span>
                <div class="min-w-0">
                    <div class="text-body-secondary dashboard-summary-label">Setoran</div>
                    <div class="fw-semibold dashboard-summary-value">Rp <?= number_format($setoranBulanIni, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card h-100 dashboard-summary-card">
            <div class="card-body d-flex align-items-center gap-2">
                <span class="badge bg-label-success dashboard-summary-icon"><i class="icon-base bx bx-money"></i></span>
                <div class="min-w-0">
                    <div class="text-body-secondary dashboard-summary-label">Saldo Kas</div>
                    <div class="fw-semibold dashboard-summary-value">Rp <?= number_format($saldoKas, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (session()->get('role') === 'Operator'): ?>
    <div class="card dashboard-actions-card mb-3 mb-md-4">
        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 gap-sm-3">
            <div class="d-none d-sm-flex align-items-center gap-3">
                <span class="badge bg-label-primary rounded p-2 flex-shrink-0"><i class="icon-base bx bx-bolt-circle fs-4"></i></span>
                <div>
                    <h6 class="mb-0">Aksi Cepat Operator</h6>
                    <small class="text-body-secondary">Input pembayaran harian atau cetak form kontrol mingguan.</small>
                </div>
            </div>
            <div class="row g-2 dashboard-action-buttons flex-grow-1 flex-lg-grow-0">
                <div class="col-6 col-lg-auto d-grid">
                    <a href="<?= esc($baseUrl) ?>/iuran" class="btn btn-primary">
                        <i class="icon-base bx bx-plus-circle me-1"></i>Input Iuran Hari Ini
                    </a>
                </div>
                <div class="col-6 col-lg-auto d-grid">
                    <a href="<?= esc($baseUrl) ?>/iuran/form-mingguan" class="btn btn-outline-primary">
                        <i class="icon-base bx bx-printer me-1"></i>Cetak Form Mingguan
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card dashboard-actions-card mb-3 mb-md-4">
        <div class="card-body d-flex align-items-center justify-content-between gap-3">
            <div>
                <small class="text-body-secondary">Penjual Aktif</small>
                <div class="fw-semibold"><?= number_format($penjualAktif, 0, ',', '.') ?> penjual</div>
            </div>
            <a href="<?= esc($baseUrl) ?>/laporan/rekap-kas" class="btn btn-sm btn-outline-primary">Lihat Rekap Kas</a>
        </div>
    </div>
<?php endif; ?>

<div class="row g-6 mb-6">
    <div class="col-12">
        <div class="card h-100">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
                <div>
                    <h5 class="mb-1">Tren Arus Kas Masuk Bulan Berjalan</h5>
                    <p class="text-body-secondary mb-0">Total iuran masuk per hari sejak awal bulan sampai hari ini.</p>
                </div>
                <div class="text-md-end">
                    <small class="text-body-secondary d-block">Total bulan berjalan</small>
                    <span class="fw-semibold">Rp <?= number_format((float) $iuranBulanBerjalan['total'], 0, ',', '.') ?></span>
                </div>
            </div>
            <div class="card-body pt-0"><div id="iuran-current-month-chart" style="min-height:320px"></div></div>
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
