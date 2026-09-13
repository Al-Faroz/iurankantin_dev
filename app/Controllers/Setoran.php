<?php

namespace App\Controllers;

use App\Models\SetoranPimpinanModel;
use App\Models\SettingModel;
use App\Services\PdfService;
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

        $periodeAwal = (string) $this->request->getPost('periode_awal');
        $periodeAkhir = (string) $this->request->getPost('periode_akhir');
        if ($periodeAwal > $periodeAkhir) {
            return redirect()->back()->withInput()->with('error', 'Periode awal tidak boleh melewati periode akhir.');
        }

        $pdfService = new PdfService();
        $setting = $this->settingModel->getCurrent();
        $binary = $pdfService->render('pdf_bukti_setoran', [
            'setting' => $setting,
            'logoDataUri' => $pdfService->imageDataUri($setting['logo'] ?? null),
            'tanggalForm' => (string) $this->request->getPost('tanggal_form'),
            'periodeAwal' => $periodeAwal,
            'periodeAkhir' => $periodeAkhir,
            'nominal' => (float) $this->request->getPost('nominal'),
        ]);

        $filename = 'form-setoran-' . date('Ymd', strtotime((string) $this->request->getPost('tanggal_form'))) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($binary);
    }

    public function input()
    {
        return view('setoran_input', [
            'title' => 'Input Setoran Resmi',
            'tanggalDefault' => Time::now('Asia/Jakarta')->toDateString(),
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $periodeAwal = (string) $this->request->getPost('periode_awal');
        $periodeAkhir = (string) $this->request->getPost('periode_akhir');
        if ($periodeAwal > $periodeAkhir) {
            return redirect()->back()->withInput()->with('error', 'Periode awal tidak boleh melewati periode akhir.');
        }

        $this->model->insert([
            'tanggal_form' => (string) $this->request->getPost('tanggal_form'),
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'nominal' => (float) $this->request->getPost('nominal'),
            'keterangan' => trim((string) $this->request->getPost('keterangan')) ?: null,
            'id_operator' => (int) session()->get('id_user'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to($this->baseUrl . '/setoran')->with('success', 'Setoran resmi berhasil dicatat ke database.');
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
