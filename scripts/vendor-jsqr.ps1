$ErrorActionPreference = 'Stop'

$commit = '34d8eec1ec5d85496f3948ff02fcfe6406f89d81'
$jsExpectedBlob = '429896a9d087eb3977cf67521f738d01af17c808'
$licenseExpectedBlob = '8f71f43fee3f78649d238238cbde51e6d7055c82'

$projectRoot = Split-Path -Parent $PSScriptRoot
$targetDir = Join-Path $projectRoot 'assets-app\qrcode-lib'
$jsTarget = Join-Path $targetDir 'jsQR.js'
$licenseTarget = Join-Path $targetDir 'jsQR.LICENSE'

New-Item -ItemType Directory -Force -Path $targetDir | Out-Null

function Download-And-VerifyGitBlob {
    param(
        [Parameter(Mandatory = $true)][string]$Url,
        [Parameter(Mandatory = $true)][string]$Target,
        [Parameter(Mandatory = $true)][string]$ExpectedBlob
    )

    $temp = "$Target.download"

    try {
        Invoke-WebRequest -Uri $Url -OutFile $temp -UseBasicParsing

        $actualBlob = (& git hash-object $temp).Trim()
        if ($LASTEXITCODE -ne 0) {
            throw 'git hash-object gagal dijalankan.'
        }

        if ($actualBlob -ne $ExpectedBlob) {
            throw "Checksum Git blob tidak cocok. Expected=$ExpectedBlob Actual=$actualBlob"
        }

        Move-Item -Force $temp $Target
    }
    finally {
        if (Test-Path $temp) {
            Remove-Item -Force $temp
        }
    }
}

Download-And-VerifyGitBlob `
    -Url "https://raw.githubusercontent.com/cozmo/jsQR/$commit/dist/jsQR.js" `
    -Target $jsTarget `
    -ExpectedBlob $jsExpectedBlob

Download-And-VerifyGitBlob `
    -Url "https://raw.githubusercontent.com/cozmo/jsQR/$commit/LICENSE" `
    -Target $licenseTarget `
    -ExpectedBlob $licenseExpectedBlob

Write-Host 'jsQR 1.4.0 berhasil disimpan dan diverifikasi:' -ForegroundColor Green
Write-Host "  $jsTarget"
Write-Host "  $licenseTarget"
Write-Host ''
Write-Host 'Selanjutnya track vendor asset:' -ForegroundColor Cyan
Write-Host '  git add assets-app/qrcode-lib/jsQR.js assets-app/qrcode-lib/jsQR.LICENSE'
Write-Host '  git commit -m "build: vendor jsQR 1.4.0 locally"'
Write-Host '  git push origin main'
