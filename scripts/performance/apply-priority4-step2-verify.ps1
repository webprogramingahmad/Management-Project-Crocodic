# Priority 4 - Step 2+: verifikasi setelah optimasi query dashboard.
# Tidak mengubah database. Hanya clear cache + smoke test.

$ErrorActionPreference = 'Stop'
$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..\..')

Write-Host ""
Write-Host "=== Priority 4 Step 2: verify dashboard query optimization ===" -ForegroundColor Cyan
Write-Host ""

Push-Location $projectRoot

$userCount = (php scripts/performance/check-db-users.php 2>&1 | Select-String 'users_count=(\d+)' | ForEach-Object { $_.Matches[0].Groups[1].Value })
Write-Host "Users: $userCount"

php artisan optimize:clear 2>&1 | Out-Host

php artisan test --filter=TaskFlowSmoke 2>&1 | Out-Host
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    Write-Error "Smoke test gagal."
}

php scripts/performance/verify-priority3-health.php 2>&1 | Out-Host
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    Write-Error "Health check gagal."
}

Pop-Location

Write-Host ""
Write-Host "Step 2 selesai. Siap load test: .\run-k6.bat staff 50 2m" -ForegroundColor Green
Write-Host ""
