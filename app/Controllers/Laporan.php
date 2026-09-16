<?php

namespace App\Controllers;

use App\Services\LaporanService;

class Laporan extends BaseController
{
    private LaporanService $service;

    public function __construct()
    {
        $this->service = new LaporanService();
    }

    public function iuran()
    {
        $filter = $this->filter(['id_penjual', 'id_golongan']);
        $rows = $this->service->iuran($filter);

        return view('laporan_iuran', [
            'title' => 'Laporan Iuran',
            'filter' => $filter,
            'rows' => $rows,
            'total' => array_sum(array_map(static fn (array $row): float => (float) $row['nominal'], $rows)),
            'penjualOptions' => $this->penjualOptions(),
            'golonganOptions' => $this->golonganOptions(),
        ]);
    }

    public function exportIuran()
    {
        $filter = $this->filter(['id_penjual', 'id_golongan']);
        $rows = $this->service->iuran($filter);
        $excelRows = array_map(static fn (array $row): array => [
            date('d-m-Y', strtotime($row['tanggal'])),
            $row['nama_penjual'],
            $row['nama_golongan'],
            (float) $row['nominal'],
            $row['keterangan'] ?? '',
            $row['nama_operator'],
        ], $rows);

        return $this->excelResponse(
            $this->service->excel('Laporan Iuran', ['Tanggal', 'Penjual', 'Golongan', 'Nominal', 'Keterangan', 'Operator'], $excelRows),
            'laporan-iuran_' . $this->periodSuffix($filter) . '.xlsx'
        );
    }

    public function pengeluaran()
    {
        $filter = $this->filter(['id_kategori_keluar']);
        $rows = $this->service->pengeluaran($filter);

        return view('laporan_pengeluaran', [
            'title' => 'Laporan Pengeluaran',
            'filter' => $filter,
            'rows' => $rows,
            'total' => array_sum(array_map(static fn (array $row): float => (float) $row['nominal'], $rows)),
            'kategoriOptions' => $this->kategoriOptions(),
        ]);
    }

    public function exportPengeluaran()
    {
        $filter = $this->filter(['id_kategori_keluar']);
        $rows = $this->service->pengeluaran($filter);
        $excelRows = array_map(static fn (array $row): array => [
            date('d-m-Y', strtotime($row['tanggal'])),
            $row['nama_kategori'],
            (float) $row['nominal'],
            $row['keterangan'] ?? '',
            $row['bukti_nota'] ?? '',
            $row['nama_operator'],
        ], $rows);

        return $this->excelResponse(
            $this->service->excel('Laporan Pengeluaran', ['Tanggal', 'Kategori', 'Nominal', 'Keterangan', 'Bukti Nota', 'Operator'], $excelRows),
            'laporan-pengeluaran_' . $this->periodSuffix($filter) . '.xlsx'
        );
    }

    public function setoran()
    {
        $filter = $this->filter([]);
        $rows = $this->service->setoran($filter);

        return view('laporan_setoran', [
            'title' => 'Laporan Setoran',
            'filter' => $filter,
            'rows' => $rows,
            'total' => array_sum(array_map(static fn (array $row): float => (float) $row['nominal'], $rows)),
        ]);
    }

    public function exportSetoran()
    {
        $filter = $this->filter([]);
        $rows = $this->service->setoran($filter);
        $excelRows = array_map(static fn (array $row): array => [
            date('d-m-Y', strtotime($row['tanggal_form'])),
            date('d-m-Y', strtotime($row['periode_awal'])),
            date('d-m-Y', strtotime($row['periode_akhir'])),
            (float) $row['nominal'],
            $row['keterangan'] ?? '',
            $row['nama_operator'],
        ], $rows);

        return $this->excelResponse(
            $this->service->excel('Laporan Setoran', ['Tanggal Form', 'Periode Awal', 'Periode Akhir', 'Nominal', 'Keterangan', 'Operator'], $excelRows),
            'laporan-setoran_' . $this->periodSuffix($filter) . '.xlsx'
        );
    }

    public function rekapKas()
    {
        $filter = $this->filter([]);
        $rekap = $this->service->rekapKas($filter);

        return view('laporan_rekap_kas', [
            'title' => 'Rekap Kas',
            'filter' => $filter,
            'rekap' => $rekap,
        ]);
    }

    public function exportRekapKas()
    {
        $filter = $this->filter([]);
        $rekap = $this->service->rekapKas($filter);
        $periodeAwal = date('d-m-Y', strtotime($filter['tanggal_awal']));

        $excelRows = [[
            $periodeAwal,
            'Saldo Awal',
            'Saldo sebelum periode laporan',
            0,
            0,
            (float) $rekap['saldo_awal'],
        ]];

        foreach ($rekap['entries'] as $row) {
            $excelRows[] = [
                date('d-m-Y', strtotime($row['tanggal'])),
                $row['jenis'],
                $row['uraian'],
                (float) $row['masuk'],
                (float) $row['keluar'],
                (float) $row['saldo'],
            ];
        }

        return $this->excelResponse(
            $this->service->excel('Rekap Kas', ['Tanggal', 'Jenis', 'Uraian', 'Masuk', 'Keluar', 'Saldo'], $excelRows),
            'rekap-kas_' . $this->periodSuffix($filter) . '.xlsx'
        );
    }

    private function filter(array $extraKeys): array
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));
        $awalDefault = $now->modify('first day of this month')->format('Y-m-d');
        $akhirDefault = $now->format('Y-m-d');

        $awal = (string) ($this->request->getGet('tanggal_awal') ?: $awalDefault);
        $akhir = (string) ($this->request->getGet('tanggal_akhir') ?: $akhirDefault);

        if (! $this->isValidDate($awal)) {
            $awal = $awalDefault;
        }
        if (! $this->isValidDate($akhir)) {
            $akhir = $akhirDefault;
        }
        if ($awal > $akhir) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        $filter = [
            'tanggal_awal' => $awal,
            'tanggal_akhir' => $akhir,
        ];

        foreach ($extraKeys as $key) {
            $value = $this->request->getGet($key);
            $filter[$key] = is_numeric($value) && (int) $value > 0 ? (int) $value : null;
        }

        return $filter;
    }

    private function periodSuffix(array $filter): string
    {
        $awal = date('d-m-Y', strtotime((string) $filter['tanggal_awal']));
        $akhir = date('d-m-Y', strtotime((string) $filter['tanggal_akhir']));

        return $awal . '_sd_' . $akhir;
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, new \DateTimeZone('Asia/Jakarta'));

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function penjualOptions(): array
    {
        return db_connect()->table('penjual')->select('id_penjual, nama_penjual')->orderBy('nama_penjual', 'ASC')->get()->getResultArray();
    }

    private function golonganOptions(): array
    {
        return db_connect()->table('golongan_penjual')->select('id_golongan, nama_golongan')->orderBy('nominal_iuran', 'DESC')->get()->getResultArray();
    }

    private function kategoriOptions(): array
    {
        return db_connect()->table('kategori_pengeluaran')->select('id_kategori_keluar, nama_kategori')->orderBy('nama_kategori', 'ASC')->get()->getResultArray();
    }

    private function excelResponse(string $binary, string $filename)
    {
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($binary);
    }
}
