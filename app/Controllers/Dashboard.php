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

        return view('dashboard_index', [
            'title' => 'Dashboard',
            'today' => $today,
            'iuranHariIni' => $iuranHariIni,
            'pengeluaranHariIni' => $pengeluaranHariIni,
            'setoranHariIni' => $setoranHariIni,
            'saldoKas' => $totalIuran - $totalPengeluaran - $totalSetoran,
            'penjualAktif' => $penjualAktif,
        ]);
    }

    private function sumNominal($builder): float
    {
        $row = $builder->selectSum('nominal', 'total')->get()->getRowArray();

        return (float) ($row['total'] ?? 0);
    }
}
