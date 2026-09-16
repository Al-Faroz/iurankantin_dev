<?php

namespace App\Services;

use App\Models\PenjualModel;
use App\Models\SettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;
use RuntimeException;
use ZipArchive;

class KartuAnggotaService
{
    private PenjualModel $penjualModel;
    private SettingModel $settingModel;
    private ImageManager $imageManager;
    private QrService $qrService;

    public function __construct()
    {
        $this->penjualModel = new PenjualModel();
        $this->settingModel = new SettingModel();
        $this->imageManager = new ImageManager(new Driver());
        $this->qrService = new QrService();
    }

    public function ensureCodes(int $idPenjual, bool $regenerateVerification = false): array
    {
        $penjual = $this->penjualModel->find($idPenjual);
        if ($penjual === null) {
            throw PageNotFoundException::forPageNotFound('Penjual tidak ditemukan.');
        }

        $update = [];

        // Kode kartu dibuat sekali dan dipertahankan selama data penjual masih sama.
        if (empty($penjual['kode_kartu'])) {
            $update['kode_kartu'] = 'KTK-' . str_pad((string) $idPenjual, 4, '0', STR_PAD_LEFT) . '-' . date('Y');
        }

        // Token verifikasi boleh diregenerate. Unique index di database menjadi lapisan
        // perlindungan tambahan terhadap benturan token yang secara praktis sudah sangat kecil.
        if ($regenerateVerification || empty($penjual['kode_verifikasi'])) {
            $update['kode_verifikasi'] = bin2hex(random_bytes(32));
        }

        if ($update !== [] && $this->penjualModel->update($idPenjual, $update) === false) {
            throw new RuntimeException('Kode kartu gagal disimpan ke database. Silakan coba kembali.');
        }

        return $this->detail($idPenjual);
    }

    public function detail(int $idPenjual): array
    {
        $row = $this->penjualModel
            ->select('penjual.*, golongan_penjual.nama_golongan, golongan_penjual.nominal_iuran')
            ->join('golongan_penjual', 'golongan_penjual.id_golongan = penjual.id_golongan', 'left')
            ->where('penjual.id_penjual', $idPenjual)
            ->first();

        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Penjual tidak ditemukan.');
        }

        return $row;
    }

    public function renderFront(array $penjual): string
    {
        $setting = $this->settingModel->getCurrent();
        $background = $this->backgroundPath($setting['background_kartu_depan'] ?? null, 'Background kartu depan belum diupload melalui Setting.');
        $image = $this->imageManager->read($background)->resize(1011, 638);

        $qrData = $this->qrService->png($this->qrService->verificationUrl((string) $penjual['kode_verifikasi']), 120);
        $qrImage = $this->imageManager->read($qrData)->resize(120, 120);
        $image->place($qrImage, 'top-left', 810, 375);

        $fontRegular = ROOTPATH . 'assets/fonts/Poppins/Poppins-Regular.ttf';
        $fontBold = ROOTPATH . 'assets/fonts/Poppins/Poppins-Bold.ttf';

        $image->text(mb_strtoupper((string) $penjual['nama_penjual'], 'UTF-8'), 40, 175, function (FontFactory $font) use ($fontBold): void {
            $font->filename($fontBold);
            $font->size(42);
            $font->color('#ffffff');
            $font->valign('top');
            $font->lineHeight(1.05);
            $font->wrap(570);
        });

        $this->writeMeta($image, 'GOLONGAN', (string) ($penjual['nama_golongan'] ?? '-'), 40, 340, $fontRegular, $fontBold);
        $this->writeMeta($image, 'LOKASI / LAPAK', (string) ($penjual['lokasi_lapak'] ?: '-'), 270, 340, $fontRegular, $fontBold);
        $this->writeMeta($image, 'NO. HP', (string) ($penjual['no_hp'] ?: '-'), 40, 400, $fontRegular, $fontBold);
        $this->writeMeta($image, 'BERGABUNG', date('d-m-Y', strtotime((string) $penjual['tanggal_daftar'])), 270, 400, $fontRegular, $fontBold);
        $this->writeAddress($image, (string) ($penjual['alamat'] ?: '-'), 40, 465, $fontRegular, $fontBold);

        $image->text((string) $penjual['kode_kartu'], 870, 505, function (FontFactory $font) use ($fontRegular): void {
            $font->filename($fontRegular);
            $font->size(9);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('top');
        });

        return (string) $image->toJpeg(92, progressive: true, strip: true);
    }

    public function renderBack(): string
    {
        $setting = $this->settingModel->getCurrent();
        $background = $this->backgroundPath($setting['background_kartu_belakang'] ?? null, 'Background kartu belakang belum diupload melalui Setting.');
        $image = $this->imageManager->read($background)->resize(1011, 638);

        return (string) $image->toJpeg(92, progressive: true, strip: true);
    }

    public function zip(array $files): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZIP belum aktif. Aktifkan ext-zip untuk download kartu lengkap/bulk.');
        }

        $tmp = tempnam(WRITEPATH . 'cache', 'kartu_');
        if ($tmp === false) {
            throw new RuntimeException('Gagal membuat file ZIP sementara.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tmp);
            throw new RuntimeException('Gagal membuat arsip ZIP kartu.');
        }

        foreach ($files as $name => $binary) {
            $zip->addFromString($name, $binary);
        }
        $zip->close();

        try {
            $binary = file_get_contents($tmp);
            if ($binary === false) {
                throw new RuntimeException('Gagal membaca arsip ZIP kartu.');
            }
            return $binary;
        } finally {
            @unlink($tmp);
        }
    }

    public function safeName(array $penjual): string
    {
        $name = strtolower((string) $penjual['nama_penjual']);
        $name = preg_replace('/[^a-z0-9]+/i', '-', $name) ?: 'penjual';

        return trim($name, '-') . '-' . strtolower((string) $penjual['kode_kartu']);
    }

    private function writeMeta($image, string $label, string $value, int $x, int $y, string $fontRegular, string $fontBold): void
    {
        $image->text($label, $x, $y, function (FontFactory $font) use ($fontRegular): void {
            $font->filename($fontRegular);
            $font->size(11);
            $font->color('#d9d9d9');
            $font->valign('top');
        });

        $image->text($value, $x, $y + 18, function (FontFactory $font) use ($fontBold): void {
            $font->filename($fontBold);
            $font->size(19);
            $font->color('#ffffff');
            $font->valign('top');
            $font->wrap(210);
        });
    }

    private function writeAddress($image, string $value, int $x, int $y, string $fontRegular, string $fontBold): void
    {
        $image->text('ALAMAT', $x, $y, function (FontFactory $font) use ($fontRegular): void {
            $font->filename($fontRegular);
            $font->size(10);
            $font->color('#d9d9d9');
            $font->valign('top');
        });

        $image->text($value, $x, $y + 17, function (FontFactory $font) use ($fontBold): void {
            $font->filename($fontBold);
            $font->size(15);
            $font->color('#ffffff');
            $font->valign('top');
            $font->lineHeight(1.05);
            $font->wrap(650);
        });
    }

    private function backgroundPath(?string $relativePath, string $message): string
    {
        if ($relativePath === null || $relativePath === '') {
            throw new RuntimeException($message);
        }

        $fullPath = ROOTPATH . ltrim($relativePath, '/');
        if (! is_file($fullPath)) {
            throw new RuntimeException($message);
        }

        return $fullPath;
    }
}
