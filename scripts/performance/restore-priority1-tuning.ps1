param(
    [Parameter(Mandatory = $true)]
    [string]$BackupDir
)

$ErrorActionPreference = 'Stop'

$map = @{
    'my.ini' = 'C:\xampp\mysql\bin\my.ini'
    'httpd-mpm.conf' = 'C:\xampp\apache\conf\extra\httpd-mpm.conf'
    'php.ini' = 'C:\xampp\php\php.ini'
}

if (-not (Test-Path $BackupDir)) {
    Write-Error "Folder backup tidak ditemukan: $BackupDir"
}

Write-Host "Restore dari: $BackupDir" -ForegroundColor Cyan

foreach ($entry in $map.GetEnumerator()) {
    $src = Join-Path $BackupDir $entry.Key
    if (-not (Test-Path $src)) {
        Write-Warning "Lewati (tidak ada): $($entry.Key)"
        continue
    }
    Copy-Item $src $entry.Value -Force
    Write-Host "Restored: $($entry.Key)" -ForegroundColor Green
}

& 'C:\xampp\apache\bin\httpd.exe' -t
Write-Host ""
Write-Host "Restart Apache dan MySQL di XAMPP Control Panel." -ForegroundColor Yellow
