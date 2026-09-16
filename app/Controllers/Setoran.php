<?php

namespace App\Controllers;

use App\Models\SetoranPimpinanModel;
use App\Models\SettingModel;
use App\Services\AuditTransaksiService;
use App\Services\BuktiSetoranService;
use App\Services\BuktiTransaksiStorageService;
use App\Services\PdfService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;
use RuntimeException;

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
        $today = Time::now('Asia/Jakarta')->toDateString();

        return view('setoran_cetak_form', [
            'title' => 'Cetak Form Setoran',
            'tanggalDefault' => $today,
            'tanggalMaks' => $today,
        ]);
    }

    public function cetakPdf()
    {
        if (! $this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tanggalForm = (string) $this->request->getPost('tanggal_form');
        if ($tanggalForm > Time::now('Asia/Jakarta')->toDateString()) {
            return redirect()->back()->withInput()->with('error', 'Tanggal Form Setoran tidak boleh melebihi hari ini.');
        }

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
        $today = Time::now('Asia/Jakarta')->toDateString();

        return view('setoran_input', [
            'title' => 'Input Setoran Resmi',
            'tanggalDefault' => $today,
            'tanggalMaks' => $today,
            'saldoTersedia' => $this->saldoKas(),
            'setoran' => null,
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->payload();
        if ($payload['tanggal_form'] > Time::now('Asia/Jakarta')->toDateString()) {
            return redirect()->back()->withInput()->with('error', 'Tanggal Setoran Resmi tidak boleh melebihi hari ini.');
        }
        if ($payload['periode_awal'] > $payload['periode_akhir']) {
            return redirect()->back()->withInput()->with('error', 'Periode awal tidak boleh melewati periode akhir.');
        }

        $saldoSebelum = $this->saldoKas();
        $melebihiSaldo = (float) $payload['nominal'] > $saldoSebelum;

        $file = $this->request->getFile('bukti_setoran');
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return redirect()->back()->withInput()->with('error', 'Foto bukti setoran wajib diunggah untuk setoran resmi baru.');
        }

        $buktiPath = null;
        try {
            $buktiPath = (new BuktiSetoranService())->simpan($file);
            $payload['bukti_setoran'] = $buktiPath;
            $payload['id_operator'] = (int) session()->get('id_user');
            $payload['created_at'] = date('Y-m-d H:i:s');

            $idSetoran = $this->model->insert($payload, true);
            if ($idSetoran === false) {
                throw new RuntimeException('Gagal menyimpan setoran resmi ke database.');
            }
        } catch (RuntimeException $e) {
            if ($buktiPath !== null) {
                $this->hapusBukti($buktiPath);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        (new AuditTransaksiService())->catat('CREATE', 'setoran', (int) $idSetoran, [
            'setoran' => $this->ringkasSetoran($payload),
            'saldo_sebelum' => $saldoSebelum,
            'melebihi_saldo' => $melebihiSaldo,
        ]);

        return redirect()->to($this->baseUrl . '/setoran')->with('success', 'Setoran resmi dan bukti foto berhasil dicatat.');
    }

    public function edit(int $id)
    {
        $setoran = $this->model->find($id);
        if ($setoran === null) {
            throw PageNotFoundException::forPageNotFound('Setoran pimpinan tidak ditemukan.');
        }

        $today = Time::now('Asia/Jakarta')->toDateString();

        return view('setoran_input', [
            'title' => 'Edit Setoran Pimpinan',
            'tanggalDefault' => $today,
            'tanggalMaks' => $today,
            'saldoTersedia' => $this->saldoKas() + (float) $setoran['nominal'],
            'setoran' => $setoran,
        ]);
    }

    public function update(int $id)
    {
        $setoranLama = $this->model->find($id);
        if ($setoranLama === null) {
            throw PageNotFoundException::forPageNotFound('Setoran pimpinan tidak ditemukan.');
        }

        if (! $this->validate($this->rules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->payload();
        if ($payload['tanggal_form'] > Time::now('Asia/Jakarta')->toDateString()) {
            return redirect()->back()->withInput()->with('error', 'Tanggal Setoran Resmi tidak boleh melebihi hari ini.');
        }
        if ($payload['periode_awal'] > $payload['periode_akhir']) {
            return redirect()->back()->withInput()->with('error', 'Periode awal tidak boleh melewati periode akhir.');
        }

        $saldoSebelum = $this->saldoKas() + (float) $setoranLama['nominal'];
        $melebihiSaldo = (float) $payload['nominal'] > $saldoSebelum;
        $buktiBaru = null;
        $file = $this->request->getFile('bukti_setoran');

        try {
            if ($file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                $buktiBaru = (new BuktiSetoranService())->simpan($file);
                $payload['bukti_setoran'] = $buktiBaru;
            }

            // id_operator dan created_at dipertahankan sebagai jejak pencatat awal.
            if ($this->model->update($id, $payload) === false) {
                throw new RuntimeException('Gagal memperbarui setoran pimpinan.');
            }

            if ($buktiBaru !== null && ! empty($setoranLama['bukti_setoran'])) {
                $this->hapusBukti((string) $setoranLama['bukti_setoran']);
            }
        } catch (RuntimeException $e) {
            if ($buktiBaru !== null) {
                $this->hapusBukti($buktiBaru);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $setoranBaru = array_merge($setoranLama, $payload);
        (new AuditTransaksiService())->catat('UPDATE', 'setoran', $id, [
            'sebelum' => $this->ringkasSetoran($setoranLama),
            'sesudah' => $this->ringkasSetoran($setoranBaru),
            'saldo_sebelum' => $saldoSebelum,
            'melebihi_saldo' => $melebihiSaldo,
        ]);

        return redirect()->to($this->baseUrl . '/setoran')->with('success', 'Setoran pimpinan berhasil diperbarui.');
    }

    public function bukti(int $id)
    {
        $setoran = $this->model->find($id);
        if ($setoran === null) {
            throw PageNotFoundException::forPageNotFound('Setoran pimpinan tidak ditemukan.');
        }

        $fullPath = (new BuktiTransaksiStorageService())->resolveSetoran($setoran['bukti_setoran'] ?? null);
        if ($fullPath === null) {
            throw PageNotFoundException::forPageNotFound('Bukti setoran tidak ditemukan.');
        }

        return $this->imageResponse($fullPath);
    }

    public function hapus(int $id)
    {
        $setoran = $this->model->find($id);
        if ($setoran === null) {
            throw PageNotFoundException::forPageNotFound('Setoran pimpinan tidak ditemukan.');
        }

        // Bukti baru dihapus hanya jika transaksi benar-benar berhasil dihapus dari database.
        if ($this->model->delete($id, true) === false) {
            return redirect()->to($this->baseUrl . '/setoran')
                ->with('error', 'Setoran pimpinan gagal dihapus. Silakan coba kembali.');
        }

        (new AuditTransaksiService())->catat('DELETE', 'setoran', $id, [
            'sebelum' => $this->ringkasSetoran($setoran),
        ]);

        if (! empty($setoran['bukti_setoran'])) {
            $this->hapusBukti((string) $setoran['bukti_setoran']);
        }

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
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($binary);
    }

    private function imageResponse(string $fullPath)
    {
        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw PageNotFoundException::forPageNotFound('Bukti setoran tidak dapat dibaca.');
        }

        $mime = mime_content_type($fullPath) ?: 'image/jpeg';
        $filename = basename($fullPath);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'private, no-store, max-age=0')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($content);
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

    private function saldoKas(): float
    {
        $db = db_connect();
        $iuran = (float) ($db->table('transaksi_iuran')->selectSum('nominal', 'total')->get()->getRowArray()['total'] ?? 0);
        $pengeluaran = (float) ($db->table('transaksi_pengeluaran')->selectSum('nominal', 'total')->get()->getRowArray()['total'] ?? 0);
        $setoran = (float) ($db->table('setoran_pimpinan')->selectSum('nominal', 'total')->get()->getRowArray()['total'] ?? 0);

        return $iuran - $pengeluaran - $setoran;
    }

    private function ringkasSetoran(array $row): array
    {
        return [
            'tanggal_form' => (string) ($row['tanggal_form'] ?? ''),
            'periode_awal' => (string) ($row['periode_awal'] ?? ''),
            'periode_akhir' => (string) ($row['periode_akhir'] ?? ''),
            'nominal' => (float) ($row['nominal'] ?? 0),
            'keterangan' => $row['keterangan'] ?? null,
            'ada_bukti' => ! empty($row['bukti_setoran']),
        ];
    }

    private function hapusBukti(string $relativePath): void
    {
        (new BuktiTransaksiStorageService())->hapusSetoran($relativePath);
    }
}
