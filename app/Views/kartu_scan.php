<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-1">Scan Kartu Anggota</h5>
                    <p class="text-body-secondary mb-0">Arahkan kamera ke QR kartu penjual. Pada server produksi, kamera browser membutuhkan HTTPS.</p>
                </div>
                <a href="<?= esc($baseUrl) ?>/kartu" class="btn btn-outline-secondary text-nowrap">
                    <i class="icon-base bx bx-arrow-back me-1"></i>Kembali
                </a>
            </div>
            <div class="card-body">
                <div class="ratio ratio-4x3 bg-dark rounded overflow-hidden mb-4 position-relative">
                    <video id="qr-video" class="w-100 h-100 object-fit-cover" muted playsinline></video>
                    <canvas id="qr-canvas" class="d-none"></canvas>
                    <div class="position-absolute top-50 start-50 translate-middle border border-3 border-white rounded" style="width: 58%; aspect-ratio: 1 / 1; pointer-events: none; opacity: .72;"></div>
                </div>

                <div id="qr-status" class="alert alert-secondary mb-4" role="status">
                    Tekan “Mulai Kamera” untuk memindai QR kartu.
                </div>

                <div class="d-grid d-sm-flex gap-2 mb-4">
                    <button type="button" class="btn btn-primary flex-sm-fill" id="qr-start">
                        <i class="icon-base bx bx-camera me-1"></i>Mulai Kamera
                    </button>
                    <button type="button" class="btn btn-outline-danger flex-sm-fill" id="qr-stop" disabled>
                        <i class="icon-base bx bx-stop-circle me-1"></i>Hentikan
                    </button>
                </div>

                <div class="border rounded p-3">
                    <label for="qr-file" class="form-label fw-semibold">Atau scan dari gambar</label>
                    <input type="file" class="form-control" id="qr-file" accept="image/*" capture="environment">
                    <div class="form-text">Pilihan ini berguna bila QR sudah tersimpan sebagai foto/screenshot.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.KARTU_SCAN_BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<!-- jsQR dipin lokal. Chrome/Chromium juga mempunyai fallback BarcodeDetector bila tersedia. -->
<script src="<?= esc($baseUrl) ?>/assets-app/qrcode-lib/jsQR.js" onerror="this.remove()"></script>
<script src="<?= esc($baseUrl) ?>/assets-app/qrcode-lib/kartu-scanner.js"></script>

<?= $this->include('layout_footer') ?>
