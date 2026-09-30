<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$tanggalSabtu = old('tanggal_sabtu') ?: $tanggalSabtuDefault;
$lokasiPilihan = is_array($lokasiPilihan ?? null) ? $lokasiPilihan : [];
$lokasiOld = old('lokasi');
$lokasiDipilih = is_array($lokasiOld)
    ? array_values(array_unique(array_map(static fn ($value): string => trim((string) $value), $lokasiOld)))
    : $lokasiPilihan;
$lokasiMap = array_fill_keys($lokasiDipilih, true);
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-1">Cetak Form Iuran Mingguan</h5>
                <p class="text-body-secondary mb-0">Satu lembar A4 landscape berisi dua salinan A5 portrait yang sama, untuk pencatatan manual Sabtu sampai Kamis.</p>
            </div>
            <div class="card-body">
                <?php if (! $lokasiPilihan): ?>
                    <div class="alert alert-warning mb-0">
                        Belum ada Lokasi Lapak pada Penjual aktif. Isi Lokasi Lapak pada master Penjual terlebih dahulu sebelum mencetak form mingguan.
                    </div>
                <?php else: ?>
                    <form action="<?= esc($baseUrl) ?>/iuran/form-mingguan/pdf" method="post" target="_blank" id="form-mingguan">
                        <?= csrf_field() ?>

                        <div class="row g-4">
                            <div class="col-12 col-lg-5">
                                <label for="tanggal_sabtu" class="form-label">Tanggal Sabtu Awal Minggu <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control"
                                       id="tanggal_sabtu"
                                       name="tanggal_sabtu"
                                       value="<?= esc((string) $tanggalSabtu) ?>"
                                       required>
                                <div class="form-text">Tanggal harus jatuh pada hari Sabtu. Lima kolom berikutnya dibuat otomatis: Minggu, Senin, Selasa, Rabu, Kamis.</div>
                            </div>

                            <div class="col-12 col-lg-7">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-2">
                                    <label class="form-label mb-0">Lokasi Lapak <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="lokasi-semua">Pilih Semua</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="lokasi-kosongkan">Kosongkan</button>
                                    </div>
                                </div>

                                <div class="border rounded p-3">
                                    <div class="row g-2">
                                        <?php foreach ($lokasiPilihan as $index => $lokasi): ?>
                                            <?php $checked = isset($lokasiMap[$lokasi]); ?>
                                            <div class="col-12 col-sm-6">
                                                <div class="form-check">
                                                    <input class="form-check-input lokasi-check"
                                                           type="checkbox"
                                                           name="lokasi[]"
                                                           value="<?= esc($lokasi) ?>"
                                                           id="lokasi-<?= $index ?>"
                                                           <?= $checked ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="lokasi-<?= $index ?>">
                                                        <?= esc($lokasi) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="form-text">
                                    Hanya Penjual aktif dari lokasi yang dicentang yang masuk ke PDF.
                                    <span class="fw-semibold"><span id="jumlah-lokasi">0</span> lokasi dipilih.</span>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            PDF tetap berukuran <strong>A4 landscape</strong>. Isi form dicetak dua kali berdampingan; masing-masing sisi berukuran setara <strong>A5 portrait</strong> agar dapat dipotong menjadi dua lembar.
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                            <a href="<?= esc($baseUrl) ?>/iuran" class="btn btn-outline-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base bx bx-printer me-1"></i>Buka PDF Mingguan
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($lokasiPilihan): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-mingguan');
    const checks = Array.from(document.querySelectorAll('.lokasi-check'));
    const countEl = document.getElementById('jumlah-lokasi');
    const semuaBtn = document.getElementById('lokasi-semua');
    const kosongkanBtn = document.getElementById('lokasi-kosongkan');

    function updateCount() {
        const count = checks.filter(function (check) {
            return check.checked;
        }).length;

        if (countEl) {
            countEl.textContent = String(count);
        }

        return count;
    }

    checks.forEach(function (check) {
        check.addEventListener('change', updateCount);
    });

    if (semuaBtn) {
        semuaBtn.addEventListener('click', function () {
            checks.forEach(function (check) {
                check.checked = true;
            });
            updateCount();
        });
    }

    if (kosongkanBtn) {
        kosongkanBtn.addEventListener('click', function () {
            checks.forEach(function (check) {
                check.checked = false;
            });
            updateCount();
        });
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            if (updateCount() > 0) {
                return;
            }

            event.preventDefault();

            if (window.Swal) {
                Swal.fire({
                    title: 'Lokasi belum dipilih',
                    text: 'Pilih minimal satu Lokasi Lapak untuk dicetak.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            } else {
                window.alert('Pilih minimal satu Lokasi Lapak untuk dicetak.');
            }
        });
    }

    updateCount();
});
</script>
<?php endif; ?>

<?= $this->include('layout_footer') ?>
