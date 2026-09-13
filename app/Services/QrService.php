<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;

class QrService
{
    public function png(string $data, int $size = 300): string
    {
        $builder = new Builder(
            data: $data,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: $size,
            margin: 8
        );

        return $builder->build()->getString();
    }

    public function verificationUrl(string $token): string
    {
        return rtrim((string) config('App')->baseURL, '/') . '/verifikasi/' . rawurlencode($token);
    }
}
