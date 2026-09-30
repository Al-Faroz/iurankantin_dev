<?php

namespace App\Controllers;

use App\Models\SettingModel;
use App\Services\AuditTransaksiService;
use App\Services\IuranService;
use App\Services\PdfService;
use CodeIgniter\I18n\Time;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

class Iuran extends BaseController
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        $today = Time::now('Asia/Jakarta')->toDateString();
        $tanggal = (string) ($this->request->getGet('tanggal') ?: $today);
        if (! $this->isValidDate($tanggal) || $tanggal > $today) {
            $tanggal = $today;
        }

        return view('iuran_bulk', [
            'title' => 'Input Iuran Harian',
            'tanggalDefault' => $tanggal,
            'tanggalMaks' => $today,
            'penjual' => $this->getPenjualAktif(),
            'penjualSudahBayar' => $this->getPenjualSudahBayar($tanggal),
        ]);
    }

    public function store()
    {
        if (! $this->validate(['tanggal' => 'required|valid_date[Y-m-d]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tanggal = (string) $this->request->getPost('tanggal');
        $today = Time::now('Asia/Jakarta')->toDateString();
        if ($tanggal > $today) {
            return redirect()->back()->withInput()->with('error', 'Tanggal Iuran tidak boleh melebihi hari ini.');
        }

        $bayar = $this->request->getPost('bayar');
        $nominal = $this->request->getPost('nominal');
        $keterangan = $this->request->getPost('keterangan');
        $bayar = is_array($bayar) ? $bayar : [];
        $nominal = is_array($nominal) ? $nominal : [];
        $keterangan = is_array($keterangan) ? $keterangan : [];

        try {
            $jumlah = (new IuranService())->simpanBulk(
                $tanggal,
                (int) session()->get('id_user'),
                $bayar,
                $nominal,
                $keterangan
            );
        } catch (RuntimeException $e) {
            return redirect()->to($this->baseUrl . '/iuran?' . http_build_query(['tanggal' => $tanggal]))
                ->withInput()
                ->with('error', $e->getMessage());
        }

        $ids = array_map('intval', array_keys($bayar));
        $totalNominal = 0.0;
        foreach ($ids as $idPenjual) {
            $totalNominal += (float) ($nominal[$idPenjual] ?? $nominal[(string) $idPenjual] ?? 0);
        }

        (new AuditTransaksiService())->catat('CREATE_BULK', 'iuran', null, [
            'tanggal' => $tanggal,
            'jumlah_transaksi' => $jumlah,
            'total_nominal' => $totalNominal,
            'id_penjual' => $ids,
        ]);

        return redirect()->to($this->baseUrl . '/iuran?' . http_build_query(['tanggal' => $tanggal]))
            ->with('success', $jumlah . ' transaksi iuran berhasil disimpan.');
    }

    public function formMingguan()
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
        $saturday = (int) $now->format('N') === 6 ? $now : $now->modify('last saturday');

        return view('iuran_form_mingguan', [
            'title' => 'Cetak Form Iuran Mingguan',
            'tanggalSabtuDefault' => $saturday->format('Y-m-d'),
            'lokasiPilihan' => $this->getLokasiAktif(),
        ]);
    }

    public function cetakFormMingguan()
    {
        if (! $this->validate(['tanggal_sabtu' => 'required|valid_date[Y-m-d]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tanggalSabtu = (string) $this->request->getPost('tanggal_sabtu');
        $start = new DateTimeImmutable($tanggalSabtu, new DateTimeZone('Asia/Jakarta'));
        if ((int) $start->format('N') !== 6) {
            return redirect()->back()->withInput()->with('error', 'Tanggal awal harus hari Sabtu.');
        }

        $lokasiPost = $this->request->getPost('lokasi');
        if (! is_array($lokasiPost) || $lokasiPost === []) {
            return redirect()->back()->withInput()->with('error', 'Pilih minimal satu Lokasi Lapak untuk dicetak.');
        }

        $lokasiTersedia = $this->getLokasiAktif();
        $lokasiDiminta = array_values(array_unique(array_filter(
            array_map(static fn ($value): string => trim((string) $value), $lokasiPost),
            static fn (string $value): bool => $value !== ''
        )));
        $lokasiDipilih = array_values(array_intersect($lokasiTersedia, $lokasiDiminta));

        if ($lokasiDipilih === []) {
            return redirect()->back()->withInput()->with('error', 'Lokasi Lapak yang dipilih tidak valid atau sudah tidak memiliki Penjual aktif.');
        }

        $penjual = $this->getPenjualAktif($lokasiDipilih);
        if ($penjual === []) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada Penjual aktif pada Lokasi Lapak yang dipilih.');
        }

        $days = [];
        $dayNames = ['Sabtu', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        for ($i = 0; $i < 6; $i++) {
            $date = $start->modify('+' . $i . ' day');
            $days[] = [
                'nama' => $dayNames[$i],
                'tanggal' => $date->format('Y-m-d'),
                'label' => $date->format('d-m-Y'),
            ];
        }

        $setting = (new SettingModel())->getCurrent();
        $pdfService = new PdfService();
        $binary = $pdfService->render('pdf_form_mingguan', [
            'setting' => $setting,
            'penjual' => $penjual,
            'days' => $days,
            'lokasiDipilih' => $lokasiDipilih,
        ], 'A4', 'landscape');

        $filename = 'form-iuran-mingguan-' . $start->format('Ymd') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($binary);
    }

    private function getPenjualAktif(?array $lokasi = null): array
    {
        $rows = db_connect()->table('penjual')
            ->select('penjual.id_penjual, penjual.nama_penjual, penjual.lokasi_lapak, golongan_penjual.nama_golongan, golongan_penjual.nominal_iuran')
            ->join('golongan_penjual', 'golongan_penjual.id_golongan = penjual.id_golongan')
            ->where('penjual.status_aktif', 'Aktif')
            ->where('penjual.deleted_at', null)
            ->where('golongan_penjual.deleted_at', null)
            ->orderBy('golongan_penjual.nominal_iuran', 'DESC')
            ->orderBy('penjual.nama_penjual', 'ASC')
            ->get()
            ->getResultArray();

        if ($lokasi === null) {
            return $rows;
        }

        $lokasiMap = [];
        foreach ($lokasi as $namaLokasi) {
            $namaLokasi = trim((string) $namaLokasi);
            if ($namaLokasi !== '') {
                $lokasiMap[$namaLokasi] = true;
            }
        }

        return array_values(array_filter(
            $rows,
            static fn (array $row): bool => isset($lokasiMap[trim((string) ($row['lokasi_lapak'] ?? ''))])
        ));
    }

    private function getLokasiAktif(): array
    {
        $lokasi = [];

        foreach ($this->getPenjualAktif() as $row) {
            $namaLokasi = trim((string) ($row['lokasi_lapak'] ?? ''));
            if ($namaLokasi !== '') {
                $lokasi[$namaLokasi] = true;
            }
        }

        $hasil = array_keys($lokasi);
        natcasesort($hasil);

        return array_values($hasil);
    }

    private function getPenjualSudahBayar(string $tanggal): array
    {
        $rows = db_connect()->table('transaksi_iuran')
            ->select('transaksi_iuran.id_penjual')
            ->join('penjual', 'penjual.id_penjual = transaksi_iuran.id_penjual')
            ->where('transaksi_iuran.tanggal', $tanggal)
            ->where('penjual.status_aktif', 'Aktif')
            ->where('penjual.deleted_at', null)
            ->groupBy('transaksi_iuran.id_penjual')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): int => (int) $row['id_penjual'], $rows);
    }

    private function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('Asia/Jakarta'));

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
