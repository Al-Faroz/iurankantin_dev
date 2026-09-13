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
            margin: 8mm 7mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5pt;
            color: #111;
        }

        .voucher {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .voucher td {
            border: 0.7pt solid #333;
        }

        .header-logo,
        .header-title {
            height: 23mm;
            background: #aaa;
        }

        .header-logo {
            width: 22%;
            text-align: center;
            vertical-align: middle;
        }

        .header-title {
            width: 78%;
            padding: 2mm 4mm;
            text-align: center;
            vertical-align: middle;
            color: #fff;
            font-weight: bold;
            font-size: 15pt;
            line-height: 1.25;
        }

        .logo {
            max-width: 17mm;
            max-height: 17mm;
        }

        .label {
            width: 22%;
            padding: 2.2mm 2.5mm;
            vertical-align: middle;
        }

        .value {
            width: 78%;
            padding: 2.2mm 2.5mm;
            vertical-align: middle;
        }

        .amount {
            height: 15mm;
            padding: 2mm;
            text-align: center;
            vertical-align: middle;
            font-size: 13pt;
            font-weight: bold;
        }

        .statement {
            height: 29mm;
            padding: 3mm 14mm;
            text-align: center;
            vertical-align: middle;
            line-height: 1.45;
        }

        .signature {
            width: 50%;
            height: 36mm;
            padding: 2.5mm 2mm 1.5mm;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 21mm;
        }

        .signature-line {
            width: 38mm;
            margin: 0 auto 1mm;
            border-top: 0.7pt solid #333;
        }

        .copy-gap {
            height: 18mm;
        }
    </style>
</head>
<body>
<?php for ($copy = 0; $copy < 2; $copy++): ?>
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

    <?php if ($copy === 0): ?>
        <div class="copy-gap"></div>
    <?php endif; ?>
<?php endfor; ?>
</body>
</html>
