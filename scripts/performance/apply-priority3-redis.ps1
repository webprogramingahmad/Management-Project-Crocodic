# Priority 3: Redis cache + session (dengan backup .env dan verifikasi data).
# Jika Redis belum terpasang, gunakan -UseXamppFallback untuk mengurangi beban MySQL tanpa Redis.
#
# Jalankan dari folder project:
#   powershell -File scripts\performance\apply-priority3-redis.ps1
#   powershell -File scripts\performance\apply-priority3-redis.ps1 -UseXamppFallback

param(
    [switch]$UseXamppFallback
)

$ErrorActionPreference = 'Stop'
$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..\..')
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupDir = Join-Path $PSScriptRoot "backups\priority3-$stamp"
$envPath = Join-Path $projectRoot '.env'

Write-Host ""
Write-Host "=== Priority 3: Cache & Session ===" -ForegroundColor Cyan
Write-Host "Project: $projectRoot"
Write-Host ""

if (-not (Test-Path $envPath)) {
    Write-Error ".env tidak ditemukan: $envPath"
}

New-Item -ItemType Directory -Force -Path $backupDir | Out-Null
Copy-Item $envPath (Join-Path $backupDir '.env') -Force
Write-Host "Backup .env -> $backupDir" -ForegroundColor Green

Push-Location $projectRoot

$userCountBefore = (php scripts/performance/check-db-users.php 2>&1 | Select-String 'users_count=(\d+)' | ForEach-Object { $_.Matches[0].Groups[1].Value })
Write-Host "Sebelum: users_count=$userCountBefore"

if (-not (Test-Path 'vendor/predis/predis')) {
    Write-Host "Menginstall predis/predis (client Redis PHP, tanpa ekstensi)..." -ForegroundColor Yellow
    composer require predis/predis:^2.3 --no-interaction --no-progress
    if ($LASTEXITCODE -ne 0) {
        Pop-Location
        Write-Error "composer require predis/predis gagal."
    }
}

$redisOk = $false
if (-not $UseXamppFallback) {
    php scripts/performance/check-priority3-redis.php 2>&1
    if ($LASTEXITCODE -eq 0) {
        $redisOk = $true
    }
}

function Set-EnvLine {
    param(
        [string]$Content,
        [string]$Key,
        [string]$Value
    )
    $pattern = "(?m)^$([regex]::Escape($Key))=.*$"
    if ($Content -match $pattern) {
        return [regex]::Replace($Content, $pattern, "$Key=$Value")
    }
    return $Content.TrimEnd() + "`r`n$Key=$Value`r`n"
}

$envContent = Get-Content $envPath -Raw

if ($redisOk) {
    Write-Host ""
    Write-Host "Mode: Redis (cache + session)" -ForegroundColor Green
    $envContent = Set-EnvLine $envContent 'REDIS_CLIENT' 'predis'
    $envContent = Set-EnvLine $envContent 'CACHE_STORE' 'redis'
    $envContent = Set-EnvLine $envContent 'SESSION_DRIVER' 'redis'
} else {
    Write-Host ""
    Write-Host "Redis belum tersedia - mode fallback XAMPP (aman, tanpa Redis)" -ForegroundColor Yellow
    Write-Host "  CACHE_STORE=database  (tabel cache, bukan file lock)"
    Write-Host "  SESSION_DRIVER=file   (session keluar dari MySQL, kurangi beban DB)"
    Write-Host ""
    Write-Host "Setelah install Redis (Memurai), jalankan ulang script ini tanpa -UseXamppFallback."
    Write-Host "Download Memurai: https://www.memurai.com/get-memurai"
    Write-Host ""
    $envContent = Set-EnvLine $envContent 'REDIS_CLIENT' 'predis'
    $envContent = Set-EnvLine $envContent 'CACHE_STORE' 'database'
    $envContent = Set-EnvLine $envContent 'SESSION_DRIVER' 'file'
}

Set-Content -Path $envPath -Value $envContent -NoNewline

# Pastikan tabel cache ada (hanya migrasi cache - tidak menyentuh users/projects/tasks)
php artisan migrate --force --no-interaction --path=database/migrations/0001_01_01_000001_create_cache_table.php 2>&1 | Out-Host
if ($LASTEXITCODE -ne 0) {
    Copy-Item (Join-Path $backupDir '.env') $envPath -Force
    Pop-Location
    Write-Error "Migrasi cache gagal - .env dikembalikan."
}

php artisan optimize:clear 2>&1 | Out-Host
if ($LASTEXITCODE -ne 0) {
    Copy-Item (Join-Path $backupDir '.env') $envPath -Force
    Pop-Location
    Write-Error "optimize:clear gagal - .env dikembalikan."
}

php scripts/performance/verify-priority3-health.php 2>&1
if ($LASTEXITCODE -ne 0) {
    Copy-Item (Join-Path $backupDir '.env') $envPath -Force
    php artisan optimize:clear 2>&1 | Out-Null
    Pop-Location
    Write-Error "Health check gagal - .env dikembalikan."
}

$userCountAfter = (php scripts/performance/check-db-users.php 2>&1 | Select-String 'users_count=(\d+)' | ForEach-Object { $_.Matches[0].Groups[1].Value })
Write-Host "Sesudah: users_count=$userCountAfter"

if ($userCountBefore -ne $userCountAfter) {
    Copy-Item (Join-Path $backupDir '.env') $envPath -Force
    php artisan optimize:clear 2>&1 | Out-Null
    Pop-Location
    Write-Error "Jumlah user berubah - .env dikembalikan. Periksa database."
}

Pop-Location

Write-Host ""
Write-Host "Priority 3 selesai." -ForegroundColor Green
Write-Host "Restore: scripts\performance\restore-priority3-redis.ps1 -BackupDir `"$backupDir`""
Write-Host ""
Write-Host "Catatan: ganti session driver = semua user perlu login ulang (data akun tidak hilang)."
Write-Host "Jangan jalankan config:cache saat development - gunakan optimize:clear jika login bermasalah."
Write-Host ""
