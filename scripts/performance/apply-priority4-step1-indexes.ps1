# Priority 4 - Step 1: index database untuk query dashboard (aman, tidak ubah data/logic).
# Jalankan: powershell -File scripts\performance\apply-priority4-step1-indexes.ps1

$ErrorActionPreference = 'Stop'
$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..\..')

Write-Host ""
Write-Host "=== Priority 4 Step 1: dashboard indexes ===" -ForegroundColor Cyan
Write-Host ""

Push-Location $projectRoot

$userCountBefore = (php scripts/performance/check-db-users.php 2>&1 | Select-String 'users_count=(\d+)' | ForEach-Object { $_.Matches[0].Groups[1].Value })
Write-Host "Users sebelum: $userCountBefore"

php artisan migrate --force --no-interaction --path=database/migrations/2026_06_28_100000_add_priority4_dashboard_user_indexes.php 2>&1 | Out-Host
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    Write-Error "Migrasi index gagal."
}

$userCountAfter = (php scripts/performance/check-db-users.php 2>&1 | Select-String 'users_count=(\d+)' | ForEach-Object { $_.Matches[0].Groups[1].Value })
Write-Host "Users sesudah: $userCountAfter"

if ($userCountBefore -ne $userCountAfter) {
    Pop-Location
    Write-Error "Jumlah user berubah - hentikan dan periksa database."
}

php artisan test --filter=TaskFlowSmoke 2>&1 | Out-Host
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    Write-Error "Smoke test gagal setelah migrasi index."
}

Pop-Location

Write-Host ""
Write-Host "Step 1 selesai." -ForegroundColor Green
Write-Host ""
