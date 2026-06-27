# Priority 1: tuning MySQL + Apache + PHP untuk load test (aman, dengan backup).
# Jalankan: klik kanan -> Run with PowerShell (Administrator disarankan)

$ErrorActionPreference = 'Stop'
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'

$files = @(
    @{ Path = 'C:\xampp\mysql\bin\my.ini'; Name = 'my.ini' },
    @{ Path = 'C:\xampp\apache\conf\extra\httpd-mpm.conf'; Name = 'httpd-mpm.conf' },
    @{ Path = 'C:\xampp\php\php.ini'; Name = 'php.ini' }
)

$backupDir = Join-Path $PSScriptRoot "backups\priority1-$stamp"
New-Item -ItemType Directory -Force -Path $backupDir | Out-Null

Write-Host ""
Write-Host "=== Priority 1 tuning (project-crocodic) ===" -ForegroundColor Cyan
Write-Host "Backup ke: $backupDir"
Write-Host ""

foreach ($file in $files) {
    if (-not (Test-Path $file.Path)) {
        Write-Error "File tidak ditemukan: $($file.Path)"
    }
    Copy-Item $file.Path (Join-Path $backupDir $file.Name) -Force
    Write-Host "Backup: $($file.Name)" -ForegroundColor Green
}

# --- MySQL ---
$myIni = Get-Content 'C:\xampp\mysql\bin\my.ini' -Raw

$myReplacements = @{
    'max_allowed_packet=1M' = 'max_allowed_packet=64M'
    'innodb_buffer_pool_size=16M' = 'innodb_buffer_pool_size=256M'
}

foreach ($pair in $myReplacements.GetEnumerator()) {
    if ($myIni -notmatch [regex]::Escape($pair.Key)) {
        Write-Warning "MySQL: pola tidak ditemukan ($($pair.Key)) - lewati."
    } else {
        $myIni = $myIni.Replace($pair.Key, $pair.Value)
    }
}

if ($myIni -notmatch '(?m)^max_connections\s*=') {
    $myIni = $myIni -replace '(?m)^innodb_lock_wait_timeout=50', "innodb_lock_wait_timeout=50`r`nmax_connections=200`r`nwait_timeout=120`r`ntable_open_cache=400`r`nthread_cache_size=16"
}

if ($myIni -notmatch 'project-crocodic load-test tuning') {
    $myIni = $myIni -replace '(?m)^(\[mysqld\])', "`$1`r`n# project-crocodic load-test tuning (priority 1)"
}

Set-Content -Path 'C:\xampp\mysql\bin\my.ini' -Value $myIni -NoNewline
Write-Host "MySQL my.ini diperbarui." -ForegroundColor Green

# --- Apache MPM (Windows) ---
$mpm = Get-Content 'C:\xampp\apache\conf\extra\httpd-mpm.conf' -Raw
if ($mpm -match 'ThreadsPerChild\s+150') {
    $mpm = $mpm -replace 'ThreadsPerChild\s+150', 'ThreadsPerChild        200'
    Set-Content -Path 'C:\xampp\apache\conf\extra\httpd-mpm.conf' -Value $mpm -NoNewline
    Write-Host "Apache httpd-mpm.conf: ThreadsPerChild 150 -> 200" -ForegroundColor Green
} else {
    Write-Warning "Apache: ThreadsPerChild 150 tidak ditemukan - tidak diubah."
}

# --- PHP ---
$phpIni = Get-Content 'C:\xampp\php\php.ini' -Raw

if ($phpIni -match ';realpath_cache_size = 4096k') {
    $phpIni = $phpIni.Replace(';realpath_cache_size = 4096k', 'realpath_cache_size = 4096K')
}
if ($phpIni -match ';realpath_cache_ttl = 120') {
    $phpIni = $phpIni.Replace(';realpath_cache_ttl = 120', 'realpath_cache_ttl = 600')
}

Set-Content -Path 'C:\xampp\php\php.ini' -Value $phpIni -NoNewline
Write-Host "PHP php.ini: realpath_cache diaktifkan." -ForegroundColor Green

Write-Host ""
Write-Host "Validasi konfigurasi Apache..." -ForegroundColor Cyan
& 'C:\xampp\apache\bin\httpd.exe' -t
if ($LASTEXITCODE -ne 0) {
    Write-Error "Konfigurasi Apache tidak valid. Restore backup dari $backupDir"
}

Write-Host ""
Write-Host "Selesai. RESTART Apache dan MySQL di XAMPP Control Panel." -ForegroundColor Yellow
Write-Host "Restore backup: scripts\performance\restore-priority1-tuning.ps1 -BackupDir `"$backupDir`""
Write-Host ""
