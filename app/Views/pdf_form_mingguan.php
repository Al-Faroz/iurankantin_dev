<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Form Iuran Mingguan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 4mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #111;
        }

        table.page {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.page > tbody > tr > td.half {
            width: 50%;
            vertical-align: top;
            padding: 0 2mm;
        }

        table.page > tbody > tr > td.half:first-child {
            padding-left: 0;
            padding-right: 2.5mm;
        }

        table.page > tbody > tr > td.half:last-child {
            padding-left: 2.5mm;
            padding-right: 0;
        }

        .copy {
            width: 100%;
        }

        .school {
            text-align: center;
            margin: 0 0 1.2mm;
        }

        .school-name {
            margin: 0;
            font-size: 9.5pt;
            font-weight: 700;
            line-height: 1.05;
        }

        .school-address {
            margin: 0.6mm 0 0;
            font-size: 4.8pt;
            line-height: 1.15;
        }

        .form-title {
            margin: 1.2mm 0 0;
            text-align: center;
            font-size: 7.4pt;
            font-weight: 700;
            line-height: 1.1;
        }

        .period {
            margin: 0.7mm 0 1.6mm;
            text-align: center;
            font-size: 5.4pt;
            line-height: 1.1;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.data th,
        table.data td {
            border: 0.25mm solid #333;
            padding: 0.55mm 0.45mm;
            vertical-align: middle;
        }

        table.data th {
            text-align: center;
            font-size: 4.7pt;
            line-height: 1.05;
            font-weight: 700;
            background: #f4f4f4;
        }

        table.data td {
            height: 5.1mm;
            font-size: 5pt;
            line-height: 1.05;
        }

        .no {
            width: 5%;
            text-align: center;
        }

        .seller {
            width: 29%;
        }

        .class {
            width: 10%;
            text-align: center;
        }

        .day {
            width: 8%;
            text-align: center;
        }

        .total {
            width: 8%;
            text-align: center;
        }

        .seller-name {
            display: block;
            font-weight: 600;
        }

        .seller-location {
            display: block;
            margin-top: 0.25mm;
            font-size: 4.25pt;
            color: #444;
            line-height: 1.05;
        }
    </style>
</head>
<body>
<table class="page">
    <tbody>
    <tr>
        <?php for ($copy = 0; $copy < 2; $copy++): ?>
            <td class="half">
                <div class="copy">
                    <div class="school">
                        <p class="school-name"><?= esc($setting['nama_madrasah'] ?: 'MTsN 4 Jombang') ?></p>
                        <?php if (! empty($setting['alamat_madrasah'])): ?>
                            <p class="school-address"><?= esc($setting['alamat_madrasah']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-title">FORM PENCATATAN IURAN KANTIN MINGGUAN</div>
                    <div class="period">
                        Periode: <?= esc($days[0]['label']) ?> s.d. <?= esc($days[5]['label']) ?>
                    </div>

                    <table class="data">
                        <thead>
                            <tr>
                                <th class="no">No</th>
                                <th class="seller">Nama Penjual</th>
                                <th class="class">Gol</th>
                                <?php foreach ($days as $day): ?>
                                    <th class="day">
                                        <?= esc($day['nama']) ?><br>
                                        <?= esc(substr($day['label'], 0, 5)) ?>
                                    </th>
                                <?php endforeach; ?>
                                <th class="total">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($penjual as $index => $row): ?>
                            <tr>
                                <td class="no"><?= $index + 1 ?></td>
                                <td class="seller">
                                    <span class="seller-name"><?= esc($row['nama_penjual']) ?></span>
                                    <?php if (! empty($row['lokasi_lapak'])): ?>
                                        <span class="seller-location"><?= esc($row['lokasi_lapak']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="class"><?= esc($row['nama_golongan']) ?></td>
                                <?php foreach ($days as $day): ?>
                                    <td class="day"></td>
                                <?php endforeach; ?>
                                <td class="total"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </td>
        <?php endfor; ?>
    </tr>
    </tbody>
</table>
</body>
</html>
