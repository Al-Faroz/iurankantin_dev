<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$segment2 = request()->getUri()->getSegment(2);
?>
<div class="nav-align-top mb-4">
    <ul class="nav nav-pills flex-column flex-sm-row gap-2">
        <li class="nav-item"><a class="nav-link <?= $segment2 === 'iuran' ? 'active' : '' ?>" href="<?= esc($baseUrl) ?>/laporan/iuran">Iuran</a></li>
        <li class="nav-item"><a class="nav-link <?= $segment2 === 'pengeluaran' ? 'active' : '' ?>" href="<?= esc($baseUrl) ?>/laporan/pengeluaran">Pengeluaran</a></li>
        <li class="nav-item"><a class="nav-link <?= $segment2 === 'setoran' ? 'active' : '' ?>" href="<?= esc($baseUrl) ?>/laporan/setoran">Setoran</a></li>
        <li class="nav-item"><a class="nav-link <?= $segment2 === 'rekap-kas' ? 'active' : '' ?>" href="<?= esc($baseUrl) ?>/laporan/rekap-kas">Rekap Kas</a></li>
    </ul>
</div>
