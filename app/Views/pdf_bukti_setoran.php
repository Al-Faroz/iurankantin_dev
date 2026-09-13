<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Form Setoran Kantin</title>
    <style>
        @page { margin: 24mm 20mm 22mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #111; }
        .header { width: 100%; border-bottom: 2px solid #111; padding-bottom: 10px; margin-bottom: 24px; }
        .header td { vertical-align: middle; }
        .logo { width: 70px; height: 70px; object-fit: contain; }
        .school { text-align: center; }
        .school h1 { font-size: 16pt; margin: 0 0 5px; }
        .school p { margin: 0; font-size: 9.5pt; }
        h2 { text-align: center; font-size: 14pt; margin: 0 0 22px; text-decoration: underline; }
        .detail { width: 100%; border-collapse: collapse; margin-bottom: 26px; }
        .detail td { padding: 7px 4px; vertical-align: top; }
        .detail td:first-child { width: 34%; font-weight: bold; }
        .amount { border: 1.5px solid #111; padding: 13px 16px; margin: 10px 0 24px; font-size: 14pt; font-weight: bold; text-align: center; }
        .note { line-height: 1.6; margin: 0 0 40px; }
        .sign { width: 100%; border-collapse: collapse; margin-top: 38px; }
        .sign td { width: 50%; text-align: center; vertical-align: top; }
        .space { height: 75px; }
        .line { display: inline-block; min-width: 170px; border-bottom: 1px solid #111; }
        .footer-note { font-size: 8.5pt; color: #555; margin-top: 28px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width:80px">
                <?php if ($logoDataUri): ?>
                    <img class="logo" src="<?= esc($logoDataUri, 'attr') ?>" alt="Logo">
                <?php endif; ?>
            </td>
            <td class="school">
                <h1><?= esc($setting['nama_madrasah'] ?: 'MTsN 4 Jombang') ?></h1>
                <?php if (! empty($setting['alamat_madrasah'])): ?>
                    <p><?= esc($setting['alamat_madrasah']) ?></p>
                <?php endif; ?>
            </td>
            <td style="width:80px"></td>
        </tr>
    </table>

    <h2>FORM SETORAN KAS KANTIN</h2>

    <table class="detail">
        <tr><td>Tanggal Form</td><td>: <?= esc(date('d-m-Y', strtotime($tanggalForm))) ?></td></tr>
        <tr><td>Periode Iuran</td><td>: <?= esc(date('d-m-Y', strtotime($periodeAwal))) ?> s.d. <?= esc(date('d-m-Y', strtotime($periodeAkhir))) ?></td></tr>
    </table>

    <div class="amount">JUMLAH SETORAN: Rp <?= number_format($nominal, 0, ',', '.') ?></div>

    <p class="note">
        Telah diserahkan dana kas kantin sesuai nominal di atas kepada pimpinan madrasah untuk periode yang tercantum. Form ini digunakan sebagai bukti fisik penyerahan dana dan ditandatangani setelah dana diterima.
    </p>

    <table class="sign">
        <tr>
            <td>Yang Menyerahkan,<div class="space"></div><span class="line"></span><br>Operator Kantin</td>
            <td>Yang Menerima,<div class="space"></div><span class="line"></span><br>Pimpinan Madrasah</td>
        </tr>
    </table>

    <div class="footer-note">Catatan: pencetakan form ini belum mencatat transaksi setoran ke database aplikasi.</div>
</body>
</html>
