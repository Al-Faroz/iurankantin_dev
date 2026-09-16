<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class LaporanService
{
    public function iuran(array $filter): array
    {
        $builder = db_connect()->table('transaksi_iuran')
            ->select('transaksi_iuran.*, penjual.nama_penjual, golongan_penjual.nama_golongan, users.nama AS nama_operator')
            ->join('penjual', 'penjual.id_penjual = transaksi_iuran.id_penjual')
            ->join('golongan_penjual', 'golongan_penjual.id_golongan = penjual.id_golongan')
            ->join('users', 'users.id_user = transaksi_iuran.id_operator');

        $this->applyDateFilter($builder, 'transaksi_iuran.tanggal', $filter);

        if (! empty($filter['id_penjual'])) {
            $builder->where('transaksi_iuran.id_penjual', (int) $filter['id_penjual']);
        }
        if (! empty($filter['id_golongan'])) {
            $builder->where('penjual.id_golongan', (int) $filter['id_golongan']);
        }

        return $builder
            ->orderBy('transaksi_iuran.tanggal', 'DESC')
            ->orderBy('transaksi_iuran.id_transaksi', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function pengeluaran(array $filter): array
    {
        $builder = db_connect()->table('transaksi_pengeluaran')
            ->select('transaksi_pengeluaran.*, kategori_pengeluaran.nama_kategori, users.nama AS nama_operator')
            ->join('kategori_pengeluaran', 'kategori_pengeluaran.id_kategori_keluar = transaksi_pengeluaran.id_kategori_keluar')
            ->join('users', 'users.id_user = transaksi_pengeluaran.id_operator');

        $this->applyDateFilter($builder, 'transaksi_pengeluaran.tanggal', $filter);

        if (! empty($filter['id_kategori_keluar'])) {
            $builder->where('transaksi_pengeluaran.id_kategori_keluar', (int) $filter['id_kategori_keluar']);
        }

        return $builder
            ->orderBy('transaksi_pengeluaran.tanggal', 'DESC')
            ->orderBy('transaksi_pengeluaran.id_pengeluaran', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function setoran(array $filter): array
    {
        $builder = db_connect()->table('setoran_pimpinan')
            ->select('setoran_pimpinan.*, users.nama AS nama_operator')
            ->join('users', 'users.id_user = setoran_pimpinan.id_operator');

        $this->applyDateFilter($builder, 'setoran_pimpinan.tanggal_form', $filter);

        return $builder
            ->orderBy('setoran_pimpinan.tanggal_form', 'DESC')
            ->orderBy('setoran_pimpinan.id_setoran', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function rekapKas(array $filter): array
    {
        $db = db_connect();
        $entries = [];

        // Rekap kas hanya membutuhkan total iuran per tanggal, bukan rincian per penjual.
        // Rincian individual tetap tersedia di menu Laporan Iuran.
        $iuran = $db->table('transaksi_iuran')
            ->select('transaksi_iuran.tanggal, SUM(transaksi_iuran.nominal) AS total_nominal, COUNT(transaksi_iuran.id_transaksi) AS jumlah_transaksi', false);
        $this->applyDateFilter($iuran, 'transaksi_iuran.tanggal', $filter);

        $iuranRows = $iuran
            ->groupBy('transaksi_iuran.tanggal')
            ->get()
            ->getResultArray();

        foreach ($iuranRows as $row) {
            $jumlahTransaksi = (int) ($row['jumlah_transaksi'] ?? 0);
            $entries[] = [
                'tanggal' => $row['tanggal'],
                'urutan' => 1,
                'id' => 0,
                'jenis' => 'Iuran',
                'uraian' => 'Total Iuran Harian' . ($jumlahTransaksi > 0 ? ' (' . $jumlahTransaksi . ' transaksi)' : ''),
                'masuk' => (float) ($row['total_nominal'] ?? 0),
                'keluar' => 0.0,
            ];
        }

        $pengeluaran = $db->table('transaksi_pengeluaran')
            ->select('transaksi_pengeluaran.id_pengeluaran AS id, transaksi_pengeluaran.tanggal, transaksi_pengeluaran.nominal, transaksi_pengeluaran.keterangan, kategori_pengeluaran.nama_kategori')
            ->join('kategori_pengeluaran', 'kategori_pengeluaran.id_kategori_keluar = transaksi_pengeluaran.id_kategori_keluar');
        $this->applyDateFilter($pengeluaran, 'transaksi_pengeluaran.tanggal', $filter);

        foreach ($pengeluaran->get()->getResultArray() as $row) {
            $entries[] = [
                'tanggal' => $row['tanggal'],
                'urutan' => 2,
                'id' => (int) $row['id'],
                'jenis' => 'Pengeluaran',
                'uraian' => $row['nama_kategori'] . ($row['keterangan'] ? ' - ' . $row['keterangan'] : ''),
                'masuk' => 0.0,
                'keluar' => (float) $row['nominal'],
            ];
        }

        $setoran = $db->table('setoran_pimpinan')
            ->select('id_setoran AS id, tanggal_form AS tanggal, nominal, keterangan, periode_awal, periode_akhir');
        $this->applyDateFilter($setoran, 'tanggal_form', $filter);

        foreach ($setoran->get()->getResultArray() as $row) {
            $entries[] = [
                'tanggal' => $row['tanggal'],
                'urutan' => 3,
                'id' => (int) $row['id'],
                'jenis' => 'Setoran',
                'uraian' => 'Setoran ke pimpinan periode ' . date('d-m-Y', strtotime($row['periode_awal'])) . ' s.d. ' . date('d-m-Y', strtotime($row['periode_akhir'])) . ($row['keterangan'] ? ' - ' . $row['keterangan'] : ''),
                'masuk' => 0.0,
                'keluar' => (float) $row['nominal'],
            ];
        }

        usort($entries, static function (array $a, array $b): int {
            return [$a['tanggal'], $a['urutan'], $a['id']] <=> [$b['tanggal'], $b['urutan'], $b['id']];
        });

        $saldoAwal = $this->saldoSebelum((string) ($filter['tanggal_awal'] ?? ''));
        $saldo = $saldoAwal;
        foreach ($entries as &$entry) {
            $saldo += $entry['masuk'] - $entry['keluar'];
            $entry['saldo'] = $saldo;
        }
        unset($entry);

        return [
            'saldo_awal' => $saldoAwal,
            'entries' => $entries,
            'saldo_akhir' => $saldo,
        ];
    }

    public function excel(string $title, array $headers, array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr(preg_replace('/[^A-Za-z0-9 _-]/', '', $title) ?: 'Laporan', 0, 31));

        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        foreach ($rows as $rowIndex => $row) {
            foreach (array_values($row) as $columnIndex => $value) {
                $sheet->setCellValue([$columnIndex + 1, $rowIndex + 2], $value);
            }
        }

        $lastColumn = count($headers);
        if ($lastColumn > 0) {
            $sheet->getStyle([1, 1, $lastColumn, 1])->getFont()->setBold(true);
            for ($column = 1; $column <= $lastColumn; $column++) {
                $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
            }
        }

        $tmp = tempnam(WRITEPATH . 'cache', 'xlsx_');
        if ($tmp === false) {
            throw new RuntimeException('Gagal membuat file Excel sementara.');
        }

        try {
            (new Xlsx($spreadsheet))->save($tmp);
            $binary = file_get_contents($tmp);
            if ($binary === false) {
                throw new RuntimeException('Gagal membaca file Excel hasil export.');
            }
            return $binary;
        } finally {
            @unlink($tmp);
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function applyDateFilter($builder, string $field, array $filter): void
    {
        if (! empty($filter['tanggal_awal'])) {
            $builder->where($field . ' >=', $filter['tanggal_awal']);
        }
        if (! empty($filter['tanggal_akhir'])) {
            $builder->where($field . ' <=', $filter['tanggal_akhir']);
        }
    }

    private function saldoSebelum(string $tanggalAwal): float
    {
        if ($tanggalAwal === '') {
            return 0.0;
        }

        $db = db_connect();
        $sum = static function ($builder): float {
            $row = $builder->selectSum('nominal', 'total')->get()->getRowArray();
            return (float) ($row['total'] ?? 0);
        };

        $iuran = $sum($db->table('transaksi_iuran')->where('tanggal <', $tanggalAwal));
        $pengeluaran = $sum($db->table('transaksi_pengeluaran')->where('tanggal <', $tanggalAwal));
        $setoran = $sum($db->table('setoran_pimpinan')->where('tanggal_form <', $tanggalAwal));

        return $iuran - $pengeluaran - $setoran;
    }
}
