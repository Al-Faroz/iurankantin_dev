<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $today = Time::now('Asia/Jakarta')->toDateString();

        $iuranHariIni = $this->sumNominal($db->table('transaksi_iuran')->where('tanggal', $today));
        $pengeluaranHariIni = $this->sumNominal($db->table('transaksi_pengeluaran')->where('tanggal', $today));
        $setoranHariIni = $this->sumNominal($db->table('setoran_pimpinan')->where('tanggal_form', $today));

        $totalIuran = $this->sumNominal($db->table('transaksi_iuran'));
        $totalPengeluaran = $this->sumNominal($db->table('transaksi_pengeluaran'));
        $totalSetoran = $this->sumNominal($db->table('setoran_pimpinan'));

        $penjualAktif = $db->table('penjual')
            ->where('status_aktif', 'Aktif')
            ->where('deleted_at', null)
            ->countAllResults();

        $chart = $this->monthlyTrend();

        return view('dashboard_index', [
            'title' => 'Dashboard',
            'today' => $today,
            'iuranHariIni' => $iuranHariIni,
            'pengeluaranHariIni' => $pengeluaranHariIni,
            'setoranHariIni' => $setoranHariIni,
            'saldoKas' => $totalIuran - $totalPengeluaran - $totalSetoran,
            'penjualAktif' => $penjualAktif,
            'chartLabels' => $chart['labels'],
            'chartValues' => $chart['values'],
        ]);
    }

    private function sumNominal($builder): float
    {
        $row = $builder->selectSum('nominal', 'total')->get()->getRowArray();

        return (float) ($row['total'] ?? 0);
    }

    private function monthlyTrend(): array
    {
        $db = db_connect();
        $now = new \DateTimeImmutable('first day of this month', new \DateTimeZone('Asia/Jakarta'));
        $start = $now->modify('-5 months');
        $startDate = $start->format('Y-m-d');

        $iuran = $this->monthlyMap($db->table('transaksi_iuran'), 'tanggal', $startDate);
        $pengeluaran = $this->monthlyMap($db->table('transaksi_pengeluaran'), 'tanggal', $startDate);
        $setoran = $this->monthlyMap($db->table('setoran_pimpinan'), 'tanggal_form', $startDate);

        $labels = [];
        $values = [];
        $monthNames = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        for ($i = 0; $i < 6; $i++) {
            $month = $start->modify('+' . $i . ' months');
            $key = $month->format('Y-m');
            $labels[] = $monthNames[(int) $month->format('n')] . ' ' . $month->format('Y');
            $values[] = ($iuran[$key] ?? 0) - ($pengeluaran[$key] ?? 0) - ($setoran[$key] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
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
}
