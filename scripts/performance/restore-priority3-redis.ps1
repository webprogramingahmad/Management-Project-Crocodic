param(
    [Parameter(Mandatory = $true)]
    [string]$BackupDir
)

$ErrorActionPreference = 'Stop'
$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..\..')
$envBackup = Join-Path $BackupDir '.env'
$envPath = Join-Path $projectRoot '.env'

if (-not (Test-Path $envBackup)) {
    Write-Error "Backup .env tidak ditemukan: $envBackup"
}

Write-Host "Restore Priority 3 dari: $BackupDir" -ForegroundColor Cyan

Copy-Item $envBackup $envPath -Force
Write-Host "Restored: .env" -ForegroundColor Green

Push-Location $projectRoot
php artisan optimize:clear 2>&1 | Out-Host
Pop-Location

Write-Host ""
Write-Host "Selesai. Login ulang jika session driver berubah." -ForegroundColor Yellow
Write-Host ""
