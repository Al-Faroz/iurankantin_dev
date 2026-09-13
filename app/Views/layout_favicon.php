<?php
$faviconBaseUrl = $baseUrl ?? rtrim((string) config('App')->baseURL, '/');
$faviconPath = null;

try {
    $settingFavicon = db_connect()->table('setting')
        ->select('logo')
        ->where('id_setting', 1)
        ->get()
        ->getRowArray();

    $candidate = (string) ($settingFavicon['logo'] ?? '');
    if ($candidate !== '' && str_starts_with($candidate, 'uploads/branding/') && is_file(ROOTPATH . $candidate)) {
        $faviconPath = $candidate;
    }
} catch (\Throwable $e) {
    // Fresh install atau database belum siap: gunakan favicon bawaan.
}

if ($faviconPath !== null) {
    $extension = strtolower(pathinfo($faviconPath, PATHINFO_EXTENSION));
    $faviconType = match ($extension) {
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        default => 'image/x-icon',
    };
    $faviconHref = $faviconBaseUrl . '/' . ltrim($faviconPath, '/');
} else {
    $faviconType = 'image/x-icon';
    $faviconHref = $faviconBaseUrl . '/favicon.ico';
}
?>
<link rel="icon" type="<?= esc($faviconType) ?>" href="<?= esc($faviconHref) ?>">
<link rel="shortcut icon" href="<?= esc($faviconHref) ?>">
