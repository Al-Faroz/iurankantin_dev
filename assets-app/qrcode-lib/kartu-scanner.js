(function () {
    'use strict';

    var video = document.getElementById('qr-video');
    var canvas = document.getElementById('qr-canvas');
    var startButton = document.getElementById('qr-start');
    var stopButton = document.getElementById('qr-stop');
    var fileInput = document.getElementById('qr-file');
    var statusBox = document.getElementById('qr-status');

    if (!video || !canvas || !startButton || !stopButton || !statusBox) {
        return;
    }

    var context = canvas.getContext('2d', { willReadFrequently: true });
    var stream = null;
    var running = false;
    var detector = null;
    var scanTimer = null;
    var baseUrl = String(window.KARTU_SCAN_BASE_URL || '').replace(/\/$/, '');

    function setStatus(message, type) {
        statusBox.className = 'alert mb-0 alert-' + (type || 'secondary');
        statusBox.textContent = message;
    }

    function stopCamera() {
        running = false;

        if (scanTimer !== null) {
            window.clearTimeout(scanTimer);
            scanTimer = null;
        }

        if (stream) {
            stream.getTracks().forEach(function (track) {
                track.stop();
            });
            stream = null;
        }

        video.srcObject = null;
        startButton.disabled = false;
        stopButton.disabled = true;
    }

    function verificationUrl(value) {
        if (!value) {
            return null;
        }

        try {
            var url = new URL(String(value), window.location.origin);
            var base = new URL(baseUrl || window.location.origin, window.location.origin);
            var basePath = base.pathname.replace(/\/$/, '');
            var expectedPrefix = basePath + '/verifikasi/';

            if (url.origin !== window.location.origin || !url.pathname.startsWith(expectedPrefix)) {
                return null;
            }

            return url.href;
        } catch (error) {
            return null;
        }
    }

    function handleResult(value) {
        var url = verificationUrl(value);
        if (!url) {
            setStatus('QR terbaca, tetapi bukan QR kartu aplikasi ini.', 'warning');
            return false;
        }

        stopCamera();
        setStatus('Kartu ditemukan. Membuka data penjual...', 'success');
        window.location.assign(url);
        return true;
    }

    function prepareCanvas() {
        var width = video.videoWidth || 640;
        var height = video.videoHeight || 480;
        canvas.width = width;
        canvas.height = height;
        context.drawImage(video, 0, 0, width, height);
        return { width: width, height: height };
    }

    function scanWithJsQr() {
        if (typeof window.jsQR !== 'function') {
            return false;
        }

        var size = prepareCanvas();
        var imageData = context.getImageData(0, 0, size.width, size.height);
        var result = window.jsQR(imageData.data, size.width, size.height, {
            inversionAttempts: 'attemptBoth'
        });

        return result && result.data ? handleResult(result.data) : false;
    }

    async function scanWithBarcodeDetector() {
        if (!detector) {
            return false;
        }

        try {
            var results = await detector.detect(video);
            if (results && results.length > 0 && results[0].rawValue) {
                return handleResult(results[0].rawValue);
            }
        } catch (error) {
            // Frame video mungkin belum siap; lanjutkan loop berikutnya.
        }

        return false;
    }

    async function scanLoop() {
        if (!running) {
            return;
        }

        var found = scanWithJsQr();
        if (!found) {
            found = await scanWithBarcodeDetector();
        }

        if (!found && running) {
            scanTimer = window.setTimeout(scanLoop, 220);
        }
    }

    async function startCamera() {
        if (!window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            setStatus('Kamera browser membutuhkan HTTPS pada server produksi.', 'danger');
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('Browser ini tidak mendukung akses kamera.', 'danger');
            return;
        }

        if (typeof window.BarcodeDetector === 'function') {
            try {
                detector = new window.BarcodeDetector({ formats: ['qr_code'] });
            } catch (error) {
                detector = null;
            }
        }

        if (typeof window.jsQR !== 'function' && detector === null) {
            setStatus('Decoder QR lokal belum tersedia pada browser ini. Gunakan Chrome Android terbaru atau pasang library jsQR lokal.', 'danger');
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });

            video.srcObject = stream;
            video.setAttribute('playsinline', '');
            await video.play();

            running = true;
            startButton.disabled = true;
            stopButton.disabled = false;
            setStatus('Arahkan kamera ke QR pada kartu anggota.', 'info');
            scanLoop();
        } catch (error) {
            stopCamera();
            setStatus('Kamera tidak dapat dibuka. Pastikan izin kamera sudah diberikan.', 'danger');
        }
    }

    async function scanFile(file) {
        if (!file) {
            return;
        }

        try {
            var bitmap = await createImageBitmap(file);

            if (typeof window.jsQR === 'function') {
                canvas.width = bitmap.width;
                canvas.height = bitmap.height;
                context.drawImage(bitmap, 0, 0);
                var imageData = context.getImageData(0, 0, bitmap.width, bitmap.height);
                var result = window.jsQR(imageData.data, bitmap.width, bitmap.height, {
                    inversionAttempts: 'attemptBoth'
                });

                if (result && result.data && handleResult(result.data)) {
                    return;
                }
            }

            if (typeof window.BarcodeDetector === 'function') {
                var fileDetector = new window.BarcodeDetector({ formats: ['qr_code'] });
                var results = await fileDetector.detect(bitmap);
                if (results && results.length > 0 && results[0].rawValue && handleResult(results[0].rawValue)) {
                    return;
                }
            }

            setStatus('QR kartu tidak ditemukan pada gambar tersebut.', 'warning');
        } catch (error) {
            setStatus('Gambar tidak dapat diproses.', 'danger');
        }
    }

    startButton.addEventListener('click', startCamera);
    stopButton.addEventListener('click', stopCamera);
    fileInput.addEventListener('change', function () {
        stopCamera();
        scanFile(fileInput.files && fileInput.files[0] ? fileInput.files[0] : null);
    });
    window.addEventListener('beforeunload', stopCamera);
})();
