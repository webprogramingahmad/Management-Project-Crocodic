# Priority 5: verifikasi optimasi panel notifikasi dashboard.
$ErrorActionPreference = 'Stop'
$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..\..')

Write-Host ""
Write-Host "=== Priority 5: verify dashboard notifications ===" -ForegroundColor Cyan
Write-Host ""

Push-Location $projectRoot

$userCount = (php scripts/performance/check-db-users.php 2>&1 | Select-String 'users_count=(\d+)' | ForEach-Object { $_.Matches[0].Groups[1].Value })
Write-Host "Users: $userCount"

php artisan optimize:clear 2>&1 | Out-Host

php artisan test --filter=Task 2>&1 | Out-Host
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    Write-Error "Task tests gagal."
}

Pop-Location

Write-Host ""
Write-Host "Priority 5 selesai. Siap load test: .\run-k6.bat staff 50 2m" -ForegroundColor Green
Write-Host ""
