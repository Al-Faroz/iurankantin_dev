<?php

namespace App\Controllers;

use App\Models\SettingModel;
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
        $tanggal = (string) ($this->request->getGet('tanggal') ?: Time::now('Asia/Jakarta')->toDateString());
        if (! $this->isValidDate($tanggal)) {
            $tanggal = Time::now('Asia/Jakarta')->toDateString();
        }

        return view('iuran_bulk', [
            'title' => 'Input Iuran Harian',
            'tanggalDefault' => $tanggal,
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
        $bayar = $this->request->getPost('bayar');
        $nominal = $this->request->getPost('nominal');
        $keterangan = $this->request->getPost('keterangan');

        try {
            $jumlah = (new IuranService())->simpanBulk(
                $tanggal,
                (int) session()->get('id_user'),
                is_array($bayar) ? $bayar : [],
                is_array($nominal) ? $nominal : [],
                is_array($keterangan) ? $keterangan : []
            );
        } catch (RuntimeException $e) {
            return redirect()->to($this->baseUrl . '/iuran?' . http_build_query(['tanggal' => $tanggal]))
                ->withInput()
                ->with('error', $e->getMessage());
        }

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
            'logoDataUri' => $pdfService->imageDataUri($setting['logo'] ?? null),
            'penjual' => $this->getPenjualAktif(),
            'days' => $days,
        ], 'A4', 'landscape');

        $filename = 'form-iuran-mingguan-' . $start->format('Ymd') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($binary);
    }

    private function getPenjualAktif(): array
    {
        return db_connect()->table('penjual')
            ->select('penjual.id_penjual, penjual.nama_penjual, penjual.lokasi_lapak, golongan_penjual.nama_golongan, golongan_penjual.nominal_iuran')
            ->join('golongan_penjual', 'golongan_penjual.id_golongan = penjual.id_golongan')
            ->where('penjual.status_aktif', 'Aktif')
            ->where('penjual.deleted_at', null)
            ->where('golongan_penjual.deleted_at', null)
            ->orderBy('golongan_penjual.nominal_iuran', 'DESC')
            ->orderBy('penjual.nama_penjual', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getPenjualSudahBayar(string $tanggal): array
    {
        $rows = db_connect()->table('transaksi_iuran')
            ->select('id_penjual')
            ->where('tanggal', $tanggal)
            ->groupBy('id_penjual')
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
