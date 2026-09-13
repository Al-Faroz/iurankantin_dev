<?php

namespace App\Controllers;

use App\Models\SetoranPimpinanModel;
use App\Models\SettingModel;
use App\Services\PdfService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;

class Setoran extends BaseController
{
    private SetoranPimpinanModel $model;
    private SettingModel $settingModel;
    private string $baseUrl;

    public function __construct()
    {
        $this->model = new SetoranPimpinanModel();
        $this->settingModel = new SettingModel();
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        $rows = $this->model
            ->select('setoran_pimpinan.*, users.nama AS nama_operator')
            ->join('users', 'users.id_user = setoran_pimpinan.id_operator')
            ->orderBy('tanggal_form', 'DESC')
            ->orderBy('id_setoran', 'DESC')
            ->findAll();

        return view('setoran_index', [
            'title' => 'Setoran Pimpinan',
            'setoran' => $rows,
        ]);
    }

    public function cetakForm()
    {
        return view('setoran_cetak_form', [
            'title' => 'Cetak Form Setoran',
            'tanggalDefault' => Time::now('Asia/Jakarta')->toDateString(),
        ]);
    }

    public function cetakPdf()
    {
        if (! $this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tanggalForm = (string) $this->request->getPost('tanggal_form');
        $periodeAwal = (string) $this->request->getPost('periode_awal');
        $periodeAkhir = (string) $this->request->getPost('periode_akhir');
        if ($periodeAwal > $periodeAkhir) {
            return redirect()->back()->withInput()->with('error', 'Periode awal tidak boleh melewati periode akhir.');
        }

        return $this->pdfResponse(
            $tanggalForm,
            $periodeAwal,
            $periodeAkhir,
            (float) $this->request->getPost('nominal'),
            'form-setoran-' . date('Ymd', strtotime($tanggalForm)) . '.pdf'
        );
    }

    public function cetakUlang(int $id)
    {
        $setoran = $this->model->find($id);
        if ($setoran === null) {
            throw PageNotFoundException::forPageNotFound('Setoran pimpinan tidak ditemukan.');
        }

        return $this->pdfResponse(
            (string) $setoran['tanggal_form'],
            (string) $setoran['periode_awal'],
            (string) $setoran['periode_akhir'],
            (float) $setoran['nominal'],
            'form-setoran-' . $id . '-' . date('Ymd', strtotime((string) $setoran['tanggal_form'])) . '.pdf'
        );
    }

    public function input()
    {
        return view('setoran_input', [
            'title' => 'Input Setoran Resmi',
            'tanggalDefault' => Time::now('Asia/Jakarta')->toDateString(),
            'setoran' => null,
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->payload();
        if ($payload['periode_awal'] > $payload['periode_akhir']) {
            return redirect()->back()->withInput()->with('error', 'Periode awal tidak boleh melewati periode akhir.');
        }

        $payload['id_operator'] = (int) session()->get('id_user');
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->model->insert($payload);

        return redirect()->to($this->baseUrl . '/setoran')->with('success', 'Setoran resmi berhasil dicatat ke database.');
    }

    public function edit(int $id)
    {
        $setoran = $this->model->find($id);
        if ($setoran === null) {
            throw PageNotFoundException::forPageNotFound('Setoran pimpinan tidak ditemukan.');
        }

        return view('setoran_input', [
            'title' => 'Edit Setoran Pimpinan',
            'tanggalDefault' => Time::now('Asia/Jakarta')->toDateString(),
            'setoran' => $setoran,
        ]);
    }

    public function update(int $id)
    {
        if ($this->model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Setoran pimpinan tidak ditemukan.');
        }

        if (! $this->validate($this->rules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->payload();
        if ($payload['periode_awal'] > $payload['periode_akhir']) {
            return redirect()->back()->withInput()->with('error', 'Periode awal tidak boleh melewati periode akhir.');
        }

        // id_operator dan created_at dipertahankan sebagai jejak pencatat awal.
        $this->model->update($id, $payload);

        return redirect()->to($this->baseUrl . '/setoran')->with('success', 'Setoran pimpinan berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        if ($this->model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Setoran pimpinan tidak ditemukan.');
        }

        // Setoran merupakan transaksi. Koreksi data salah menggunakan hard delete sesuai kebutuhan aplikasi.
        $this->model->delete($id, true);

        return redirect()->to($this->baseUrl . '/setoran')->with('success', 'Setoran pimpinan berhasil dihapus permanen.');
    }

    private function pdfResponse(
        string $tanggalForm,
        string $periodeAwal,
        string $periodeAkhir,
        float $nominal,
        string $filename
    ) {
        $pdfService = new PdfService();
        $setting = $this->settingModel->getCurrent();
        $binary = $pdfService->render('pdf_bukti_setoran', [
            'setting' => $setting,
            'logoDataUri' => $pdfService->imageDataUri($setting['logo'] ?? null),
            'tanggalForm' => $tanggalForm,
            'periodeAwal' => $periodeAwal,
            'periodeAkhir' => $periodeAkhir,
            'nominal' => $nominal,
        ]);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($binary);
    }

    private function payload(): array
    {
        return [
            'tanggal_form' => (string) $this->request->getPost('tanggal_form'),
            'periode_awal' => (string) $this->request->getPost('periode_awal'),
            'periode_akhir' => (string) $this->request->getPost('periode_akhir'),
            'nominal' => (float) $this->request->getPost('nominal'),
            'keterangan' => trim((string) $this->request->getPost('keterangan')) ?: null,
        ];
    }

    private function rules(bool $withKeterangan): array
    {
        $rules = [
            'tanggal_form' => 'required|valid_date[Y-m-d]',
            'periode_awal' => 'required|valid_date[Y-m-d]',
            'periode_akhir' => 'required|valid_date[Y-m-d]',
            'nominal' => 'required|numeric|greater_than[0]',
        ];

        if ($withKeterangan) {
            $rules['keterangan'] = 'permit_empty|max_length[255]';
        }

        return $rules;
    }
}
