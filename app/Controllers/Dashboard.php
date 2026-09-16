<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $today = Time::now('Asia/Jakarta')->toDateString();
        $monthStart = (new \DateTimeImmutable('first day of this month', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d');

        // Ringkasan utama dashboard menampilkan total bulan berjalan, bukan hanya hari ini.
        $iuranBulanIni = $this->sumNominal(
            $db->table('transaksi_iuran')
                ->where('tanggal >=', $monthStart)
                ->where('tanggal <=', $today)
        );
        $pengeluaranBulanIni = $this->sumNominal(
            $db->table('transaksi_pengeluaran')
                ->where('tanggal >=', $monthStart)
                ->where('tanggal <=', $today)
        );
        $setoranBulanIni = $this->sumNominal(
            $db->table('setoran_pimpinan')
                ->where('tanggal_form >=', $monthStart)
                ->where('tanggal_form <=', $today)
        );

        $totalIuran = $this->sumNominal($db->table('transaksi_iuran'));
        $totalPengeluaran = $this->sumNominal($db->table('transaksi_pengeluaran'));
        $totalSetoran = $this->sumNominal($db->table('setoran_pimpinan'));

        $penjualAktif = $db->table('penjual')
            ->where('status_aktif', 'Aktif')
            ->where('deleted_at', null)
            ->countAllResults();

        $iuranBulanBerjalan = $this->currentMonthDailyTrend();
        $iuranEnamBulan = $this->sixMonthTrend('transaksi_iuran', 'tanggal');
        $pengeluaranEnamBulan = $this->sixMonthTrend('transaksi_pengeluaran', 'tanggal');

        return view('dashboard_index', [
            'title' => 'Dashboard',
            'today' => $today,
            'monthStart' => $monthStart,
            'iuranBulanIni' => $iuranBulanIni,
            'pengeluaranBulanIni' => $pengeluaranBulanIni,
            'setoranBulanIni' => $setoranBulanIni,
            'saldoKas' => $totalIuran - $totalPengeluaran - $totalSetoran,
            'penjualAktif' => $penjualAktif,
            'iuranBulanBerjalan' => $iuranBulanBerjalan,
            'iuranEnamBulan' => $iuranEnamBulan,
            'pengeluaranEnamBulan' => $pengeluaranEnamBulan,
        ]);
    }

    private function sumNominal($builder): float
    {
        $row = $builder->selectSum('nominal', 'total')->get()->getRowArray();

        return (float) ($row['total'] ?? 0);
    }

    private function currentMonthDailyTrend(): array
    {
        $timezone = new \DateTimeZone('Asia/Jakarta');
        $today = new \DateTimeImmutable('today', $timezone);
        $start = $today->modify('first day of this month');

        $rows = db_connect()->table('transaksi_iuran')
            ->select('tanggal, SUM(nominal) AS total', false)
            ->where('tanggal >=', $start->format('Y-m-d'))
            ->where('tanggal <=', $today->format('Y-m-d'))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['tanggal']] = (float) $row['total'];
        }

        $labels = [];
        $values = [];
        for ($date = $start; $date <= $today; $date = $date->modify('+1 day')) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d') . ' ' . $this->monthName((int) $date->format('n'));
            $values[] = $map[$key] ?? 0.0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'total' => array_sum($values),
        ];
    }

    private function sixMonthTrend(string $table, string $dateField): array
    {
        $timezone = new \DateTimeZone('Asia/Jakarta');
        $currentMonth = new \DateTimeImmutable('first day of this month', $timezone);
        $start = $currentMonth->modify('-5 months');
        $map = $this->monthlyMap(db_connect()->table($table), $dateField, $start->format('Y-m-d'));

        $labels = [];
        $values = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $start->modify('+' . $i . ' months');
            $key = $month->format('Y-m');
            $labels[] = $this->monthName((int) $month->format('n')) . ' ' . $month->format('Y');
            $values[] = $map[$key] ?? 0.0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'total' => array_sum($values),
        ];
    }

    private function monthlyMap($builder, string $dateField, string $startDate): array
    {
        $rows = $builder
            ->select("DATE_FORMAT({$dateField}, '%Y-%m') AS bulan, SUM(nominal) AS total", false)
            ->where($dateField . ' >=', $startDate)
            ->groupBy("DATE_FORMAT({$dateField}, '%Y-%m')", false)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['bulan']] = (float) $row['total'];
        }

        return $map;
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ][$month] ?? '';
    }
}
