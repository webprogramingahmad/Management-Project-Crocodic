# Priority 2: Laravel production caches (config, route, view).
# Jalankan sebelum load test. Untuk development aktif, pakai clear-priority2-laravel-cache.ps1

$ErrorActionPreference = 'Stop'
$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..\..')

Write-Host ""
Write-Host "=== Priority 2: Laravel cache (config + route + view) ===" -ForegroundColor Cyan
Write-Host "Project: $projectRoot"
Write-Host ""

Push-Location $projectRoot

php artisan config:cache
if ($LASTEXITCODE -ne 0) { Pop-Location; exit $LASTEXITCODE }

php artisan route:cache
if ($LASTEXITCODE -ne 0) { Pop-Location; exit $LASTEXITCODE }

php artisan view:cache
if ($LASTEXITCODE -ne 0) { Pop-Location; exit $LASTEXITCODE }

Pop-Location

Write-Host ""
Write-Host "Selesai. Cache Laravel aktif untuk load test." -ForegroundColor Green
Write-Host "Catatan: setelah ubah .env / route / view, jalankan clear-priority2-laravel-cache.ps1"
Write-Host ""
