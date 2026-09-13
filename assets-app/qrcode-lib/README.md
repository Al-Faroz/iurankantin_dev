# QR Scanner Library

Aplikasi memuat decoder QR dari file lokal berikut:

```text
assets-app/qrcode-lib/jsQR.js
```

Versi yang dipin adalah **jsQR 1.4.0**, commit resmi:

```text
34d8eec1ec5d85496f3948ff02fcfe6406f89d81
```

Source resmi:

```text
https://github.com/cozmo/jsQR/blob/34d8eec1ec5d85496f3948ff02fcfe6406f89d81/dist/jsQR.js
```

## Mengambil file vendor secara lokal

Dari root project di PowerShell:

```powershell
Invoke-WebRequest `
  -Uri "https://raw.githubusercontent.com/cozmo/jsQR/34d8eec1ec5d85496f3948ff02fcfe6406f89d81/dist/jsQR.js" `
  -OutFile ".\assets-app\qrcode-lib\jsQR.js"
```

Setelah file tersimpan, commit sebagai vendor asset aplikasi:

```powershell
git add assets-app/qrcode-lib/jsQR.js
git commit -m "build: vendor jsQR 1.4.0 locally"
git push origin main
```

Tidak ada CDN yang dipakai saat runtime. `kartu-scanner.js` juga mempunyai fallback ke `BarcodeDetector` pada browser Chromium yang mendukungnya, tetapi `jsQR.js` tetap disediakan untuk kompatibilitas browser yang lebih luas.

> Kamera browser pada production membutuhkan HTTPS. `localhost` tetap dapat memakai kamera pada browser modern.
