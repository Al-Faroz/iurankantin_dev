<?php
$namaMadrasah = trim((string) ($setting['nama_madrasah'] ?? '')) ?: 'MTsN 4 Jombang';
$tanggalCetak = date('d-m-Y', strtotime((string) $tanggalForm));
$periodeCetak = date('d-m-Y', strtotime((string) $periodeAwal))
    . ' s.d. '
    . date('d-m-Y', strtotime((string) $periodeAkhir));
$nominalCetak = 'Rp. ' . number_format((float) $nominal, 0, ',', '.') . ',-';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Form Setoran Kas Kantin</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 5mm 6mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5pt;
            line-height: 1.2;
            color: #111;
        }

        .voucher-wrap {
            width: 100%;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .voucher {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        .voucher td {
            border: 0.7pt solid #333;
        }

        .header-logo,
        .header-title {
            height: 18mm;
            background: #aaa;
        }

        .header-logo {
            width: 22%;
            text-align: center;
            vertical-align: middle;
        }

        .header-title {
            width: 78%;
            padding: 1.5mm 3mm;
            text-align: center;
            vertical-align: middle;
            color: #fff;
            font-weight: bold;
            font-size: 13pt;
            line-height: 1.15;
        }

        .logo {
            max-width: 14mm;
            max-height: 14mm;
        }

        .label {
            width: 22%;
            padding: 1.3mm 2mm;
            vertical-align: middle;
        }

        .value {
            width: 78%;
            padding: 1.3mm 2mm;
            vertical-align: middle;
        }

        .amount {
            height: 11mm;
            padding: 1.5mm;
            text-align: center;
            vertical-align: middle;
            font-size: 11.5pt;
            font-weight: bold;
        }

        .statement {
            height: 21mm;
            padding: 2mm 10mm;
            text-align: center;
            vertical-align: middle;
            line-height: 1.3;
        }

        .signature {
            width: 50%;
            height: 27mm;
            padding: 2mm 2mm 1mm;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 14mm;
        }

        .signature-line {
            width: 36mm;
            margin: 0 auto 0.8mm;
            border-top: 0.7pt solid #333;
        }

        .copy-gap {
            height: 7mm;
            border-bottom: 0.5pt dashed #999;
            margin-bottom: 7mm;
        }
    </style>
</head>
<body>
<?php for ($copy = 0; $copy < 2; $copy++): ?>
    <div class="voucher-wrap">
        <table class="voucher">
            <tr>
                <td class="header-logo">
                    <?php if (! empty($logoDataUri)): ?>
                        <img class="logo" src="<?= esc($logoDataUri) ?>" alt="Logo">
                    <?php endif; ?>
                </td>
                <td class="header-title">
                    FORM SETORAN KAS KANTIN<br>
                    <?= esc($namaMadrasah) ?>
                </td>
            </tr>
            <tr>
                <td class="label">Tanggal Form</td>
                <td class="value"><?= esc($tanggalCetak) ?></td>
            </tr>
            <tr>
                <td class="label">Periode Iuran</td>
                <td class="value"><?= esc($periodeCetak) ?></td>
            </tr>
            <tr>
                <td class="amount" colspan="2">JUMLAH SETORAN: <?= esc($nominalCetak) ?></td>
            </tr>
            <tr>
                <td class="statement" colspan="2">
                    Telah diserahkan dana kas kantin sesuai nominal di atas kepada pimpinan madrasah<br>
                    untuk periode yang tercantum. Form ini digunakan sebagai bukti fisik penyerahan<br>
                    dana dan ditandatangani setelah dana diterima.
                </td>
            </tr>
            <tr>
                <td class="signature">
                    Yang Menyerahkan,
                    <div class="signature-space"></div>
                    <div class="signature-line"></div>
                    Operator Kantin
                </td>
                <td class="signature">
                    Yang Menerima,
                    <div class="signature-space"></div>
                    <div class="signature-line"></div>
                    Pimpinan Madrasah
                </td>
            </tr>
        </table>
    </div>

    <?php if ($copy === 0): ?>
        <div class="copy-gap"></div>
    <?php endif; ?>
<?php endfor; ?>
</body>
</html>
