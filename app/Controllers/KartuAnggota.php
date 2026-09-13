<?php

namespace App\Controllers;

use App\Models\PenjualModel;
use App\Models\SettingModel;
use App\Services\KartuAnggotaService;
use RuntimeException;

class KartuAnggota extends BaseController
{
    private KartuAnggotaService $service;
    private string $baseUrl;

    public function __construct()
    {
        $this->service = new KartuAnggotaService();
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        return view('kartu_index', [
            'title' => 'Kartu Anggota Kantin',
            'penjual' => (new PenjualModel())->getWithGolongan(),
            'setting' => (new SettingModel())->getCurrent(),
        ]);
    }

    public function generate(int $id)
    {
        $this->service->ensureCodes($id, false);

        return redirect()->to($this->baseUrl . '/kartu')->with('success', 'Kode kartu dan QR berhasil dibuat.');
    }

    public function regenerate(int $id)
    {
        $this->service->ensureCodes($id, true);

        return redirect()->to($this->baseUrl . '/kartu')->with('success', 'Token verifikasi kartu berhasil diperbarui. QR lama tidak berlaku lagi.');
    }

    public function downloadFront(int $id)
    {
        try {
            $penjual = $this->service->ensureCodes($id, false);
            $binary = $this->service->renderFront($penjual);
        } catch (RuntimeException $e) {
            return redirect()->to($this->baseUrl . '/kartu')->with('error', $e->getMessage());
        }

        return $this->binaryResponse($binary, 'image/jpeg', $this->service->safeName($penjual) . '-depan.jpg');
    }

    public function downloadComplete(int $id)
    {
        try {
            $penjual = $this->service->ensureCodes($id, false);
            $base = $this->service->safeName($penjual);
            $zip = $this->service->zip([
                $base . '-depan.jpg' => $this->service->renderFront($penjual),
                $base . '-belakang.jpg' => $this->service->renderBack(),
            ]);
        } catch (RuntimeException $e) {
            return redirect()->to($this->baseUrl . '/kartu')->with('error', $e->getMessage());
        }

        return $this->binaryResponse($zip, 'application/zip', $base . '-lengkap.zip');
    }

    public function downloadAllFront()
    {
        try {
            $files = [];
            foreach ($this->activeSellers() as $row) {
                $penjual = $this->service->ensureCodes((int) $row['id_penjual'], false);
                $files[$this->service->safeName($penjual) . '-depan.jpg'] = $this->service->renderFront($penjual);
            }

            if ($files === []) {
                throw new RuntimeException('Tidak ada penjual aktif untuk dibuatkan kartu.');
            }

            $zip = $this->service->zip($files);
        } catch (RuntimeException $e) {
            return redirect()->to($this->baseUrl . '/kartu')->with('error', $e->getMessage());
        }

        return $this->binaryResponse($zip, 'application/zip', 'kartu-anggota-semua-depan.zip');
    }

    public function downloadAllComplete()
    {
        try {
            $files = [];
            $back = $this->service->renderBack();
            foreach ($this->activeSellers() as $row) {
                $penjual = $this->service->ensureCodes((int) $row['id_penjual'], false);
                $base = $this->service->safeName($penjual);
                $files[$base . '-depan.jpg'] = $this->service->renderFront($penjual);
                $files[$base . '-belakang.jpg'] = $back;
            }

            if ($files === []) {
                throw new RuntimeException('Tidak ada penjual aktif untuk dibuatkan kartu.');
            }

            $zip = $this->service->zip($files);
        } catch (RuntimeException $e) {
            return redirect()->to($this->baseUrl . '/kartu')->with('error', $e->getMessage());
        }

        return $this->binaryResponse($zip, 'application/zip', 'kartu-anggota-semua-lengkap.zip');
    }

    public function scan()
    {
        return view('kartu_scan', [
            'title' => 'Scan Kartu',
            'baseUrl' => $this->baseUrl,
        ]);
    }

    private function activeSellers(): array
    {
        return db_connect()->table('penjual')
            ->select('id_penjual')
            ->where('status_aktif', 'Aktif')
            ->where('deleted_at', null)
            ->orderBy('nama_penjual', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function binaryResponse(string $binary, string $contentType, string $filename)
    {
        return $this->response
            ->setHeader('Content-Type', $contentType)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($binary);
    }
}
