<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Form Iuran Mingguan</title>
    <style>
        @page { margin: 12mm 12mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5pt; color: #111; }
        .header { width: 100%; border-bottom: 2px solid #111; padding-bottom: 6px; margin-bottom: 10px; }
        .header td { vertical-align: middle; }
        .logo { width: 52px; height: 52px; object-fit: contain; }
        .school { text-align: center; }
        .school h1 { font-size: 14pt; margin: 0 0 3px; }
        .school p { margin: 0; font-size: 8.5pt; }
        h2 { text-align: center; font-size: 12pt; margin: 8px 0 10px; }
        .period { text-align: center; margin-bottom: 10px; }
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.data th, table.data td { border: 1px solid #333; padding: 4px 3px; }
        table.data th { background: #ececec; text-align: center; font-size: 7.5pt; }
        table.data td { height: 20px; }
        .no { width: 4%; text-align: center; }
        .seller { width: 20%; }
        .class { width: 9%; text-align: center; }
        .day { width: 9%; text-align: center; }
        .total { width: 10%; text-align: center; }
        .seller small { color: #555; }
        .footer { margin-top: 8px; font-size: 7.5pt; color: #555; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width:62px">
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
            <td style="width:62px"></td>
        </tr>
    </table>

    <h2>FORM PENCATATAN IURAN KANTIN MINGGUAN</h2>
    <div class="period">Periode: <?= esc($days[0]['label']) ?> s.d. <?= esc($days[5]['label']) ?></div>

    <table class="data">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="seller">Nama Penjual</th>
                <th class="class">Golongan</th>
                <?php foreach ($days as $day): ?>
                    <th class="day"><?= esc($day['nama']) ?><br><?= esc(substr($day['label'], 0, 5)) ?></th>
                <?php endforeach; ?>
                <th class="total">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($penjual as $index => $row): ?>
            <tr>
                <td class="no"><?= $index + 1 ?></td>
                <td class="seller"><?= esc($row['nama_penjual']) ?><?php if ($row['lokasi_lapak']): ?><br><small><?= esc($row['lokasi_lapak']) ?></small><?php endif; ?></td>
                <td class="class"><?= esc($row['nama_golongan']) ?></td>
                <?php foreach ($days as $day): ?><td class="day"></td><?php endforeach; ?>
                <td class="total"></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">Nominal iuran standar mengikuti golongan masing-masing penjual. Lembar ini digunakan untuk pencatatan manual sebelum data dipindahkan ke aplikasi.</div>
</body>
</html>
