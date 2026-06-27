# Hapus Laravel production caches — kembali ke mode development normal.
$ErrorActionPreference = 'Stop'
$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..\..')

Write-Host ""
Write-Host "=== Clear Priority 2 Laravel cache ===" -ForegroundColor Cyan
Write-Host ""

Push-Location $projectRoot

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

Pop-Location

Write-Host ""
Write-Host "Selesai. Config/route/view cache dihapus." -ForegroundColor Green
Write-Host ""
