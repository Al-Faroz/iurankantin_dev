<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$tanggalForm = old('tanggal_form') ?: $tanggalDefault;
$periodeAwal = old('periode_awal') ?: '';
$periodeAkhir = old('periode_akhir') ?: '';
$nominal = old('nominal') ?: '';
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-1">Tahap 1 — Cetak Form Setoran</h5>
                <p class="text-body-secondary mb-0">Tahap ini hanya membuat PDF. Data belum disimpan ke database dan belum mengurangi saldo kas.</p>
            </div>
            <div class="card-body">
                <form action="<?= esc($baseUrl) ?>/setoran/cetak-pdf" method="post" id="form-cetak-setoran">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label for="tanggal_form" class="form-label">Tanggal Form <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_form" name="tanggal_form" value="<?= esc((string) $tanggalForm) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="nominal" class="form-label">Besar Setoran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="nominal" name="nominal" value="<?= esc((string) $nominal) ?>" min="1" step="1" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="periode_awal" class="form-label">Periode Awal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="periode_awal" name="periode_awal" value="<?= esc((string) $periodeAwal) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="periode_akhir" class="form-label">Periode Akhir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="periode_akhir" name="periode_akhir" value="<?= esc((string) $periodeAkhir) ?>" required>
                        </div>
                    </div>

                    <div class="alert alert-info mt-5 mb-0">
                        Setelah PDF dicetak dan dana diserahkan kepada pimpinan, kembali ke aplikasi lalu gunakan menu <strong>Input Setoran Resmi</strong>.
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-5">
                        <a href="<?= esc($baseUrl) ?>/setoran" class="btn btn-outline-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-printer me-1"></i>Buka PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-cetak-setoran');
    const tanggalForm = document.getElementById('tanggal_form');
    const periodeAwal = document.getElementById('periode_awal');
    const periodeAkhir = document.getElementById('periode_akhir');
    const nominal = document.getElementById('nominal');

    if (!form) {
        return;
    }

    form.addEventListener('submit', function (event) {
        if (periodeAwal.value && periodeAkhir.value && periodeAwal.value > periodeAkhir.value) {
            event.preventDefault();

            if (window.Swal) {
                Swal.fire({
                    title: 'Periode tidak valid',
                    text: 'Periode awal tidak boleh melewati periode akhir.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            } else {
                window.alert('Periode awal tidak boleh melewati periode akhir.');
            }
            return;
        }

        // Response PDF dikirim sebagai attachment. Kosongkan isian setelah permintaan download dikirim.
        window.setTimeout(function () {
            tanggalForm.value = '';
            periodeAwal.value = '';
            periodeAkhir.value = '';
            nominal.value = '';
        }, 150);
    });
});
</script>

<?= $this->include('layout_footer') ?>
